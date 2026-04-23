@extends('layouts.admin')

@section('title', "Today's Attendance")

@section('header-title')
    <div>
        {{-- <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Attendance Report</h1> --}}
        <p class="text-xs text-gray-400 font-medium mt-0.5">Attendance Live Report for {{ \Carbon\Carbon::parse($todayDate)->format('l, d M Y') }}</p>
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .table-container { background: #fff; border: 1.5px solid #f1f5f9; border-radius: 16px; overflow: hidden; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th { background: #f8fafc; padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
    .data-table td { padding: 16px 20px; font-size: 13px; color: #334155; border-bottom: 1px solid #f8fafc; vertical-align: middle; white-space: nowrap; }
    .data-table tr:hover td { background: #f8fafc; }
    .data-table tr:last-child td { border-bottom: none; }
    
    .status-badge { display: inline-flex; items-align: center; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
    .status-present { background: #ecfdf5; color: #10b981; }
    .status-late { background: #fffbeb; color: #f59e0b; }
    .status-half_day { background: #fff7ed; color: #f97316; }
    .status-absent { background: #fef2f2; color: #ef4444; }

    .form-label { display: block; font-size: 12px; font-weight: 700; color: #4b5563; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
    .form-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: #1f2937; outline: none; }
    .form-input:focus { border-color: var(--brand-500); }
</style>
@endpush

@section('content')

@php
    // Calculate quick stats for HR
    $totalPresent = $attendances->whereIn('status', ['present', 'late', 'half_day'])->count();
    $totalLate = $attendances->where('status', 'late')->count();
    $totalAbsent = $attendances->where('status', 'absent')->count();
@endphp

<div x-data="todayAttendance()" x-init="init()" class="w-full pb-10 space-y-6">

    {{-- ── Stats Row ── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border-1.5 border-gray-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-green-500"><i data-lucide="users" class="w-6 h-6"></i></div>
            <div>
                <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Total Present</p>
                <p class="text-2xl font-black text-gray-900">{{ $totalPresent }}</p>
            </div>
        </div>
        <div class="bg-white border-1.5 border-gray-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500"><i data-lucide="clock-3" class="w-6 h-6"></i></div>
            <div>
                <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Late Arrivals</p>
                <p class="text-2xl font-black text-gray-900">{{ $totalLate }}</p>
            </div>
        </div>
        <div class="bg-white border-1.5 border-gray-100 rounded-2xl p-5 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-500"><i data-lucide="user-minus" class="w-6 h-6"></i></div>
            <div>
                <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Absent / Missing</p>
                <p class="text-2xl font-black text-gray-900">{{ $totalAbsent }}</p>
            </div>
        </div>
    </div>

    {{-- ── Main Data Table ── --}}
    <div class="table-container">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-[15px] font-black text-gray-800">Employee Logs</h2>
            <button onclick="window.location.reload()" class="flex items-center gap-2 text-[12px] font-bold text-gray-500 hover:text-brand-600 transition-colors">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full min-w-[800px]">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>In Time</th>
                        <th>Out Time</th>
                        <th>Location / Store</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-[12px] font-bold text-gray-500">
                                    {{ substr($att->employee->user->name ?? '?', 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ $att->employee->user->name ?? 'Unknown' }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $att->employee->employee_code }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-[12px] font-semibold text-gray-600 bg-gray-50 px-2.5 py-1 rounded-md border border-gray-200">
                                {{ $att->employee->department->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            @if($att->check_in_time)
                                <p class="font-bold text-gray-700">{{ $att->check_in_time->format('h:i A') }}</p>
                                @if($att->is_overridden) <span class="text-[10px] text-red-500 font-bold">*Overridden</span> @endif
                            @else
                                <span class="text-gray-300">--:--</span>
                            @endif
                        </td>
                        <td>
                            @if($att->check_out_time)
                                <p class="font-bold text-gray-700">{{ $att->check_out_time->format('h:i A') }}</p>
                                <p class="text-[10px] text-gray-400">{{ $att->worked_hours }} hrs worked</p>
                            @else
                                <span class="text-[11px] text-blue-500 font-semibold italic">Working...</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-1.5 text-gray-500">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                <span class="text-[12px] font-medium">{{ $att->store->name ?? 'Head Office' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $att->status }}">
                                {{ str_replace('_', ' ', $att->status) }}
                            </span>
                        </td>
                        <td class="text-right">
                            <button @click="openOverrideModal({{ json_encode($att) }})" 
                                    class="w-11 h-11 md:w-8 md:h-8 inline-flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600 transition-all" title="Override Record">
                                <i data-lucide="edit-3" class="w-5 h-5 md:w-4 md:h-4"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i data-lucide="inbox" class="w-10 h-10 mb-2 opacity-50"></i>
                                <p class="text-[13px] font-bold">No attendance records found for today.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── OVERRIDE MODAL ── --}}
    <template x-teleport="body">
        <div x-show="showModal" x-cloak class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="closeModal()">
                
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div>
                        <h3 class="text-[15px] font-black text-gray-900">Override Attendance</h3>
                        <p class="text-[12px] text-gray-500 mt-0.5" x-text="recordData?.employee?.user?.name"></p>
                    </div>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Status</label>
                        <select x-model="formData.status" class="form-input">
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="half_day">Half Day</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Check In Time</label>
                            <input type="datetime-local" x-model="formData.check_in_time" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Check Out Time</label>
                            <input type="datetime-local" x-model="formData.check_out_time" class="form-input">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Reason for Override <span class="text-red-500">*</span></label>
                        <textarea x-model="formData.reason" class="form-input" rows="3" placeholder="e.g., Forgot to scan QR, System error..."></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
                    <button @click="closeModal()" class="px-4 py-2 text-[13px] font-bold text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                    <button @click="submitOverride()" :disabled="isSubmitting" class="px-5 py-2 text-[13px] font-bold text-white rounded-xl transition-colors disabled:opacity-50 flex items-center gap-2" style="background: var(--brand-600)">
                        <span x-show="!isSubmitting">Save Override</span>
                        <span x-show="isSubmitting">Saving...</span>
                    </button>
                </div>

            </div>
        </div>
    </template>

</div>

@endsection

@push('scripts')
<script>
function todayAttendance() {
    return {
        showModal: false,
        isSubmitting: false,
        recordData: null,
        formData: {
            id: null,
            status: '',
            check_in_time: '',
            check_out_time: '',
            reason: ''
        },

        init() {
            if (window.lucide) lucide.createIcons();
        },

        // Format MySQL datetime to HTML5 datetime-local format
        formatDateForInput(dateStr) {
            if (!dateStr) return '';
            // Remove the space and replace with T (e.g. 2026-04-04 14:30:00 -> 2026-04-04T14:30)
            return dateStr.replace(' ', 'T').substring(0, 16);
        },

        openOverrideModal(attendance) {
            this.recordData = attendance;
            this.formData.id = attendance.id;
            this.formData.status = attendance.status;
            this.formData.check_in_time = this.formatDateForInput(attendance.check_in_time);
            this.formData.check_out_time = this.formatDateForInput(attendance.check_out_time);
            this.formData.reason = attendance.override_reason || '';
            
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.recordData = null;
        },

        async submitOverride() {
            if (!this.formData.reason.trim()) {
                if(typeof BizAlert !== 'undefined') BizAlert.toast('A reason is required for audits.', 'error');
                else alert('A reason is required.');
                return;
            }

            this.isSubmitting = true;

            try {
                // Laravel Route for override: /admin/hrm/attendance/{attendance}/override
                const url = `{{ url('admin/hrm/attendance') }}/${this.formData.id}/override`;
                
                // Format dates back for Laravel (Y-m-d H:i:s)
                let payload = {
                    status: this.formData.status,
                    reason: this.formData.reason,
                    check_in_time: this.formData.check_in_time ? this.formData.check_in_time.replace('T', ' ') + ':00' : null,
                    check_out_time: this.formData.check_out_time ? this.formData.check_out_time.replace('T', ' ') + ':00' : null,
                };

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success) {
                    if(typeof BizAlert !== 'undefined') BizAlert.toast('Attendance overridden successfully!', 'success');
                    this.closeModal();
                    // Reload to update stats and table
                    setTimeout(() => window.location.reload(), 1500); 
                } else {
                    if(typeof BizAlert !== 'undefined') BizAlert.toast(data.message, 'error');
                    else alert(data.message);
                }
            } catch (e) {
                console.error(e);
                if(typeof BizAlert !== 'undefined') BizAlert.toast('Network error occurred.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endpush