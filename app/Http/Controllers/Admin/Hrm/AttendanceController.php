<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Hrm\ScanAttendanceRequest;
use App\Http\Requests\Admin\Hrm\StoreQrScanRequest;
use App\Models\Hrm\Attendance;
use App\Models\Hrm\Employee;
use App\Services\Hrm\AnnouncementService;
use App\Services\Hrm\AttendanceService;
use App\Services\Hrm\QrTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected QrTokenService $qrTokenService,
        protected AnnouncementService $announcementService
    ) {}

    /**
     * Generate a new QR code for attendance scanning.
     */
    public function generateQr(Request $request)
    {
        try {
            $storeId = $request->input('store_id', session('current_store_id'));

            if (!$storeId) {
                return response()->json(['success' => false, 'message' => 'Store not selected.'], 422);
            }

            $result = $this->qrTokenService->generate((int) $storeId);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Process QR scan and mark attendance.
     */
    public function scan(ScanAttendanceRequest $request)
    {
        if ($this->announcementService->hasPendingMandatory($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Please acknowledge all mandatory announcements before marking attendance.',
            ], 422);
        }

        try {
            $attendance = $this->attendanceService->scan($request->validated());
            $action = $attendance->check_out_time ? 'checked out' : 'checked in';

            return response()->json([
                'success' => true,
                'message' => "Successfully {$action}!",
                'data' => $attendance->load('employee.user'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Process static QR scan (store_id + GPS) and mark attendance.
     */
    public function scanStore(StoreQrScanRequest $request)
    {
        if ($this->announcementService->hasPendingMandatory($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Please acknowledge all mandatory announcements before marking attendance.',
            ], 422);
        }

        try {
            $result = $this->attendanceService->scanByStore(
                (int) $request->validated('store_id'),
                (float) $request->validated('latitude'),
                (float) $request->validated('longitude'),
                $request
            );

            // Handle the Early Checkout Confirmation intercept
            if (isset($result['requires_confirmation']) && $result['requires_confirmation'] === true) {
                return response()->json([
                    'success' => true,
                    'requires_confirmation' => true,
                    'message' => $result['message'],
                    'type' => $result['type']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'type' => $result['type'] ?? 'success',
                'data' => $result['attendance'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get today's attendance status for the current user.
     */
    public function today()
    {
        $employee = Employee::where('user_id', Auth::id())->first();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'No employee record found.'], 404);
        }

        $attendance = $this->attendanceService->getTodayStatus($employee->id);

        return response()->json(['success' => true, 'data' => $attendance]);
    }

    /**
     * Attendance report with filters.
     */
    public function report(Request $request)
    {
        $filters = $request->only([
            'date', 'date_from', 'date_to', 'employee_id',
            'store_id', 'status', 'department_id', 'per_page',
        ]);

        $report = $this->attendanceService->getReport($filters);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $report]);
        }

        $employees = Employee::active()->with('user')->get();

        return view('admin.hrm.attendance.report', compact('report', 'employees', 'filters'));
    }

    /**
     * Admin override of an attendance record.
     */
    public function override(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:present,absent,late,half_day,on_leave,holiday,week_off'],
            'check_in_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'check_out_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $attendance = $this->attendanceService->override($attendance->id, $validated);
            return response()->json(['success' => true, 'message' => 'Attendance overridden.', 'data' => $attendance]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
