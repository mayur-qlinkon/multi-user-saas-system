<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use App\Models\Hrm\WorkLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkLogController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkLog::with(['employee.user', 'task', 'approvedByUser']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->forDateRange($request->date_from, $request->date_to);
        }
        if ($request->filled('hrm_task_id')) {
            $query->where('hrm_task_id', $request->hrm_task_id);
        }

        $logs = $query->orderBy('log_date', 'desc')
            ->paginate(25)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $logs]);
        }

        $employees = Employee::active()->with('user')->get();

        return view('admin.hrm.work-logs.index', compact('logs', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'hrm_task_id' => ['nullable', 'exists:hrm_tasks,id'],
            'log_date' => ['required', 'date'],
            'hours_worked' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'description' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $log = WorkLog::create($validated);

        return response()->json(['success' => true, 'message' => 'Work log created.', 'data' => $log]);
    }

    public function update(Request $request, WorkLog $workLog)
    {
        $validated = $request->validate([
            'hrm_task_id' => ['nullable', 'exists:hrm_tasks,id'],
            'log_date' => ['required', 'date'],
            'hours_worked' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'description' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $workLog->update($validated);

        return response()->json(['success' => true, 'message' => 'Work log updated.', 'data' => $workLog]);
    }

    public function destroy(WorkLog $workLog)
    {
        $workLog->delete();

        return response()->json(['success' => true, 'message' => 'Work log deleted.']);
    }

    public function approve(Request $request, WorkLog $workLog)
    {
        $action = $request->input('action', 'approve');

        if ($action === 'reject') {
            $request->validate(['remarks' => ['required', 'string']]);
            $workLog->update([
                'status' => WorkLog::STATUS_REJECTED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'admin_remarks' => $request->input('remarks'),
            ]);
        } else {
            $workLog->update([
                'status' => WorkLog::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'admin_remarks' => $request->input('remarks'),
            ]);
        }

        return response()->json(['success' => true, 'message' => "Work log {$action}d.", 'data' => $workLog]);
    }
}
