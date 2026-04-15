<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Events\Hrm\LeaveRequested;
use App\Http\Controllers\Controller;
use App\Models\Hrm\Leave;
use App\Models\Hrm\LeaveBalance;
use App\Models\Hrm\LeaveType;
use App\Services\Hrm\LeaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MyLeaveController extends Controller
{
    public function __construct(protected LeaveService $leaveService) {}

    protected function myEmployee()
    {
        $emp = Auth::user()->employee;
        abort_if(! $emp, 403, 'No employee record linked to your account.');

        return $emp;
    }

    public function index(Request $request)
    {
        if (! Auth::user()->employee) {
            return view('admin.hrm.employee.no-profile');
        }
        $employee = $this->myEmployee();
        $year = now()->year;
        $leaveTypes = LeaveType::active()->ordered()->get();

        // Leave balances
        $balanceRows = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $year)->get()->keyBy('leave_type_id');

        $palette = ['#6366f1', '#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'];
        $leaveBalances = $leaveTypes->values()->map(fn ($t, $i) => [
            'id' => $t->id,
            'name' => $t->name,
            'color' => $palette[$i % count($palette)],
            'allocated' => $balanceRows->get($t->id)?->allocated ?? $t->default_days_per_year,
            'used' => $balanceRows->get($t->id)?->used ?? 0,
            'available' => $balanceRows->get($t->id)?->available ?? $t->default_days_per_year,
        ]);

        // My leave requests
        $query = Leave::where('employee_id', $employee->id)->with('leaveType');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        if ($request->filled('year')) {
            $query->whereYear('from_date', $request->year);
        }

        $leaves = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $leaves]);
        }

        return view('admin.hrm.my-leaves.index', compact(
            'employee', 'leaveBalances', 'leaveTypes', 'leaves', 'year'
        ));
    }

    public function store(Request $request)
    {
        $employee = $this->myEmployee();

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'from_date' => ['required', 'date', 'after_or_equal:today'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'total_days' => ['required', 'numeric', 'min:0.5'],
            'day_type' => ['required', Rule::in(['full_day', 'first_half', 'second_half'])],
            'reason' => ['required', 'string', 'max:1000'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $validated['employee_id'] = $employee->id;
        $validated['company_id'] = Auth::user()->company_id;

        if ($request->hasFile('document')) {
            $validated['document'] = $request->file('document')->store('hrm/leave-documents', 'public');
        }

        try {
            $leave = $this->leaveService->apply($validated);
            Log::info('[LeaveRequested] Event fired', [
                'leave_id' => $leave->id,
            ]);
            event(new LeaveRequested($leave));

            return response()->json(['success' => true, 'message' => 'Leave request submitted successfully.', 'data' => $leave]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function show(Leave $leave)
    {
        $this->authorizeLeave($leave);
        $leave->load('leaveType');

        return response()->json(['success' => true, 'data' => $leave]);
    }

    public function update(Request $request, Leave $leave)
    {
        $this->authorizeLeave($leave);

        if ($leave->status !== Leave::STATUS_PENDING) {
            return response()->json(['success' => false, 'message' => 'Only pending leave requests can be edited.'], 422);
        }

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'total_days' => ['required', 'numeric', 'min:0.5'],
            'day_type' => ['required', Rule::in(['full_day', 'first_half', 'second_half'])],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $leave->update($validated);

        return response()->json(['success' => true, 'message' => 'Leave request updated.', 'data' => $leave->fresh('leaveType')]);
    }

    public function destroy(Leave $leave)
    {
        $this->authorizeLeave($leave);

        if (! in_array($leave->status, [Leave::STATUS_PENDING, Leave::STATUS_REJECTED])) {
            return response()->json(['success' => false, 'message' => 'Only pending or rejected requests can be deleted.'], 422);
        }

        $leave->delete();

        return response()->json(['success' => true, 'message' => 'Leave request deleted.']);
    }

    protected function authorizeLeave(Leave $leave): void
    {
        $employee = $this->myEmployee();
        abort_if($leave->employee_id !== $employee->id, 403, 'Unauthorized.');
    }
}
