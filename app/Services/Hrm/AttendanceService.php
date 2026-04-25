<?php

namespace App\Services\Hrm;

use App\Models\Hrm\Attendance;
use App\Models\Hrm\AttendanceLog;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Holiday;
use App\Models\Hrm\Shift;
use App\Models\Store;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AttendanceService
{
    /**
     * Process a store QR scan using the single attendance engine.
     *
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function scan(array $data, Request $request): array
    {
        $user = $request->user();
        $companyId = $user->company_id;
        $now = now();
        $forceCheckout = $request->boolean('force_checkout');

        $lock = Cache::lock("attendance_scan_user_{$user->id}", 10);

        if (! $lock->get()) {
            throw new \Exception('Your attendance is currently processing. Please wait a moment.');
        }

        $attendance = null;
        $action = AttendanceLog::ACTION_CHECK_IN;

        $logBase = [
            'company_id' => $companyId,
            'method' => Attendance::METHOD_QR,
            'punched_at' => $now,
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'device_info' => $request->header('X-Device-Info'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        try {
            $employee = $this->resolveEmployee($user->id);
            $logBase['employee_id'] = $employee->id;

            $store = Store::query()
                ->whereKey($data['store_id'])
                ->where('company_id', $companyId)
                ->firstOrFail();

            $this->ensureEmployeeHasStoreAccess($employee, $store->id);
            $this->validateGps($store, (float) $data['latitude'], (float) $data['longitude']);

            $shift = $employee->shift;
            if (! $shift) {
                throw new InvalidArgumentException('No shift is assigned to your employee profile.');
            }

            $shiftWindow = $this->buildShiftWindowForMoment($shift, $now);

            $attendance = Attendance::query()
                ->where('company_id', $companyId)
                ->where('employee_id', $employee->id)
                ->whereDate('date', $shiftWindow['attendance_date'])
                ->first();

            if (! $attendance) {
                $holidayResult = $this->evaluateHolidayPolicy($employee, $shiftWindow['attendance_date']);

                $attendanceData = [
                    'company_id' => $companyId,
                    'employee_id' => $employee->id,
                    'store_id' => $store->id,
                    'date' => $shiftWindow['attendance_date'],
                    'check_in_time' => $now,
                    'check_in_lat' => $data['latitude'],
                    'check_in_lng' => $data['longitude'],
                    'check_in_method' => Attendance::METHOD_QR,
                ];

                if ($holidayResult !== null) {
                    $attendanceData = array_merge($attendanceData, $holidayResult);
                    $message = $holidayResult['status'] === Attendance::STATUS_PENDING
                        ? 'Holiday attendance recorded. Pending manager approval.'
                        : 'Holiday attendance recorded. Marked as working on holiday.';
                    $type = $holidayResult['status'] === Attendance::STATUS_PENDING ? 'warning' : 'success';
                } else {
                    [$status, $message, $type] = $this->determineCheckInStatus($now, $shiftWindow);
                    $attendanceData['status'] = $status;
                }

                $attendance = DB::transaction(fn () => Attendance::create($attendanceData));

                $this->logAttempt(
                    array_merge($logBase, [
                        'action' => AttendanceLog::ACTION_CHECK_IN,
                        'attendance_id' => $attendance->id,
                    ]),
                    true,
                    $message
                );

                return [
                    'attendance' => $attendance->load('employee.user'),
                    'action' => AttendanceLog::ACTION_CHECK_IN,
                    'message' => $message,
                    'type' => $type,
                ];
            }

            $action = AttendanceLog::ACTION_CHECK_OUT;

            if ($attendance->check_out_time) {
                throw new InvalidArgumentException('Attendance already completed for this shift.');
            }

            $shiftWindow = $this->buildShiftWindowForDate($shift, $attendance->date->copy());

            if (! $forceCheckout && $now->lt($shiftWindow['early_leave_before'])) {
                return [
                    'requires_confirmation' => true,
                    'action' => $action,
                    'message' => 'You are checking out before the allowed early-leave threshold. Do you want to continue?',
                    'type' => 'warning',
                ];
            }

            $workedMinutes = $attendance->check_in_time->diffInMinutes($now);
            $workedHours = round($workedMinutes / 60, 2);
            $status = $attendance->status;

            if ($workedMinutes < $shiftWindow['min_working_minutes']) {
                $status = Attendance::STATUS_HALF_DAY;
            }

            $message = $forceCheckout && $now->lt($shiftWindow['early_leave_before'])
                ? 'Check-out recorded before the early-leave threshold.'
                : 'Check-out recorded successfully.';
            $type = $forceCheckout && $now->lt($shiftWindow['early_leave_before'])
                ? 'warning'
                : 'success';

            $attendance->update([
                'check_out_time' => $now,
                'check_out_lat' => $data['latitude'],
                'check_out_lng' => $data['longitude'],
                'check_out_method' => Attendance::METHOD_QR,
                'worked_hours' => $workedHours,
                'overtime_hours' => $this->calculateOvertimeHours($workedMinutes, $shiftWindow),
                'status' => $status,
            ]);

            $this->logAttempt(
                array_merge($logBase, [
                    'action' => $action,
                    'attendance_id' => $attendance->id,
                ]),
                true,
                $message
            );

            return [
                'attendance' => $attendance->fresh()->load('employee.user'),
                'action' => $action,
                'message' => $message,
                'type' => $type,
            ];
        } catch (InvalidArgumentException|DomainException $e) {
            $this->logAttempt(
                array_merge($logBase ?? [], [
                    'action' => $action,
                    'attendance_id' => $attendance?->id,
                ]),
                false,
                $e->getMessage()
            );

            throw $e;
        } finally {
            $lock->release();
        }
    }

    public function getTodayStatus(int $employeeId): ?Attendance
    {
        $employee = Employee::query()
            ->with('shift')
            ->find($employeeId);

        if (! $employee) {
            return null;
        }

        if (! $employee->shift) {
            return Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereDate('date', today()->toDateString())
                ->first();
        }

        $shiftWindow = $this->buildShiftWindowForMoment($employee->shift, now());

        return Attendance::query()
            ->where('employee_id', $employeeId)
            ->whereDate('date', $shiftWindow['attendance_date'])
            ->first();
    }

    public function override(int $attendanceId, array $data): Attendance
    {
        return DB::transaction(function () use ($attendanceId, $data) {
            $attendance = Attendance::findOrFail($attendanceId);

            $updateData = [
                'is_overridden' => true,
                'overridden_by' => $data['overridden_by'] ?? Auth::id(),
                'override_reason' => $data['reason'] ?? null,
            ];

            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }

            if (isset($data['check_in_time'])) {
                $updateData['check_in_time'] = $data['check_in_time'];
                $updateData['check_in_method'] = Attendance::METHOD_MANUAL;
            }

            if (isset($data['check_out_time'])) {
                $updateData['check_out_time'] = $data['check_out_time'];
                $updateData['check_out_method'] = Attendance::METHOD_MANUAL;
            }

            $checkIn = $data['check_in_time'] ?? $attendance->check_in_time;
            $checkOut = $data['check_out_time'] ?? $attendance->check_out_time;

            if ($checkIn && $checkOut) {
                $checkIn = $checkIn instanceof Carbon ? $checkIn : Carbon::parse($checkIn);
                $checkOut = $checkOut instanceof Carbon ? $checkOut : Carbon::parse($checkOut);
                $updateData['worked_hours'] = round($checkIn->diffInMinutes($checkOut) / 60, 2);
            }

            $attendance->update($updateData);

            return $attendance->fresh();
        });
    }

   /**
     * Evaluate the configured holiday attendance policy for a given employee and date.
     *
     * Detection covers: single-day holidays, multi-day ranges (date→end_date), and
     * recurring holidays (match by month/day, ignoring year) including recurring
     * multi-day ranges (safely handling cross-year ranges like Dec 25 - Jan 5).
     *
     * @return array{is_holiday: bool, working_on_holiday: bool, status: string}|null
     * @throws DomainException when policy is `block`.
     */
    public function evaluateHolidayPolicy(Employee $employee, $date): ?array
    {
        // 1. Determine correct timezone (Company specific, fallback to app default)
        $timezone = get_setting('timezone', config('app.timezone'), $employee->company_id);

        // 2. Safely normalize the incoming date to the business timezone
        if ($date instanceof \Carbon\Carbon) {
            $target = $date->copy()->setTimezone($timezone);
        } else {
            $target = \Carbon\Carbon::parse((string) $date, $timezone);
        }

        // Now these strings reflect the exact local date, regardless of server UTC time
        $targetDate = $target->toDateString();
        $targetMonthDay = $target->format('m-d');

        $driver = DB::connection()->getDriverName();

        // Database dialect formatting
        $dateMd = match ($driver) {
            'sqlite' => "strftime('%m-%d', date)",
            'pgsql'  => "to_char(date, 'MM-DD')",
            default  => "DATE_FORMAT(date, '%m-%d')", // MySQL/MariaDB
        };

        $endOrDateMd = match ($driver) {
            'sqlite' => "strftime('%m-%d', COALESCE(end_date, date))",
            'pgsql'  => "to_char(COALESCE(end_date, date), 'MM-DD')",
            default  => "DATE_FORMAT(COALESCE(end_date, date), '%m-%d')", // MySQL/MariaDB
        };

        // Existing robust holiday detection logic remains untouched
        $isHoliday = Holiday::query()
            ->where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->where(function ($query) use ($targetDate, $targetMonthDay, $dateMd, $endOrDateMd) {
                
                // Scenario A: Non-recurring (exact date match)
                $query->where(function ($q) use ($targetDate) {
                    $q->where('is_recurring', false)
                        ->whereDate('date', '<=', $targetDate)
                        ->where(function ($q2) use ($targetDate) {
                            $q2->whereDate('date', $targetDate)
                               ->orWhereDate('end_date', '>=', $targetDate);
                        });
                })
                
                // Scenario B: Recurring holidays (annual match ignoring year)
                ->orWhere(function ($q) use ($targetMonthDay, $dateMd, $endOrDateMd) {
                    $q->where('is_recurring', true)
                      ->where(function ($sub) use ($targetMonthDay, $dateMd, $endOrDateMd) {
                          
                          // B1: Normal range (e.g., Mar 1 to Mar 5) -> Start <= End
                          $sub->where(function ($normal) use ($targetMonthDay, $dateMd, $endOrDateMd) {
                              $normal->whereRaw("{$dateMd} <= {$endOrDateMd}")
                                     ->whereRaw("{$dateMd} <= ?", [$targetMonthDay])
                                     ->whereRaw("{$endOrDateMd} >= ?", [$targetMonthDay]);
                          })
                          
                          // B2: Cross-year range (e.g., Dec 25 to Jan 5) -> Start > End
                          ->orWhere(function ($crossYear) use ($targetMonthDay, $dateMd, $endOrDateMd) {
                              $crossYear->whereRaw("{$dateMd} > {$endOrDateMd}")
                                        ->where(function ($orTarget) use ($targetMonthDay, $dateMd, $endOrDateMd) {
                                            $orTarget->whereRaw("{$dateMd} <= ?", [$targetMonthDay])
                                                     ->orWhereRaw("{$endOrDateMd} >= ?", [$targetMonthDay]);
                                        });
                          });

                      });
                });
            })
            ->exists();

        if (! $isHoliday) {
            return null;
        }

        $policy = (string) get_setting('attendance.holiday_policy', 'block', $employee->company_id);

        if ($policy === 'block') {
            throw new \DomainException('Today is a holiday. Attendance is not allowed.');
        }

        if ($policy === 'approval') {
            return [
                'is_holiday' => true,
                'working_on_holiday' => true,
                'status' => Attendance::STATUS_PENDING,
            ];
        }

        return [
            'is_holiday' => true,
            'working_on_holiday' => true,
            'status' => Attendance::STATUS_PRESENT,
        ];
    }

    public function getReport(array $filters): LengthAwarePaginator
    {
        $query = Attendance::with(['employee.user', 'employee.department', 'store']);

        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $query->forDateRange($filters['date_from'], $filters['date_to']);
        } elseif (! empty($filters['date'])) {
            $query->forDate($filters['date']);
        }

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['department_id'])) {
            $query->whereHas('employee', fn ($builder) => $builder->where('department_id', $filters['department_id']));
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate($filters['per_page'] ?? 25)
            ->withQueryString();
    }

    public function autoCheckoutMissing(): int
    {
        $attendances = Attendance::query()
            ->pendingCheckout()
            ->with('employee.shift')
            ->whereDate('date', '>=', today()->subDay()->toDateString())
            ->get();

        $count = 0;
        $now = now();

        foreach ($attendances as $attendance) {
            if (! $attendance->employee?->shift) {
                continue;
            }

            $shiftWindow = $this->buildShiftWindowForDate(
                $attendance->employee->shift,
                $attendance->date->copy()
            );

            if ($now->lt($shiftWindow['shift_end'])) {
                continue;
            }

            $checkoutTime = $shiftWindow['shift_end'];
            $workedMinutes = $attendance->check_in_time->diffInMinutes($checkoutTime);
            $workedHours = round($workedMinutes / 60, 2);
            $status = $attendance->status;

            if ($workedMinutes < $shiftWindow['min_working_minutes']) {
                $status = Attendance::STATUS_HALF_DAY;
            }

            $attendance->update([
                'check_out_time' => $checkoutTime,
                'check_out_method' => Attendance::METHOD_AUTO,
                'worked_hours' => $workedHours,
                'overtime_hours' => 0,
                'status' => $status,
            ]);

            $count++;
        }

        return $count;
    }

    protected function resolveEmployee(int $userId): Employee
    {
        $employee = Employee::query()
            ->with(['shift', 'user.stores'])
            ->where('user_id', $userId)
            ->active()
            ->first();

        if (! $employee) {
            throw new InvalidArgumentException('You are not registered as an active employee.');
        }

        return $employee;
    }

    protected function ensureEmployeeHasStoreAccess(Employee $employee, int $storeId): void
    {
        $hasStoreAccess = (int) $employee->store_id === $storeId;

        if (! $hasStoreAccess && $employee->user) {
            $hasStoreAccess = $employee->user->stores->contains('id', $storeId);
        }

        if (! $hasStoreAccess) {
            throw new InvalidArgumentException('You are not assigned to this store.');
        }
    }

    protected function validateGps(Store $store, float $lat, float $lng): void
    {
        if (abs($lat) < 0.001 && abs($lng) < 0.001) {
            throw new InvalidArgumentException('Invalid GPS coordinates. Please enable location services.');
        }

        if ($store->office_lat === null || $store->office_lng === null || $store->gps_radius_meters === null) {
            throw new InvalidArgumentException('This store does not have attendance GPS settings configured.');
        }

        $distance = $this->haversineDistance($lat, $lng, (float) $store->office_lat, (float) $store->office_lng);

        if ($distance > (int) $store->gps_radius_meters + 20) {
            throw new InvalidArgumentException('You are outside the allowed attendance radius for this store.');
        }
    }

    /**
     * @return array{
     *     attendance_date: string,
     *     shift_start: Carbon,
     *     shift_end: Carbon,
     *     late_mark_after: Carbon,
     *     half_day_after: ?Carbon,
     *     early_leave_before: Carbon,
     *     min_working_minutes: int,
     *     overtime_after_minutes: int
     * }
     */
    protected function buildShiftWindowForMoment(Shift $shift, Carbon $moment): array
    {
        $attendanceDate = $moment->toDateString();
        $shiftStartToday = Carbon::parse($moment->toDateString().' '.$shift->start_time);
        $shiftEndToday = Carbon::parse($moment->toDateString().' '.$shift->end_time);

        if ($shiftEndToday->lessThanOrEqualTo($shiftStartToday) && $moment->lt($shiftEndToday)) {
            $attendanceDate = $moment->copy()->subDay()->toDateString();
        }

        return $this->buildShiftWindowForDate($shift, Carbon::parse($attendanceDate));
    }

    /**
     * @return array{
     *     attendance_date: string,
     *     shift_start: Carbon,
     *     shift_end: Carbon,
     *     late_mark_after: Carbon,
     *     half_day_after: ?Carbon,
     *     early_leave_before: Carbon,
     *     min_working_minutes: int,
     *     overtime_after_minutes: int
     * }
     */
    protected function buildShiftWindowForDate(Shift $shift, Carbon $attendanceDate): array
    {
        $shiftStart = Carbon::parse($attendanceDate->toDateString().' '.$shift->start_time);
        $shiftEnd = Carbon::parse($attendanceDate->toDateString().' '.$shift->end_time);

        if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
            $shiftEnd->addDay();
        }

        $scheduledMinutes = max($shiftStart->diffInMinutes($shiftEnd), 1);
        $lateMarkAfter = $shift->late_mark_after
            ? $this->buildShiftThreshold($attendanceDate, $shift->late_mark_after, $shiftStart, $shiftEnd)
            : $shiftStart->copy();
        $halfDayAfter = $shift->half_day_after
            ? $this->buildShiftThreshold($attendanceDate, $shift->half_day_after, $shiftStart, $shiftEnd)
            : null;
        $earlyLeaveBefore = $shift->early_leave_before
            ? $this->buildShiftThreshold($attendanceDate, $shift->early_leave_before, $shiftStart, $shiftEnd)
            : $shiftEnd->copy();

        return [
            'attendance_date' => $attendanceDate->toDateString(),
            'shift_start' => $shiftStart,
            'shift_end' => $shiftEnd,
            'late_mark_after' => $lateMarkAfter,
            'half_day_after' => $halfDayAfter,
            'early_leave_before' => $earlyLeaveBefore,
            'min_working_minutes' => (int) ($shift->min_working_hours_minutes ?: $scheduledMinutes),
            'overtime_after_minutes' => (int) ($shift->overtime_after_minutes ?? $scheduledMinutes),
        ];
    }

    protected function buildShiftThreshold(Carbon $attendanceDate, string $time, Carbon $shiftStart, Carbon $shiftEnd): Carbon
    {
        $threshold = Carbon::parse($attendanceDate->toDateString().' '.$time);

        if ($shiftEnd->toDateString() !== $shiftStart->toDateString() && $threshold->lt($shiftStart)) {
            $threshold->addDay();
        }

        return $threshold;
    }

    /**
     * @return array{0:string,1:string,2:string}
     */
    protected function determineCheckInStatus(Carbon $now, array $shiftWindow): array
    {
        if ($now->gte($shiftWindow['shift_end'])) {
            throw new InvalidArgumentException('This shift has already ended. Please contact your manager for an override.');
        }

        if ($shiftWindow['half_day_after'] && $now->gte($shiftWindow['half_day_after'])) {
            return [Attendance::STATUS_HALF_DAY, 'Check-in recorded. You have been marked half day.', 'warning'];
        }

        if ($now->gte($shiftWindow['late_mark_after'])) {
            return [Attendance::STATUS_LATE, 'Check-in recorded. You have been marked late.', 'warning'];
        }

        return [Attendance::STATUS_PRESENT, 'Check-in recorded successfully.', 'success'];
    }

    protected function calculateOvertimeHours(int $workedMinutes, array $shiftWindow): float
    {
        $overtimeMinutes = max($workedMinutes - $shiftWindow['overtime_after_minutes'], 0);

        return round($overtimeMinutes / 60, 2);
    }

    protected function logAttempt(array $data, bool $isValid, string $remarkOrReason): void
    {
        if (empty($data['employee_id'])) {
            return;
        }

        try {
            AttendanceLog::create(array_merge(array_filter($data, fn ($value) => $value !== null), [
                'is_valid' => $isValid,
                'remarks' => $isValid ? $remarkOrReason : null,
                'rejection_reason' => ! $isValid ? $remarkOrReason : null,
            ]));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
