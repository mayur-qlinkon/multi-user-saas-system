<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\HrmTask;
use App\Models\Hrm\WorkLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyWorkLogController extends Controller
{
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

        $query = WorkLog::where('employee_id', $employee->id)->with('task');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->forDateRange($request->date_from, $request->date_to);
        }

        $logs = $query->orderByDesc('log_date')->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // My assigned tasks for the task dropdown
        $tasks = HrmTask::whereHas('assignments', fn ($q) => $q->where('employee_id', $employee->id))
            ->whereIn('status', ['pending', 'in_progress', 'in_review'])
            ->orderBy('title')
            ->get();

        // Summary stats
        $totalHours = WorkLog::where('employee_id', $employee->id)->where('status', 'approved')->sum('hours_worked');
        $pendingCount = WorkLog::where('employee_id', $employee->id)->where('status', 'submitted')->count();
        $rejectedCount = WorkLog::where('employee_id', $employee->id)->where('status', 'rejected')->count();

        return view('admin.hrm.my-work-logs.index', compact(
            'employee', 'logs', 'tasks', 'totalHours', 'pendingCount', 'rejectedCount'
        ));
    }

    public function store(Request $request)
    {
        $employee = $this->myEmployee();

        $validated = $request->validate([
            'hrm_task_id' => ['nullable', 'exists:hrm_tasks,id'],
            'log_date' => ['required', 'date', 'before_or_equal:today'],
            'hours_worked' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:draft,submitted'],
        ]);

        $validated['employee_id'] = $employee->id;
        $validated['company_id'] = Auth::user()->company_id;

        $log = WorkLog::create($validated);

        return response()->json([
            'success' => true,
            'message' => $validated['status'] === 'submitted'
                ? 'Work log submitted for approval.'
                : 'Work log saved as draft.',
            'data' => $log,
        ]);
    }

    public function update(Request $request, WorkLog $workLog)
    {
        $this->authorizeLog($workLog);

        if ($workLog->status !== WorkLog::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft logs can be edited.',
            ], 422);
        }

        $validated = $request->validate([
            'hrm_task_id' => ['nullable', 'exists:hrm_tasks,id'],
            'log_date' => ['required', 'date', 'before_or_equal:today'],
            'hours_worked' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:draft,submitted'],
        ]);

        $workLog->update($validated);

        return response()->json([
            'success' => true,
            'message' => $validated['status'] === 'submitted'
                ? 'Work log submitted for approval.'
                : 'Work log updated as draft.',
            'data' => $workLog->fresh('task'),
        ]);
    }

    public function destroy(WorkLog $workLog)
    {
        $this->authorizeLog($workLog);

        if ($workLog->status !== WorkLog::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft logs can be deleted.',
            ], 422);
        }

        $workLog->delete();

        return response()->json(['success' => true, 'message' => 'Work log deleted.']);
    }

    protected function authorizeLog(WorkLog $workLog): void
    {
        $employee = $this->myEmployee();
        abort_if($workLog->employee_id !== $employee->id, 403, 'Unauthorized.');
    }
}
