@extends('layouts.admin')

@section('title', 'Attendance Settings')

@section('header-title')
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Attendance Settings</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">Configure check-in windows, thresholds, and QR/GPS options</p>
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .field-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
    .field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 10px 30px; font-size: 13.5px; outline: none; transition: border-color 150ms ease, box-shadow 150ms ease; font-family: inherit; background: #fff; }
    .field-input:focus { border-color: var(--brand-600); box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-600) 10%, transparent); }
    .field-error { font-size: 11px; color: #dc2626; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
    .field-hint { font-size: 11px; color: #9ca3af; margin-top: 4px; }
    .form-section { background: #fff; border: 1.5px solid #f1f5f9; border-radius: 14px; padding: 24px; margin-bottom: 16px; }
    .section-head { font-size: 14px; font-weight: 800; color: #1f2937; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
    .section-desc { font-size: 12px; color: #9ca3af; margin-bottom: 20px; }
</style>
@endpush

@section('content')

<div class="pb-10" x-data="attendanceSettings()">

    <form @submit.prevent="saveSettings()">

        {{-- ════════ CHECK-IN WINDOW ════════ --}}
        <div class="form-section">
            <h3 class="section-head">
                <i data-lucide="log-in" class="w-4 h-4 text-gray-500"></i>
                Check-in Window
            </h3>
            <p class="section-desc">Define the allowed time window for employees to check in</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="field-label">Check-in Start Time</label>
                    <input type="time" x-model="form.attendance_checkin_start" class="field-input">
                    <p class="field-hint">Earliest allowed check-in time</p>
                    <p class="field-error" x-show="errors.attendance_checkin_start" x-text="errors.attendance_checkin_start"></p>
                </div>
                <div>
                    <label class="field-label">Check-in End Time</label>
                    <input type="time" x-model="form.attendance_checkin_end" class="field-input">
                    <p class="field-hint">Latest allowed check-in time</p>
                    <p class="field-error" x-show="errors.attendance_checkin_end" x-text="errors.attendance_checkin_end"></p>
                </div>
            </div>
        </div>

        {{-- ════════ CHECKOUT ════════ --}}
        <div class="form-section">
            <h3 class="section-head">
                <i data-lucide="log-out" class="w-4 h-4 text-gray-500"></i>
                Checkout
            </h3>
            <p class="section-desc">Standard checkout time for the organization</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="field-label">Checkout Time</label>
                    <input type="time" x-model="form.attendance_checkout_time" class="field-input">
                    <p class="field-hint">Standard end-of-day checkout time</p>
                    <p class="field-error" x-show="errors.attendance_checkout_time" x-text="errors.attendance_checkout_time"></p>
                </div>
            </div>
        </div>

        {{-- ════════ THRESHOLDS ════════ --}}
        <div class="form-section">
            <h3 class="section-head">
                <i data-lucide="gauge" class="w-4 h-4 text-gray-500"></i>
                Thresholds
            </h3>
            <p class="section-desc">Configure late arrival and working hours thresholds</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="field-label">Late Threshold (minutes)</label>
                    <input type="number" x-model="form.attendance_late_threshold_minutes" class="field-input" min="0" step="1">
                    <p class="field-hint">Minutes after check-in start to mark as late</p>
                    <p class="field-error" x-show="errors.attendance_late_threshold_minutes" x-text="errors.attendance_late_threshold_minutes"></p>
                </div>
                <div>
                    <label class="field-label">Minimum Working Hours</label>
                    <input type="number" x-model="form.attendance_min_working_hours" class="field-input" min="0" step="0.5">
                    <p class="field-hint">Required hours for full-day attendance</p>
                    <p class="field-error" x-show="errors.attendance_min_working_hours" x-text="errors.attendance_min_working_hours"></p>
                </div>
                <div>
                    <label class="field-label">Half Day Hours</label>
                    <input type="number" x-model="form.attendance_half_day_hours" class="field-input" min="0" step="0.5">
                    <p class="field-hint">Minimum hours for half-day attendance</p>
                    <p class="field-error" x-show="errors.attendance_half_day_hours" x-text="errors.attendance_half_day_hours"></p>
                </div>
            </div>
        </div>

        {{-- ════════ QR & GPS ════════ --}}
        <div class="form-section">
            <h3 class="section-head">
                <i data-lucide="map-pin" class="w-4 h-4 text-gray-500"></i>
                QR & GPS Settings
            </h3>
            <p class="section-desc">Configure QR token and GPS geofencing parameters</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
                <div>
                    <label class="field-label">QR Expiry (seconds)</label>
                    <input type="number" x-model="form.attendance_qr_expiry_seconds" class="field-input" min="10" step="1">
                    <p class="field-hint">How long a QR token stays valid</p>
                    <p class="field-error" x-show="errors.attendance_qr_expiry_seconds" x-text="errors.attendance_qr_expiry_seconds"></p>
                </div>
                <div>
                    <label class="field-label">Scan Cooldown (seconds)</label>
                    <input type="number" x-model="form.attendance_scan_cooldown_seconds" class="field-input" min="0" step="1">
                    <p class="field-hint">Minimum gap between scans</p>
                    <p class="field-error" x-show="errors.attendance_scan_cooldown_seconds" x-text="errors.attendance_scan_cooldown_seconds"></p>
                </div>
                <div>
                    <label class="field-label">GPS Radius (meters)</label>
                    <input type="number" x-model="form.attendance_gps_radius_meters" class="field-input" min="0" step="1">
                    <p class="field-hint">Allowed distance from office location</p>
                    <p class="field-error" x-show="errors.attendance_gps_radius_meters" x-text="errors.attendance_gps_radius_meters"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="field-label">Office Latitude</label>
                    <input type="text" x-model="form.attendance_office_lat" class="field-input" placeholder="e.g. 28.6139">
                    <p class="field-hint">Latitude of your office location</p>
                    <p class="field-error" x-show="errors.attendance_office_lat" x-text="errors.attendance_office_lat"></p>
                </div>
                <div>
                    <label class="field-label">Office Longitude</label>
                    <input type="text" x-model="form.attendance_office_lng" class="field-input" placeholder="e.g. 77.2090">
                    <p class="field-hint">Longitude of your office location</p>
                    <p class="field-error" x-show="errors.attendance_office_lng" x-text="errors.attendance_office_lng"></p>
                </div>
            </div>
        </div>

        {{-- ════════ SAVE BUTTON ════════ --}}
        <div class="flex justify-end">
            <button type="submit" :disabled="saving"
                class="inline-flex items-center gap-2 text-[13px] font-bold px-6 py-2.5 rounded-lg text-white hover:opacity-90 transition-opacity disabled:opacity-50"
                style="background: var(--brand-600)">
                <template x-if="!saving">
                    <span class="flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Save Settings
                    </span>
                </template>
                <template x-if="saving">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Saving...
                    </span>
                </template>
            </button>
        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
window.attendanceSettings = function() {
    return {
        saving: false,
        errors: {},
        form: {
            attendance_checkin_start: @json($settings['attendance_checkin_start'] ?? '08:00'),
            attendance_checkin_end: @json($settings['attendance_checkin_end'] ?? '10:30'),
            attendance_checkout_time: @json($settings['attendance_checkout_time'] ?? '17:00'),
            attendance_late_threshold_minutes: @json($settings['attendance_late_threshold_minutes'] ?? '15'),
            attendance_min_working_hours: @json($settings['attendance_min_working_hours'] ?? '8'),
            attendance_half_day_hours: @json($settings['attendance_half_day_hours'] ?? '4'),
            attendance_qr_expiry_seconds: @json($settings['attendance_qr_expiry_seconds'] ?? '30'),
            attendance_scan_cooldown_seconds: @json($settings['attendance_scan_cooldown_seconds'] ?? '60'),
            attendance_gps_radius_meters: @json($settings['attendance_gps_radius_meters'] ?? '100'),
            attendance_office_lat: @json($settings['attendance_office_lat'] ?? ''),
            attendance_office_lng: @json($settings['attendance_office_lng'] ?? ''),
        },

        init() {
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async saveSettings() {
            this.saving = true;
            this.errors = {};

            try {
                const res = await fetch(`{{ route('admin.hrm.attendance-settings.update') }}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await res.json();

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        this.errors = {};
                        for (const [key, messages] of Object.entries(data.errors)) {
                            this.errors[key] = messages[0];
                        }
                        BizAlert.toast('Please fix the highlighted errors', 'error');
                    } else {
                        BizAlert.toast(data.message || 'Failed to save settings', 'error');
                    }
                    return;
                }

                BizAlert.toast(data.message || 'Attendance settings updated successfully', 'success');
            } catch (e) {
                BizAlert.toast('Network error. Please try again.', 'error');
            } finally {
                this.saving = false;
            }
        },
    };
};
</script>
@endpush
