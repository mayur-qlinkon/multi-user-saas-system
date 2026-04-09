<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Hrm\ScanAttendanceRequest;
use App\Models\Hrm\Attendance;
use App\Models\Hrm\Department;
use App\Models\Hrm\Employee;
use App\Models\Store;
use App\Services\Hrm\AnnouncementService;
use App\Services\Hrm\AttendanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected AnnouncementService $announcementService
    ) {}

    public function scan(ScanAttendanceRequest $request): JsonResponse
    {
        if ($this->announcementService->hasPendingMandatory($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Please acknowledge all mandatory announcements before marking attendance.',
            ], 422);
        }

        try {
            $result = $this->attendanceService->scan($request->validated(), $request);

            return response()->json([
                'success' => true,
                'requires_confirmation' => $result['requires_confirmation'] ?? false,
                'action' => $result['action'] ?? null,
                'message' => $result['message'],
                'type' => $result['type'] ?? 'success',
                'data' => $result['attendance'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'type' => 'error'], 422);
        }
    }

    public function today(Request $request): JsonResponse|View
    {
        $companyId = Auth::user()->company_id;
        $todayDate = now()->toDateString();

        $attendances = Attendance::with(['employee.user', 'employee.department', 'store'])
            ->where('company_id', $companyId)
            ->whereDate('date', $todayDate)
            ->orderBy('check_in_time', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $attendances]);
        }

        return view('admin.hrm.attendance.today', compact('attendances', 'todayDate'));
    }

    public function report(Request $request): JsonResponse|View
    {
        $filters = $request->only([
            'date', 'date_from', 'date_to', 'employee_id',
            'store_id', 'status', 'department_id', 'per_page',
        ]);

        $report = $this->attendanceService->getReport($filters);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $report]);
        }

        $companyId = $request->user()->company_id;
        $employees = Employee::active()
            ->where('company_id', $companyId)
            ->with('user')
            ->get();
        $departments = Department::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();
        $stores = Store::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return view('admin.hrm.attendance.report', compact('report', 'employees', 'departments', 'stores', 'filters'));
    }

    public function override(Request $request, Attendance $attendance): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:present,absent,late,half_day,on_leave,holiday,week_off'],
            'check_in_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'check_out_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $attendance = $this->attendanceService->override($attendance->id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Attendance overridden.',
                'data' => $attendance,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
