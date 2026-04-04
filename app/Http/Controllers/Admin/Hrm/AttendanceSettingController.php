<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Hrm\UpdateAttendanceSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceSettingController extends Controller
{
    protected array $settingTypes = [
        'attendance_checkin_start' => 'text',
        'attendance_checkin_end' => 'text',
        'attendance_checkout_time' => 'text',
        'attendance_late_threshold_minutes' => 'number',
        'attendance_min_working_hours' => 'number',
        'attendance_gps_radius_meters' => 'number',
        'attendance_scan_cooldown_seconds' => 'number',
        'attendance_half_day_hours' => 'number',
        'attendance_qr_expiry_seconds' => 'number',
        'attendance_office_lat' => 'text',
        'attendance_office_lng' => 'text',
    ];

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $settings = Setting::group('attendance', $companyId);

        // Merge with defaults for keys not yet set
        $defaults = [
            'attendance_checkin_start' => '08:00',
            'attendance_checkin_end' => '10:30',
            'attendance_checkout_time' => '17:00',
            'attendance_late_threshold_minutes' => '15',
            'attendance_min_working_hours' => '8',
            'attendance_gps_radius_meters' => '100',
            'attendance_scan_cooldown_seconds' => '60',
            'attendance_half_day_hours' => '4',
            'attendance_qr_expiry_seconds' => '30',
            'attendance_office_lat' => '',
            'attendance_office_lng' => '',
        ];

        $merged = collect($defaults)->map(fn($default, $key) => $settings->get($key, $default));

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $merged]);
        }

        return view('admin.hrm.attendance.settings', ['settings' => $merged]);
    }

    public function update(UpdateAttendanceSettingsRequest $request)
    {
        $companyId = Auth::user()->company_id;

        foreach ($request->validated() as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value, $companyId, 'attendance', $this->settingTypes[$key] ?? 'text');
            }
        }

        return response()->json(['success' => true, 'message' => 'Attendance settings updated.']);
    }
}
