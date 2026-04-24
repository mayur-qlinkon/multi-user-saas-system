<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExpenseRequest;
use App\Http\Requests\Admin\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ExpenseController extends Controller
{
    public function __construct(protected ExpenseService $expenseService) {}

    // ════════════════════════════════════════════════════
    //  INDEX (Data Table)
    // ════════════════════════════════════════════════════
    public function index(Request $request): View
    {
        $query = Expense::with(['category', 'user', 'approver', 'media'])
            ->latest('expense_date')
            ->latest('id');

        // Basic Filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('merchant_name', 'like', "%{$search}%")
                    ->orWhere('expense_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $expenses = $query->paginate(20)->withQueryString();

        // Fetch root categories with their children for a clean filter dropdown
        $categories = ExpenseCategory::active()->root()->with('children')->ordered()->get();

        return view('admin.expenses.index', compact('expenses', 'categories'));
    }

    // ════════════════════════════════════════════════════
    //  CREATE
    // ════════════════════════════════════════════════════
    public function create(): View
    {
        // Using the scope from your Model to build a grouped dropdown (Parent -> Child)
        $categories = ExpenseCategory::active()->root()->with('children')->ordered()->get();

        return view('admin.expenses.create', compact('categories'));
    }

    // ════════════════════════════════════════════════════
    //  STORE
    // ════════════════════════════════════════════════════
    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        try {
            $expense = $this->expenseService->store(
                data: $request->validated(),
                receipt: $request->file('receipt') // Spatie will handle this natively
            );

            return redirect()->route('admin.expenses.show', $expense->id)
                ->with('success', "Expense {$expense->expense_number} logged successfully.");

        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Failed to log expense. Please try again.');
        }
    }

    // ════════════════════════════════════════════════════
    //  SHOW (Detail View & Audit Trail)
    // ════════════════════════════════════════════════════
    public function show(Expense $expense): View
    {
        // Eager load the polymorphic Spatie Activity Log and the unified Payments
        $expense->load([
            'category',
            'user',
            'approver',
            'media',
            'payments',
            'activities.causer', // Who did what in the audit trail
        ]);

        return view('admin.expenses.show', compact('expense'));
    }

    // ════════════════════════════════════════════════════
    //  EDIT
    // ════════════════════════════════════════════════════
    public function edit(Expense $expense): View|RedirectResponse
    {
        // 🌟 GUARDRAIL: Do not allow editing of finalized financial records
        if (in_array($expense->status, ['approved', 'reimbursed'])) {
            return back()->with('error', 'Approved or Reimbursed expenses cannot be edited. Please reverse the status first.');
        }

        $categories = ExpenseCategory::active()->root()->with('children')->ordered()->get();

        return view('admin.expenses.edit', compact('expense', 'categories'));
    }

    // ════════════════════════════════════════════════════
    //  UPDATE
    // ════════════════════════════════════════════════════
    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        // 🌟 GUARDRAIL: Double-check on POST to prevent API tampering
        if (in_array($expense->status, ['approved', 'reimbursed'])) {
            return back()->with('error', 'Financial lockdown: This expense is finalized and cannot be modified.');
        }

        try {
            $this->expenseService->update(
                expense: $expense,
                data: $request->validated(),
                receipt: $request->file('receipt')
            );

            return redirect()->route('admin.expenses.show', $expense->id)
                ->with('success', 'Expense updated successfully.');

        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Failed to update expense. Please try again.');
        }
    }

    // ════════════════════════════════════════════════════
    //  DESTROY
    // ════════════════════════════════════════════════════
    public function destroy(Expense $expense): RedirectResponse
    {
        if (in_array($expense->status, ['approved', 'reimbursed'])) {
            return back()->with('error', 'Cannot delete finalized expenses. Please cancel or reverse them instead.');
        }

        $expense->delete(); // Triggers SoftDeletes

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    // ════════════════════════════════════════════════════
    //  UPDATE STATUS (Workflow Engine via AJAX)
    // ════════════════════════════════════════════════════
    public function updateStatus(Request $request, Expense $expense): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:draft,pending_approval,approved,rejected,reimbursed',
        ]);

        try {
            $this->expenseService->updateStatus($expense, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Expense status updated to '.ucfirst(str_replace('_', ' ', $request->status)),
                'status' => $request->status,
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.',
            ], 500);
        }
    }
}
