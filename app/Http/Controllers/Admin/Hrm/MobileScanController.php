<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Attendance;
use App\Models\Hrm\Employee;
use App\Models\Store;
use App\Services\Hrm\AnnouncementService;
use App\Services\Hrm\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileScanController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected AnnouncementService $announcementService
    ) {}

    /**
     * Mobile-optimized attendance scan page.
     * Employee opens this URL after scanning the printed QR poster.
     */
    public function show(Store $store)
    {
        abort_if($store->company_id !== Auth::user()->company_id, 403);

        $employee = Employee::where('user_id', Auth::id())->active()->first();

        // Get today's attendance status for this employee
        $todayAttendance = $employee
            ? Attendance::where('employee_id', $employee->id)->where('date', today())->first()
            : null;

        $action = 'check-in';
        if ($todayAttendance?->check_in_time && ! $todayAttendance?->check_out_time) {
            $action = 'check-out';
        } elseif ($todayAttendance?->check_out_time) {
            $action = 'done';
        }

        return view('admin.hrm.attendance.mobile-scan', compact('store', 'employee', 'todayAttendance', 'action'));
    }

    /**
     * Process the attendance scan submission (GPS coordinates from browser).
     */
    public function scan(Request $request, Store $store)
    {
        abort_if($store->company_id !== Auth::user()->company_id, 403);

        $validated = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        if ($this->announcementService->hasPendingMandatory(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Please acknowledge all mandatory announcements before marking attendance.',
            ], 422);
        }

        try {
            $result = $this->attendanceService->scanByStore(
                $store->id,
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $request
            );

            $attendance = $result['attendance'];
            $time = $attendance->check_in_time->format('h:i A');

            return response()->json([
                'success' => true,
                'action' => 'checked-in',
                'time' => $time,
                'message' => $result['message'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
