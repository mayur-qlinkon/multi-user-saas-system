<?php

namespace App\Services\Hrm;

use App\Models\Hrm\Attendance;
use App\Models\Hrm\AttendanceLog;
use App\Models\Hrm\Employee;
use App\Models\Hrm\QrToken;
use App\Models\Hrm\Shift;
use App\Models\Setting;
use App\Models\Store;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class AttendanceService
{
    public function __construct(
        protected QrTokenService $qrTokenService
    ) {}

    // ══════════════════════════════════════════════════════════════════
    //  Static QR Scan (Primary Flow)
    // ══════════════════════════════════════════════════════════════════

    /**
     * Process a static QR scan: validate store access, GPS, shift rules,
     * and mark check-in OR check-out. Logs every attempt to attendance_logs.
     *
     * @return array{attendance: Attendance, message: string}
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     */
   public function scanByStore(int $storeId, float $lat, float $lng, Request $request): array
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $now = now();
        $today = $now->toDateString();
        $forceCheckout = filter_var($request->input('force_checkout', false), FILTER_VALIDATE_BOOLEAN);

        $lockKey = "attendance_scan_user_{$user->id}";
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            throw new \Exception('Your attendance is currently processing. Please wait a moment.');
        }

        try {
            $logBase = [
                'company_id'  => $companyId,
                'method'      => Attendance::METHOD_QR,
                'punched_at'  => $now,
                'latitude'    => $lat,
                'longitude'   => $lng,
                'device_info' => $request->header('X-Device-Info'),
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ];

            $employee = Employee::where('user_id', $user->id)->active()->first();
            if (! $employee) throw new InvalidArgumentException('You are not registered as an active employee.');
            $logBase['employee_id'] = $employee->id;

            if (! $user->stores()->where('stores.id', $storeId)->exists()) {
                $this->logAttempt(array_merge($logBase, ['action' => AttendanceLog::ACTION_CHECK_IN]), false, 'Unauthorized store access.');
                throw new InvalidArgumentException('Unauthorized store.');
            }

            $store = Store::where('id', $storeId)->where('company_id', $companyId)->firstOrFail();

            if ($store->office_lat && $store->office_lng) {
                $distance = $this->haversineDistance($lat, $lng, (float) $store->office_lat, (float) $store->office_lng);
                $radiusMeters = $store->gps_radius_meters ?? 100;

                if ($distance > $radiusMeters) {
                    $distanceRounded = round($distance);
                    $this->logAttempt(array_merge($logBase, ['action' => AttendanceLog::ACTION_CHECK_IN]), false, "Distance: {$distanceRounded}m (Max: {$radiusMeters}m).");
                    throw new InvalidArgumentException("You are too far from the office! Distance: {$distanceRounded}m.");
                }
            }

            $existing = Attendance::where('employee_id', $employee->id)->whereDate('date', $today)->first();
            $shift = $employee->shift;

            // -----------------------------------------------------
            // FLOW A: CHECK-IN
            // -----------------------------------------------------
            if (!$existing) {
                $logBase['action'] = AttendanceLog::ACTION_CHECK_IN;
                
                [$status, $message, $msgType] = $this->evaluateCheckInStatus($now, $shift, $today);

                $attendance = DB::transaction(function () use ($companyId, $employee, $storeId, $today, $now, $lat, $lng, $status) {
                    return Attendance::create([
                        'company_id'      => $companyId,
                        'employee_id'     => $employee->id,
                        'store_id'        => $storeId,
                        'date'            => $today,
                        'check_in_time'   => $now,
                        'check_in_lat'    => $lat,
                        'check_in_lng'    => $lng,
                        'check_in_method' => Attendance::METHOD_QR,
                        'status'          => $status,
                    ]);
                });

                $this->logAttempt(array_merge($logBase, ['attendance_id' => $attendance->id]), true, $message);

                return ['attendance' => $attendance->load('employee.user'), 'message' => $message, 'type' => $msgType];
            } 
            
            // -----------------------------------------------------
            // FLOW B: CHECK-OUT
            // -----------------------------------------------------
            $logBase['action'] = AttendanceLog::ACTION_CHECK_OUT;

            if ($existing->check_out_time) {
                throw new InvalidArgumentException('You have already checked out for today.');
            }

            $cooldown = (int) Setting::get('attendance_scan_cooldown_seconds', 60, $companyId);
            if ($existing->check_in_time->diffInSeconds($now) < $cooldown) {
                throw new InvalidArgumentException("Please wait at least {$cooldown} seconds between scans.");
            }

            // Scenario E: Early Checkout Warning
            $shiftEnd = $shift ? Carbon::parse("{$today} {$shift->end_time}") : null;
            
            if ($shiftEnd && $now->lt($shiftEnd)) {
                if (!$forceCheckout) {
                    // Send signal to frontend to show SweetAlert confirmation
                    return [
                        'requires_confirmation' => true,
                        'message' => "Your shift ends at " . $shiftEnd->format('h:i A') . ".\nLeaving early may affect your attendance status. Are you sure you want to checkout now?",
                        'type' => 'warning'
                    ];
                }
                $message = "Early checkout recorded at " . $now->format('h:i A') . ".\nWarning: You left before your shift ended.";
                $msgType = 'warning';
            } else {
                // Scenario F: Normal or Overtime
                if ($shiftEnd && $now->gt($shiftEnd)) {
                    $overtimeMins = $shiftEnd->diffInMinutes($now);
                    $hours = floor($overtimeMins / 60);
                    $mins = $overtimeMins % 60;
                    $otText = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";
                    
                    $message = "Check-Out successful! Overtime tracked: {$otText}.";
                    $msgType = 'success';
                } else {
                    $message = "Check-Out successful at " . $now->format('h:i A') . ". Great job today!";
                    $msgType = 'success';
                }
            }

            $workedHours = round($existing->check_in_time->diffInMinutes($now) / 60, 2);
            $minWorkingHours = (float) Setting::get('attendance_min_working_hours', 8, $companyId);
            $overtimeHours = max(0, round($workedHours - $minWorkingHours, 2));

            $existing->update([
                'check_out_time'   => $now,
                'check_out_lat'    => $lat,
                'check_out_lng'    => $lng,
                'check_out_method' => Attendance::METHOD_QR,
                'worked_hours'     => $workedHours,
                'overtime_hours'   => $overtimeHours,
            ]);

            $this->logAttempt(array_merge($logBase, ['attendance_id' => $existing->id]), true, $message);

            return ['attendance' => $existing->load('employee.user'), 'message' => $message, 'type' => $msgType];

        } finally {
            $lock->release();
        }
    }

    protected function evaluateCheckInStatus(Carbon $now, ?Shift $shift, string $today): array
    {
        if (! $shift) {
            return [Attendance::STATUS_PRESENT, "Check-In successful at " . $now->format('h:i A') . ".", 'success'];
        }

        $shiftStart = Carbon::parse("{$today} {$shift->start_time}");
        $shiftEnd = Carbon::parse("{$today} {$shift->end_time}");
        // Assuming grace period is 15 mins like your PHP code
        $graceTime = $shiftStart->copy()->addMinutes(15); 

        // Scenario A: Completely missed shift
        if ($now->gte($shiftEnd)) {
            return [Attendance::STATUS_ABSENT, "Shift has already ended. Your attendance is recorded but marked as Absent.", 'error'];
        }
        
        // Scenario B: Early Bird
        if ($now->lt($shiftStart)) {
            return [Attendance::STATUS_PRESENT, "Early bird! You arrived before the office start time. Have a great day!", 'success'];
        }

        // Scenario C: On Time / Grace Period
        if ($now->between($shiftStart, $graceTime)) {
            return [Attendance::STATUS_PRESENT, "Check-In successful at " . $now->format('h:i A') . ".", 'success'];
        }

        // Scenario D: Late Check-in
        $delayMins = $shiftStart->diffInMinutes($now);
        $hours = floor($delayMins / 60);
        $mins = $delayMins % 60;
        
        $timeParts = [];
        if ($hours > 0) $timeParts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
        if ($mins > 0)  $timeParts[] = $mins . ' minute' . ($mins > 1 ? 's' : '');
        $lateText = implode(' and ', $timeParts);

        return [Attendance::STATUS_LATE, "You are late by {$lateText}! Please try to arrive on time tomorrow.", 'warning'];
    }

    /**
     * Log an attendance attempt (success or failure) to attendance_logs.
     */
    protected function logAttempt(array $data, bool $isValid, string $remarkOrReason): void
    {
        try {
            AttendanceLog::create(array_merge($data, [
                'is_valid' => $isValid,
                'remarks' => $isValid ? $remarkOrReason : null,
                'rejection_reason' => ! $isValid ? $remarkOrReason : null,
            ]));
        } catch (\Throwable $e) {
            report($e);
        }
    }

        // ══════════════════════════════════════════════════════════════════
    //  Dynamic QR Token Scan (Legacy)
    // ══════════════════════════════════════════════════════════════════

    /**
     * Process a QR scan: validate QR, GPS, and mark check-in or check-out.
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function scan(array $data): Attendance
    {
        $user = Auth::user();
        
        // 🌟 Add Atomic Lock here as well to protect the legacy flow
        $lockKey = "attendance_scan_user_{$user->id}";
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            throw new \Exception('Your attendance is currently processing. Please wait a moment.');
        }

        try {
            return DB::transaction(function () use ($data, $user) {
                // 1. Validate QR token
                $qrToken = $this->qrTokenService->validate($data['qr_data']);

                // 2. Find active employee
                $employee = Employee::where('user_id', $user->id)->active()->first();

                if (! $employee) {
                    throw new InvalidArgumentException('You are not registered as an active employee.');
                }

                // 3. Validate GPS
                $this->validateGps(
                    (float) $data['latitude'],
                    (float) $data['longitude'],
                    $qrToken->store_id
                );

                // 4. Determine action
                $today = now()->toDateString();
                $existing = Attendance::where('employee_id', $employee->id)
                    ->where('date', $today)
                    ->first();

                $companyId = $user->company_id;

                if (! $existing) {
                    return $this->markCheckIn($employee, $qrToken, $data, $companyId);
                }

                if ($existing->check_in_time && ! $existing->check_out_time) {
                    return $this->markCheckOut($existing, $qrToken, $data, $companyId);
                }

                throw new InvalidArgumentException('Attendance already completed for today.');
            });
        } finally {
            $lock->release();
        }
    }

    /**
     * Mark check-in for an employee.
     */
    protected function markCheckIn(Employee $employee, QrToken $qrToken, array $data, int $companyId): Attendance
    {
        $now = now();
        $today = $now->toDateString();

        // Validate check-in window
        $checkinStart = Setting::get('attendance_checkin_start', '08:00', $companyId);
        $checkinEnd = Setting::get('attendance_checkin_end', '10:30', $companyId);

        $windowStart = Carbon::parse("{$today} {$checkinStart}");
        $windowEnd = Carbon::parse("{$today} {$checkinEnd}");

        if ($now->lt($windowStart)) {
            throw new InvalidArgumentException("Check-in has not started yet. Opens at {$checkinStart}.");
        }

        if ($now->gt($windowEnd)) {
            throw new InvalidArgumentException("Check-in window has closed. Ended at {$checkinEnd}.");
        }

        // Determine late status
        $lateThreshold = (int) Setting::get('attendance_late_threshold_minutes', 15, $companyId);
        $lateTime = $windowStart->copy()->addMinutes($lateThreshold);
        $status = $now->gt($lateTime) ? Attendance::STATUS_LATE : Attendance::STATUS_PRESENT;

        $attendance = Attendance::create([
            'company_id' => $companyId,
            'employee_id' => $employee->id,
            'store_id' => $qrToken->store_id,
            'date' => $today,
            'check_in_time' => $now,
            'check_in_lat' => $data['latitude'],
            'check_in_lng' => $data['longitude'],
            'check_in_qr_token' => $qrToken->token,
            'check_in_method' => Attendance::METHOD_QR,
            'status' => $status,
        ]);

        $qrToken->markUsed(Auth::id());

        return $attendance;
    }

    /**
     * Mark check-out for an existing attendance.
     */
    protected function markCheckOut(Attendance $attendance, QrToken $qrToken, array $data, int $companyId): Attendance
    {
        $now = now();

        // Cooldown check
        $cooldown = (int) Setting::get('attendance_scan_cooldown_seconds', 60, $companyId);
        if ($attendance->check_in_time->diffInSeconds($now) < $cooldown) {
            throw new InvalidArgumentException("Please wait at least {$cooldown} seconds between scans.");
        }

        // Minimum working hours check
        $halfDayHours = (float) Setting::get('attendance_half_day_hours', 4, $companyId);
        $minCheckoutTime = $attendance->check_in_time->copy()->addHours($halfDayHours);

        if ($now->lt($minCheckoutTime)) {
            $remaining = $now->diff($minCheckoutTime)->format('%H:%I');

            throw new InvalidArgumentException("Too early for checkout. Minimum {$halfDayHours} hours required. {$remaining} remaining.");
        }

        // Calculate worked hours
        $workedHours = round($attendance->check_in_time->diffInMinutes($now) / 60, 2);

        // Determine status
        $minWorkingHours = (float) Setting::get('attendance_min_working_hours', 8, $companyId);
        if ($workedHours < $halfDayHours) {
            $status = Attendance::STATUS_HALF_DAY;
        } else {
            $status = $attendance->status; // keep 'late' or 'present'
        }

        // Calculate overtime
        $overtimeHours = max(0, round($workedHours - $minWorkingHours, 2));

        $attendance->update([
            'check_out_time' => $now,
            'check_out_lat' => $data['latitude'],
            'check_out_lng' => $data['longitude'],
            'check_out_qr_token' => $qrToken->token,
            'check_out_method' => Attendance::METHOD_QR,
            'worked_hours' => $workedHours,
            'overtime_hours' => $overtimeHours,
            'status' => $status,
        ]);

        $qrToken->markUsed(Auth::id());

        return $attendance->fresh();
    }

    // ══════════════════════════════════════════════════════════════════
    //  GPS Validation
    // ══════════════════════════════════════════════════════════════════

    /**
     * Validate GPS coordinates against office location.
     *
     * @throws InvalidArgumentException
     */
    protected function validateGps(float $lat, float $lng, ?int $storeId): void
    {
        // Basic coordinate validation
        if ($lat == 0 && $lng == 0) {
            throw new InvalidArgumentException('Invalid GPS coordinates. Please enable location services.');
        }

        // Get office coordinates (store-level first, then company fallback)
        $officeLat = null;
        $officeLng = null;
        $radiusMeters = null;

        if ($storeId) {
            $store = Store::find($storeId);
            if ($store) {
                $officeLat = $store->office_lat;
                $officeLng = $store->office_lng;
                $radiusMeters = $store->gps_radius_meters;
            }
        }

        $companyId = Auth::user()->company_id;

        if (! $officeLat || ! $officeLng) {
            $officeLat = Setting::get('attendance_office_lat', null, $companyId);
            $officeLng = Setting::get('attendance_office_lng', null, $companyId);
        }

        if (! $officeLat || ! $officeLng) {
            // GPS not configured — allow attendance but log warning
            return;
        }

        $radiusMeters = $radiusMeters ?? (int) Setting::get('attendance_gps_radius_meters', 100, $companyId);
        $distance = $this->haversineDistance($lat, $lng, (float) $officeLat, (float) $officeLng);

        if ($distance > $radiusMeters) {
            $distanceRounded = round($distance);

            throw new InvalidArgumentException(
                "You are {$distanceRounded}m away from the office. Maximum allowed distance is {$radiusMeters}m."
            );
        }
    }

    /**
     * Calculate distance between two GPS points using Haversine formula.
     *
     * @return float Distance in meters
     */
    protected function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    // ══════════════════════════════════════════════════════════════════
    //  Queries & Reports
    // ══════════════════════════════════════════════════════════════════

    /**
     * Get today's attendance status for an employee.
     */
    public function getTodayStatus(int $employeeId): ?Attendance
    {
        return Attendance::where('employee_id', $employeeId)
            ->where('date', today())
            ->first();
    }

    /**
     * Admin override of an attendance record.
     */
    public function override(int $attendanceId, array $data): Attendance
    {
        return DB::transaction(function () use ($attendanceId, $data) {
            $attendance = Attendance::findOrFail($attendanceId);

            $updateData = [
                'is_overridden' => true,
                'overridden_by' => Auth::id(),
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

            // Recalculate worked hours if both times present
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
     * Get attendance report with filters.
     */
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
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $filters['department_id']));
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate($filters['per_page'] ?? 25)
            ->withQueryString();
    }

    /**
     * Auto-checkout employees who forgot to check out.
     */
    public function autoCheckoutMissing(): int
    {
        $attendances = Attendance::today()
            ->pendingCheckout()
            ->get();

        $count = 0;

        foreach ($attendances as $attendance) {
            $companyId = $attendance->company_id;
            $checkoutTime = Setting::get('attendance_checkout_time', '17:00', $companyId);
            $checkoutDateTime = Carbon::parse(today()->toDateString().' '.$checkoutTime);

            $workedHours = round($attendance->check_in_time->diffInMinutes($checkoutDateTime) / 60, 2);
            $halfDayHours = (float) Setting::get('attendance_half_day_hours', 4, $companyId);

            $status = $attendance->status;
            if ($workedHours < $halfDayHours) {
                $status = Attendance::STATUS_HALF_DAY;
            }

            $attendance->update([
                'check_out_time' => $checkoutDateTime,
                'check_out_method' => Attendance::METHOD_AUTO,
                'worked_hours' => $workedHours,
                'status' => $status,
            ]);

            $count++;
        }

        return $count;
    }
}
