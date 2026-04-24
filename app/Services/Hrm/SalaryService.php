<?php

namespace App\Services\Hrm;

use App\Models\Hrm\Attendance;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EmployeeSalaryStructure;
use App\Models\Hrm\SalaryComponent;
use App\Models\Hrm\SalarySlip;
use App\Models\Hrm\SalarySlipItem;
use App\Models\PaymentMethod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalaryService
{
    /**
     * Generate salary slip for an employee for a given month/year.
     */
    public function generateSlip(Employee $employee, int $month, int $year): SalarySlip
    {
        // Check if slip already exists
        $existing = SalarySlip::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existing && $existing->status !== SalarySlip::STATUS_CANCELLED) {
            throw new InvalidArgumentException("Salary slip already exists for {$employee->employee_code} - {$month}/{$year}.");
        }

        return DB::transaction(function () use ($employee, $month, $year) {
            // Calculate attendance summary
            $attendanceSummary = $this->getAttendanceSummary($employee->id, $month, $year);

            // Get active salary structures
            $effectiveDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $structures = EmployeeSalaryStructure::where('employee_id', $employee->id)
                ->active()
                ->effectiveFor($effectiveDate)
                ->with('salaryComponent')
                ->get();

            if ($structures->isEmpty()) {
                throw new InvalidArgumentException("No salary structure defined for employee {$employee->employee_code}.");
            }

            // Calculate component amounts
            $componentAmounts = $this->calculateComponents($structures, $employee->basic_salary, $attendanceSummary);

            $grossEarnings = collect($componentAmounts)->where('type', SalaryComponent::TYPE_EARNING)->sum('amount');
            $totalDeductions = collect($componentAmounts)->where('type', SalaryComponent::TYPE_DEDUCTION)->sum('amount');
            $netSalary = round($grossEarnings - $totalDeductions);
            $roundOff = $netSalary - ($grossEarnings - $totalDeductions);

            // Create salary slip
            $slip = SalarySlip::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'month' => $month,
                'year' => $year,
                'working_days' => $attendanceSummary['working_days'],
                'present_days' => $attendanceSummary['present_days'],
                'absent_days' => $attendanceSummary['absent_days'],
                'leave_days' => $attendanceSummary['leave_days'],
                'overtime_hours' => $attendanceSummary['overtime_hours'],
                'gross_earnings' => $grossEarnings,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'round_off' => $roundOff,
                'status' => SalarySlip::STATUS_GENERATED,
                'generated_by' => Auth::id(),
            ]);

            // Create line items
            foreach ($componentAmounts as $index => $component) {
                SalarySlipItem::create([
                    'salary_slip_id' => $slip->id,
                    'salary_component_id' => $component['component_id'],
                    'component_name' => $component['name'],
                    'component_code' => $component['code'],
                    'type' => $component['type'],
                    'amount' => $component['amount'],
                    'calculation_detail' => $component['detail'],
                    'sort_order' => $index,
                ]);
            }

            return $slip->load('items');
        });
    }

    /**
     * Generate slips for all active employees in a company.
     */
    public function generateBulk(int $month, int $year): array
    {
        $employees = Employee::active()->get();
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($employees as $employee) {
            try {
                $this->generateSlip($employee, $month, $year);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "{$employee->employee_code}: {$e->getMessage()}";
            }
        }

        return $results;
    }

    /**
     * Approve a salary slip.
     */
    public function approve(SalarySlip $slip): SalarySlip
    {
        if (! in_array($slip->status, [SalarySlip::STATUS_GENERATED, SalarySlip::STATUS_DRAFT])) {
            throw new InvalidArgumentException('Only generated/draft slips can be approved.');
        }

        $slip->update([
            'status' => SalarySlip::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return $slip->fresh();
    }

    /**
     * Mark salary slip as paid.
     */
    public function markPaid(SalarySlip $slip, array $paymentData): SalarySlip
    {
        if ($slip->status !== SalarySlip::STATUS_APPROVED) {
            throw new InvalidArgumentException('Only approved slips can be marked as paid.');
        }

        $updates = [
            'status' => SalarySlip::STATUS_PAID,
            'payment_reference' => $paymentData['payment_reference'] ?? null,
            'payment_date' => $paymentData['payment_date'] ?? now()->toDateString(),
        ];

        // New path: resolve via PaymentMethod model and snapshot the label.
        if (! empty($paymentData['payment_method_id'])) {
            $method = PaymentMethod::find($paymentData['payment_method_id']);
            if ($method) {
                $updates['payment_method_id'] = $method->id;
                $updates['payment_method_name'] = $method->label;
            }
        }

        // Legacy fallback: if old payment_mode was provided (e.g. direct API calls),
        // keep storing it so existing slips and reports are not affected.
        if (! empty($paymentData['payment_mode'])) {
            $updates['payment_mode'] = $paymentData['payment_mode'];
        }

        $slip->update($updates);

        return $slip->fresh();
    }

    /**
     * Calculate attendance summary for a month.
     */
    protected function getAttendanceSummary(int $employeeId, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $today = now();

        // If the month hasn't ended yet, count up to today
        if ($endDate->gt($today)) {
            $endDate = $today;
        }

        $workingDays = 0;
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if (! $current->isWeekend()) {
                $workingDays++;
            }
            $current->addDay();
        }

        $attendances = Attendance::where('employee_id', $employeeId)
            ->forDateRange($startDate, $endDate)
            ->get();

        $presentDays = $attendances->whereIn('status', [
            Attendance::STATUS_PRESENT,
            Attendance::STATUS_LATE,
        ])->count();

        $halfDays = $attendances->where('status', Attendance::STATUS_HALF_DAY)->count();
        $presentDays += $halfDays * 0.5;

        $leaveDays = $attendances->where('status', Attendance::STATUS_ON_LEAVE)->count();
        $overtimeHours = $attendances->sum('overtime_hours');

        // When attendance tracking is not yet active (no records at all),
        // treat employee as fully present — no misleading absent days on payslip.
        $hasAttendanceData = $attendances->isNotEmpty();
        $absentDays = $hasAttendanceData
            ? max(0, $workingDays - $presentDays - $leaveDays)
            : 0;

        // If no attendance data, report present = working days for a clean payslip
        if (! $hasAttendanceData) {
            $presentDays = $workingDays;
        }

        return [
            'working_days' => $workingDays,
            'present_days' => (int) $presentDays,
            'absent_days' => (int) $absentDays,
            'leave_days' => $leaveDays,
            'overtime_hours' => round($overtimeHours, 2),
            'attendance_tracked' => $hasAttendanceData,
        ];
    }

    /**
     * Calculate salary component amounts based on structures.
     */
    protected function calculateComponents($structures, float $basicSalary, array $attendanceSummary): array
    {
        $components = [];
        $resolvedAmounts = [];

        // First pass: resolve all fixed amounts and basic
        foreach ($structures as $structure) {
            $comp = $structure->salaryComponent;
            if (! $comp || ! $comp->is_active) {
                continue;
            }

            if ($structure->calculation_type === SalaryComponent::CALC_FIXED) {
                $resolvedAmounts[$comp->code] = $structure->amount;
            }
        }

        // Ensure BASIC exists
        if (! isset($resolvedAmounts['BASIC'])) {
            $resolvedAmounts['BASIC'] = $basicSalary;
        }

        // Pro-rata factor based on attendance.
        // If zero attendance records exist for the month (attendance not yet set up),
        // default to full pay (factor = 1) so salary is not incorrectly zeroed out.
        $hasAttendanceData = ($attendanceSummary['present_days'] + $attendanceSummary['absent_days'] + $attendanceSummary['leave_days']) > 0;
        $proRataFactor = ($hasAttendanceData && $attendanceSummary['working_days'] > 0)
            ? $attendanceSummary['present_days'] / $attendanceSummary['working_days']
            : 1;

        // Second pass: resolve percentage-based amounts
        foreach ($structures as $structure) {
            $comp = $structure->salaryComponent;
            if (! $comp || ! $comp->is_active) {
                continue;
            }

            if ($structure->calculation_type === SalaryComponent::CALC_PERCENTAGE) {
                $baseCode = $structure->percentage_of ?? 'BASIC';
                $baseAmount = $resolvedAmounts[$baseCode] ?? $basicSalary;
                $resolvedAmounts[$comp->code] = round($baseAmount * $structure->amount / 100, 2);
            }
        }

        // Build final component list with pro-rata applied to earnings
        foreach ($structures as $structure) {
            $comp = $structure->salaryComponent;
            if (! $comp || ! $comp->is_active || ! $comp->appears_on_payslip) {
                continue;
            }

            $amount = $resolvedAmounts[$comp->code] ?? 0;

            // Apply pro-rata to earnings only
            if ($comp->type === SalaryComponent::TYPE_EARNING) {
                $amount = round($amount * $proRataFactor, 2);
            }

            $detail = null;
            if ($structure->calculation_type === SalaryComponent::CALC_PERCENTAGE) {
                $baseCode = $structure->percentage_of ?? 'BASIC';
                $detail = "{$structure->amount}% of {$baseCode}";
            }

            $components[] = [
                'component_id' => $comp->id,
                'name' => $comp->name,
                'code' => $comp->code,
                'type' => $comp->type,
                'amount' => $amount,
                'detail' => $detail,
            ];
        }

        return $components;
    }
}
