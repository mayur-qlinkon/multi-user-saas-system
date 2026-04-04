@extends('layouts.admin')

@section('title', "Today's Attendance")

@section('header-title')
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Today's Attendance</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">{{ now()->format('l, d M Y') }} &mdash; Real-time attendance overview</p>
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .stat-card { background: #fff; border: 1.5px solid #f1f5f9; border-radius: 14px; padding: 14px 16px; transition: box-shadow 150ms, border-color 150ms; }
    .stat-card:hover { border-color: #e2e8f0; box-shadow: 0 3px 12px rgba(0,0,0,0.06); }
    .status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .method-badge { display: inline-flex; align-items: center; gap: 3px; padding: 1px 6px; border-radius: 6px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: #f0f9ff; color: #0369a1; }
    .table-row { border-bottom: 1px solid #f8fafc; transition: background 100ms; }
    .table-row:hover { background: #fafbfc; }
    .table-row:last-child { border-bottom: none; }
    .emp-avatar { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; flex-shrink: 0; color: #fff; }
    .qr-section { background: #fff; border: 1.5px solid #f1f5f9; border-radius: 14px; padding: 20px; }
</style>
@endpush

@section('content')

@php
    $statusColors = \App\Models\Hrm\Attendance::STATUS_COLORS;
    $statusLabels = \App\Models\Hrm\Attendance::STATUS_LABELS;

    $totalEmployees   = $employees->count();
    $checkedInCount   = $attendances->whereNotNull('check_in_time')->count();
    $pendingCheckout  = $attendances->whereNotNull('check_in_time')->whereNull('check_out_time')->count();
    $absentCount      = $totalEmployees - $checkedInCount;
@endphp

<div class="pb-10" x-data="todayAttendance()">

    {{-- ════════ STATS BAR ════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">

        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Employees</p>
            <p class="text-2xl font-black text-gray-900">{{ number_format($totalEmployees) }}</p>
        </div>

        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Checked In</p>
            <p class="text-2xl font-black text-green-600">{{ number_format($checkedInCount) }}</p>
        </div>

        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Pending Checkout</p>
            <p class="text-2xl font-black text-amber-600">{{ number_format($pendingCheckout) }}</p>
        </div>

        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Absent / Not Yet</p>
            <p class="text-2xl font-black text-red-600">{{ number_format($absentCount) }}</p>
        </div>

    </div>

    {{-- ════════ QR CODE SECTION ════════ --}}
    <div class="qr-section mb-5">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h3 class="text-[14px] font-black text-gray-800 flex items-center gap-2">
                    <i data-lucide="qr-code" class="w-4 h-4 text-gray-500"></i>
                    QR Code Attendance
                </h3>
                <p class="text-[12px] text-gray-400 mt-0.5">Generate a temporary QR token for employees to scan</p>
            </div>

            <button @click="generateQr()" :disabled="qrLoading"
                class="inline-flex items-center gap-2 text-[12px] font-bold px-5 py-2.5 rounded-lg text-white hover:opacity-90 transition-opacity disabled:opacity-50"
                style="background: var(--brand-600)">
                <template x-if="!qrLoading">
                    <span class="flex items-center gap-2">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        Generate QR
                    </span>
                </template>
                <template x-if="qrLoading">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Generating...
                    </span>
                </template>
            </button>
        </div>

        {{-- QR Token Display --}}
        <div x-show="qrData" x-cloak class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex-1 min-w-[200px]">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Token</p>
                    <p class="text-[15px] font-black text-gray-800 font-mono tracking-wider" x-text="qrData?.token ?? ''"></p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Expires In</p>
                    <p class="text-[15px] font-black text-amber-600" x-text="qrData?.expires_in ? qrData.expires_in + 's' : ''"></p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Store</p>
                    <p class="text-[13px] font-bold text-gray-600" x-text="qrData?.store ?? 'Current'"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ TODAY'S TABLE ════════ --}}
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">

        <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between flex-wrap gap-2">
            <p class="text-[12px] font-bold text-gray-500">
                {{ $attendances->count() }} record{{ $attendances->count() !== 1 ? 's' : '' }} today
            </p>
        </div>

        @if($attendances->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                    <i data-lucide="clock" class="w-7 h-7 text-gray-300"></i>
                </div>
                <p class="font-semibold text-gray-500 mb-1">No attendance records yet</p>
                <p class="text-sm text-gray-400">Employees will appear here once they check in</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider w-[260px]">Employee</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Check In</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Check Out</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Worked Hours</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $att)
                            @php
                                $emp         = $att->employee;
                                $empName     = $emp->user->name ?? 'Unknown';
                                $initials    = strtoupper(substr($empName, 0, 1));
                                $avatarColors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4'];
                                $avatarBg    = $avatarColors[crc32($empName) % count($avatarColors)];
                                $sColor      = $statusColors[$att->status] ?? $statusColors['present'];
                            @endphp
                            <tr class="table-row">

                                {{-- Employee ── --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="emp-avatar" style="background: {{ $avatarBg }}">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[13px] font-bold text-gray-900 truncate max-w-[160px]">
                                                {{ $empName }}
                                            </p>
                                            <p class="text-[11px] text-gray-400 font-medium truncate">
                                                {{ $emp->employee_code ?? '---' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Check In ── --}}
                                <td class="px-3 py-3">
                                    @if($att->check_in_time)
                                        <div class="flex items-center gap-2">
                                            <span class="text-[12px] font-bold text-gray-700">
                                                {{ $att->check_in_time->format('h:i A') }}
                                            </span>
                                            @if($att->check_in_method)
                                                <span class="method-badge">{{ strtoupper($att->check_in_method) }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[12px] text-gray-300">---</span>
                                    @endif
                                </td>

                                {{-- Check Out ── --}}
                                <td class="px-3 py-3">
                                    @if($att->check_out_time)
                                        <div class="flex items-center gap-2">
                                            <span class="text-[12px] font-bold text-gray-700">
                                                {{ $att->check_out_time->format('h:i A') }}
                                            </span>
                                            @if($att->check_out_method)
                                                <span class="method-badge">{{ strtoupper($att->check_out_method) }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="status-badge" style="background: #fffbeb; color: #92400e;">
                                            <span class="w-1.5 h-1.5 rounded-full" style="background: #f59e0b"></span>
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                {{-- Worked Hours ── --}}
                                <td class="px-3 py-3">
                                    <span class="text-[12px] font-bold text-gray-700">
                                        {{ $att->worked_hours ? number_format($att->worked_hours, 1) . 'h' : '---' }}
                                    </span>
                                </td>

                                {{-- Status ── --}}
                                <td class="px-3 py-3">
                                    <span class="status-badge"
                                        style="background: {{ $sColor['bg'] }}; color: {{ $sColor['text'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $sColor['dot'] }}"></span>
                                        {{ $statusLabels[$att->status] ?? ucfirst($att->status) }}
                                    </span>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
window.todayAttendance = function() {
    return {
        qrLoading: false,
        qrData: null,

        init() {
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async generateQr() {
            this.qrLoading = true;
            this.qrData = null;

            try {
                const res = await fetch(`{{ route('admin.hrm.attendance.generate-qr') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const json = await res.json();

                if (!res.ok || !json.success) {
                    BizAlert.toast(json.message || 'Failed to generate QR', 'error');
                    return;
                }

                this.qrData = json.data;
                BizAlert.toast('QR token generated successfully', 'success');
            } catch (e) {
                BizAlert.toast('Network error. Please try again.', 'error');
            } finally {
                this.qrLoading = false;
            }
        },
    };
};
</script>
@endpush
