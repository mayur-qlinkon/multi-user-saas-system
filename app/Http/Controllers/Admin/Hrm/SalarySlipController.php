<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use App\Models\Hrm\SalarySlip;
use App\Services\Hrm\SalaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
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
            'payment_mode' => ['required', Rule::in(['bank_transfer', 'cash', 'cheque', 'upi'])],
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
