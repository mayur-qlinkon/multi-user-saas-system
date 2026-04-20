<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use App\Models\Hrm\SalaryComponent;
use App\Models\Hrm\SalarySlip;
use App\Models\Hrm\SalarySlipItem;
use App\Services\Hrm\SalaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SalarySlipController extends Controller
{
    public function __construct(
        protected SalaryService $salaryService
    ) {}

    public function index(Request $request)
    {
        $query = SalarySlip::with(['employee.user', 'employee.department']);

        if ($request->filled('month') && $request->filled('year')) {
            $query->forMonth((int) $request->month, (int) $request->year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $slips = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(25)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $slips]);
        }

        $employees = Employee::active()->with('user')->get();

        return view('admin.hrm.salary-slips.index', compact('slips', 'employees'));
    }

    public function show(SalarySlip $salarySlip)
    {
        $salarySlip->load(['employee.user', 'employee.department', 'employee.designation', 'items', 'generatedByUser', 'approvedByUser']);

        return view('admin.hrm.salary-slips.show', compact('salarySlip'));
    }

    /**
     * Generate salary slip for a single employee or bulk.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        try {
            if (! empty($validated['employee_id'])) {
                $employee = Employee::findOrFail($validated['employee_id']);
                $slip = $this->salaryService->generateSlip($employee, $validated['month'], $validated['year']);

                return response()->json(['success' => true, 'message' => 'Salary slip generated.', 'data' => $slip]);
            }

            // Bulk generation
            $results = $this->salaryService->generateBulk($validated['month'], $validated['year']);

            return response()->json([
                'success' => true,
                'message' => "Generated: {$results['success']}, Failed: {$results['failed']}",
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Manually edit a salary slip's line items while it is still editable
     * (draft / generated). Once approved or paid the slip is locked and
     * this endpoint rejects the request.
     *
     * Expected payload:
     *   items[]: { id?: int, component_name: string, type: earning|deduction, amount: numeric }
     *   round_off?: numeric
     */
    public function update(Request $request, SalarySlip $salarySlip)
    {
        if (! $salarySlip->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'This salary slip is locked and can no longer be edited.',
            ], 422);
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'exists:salary_slip_items,id'],
            'items.*.component_name' => ['required', 'string', 'max:100'],
            'items.*.type' => ['required', Rule::in([SalaryComponent::TYPE_EARNING, SalaryComponent::TYPE_DEDUCTION])],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
            'round_off' => ['nullable', 'numeric'],
        ]);

        try {
            $slip = DB::transaction(function () use ($salarySlip, $validated) {
                $existingIds = $salarySlip->items()->pluck('id')->all();
                $keptIds = [];
                $grossEarnings = 0.0;
                $totalDeductions = 0.0;

                foreach ($validated['items'] as $index => $row) {
                    $amount = round((float) $row['amount'], 2);
                    $type = $row['type'];
                    $name = trim($row['component_name']);

                    if ($type === SalaryComponent::TYPE_EARNING) {
                        $grossEarnings += $amount;
                    } else {
                        $totalDeductions += $amount;
                    }

                    // Update existing row (belonging to this slip) or create a new one.
                    if (! empty($row['id']) && in_array($row['id'], $existingIds, true)) {
                        $item = SalarySlipItem::where('salary_slip_id', $salarySlip->id)
                            ->where('id', $row['id'])
                            ->firstOrFail();

                        $item->update([
                            'component_name' => $name,
                            'type' => $type,
                            'amount' => $amount,
                            'sort_order' => $index,
                        ]);

                        $keptIds[] = $item->id;
                    } else {
                        $item = SalarySlipItem::create([
                            'salary_slip_id' => $salarySlip->id,
                            'salary_component_id' => null,
                            'component_name' => $name,
                            'component_code' => 'MANUAL-'.Str::upper(Str::random(6)),
                            'type' => $type,
                            'amount' => $amount,
                            'calculation_detail' => 'Manually added',
                            'sort_order' => $index,
                        ]);

                        $keptIds[] = $item->id;
                    }
                }

                // Remove rows the admin deleted in the UI.
                $toDelete = array_diff($existingIds, $keptIds);
                if (! empty($toDelete)) {
                    SalarySlipItem::whereIn('id', $toDelete)->delete();
                }

                // Round-off is user-driven (optional) — default to zero when absent.
                $roundOff = isset($validated['round_off']) ? round((float) $validated['round_off'], 2) : 0.0;
                $netSalary = round($grossEarnings - $totalDeductions + $roundOff, 2);

                $salarySlip->update([
                    'gross_earnings' => $grossEarnings,
                    'total_deductions' => $totalDeductions,
                    'round_off' => $roundOff,
                    'net_salary' => $netSalary,
                ]);

                return $salarySlip->fresh(['items']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Salary slip updated.',
                'data' => $slip,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function approve(SalarySlip $salarySlip)
    {
        try {
            $slip = $this->salaryService->approve($salarySlip);

            return response()->json(['success' => true, 'message' => 'Salary slip approved.', 'data' => $slip]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function markPaid(Request $request, SalarySlip $salarySlip)
    {
        $validated = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'payment_date' => ['nullable', 'date'],
        ]);

        try {
            $slip = $this->salaryService->markPaid($salarySlip, $validated);

            return response()->json(['success' => true, 'message' => 'Salary slip marked as paid.', 'data' => $slip]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy(SalarySlip $salarySlip)
    {
        if ($salarySlip->status === SalarySlip::STATUS_PAID) {
            return response()->json(['success' => false, 'message' => 'Paid salary slips cannot be deleted.'], 422);
        }

        // Permanently delete the related items first
        $salarySlip->items()->forceDelete();

        // Permanently delete the slip to free up the unique index
        $salarySlip->forceDelete();

        return response()->json(['success' => true, 'message' => 'Salary slip deleted successfully.']);
    }

    public function downloadPdf(SalarySlip $salarySlip)
    {
        $salarySlip->load(['employee.user', 'employee.department', 'employee.designation', 'items']);

        $pdf = Pdf::loadView('admin.hrm.salary-slips.pdf', compact('salarySlip'))
            ->setOption(['defaultFont' => 'DejaVu Sans']); // Ensures ₹ is supported globally

        return $pdf->download("salary-slip-{$salarySlip->slip_number}.pdf");
    }
}
