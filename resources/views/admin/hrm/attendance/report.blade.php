@extends('layouts.admin')

@section('title', 'Attendance Report')

@section('header-title')
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Attendance Report</h1>
        {{-- <p class="text-xs text-gray-400 font-medium mt-0.5">Filter and review attendance records across your team</p> --}}
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .filter-input { border: 1.5px solid #e5e7eb; border-radius: 9px; padding: 7px 10px; font-size: 12px; color: #374151; outline: none; background: #fff; font-family: inherit; transition: border-color 150ms; }
    .filter-input:focus { border-color: var(--brand-600); }
    .field-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
    .field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 10px 30px; font-size: 13.5px; outline: none; transition: border-color 150ms ease, box-shadow 150ms ease; font-family: inherit; background: #fff; }
    .field-input:focus { border-color: var(--brand-600); box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-600) 10%, transparent); }
    .field-error { font-size: 11px; color: #dc2626; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
    .status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .method-badge { display: inline-flex; align-items: center; gap: 3px; padding: 1px 6px; border-radius: 6px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: #f0f9ff; color: #0369a1; }
    .override-badge { display: inline-flex; align-items: center; gap: 3px; padding: 1px 6px; border-radius: 6px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: #fef3c7; color: #92400e; }
    .table-row { border-bottom: 1px solid #f8fafc; transition: background 100ms; }
    .table-row:hover { background: #fafbfc; }
    .table-row:last-child { border-bottom: none; }
    .emp-avatar { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; flex-shrink: 0; color: #fff; }
</style>
@endpush

@section('content')

@php
    $statusColors = \App\Models\Hrm\Attendance::STATUS_COLORS;
    $statusLabels = \App\Models\Hrm\Attendance::STATUS_LABELS;
@endphp

<div class="pb-10" x-data="attendanceReport()">

    {{-- ════════ FILTER BAR ════════ --}}
    <div class="bg-white border border-gray-100 rounded-2xl px-4 py-3 mb-4">
        <form method="GET" action="{{ route('admin.hrm.attendance.report') }}" id="report-filter-form">
            <div class="flex items-center gap-3 flex-wrap">

                {{-- Date From ── --}}
                <div>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                        class="filter-input" placeholder="From Date">
                </div>

                {{-- Date To ── --}}
                <div>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                        class="filter-input" placeholder="To Date">
                </div>

                {{-- Department ── --}}
                <select name="department_id" class="filter-input">
                    <option value="">All Departments</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Store ── --}}
                <select name="store_id" class="filter-input">
                    <option value="">All Stores</option>
                    @foreach($stores ?? [] as $store)
                        <option value="{{ $store->id }}" {{ ($filters['store_id'] ?? '') == $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Status ── --}}
                <select name="status" class="filter-input">
                    <option value="">All Status</option>
                    @foreach($statusLabels as $key => $label)
                        <option value="{{ $key }}" {{ ($filters['status'] ?? '') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                {{-- Employee Search ── --}}
                <div class="relative flex-1 min-w-[160px]">                    
                    <input type="text" name="q" value="{{ request('q') }}"
                        placeholder="Search employee..."
                        class="filter-input pl-4 w-full">
                </div>

                {{-- Submit ── --}}
                <button type="submit"
                    class="text-[12px] font-bold px-4 py-2 rounded-lg text-white hover:opacity-90 transition-opacity"
                    style="background: var(--brand-600)">
                    <i data-lucide="filter" class="w-3.5 h-3.5 inline-block mr-1"></i>
                    Filter
                </button>

                {{-- Clear ── --}}
                @if(request()->hasAny(['date_from', 'date_to', 'department_id', 'store_id', 'status', 'q']))
                    <a href="{{ route('admin.hrm.attendance.report') }}"
                        class="text-[11px] font-bold px-3 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors">
                        Clear
                    </a>
                @endif

            </div>
        </form>
    </div>

    {{-- ════════ TABLE ════════ --}}
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">

        <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between flex-wrap gap-2">
            <p class="text-[12px] font-bold text-gray-500">
                {{ $report->total() }} record{{ $report->total() !== 1 ? 's' : '' }}
                @if(request()->hasAny(['date_from', 'date_to', 'department_id', 'store_id', 'status', 'q']))
                    <span class="text-gray-400 font-medium">&mdash; filtered</span>
                @endif
            </p>
            <button @click="exportModal = true; $nextTick(() => { if(window.lucide) lucide.createIcons(); })"
                class="flex items-center gap-2 px-4 py-2 text-[12px] font-bold text-white rounded-lg transition-opacity hover:opacity-90 active:scale-95"
                style="background: var(--brand-600)">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                Export Report
            </button>
        </div>

        @if($report->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                    <i data-lucide="calendar-x" class="w-7 h-7 text-gray-300"></i>
                </div>
                <p class="font-semibold text-gray-500 mb-1">No attendance records found</p>
                <p class="text-sm text-gray-400">
                    @if(request()->hasAny(['date_from', 'date_to', 'department_id', 'store_id', 'status', 'q']))
                        Try adjusting your filters
                    @else
                        Attendance records will appear here
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider w-[50px]">#</th>
                            <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider w-[220px]">Employee</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Check In</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Check Out</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Worked</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Overtime</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $att)
                            @php
                                $emp         = $att->employee;
                                $empName     = $emp->user->name ?? 'Unknown';
                                $initials    = strtoupper(substr($empName, 0, 1));
                                $avatarColors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4'];
                                $avatarBg    = $avatarColors[crc32($empName) % count($avatarColors)];
                                $sColor      = $statusColors[$att->status] ?? $statusColors['present'];
                            @endphp
                            <tr class="table-row">

                                {{-- # ── --}}
                                <td class="px-5 py-3 text-[12px] font-bold text-gray-400">
                                    {{ $report->firstItem() + $loop->index }}
                                </td>

                                {{-- Employee ── --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="emp-avatar" style="background: {{ $avatarBg }}">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[13px] font-bold text-gray-900 truncate max-w-[140px]">
                                                {{ $empName }}
                                            </p>
                                            <p class="text-[11px] text-gray-400 font-medium truncate">
                                                {{ $emp->employee_code ?? '---' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Date ── --}}
                                <td class="px-3 py-3">
                                    <span class="text-[12px] font-bold text-gray-700">
                                        {{ $att->date->format('d M Y') }}
                                    </span>
                                    <p class="text-[10px] text-gray-400">{{ $att->date->format('l') }}</p>
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
                                        <span class="text-[12px] text-gray-300">---</span>
                                    @endif
                                </td>

                                {{-- Worked Hours ── --}}
                                <td class="px-3 py-3">
                                    <span class="text-[12px] font-bold text-gray-700">
                                        {{ $att->worked_hours ? number_format($att->worked_hours, 1) . 'h' : '---' }}
                                    </span>
                                </td>

                                {{-- Overtime ── --}}
                                <td class="px-3 py-3">
                                    <span class="text-[12px] font-bold {{ $att->overtime_hours > 0 ? 'text-blue-600' : 'text-gray-400' }}">
                                        {{ $att->overtime_hours ? number_format($att->overtime_hours, 1) . 'h' : '---' }}
                                    </span>
                                </td>

                                {{-- Status ── --}}
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="status-badge"
                                            style="background: {{ $sColor['bg'] }}; color: {{ $sColor['text'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $sColor['dot'] }}"></span>
                                            {{ $statusLabels[$att->status] ?? ucfirst($att->status) }}
                                        </span>
                                        @if($att->is_overridden)
                                            <span class="override-badge" title="Overridden: {{ $att->override_reason }}">
                                                <i data-lucide="shield-check" class="w-3 h-3"></i>
                                                Override
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Actions ── --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1 justify-end">
                                        <button @click="openOverride({{ $att->id }}, '{{ addslashes($empName) }}', '{{ $att->date->format('d M Y') }}', '{{ $att->status }}', '{{ $att->check_in_time ? $att->check_in_time->format('Y-m-d H:i:s') : '' }}', '{{ $att->check_out_time ? $att->check_out_time->format('Y-m-d H:i:s') : '' }}', {{ json_encode($att->override_reason ?? '') }})"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                            title="Override Attendance">
                                            <i data-lucide="pencil-line" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination ── --}}
            @if($report->hasPages())
                <div class="px-5 py-4 border-t border-gray-50 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-[12px] text-gray-400 font-medium">
                        Showing {{ $report->firstItem() }}&ndash;{{ $report->lastItem() }} of {{ $report->total() }}
                    </p>
                    <div class="flex items-center gap-1">
                        @if($report->onFirstPage())
                            <span class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-300 cursor-not-allowed">&larr; Prev</span>
                        @else
                            <a href="{{ $report->previousPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-600 hover:bg-gray-100 transition-colors">&larr; Prev</a>
                        @endif

                        @foreach($report->getUrlRange(max(1, $report->currentPage()-2), min($report->lastPage(), $report->currentPage()+2)) as $page => $url)
                            <a href="{{ $url }}"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-[12px] font-bold transition-colors
                                {{ $page == $report->currentPage() ? 'text-white' : 'text-gray-600 hover:bg-gray-100' }}"
                                style="{{ $page == $report->currentPage() ? 'background: var(--brand-600)' : '' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        @if($report->hasMorePages())
                            <a href="{{ $report->nextPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-600 hover:bg-gray-100 transition-colors">Next &rarr;</a>
                        @else
                            <span class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-300 cursor-not-allowed">Next &rarr;</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- ════════ EXPORT MODAL ════════ --}}
    <div x-show="exportModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden" @click.away="exportModal = false"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal Header ── --}}
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <div>
                    <h3 class="font-black text-gray-800 uppercase tracking-widest text-sm">Export Attendance</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Choose period and download as PDF or Excel</p>
                </div>
                <button @click="exportModal = false" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-5">

                {{-- Period Selector ── --}}
                <div>
                    <p class="text-[11px] font-black text-gray-500 uppercase tracking-wider mb-3">Select Period</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $val => $lbl)
                            <button type="button" @click="exportPeriod = '{{ $val }}'"
                                :class="exportPeriod === '{{ $val }}'
                                    ? 'border-2 font-black text-white'
                                    : 'border border-gray-200 font-bold text-gray-600 hover:border-gray-300 bg-white'"
                                :style="exportPeriod === '{{ $val }}' ? 'border-color: var(--brand-600); background: var(--brand-600)' : ''"
                                class="py-2.5 px-3 rounded-lg text-[12px] transition-all text-center">
                                {{ $lbl }}
                            </button>
                        @endforeach
                        {{-- Custom Range — full width --}}
                        <button type="button" @click="exportPeriod = 'custom'"
                            :class="exportPeriod === 'custom'
                                ? 'border-2 font-black text-white'
                                : 'border border-gray-200 font-bold text-gray-600 hover:border-gray-300 bg-white'"
                            :style="exportPeriod === 'custom' ? 'border-color: var(--brand-600); background: var(--brand-600)' : ''"
                            class="col-span-2 py-2.5 px-3 rounded-lg text-[12px] transition-all text-center flex items-center justify-center gap-1.5">
                            <i data-lucide="calendar-range" class="w-3.5 h-3.5"></i>
                            Custom Date Range
                        </button>
                    </div>

                    {{-- Custom date inputs (shown only when custom is selected) ── --}}
                    <div x-show="exportPeriod === 'custom'" x-transition class="mt-3 grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-wider mb-1">Start Date</label>
                            <input type="date" x-model="exportDateFrom"
                                class="filter-input w-full text-sm"
                                :max="exportDateTo || ''">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-wider mb-1">End Date</label>
                            <input type="date" x-model="exportDateTo"
                                class="filter-input w-full text-sm"
                                :min="exportDateFrom || ''">
                        </div>
                    </div>
                </div>

                {{-- Optional Filters ── --}}
                <div>
                    <p class="text-[11px] font-black text-gray-500 uppercase tracking-wider mb-3">Optional Filters</p>
                    <div class="space-y-2.5">

                        <select x-model="exportDeptId" class="filter-input w-full">
                            <option value="">All Departments</option>
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>

                        <select x-model="exportStoreId" class="filter-input w-full">
                            <option value="">All Stores</option>
                            @foreach($stores ?? [] as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>

                        <select x-model="exportStatus" class="filter-input w-full">
                            <option value="">All Statuses</option>
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>

                    </div>
                </div>

            </div>

            {{-- Modal Footer — Download Buttons ── --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex gap-3">
                <button type="button" @click="triggerExport('excel')"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 text-[13px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                    <i data-lucide="table-2" class="w-4 h-4"></i>
                    Download Excel
                </button>
                <button type="button" @click="triggerExport('pdf')"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 text-[13px] font-bold text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    Download PDF
                </button>
            </div>

        </div>
    </div>

    {{-- ════════ OVERRIDE MODAL ════════ --}}
    <div x-show="overrideModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl overflow-hidden" @click.away="overrideModal = false"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-black text-gray-800 uppercase tracking-widest text-sm">Override Attendance</h3>
                <button @click="overrideModal = false" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form @submit.prevent="submitOverride()">
                <div class="p-6 space-y-4">

                    {{-- Employee info ── --}}
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="text-[12px] text-gray-500">
                            <span class="font-bold text-gray-700" x-text="overrideEmployee"></span>
                            &mdash; <span x-text="overrideDate"></span>
                        </p>
                    </div>

                    {{-- Status ── --}}
                    <div>
                        <label class="field-label">Status</label>
                        <select x-model="overrideForm.status" class="field-input">
                            <option value="">Keep Current</option>
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Check In / Check Out ── --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Check In Time</label>
                            <input type="datetime-local" x-model="overrideForm.check_in_time" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Check Out Time</label>
                            <input type="datetime-local" x-model="overrideForm.check_out_time" class="field-input">
                        </div>
                    </div>

                    {{-- Reason ── --}}
                    <div>
                        <label class="field-label">Reason <span class="text-red-400">*</span></label>
                        <textarea x-model="overrideForm.reason" class="field-input" rows="3"
                            placeholder="Explain why this attendance is being overridden" required></textarea>
                        <p class="field-error" x-show="overrideErrors.reason" x-text="overrideErrors.reason"></p>
                    </div>

                </div>

                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button type="button" @click="overrideModal = false"
                        class="px-4 py-2 text-[13px] font-bold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="overrideSaving"
                        class="px-5 py-2 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity disabled:opacity-50"
                        style="background: var(--brand-600)">
                        <span x-show="!overrideSaving">Save Override</span>
                        <span x-show="overrideSaving" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
window.attendanceReport = function() {
    return {
        // ── Export ──
        exportModal:    false,
        exportPeriod:   'today',
        exportDateFrom: '',
        exportDateTo:   '',
        exportDeptId:   '',
        exportStoreId:  '',
        exportStatus:   '',

        triggerExport(type) {
            if (this.exportPeriod === 'custom' && (!this.exportDateFrom || !this.exportDateTo)) {
                BizAlert.toast('Please select both start and end dates.', 'error');
                return;
            }

            const base = type === 'pdf'
                ? '{{ route('admin.hrm.attendance.export.pdf') }}'
                : '{{ route('admin.hrm.attendance.export.excel') }}';

            const params = new URLSearchParams({ period: this.exportPeriod });

            if (this.exportPeriod === 'custom') {
                params.set('date_from', this.exportDateFrom);
                params.set('date_to',   this.exportDateTo);
            }

            if (this.exportDeptId)  params.set('department_id', this.exportDeptId);
            if (this.exportStoreId) params.set('store_id',      this.exportStoreId);
            if (this.exportStatus)  params.set('status',        this.exportStatus);

            window.location.href = base + '?' + params.toString();
            this.exportModal = false;
        },

        // ── Override ──
        overrideModal: false,
        overrideSaving: false,
        overrideId: null,
        overrideEmployee: '',
        overrideDate: '',
        overrideErrors: {},
        overrideForm: {
            status: '',
            check_in_time: '',
            check_out_time: '',
            reason: '',
        },

        init() {
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        // 🌟 ADD THIS HELPER FUNCTION
        // Converts "2026-04-04 14:30:00" to "2026-04-04T14:30"
        formatDateForInput(dateStr) {
            if (!dateStr) return '';
            return dateStr.replace(' ', 'T').substring(0, 16);
        },

        // 🌟 UPDATE THIS FUNCTION to accept the new time variables
        openOverride(id, empName, date, currentStatus, checkIn, checkOut, existingReason = '') {
            this.overrideId = id;
            this.overrideEmployee = empName;
            this.overrideDate = date;
            this.overrideErrors = {};
            
            this.overrideForm = {
                status: currentStatus || '',
                // Pass the raw dates through our new formatter!
                check_in_time: this.formatDateForInput(checkIn),
                check_out_time: this.formatDateForInput(checkOut),
                reason: existingReason || '',
            };
            
            this.overrideModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async submitOverride() {
            this.overrideSaving = true;
            this.overrideErrors = {};

            const payload = {};
            if (this.overrideForm.status) payload.status = this.overrideForm.status;
            if (this.overrideForm.check_in_time) payload.check_in_time = this.overrideForm.check_in_time.replace('T', ' ') + ':00';
            if (this.overrideForm.check_out_time) payload.check_out_time = this.overrideForm.check_out_time.replace('T', ' ') + ':00';
            payload.reason = this.overrideForm.reason;

            try {
                const res = await fetch(`{{ url('admin/hrm/attendance') }}/${this.overrideId}/override`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        this.overrideErrors = {};
                        for (const [key, messages] of Object.entries(data.errors)) {
                            this.overrideErrors[key] = messages[0];
                        }
                    } else {
                        BizAlert.toast(data.message || 'Failed to override', 'error');
                    }
                    return;
                }

                BizAlert.toast(data.message || 'Attendance overridden successfully', 'success');
                this.overrideModal = false;
                setTimeout(() => window.location.reload(), 600);
            } catch (e) {
                BizAlert.toast('Network error. Please try again.', 'error');
            } finally {
                this.overrideSaving = false;
            }
        },
    };
};
</script>
@endpush
