<?php

namespace App\Http\Requests\Admin\Hrm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Convert empty strings for lat/lng to null so nullable rule passes cleanly
     * on JSON requests (ConvertEmptyStringsToNull only runs on form-data, not JSON).
     */
    protected function prepareForValidation(): void
    {
        if ($this->attendance_office_lat === '') {
            $this->merge(['attendance_office_lat' => null]);
        }
        if ($this->attendance_office_lng === '') {
            $this->merge(['attendance_office_lng' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'attendance_checkin_start' => ['nullable', 'date_format:H:i'],
            'attendance_checkin_end' => ['nullable', 'date_format:H:i'],
            'attendance_checkout_time' => ['nullable', 'date_format:H:i'],
            'attendance_late_threshold_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'attendance_min_working_hours' => ['nullable', 'numeric', 'min:1', 'max:24'],
            'attendance_gps_radius_meters' => ['nullable', 'integer', 'min:10', 'max:5000'],
            'attendance_scan_cooldown_seconds' => ['nullable', 'integer', 'min:10', 'max:300'],
            'attendance_half_day_hours' => ['nullable', 'numeric', 'min:1', 'max:12'],
            'attendance_qr_expiry_seconds' => ['nullable', 'integer', 'min:10', 'max:300'],
            'attendance_office_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'attendance_office_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function attributes(): array
    {
        return [
            'attendance_checkin_start' => 'check-in start time',
            'attendance_checkin_end' => 'check-in end time',
            'attendance_checkout_time' => 'checkout time',
            'attendance_late_threshold_minutes' => 'late threshold',
            'attendance_min_working_hours' => 'minimum working hours',
            'attendance_gps_radius_meters' => 'GPS radius',
            'attendance_scan_cooldown_seconds' => 'scan cooldown',
            'attendance_half_day_hours' => 'half-day hours',
            'attendance_qr_expiry_seconds' => 'QR expiry',
        ];
    }
}
