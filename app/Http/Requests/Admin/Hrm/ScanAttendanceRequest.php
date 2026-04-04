<?php

namespace App\Http\Requests\Admin\Hrm;

use Illuminate\Foundation\Http\FormRequest;

class ScanAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qr_data' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->latitude == 0 && $this->longitude == 0) {
                $validator->errors()->add('latitude', 'Invalid GPS coordinates. Please enable location services.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'qr_data.required' => 'QR code data is required. Please scan the QR code.',
            'latitude.required' => 'Location access is required for attendance.',
            'longitude.required' => 'Location access is required for attendance.',
        ];
    }
}
