@extends('layouts.admin')

@section('title', 'CRM Leads')

@section('header-title')
    <div>
        <h1 class="text-[17px] font-bold text-gray-800 leading-none">CRM Leads</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">Track and manage your sales pipeline</p>
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .stat-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 14px;
        padding: 14px 16px;
        transition: box-shadow 150ms, border-color 150ms;
    }

    .stat-card:hover {
        border-color: #e2e8f0;
        box-shadow: 0 3px 12px rgba(0,0,0,0.06);
    }

    .priority-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .score-bar {
        height: 4px;
        border-radius: 2px;
        background: #f1f5f9;
        overflow: hidden;
        width: 48px;
    }

    .score-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 300ms ease;
    }

    .filter-input {
        border: 1.5px solid #e5e7eb;
        border-radius: 9px;
        padding: 7px 10px;
        font-size: 12px;
        color: #374151;
        outline: none;
        background: #fff;
        font-family: inherit;
        transition: border-color 150ms;
    }
    .search-input{
        padding-left: 30px;
    }

    .filter-input:focus { border-color: var(--brand-600); }

    .table-row {
        border-bottom: 1px solid #f8fafc;
        transition: background 100ms;
    }

    .table-row:hover { background: #fafbfc; }
    .table-row:last-child { border-bottom: none; }

    .lead-avatar {
        width: 34px; height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 800;
        flex-shrink: 0;
        color: #fff;
    }
</style>
@endpush

@section('content')

@php
    $priorityColors = [
        'hot'    => ['bg' => '#fef2f2', 'text' => '#dc2626', 'dot' => '#ef4444'],
        'high'   => ['bg' => '#fff7ed', 'text' => '#c2410c', 'dot' => '#f97316'],
        'medium' => ['bg' => '#fefce8', 'text' => '#a16207', 'dot' => '#eab308'],
        'low'    => ['bg' => '#f9fafb', 'text' => '#6b7280', 'dot' => '#9ca3af'],
    ];
@endphp

<div class="pb-10" x-data="leadsPage()" x-init="init()">

    {{-- ════════ STATS BAR ════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">

        <a href="{{ route('admin.crm.leads.index') }}"
            class="stat-card block">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total</p>
            <p class="text-2xl font-black text-gray-900">{{ number_format($stats['total']) }}</p>
        </a>

        <a href="{{ route('admin.crm.leads.index', ['converted' => 0]) }}"
            class="stat-card block">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Active</p>
            <p class="text-2xl font-black text-gray-900">
                {{ number_format($stats['total'] - $stats['converted']) }}
            </p>
        </a>

        <a href="{{ route('admin.crm.leads.index', ['priority' => 'hot']) }}"
            class="stat-card block">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Hot 🔥</p>
            <p class="text-2xl font-black text-red-600">{{ number_format($stats['hot']) }}</p>
        </a>

        <a href="{{ route('admin.crm.leads.index', ['overdue' => 1]) }}"
            class="stat-card block">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Overdue</p>
            <p class="text-2xl font-black {{ $stats['overdue'] > 0 ? 'text-orange-600' : 'text-gray-900' }}">
                {{ number_format($stats['overdue']) }}
            </p>
        </a>

        <a href="{{ route('admin.crm.leads.index', ['converted' => 1]) }}"
            class="stat-card block">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Converted</p>
            <p class="text-2xl font-black text-green-600">{{ number_format($stats['converted']) }}</p>
        </a>

        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Pipeline Value</p>
            <p class="text-lg font-black text-gray-900">
                ₹{{ number_format($stats['total_value'] / 1000, 1) }}k
            </p>
        </div>

    </div>

    {{-- ════════ TOOLBAR ════════ --}}
    <div class="bg-white border border-gray-100 rounded-2xl px-4 py-3 mb-4">
        <form method="GET" action="{{ route('admin.crm.leads.index') }}" id="filter-form">

            <div class="flex items-center gap-3 flex-wrap">

                {{-- Search ── --}}
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                        placeholder="Search name, phone, email..."
                        class="filter-input search-input pl-8 w-full">
                </div>

                {{-- Pipeline ── --}}
                <select name="pipeline_id" class="filter-input" onchange="this.form.submit()">
                    <option value="">All Pipelines</option>
                    @foreach($pipelines as $pl)
                        <option value="{{ $pl->id }}" {{ ($filters['pipeline_id'] ?? '') == $pl->id ? 'selected' : '' }}>
                            {{ $pl->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Stage ── --}}
                <select name="stage_id" class="filter-input" onchange="this.form.submit()">
                    <option value="">All Stages</option>
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" {{ ($filters['stage_id'] ?? '') == $stage->id ? 'selected' : '' }}>
                            {{ $stage->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Priority ── --}}
                <select name="priority" class="filter-input" onchange="this.form.submit()">
                    <option value="">All Priority</option>
                    <option value="hot"    {{ ($filters['priority'] ?? '') === 'hot'    ? 'selected' : '' }}>🔥 Hot</option>
                    <option value="high"   {{ ($filters['priority'] ?? '') === 'high'   ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low"    {{ ($filters['priority'] ?? '') === 'low'    ? 'selected' : '' }}>Low</option>
                </select>

                {{-- Source ── --}}
                <select name="source_id" class="filter-input" onchange="this.form.submit()">
                    <option value="">All Sources</option>
                    @foreach($sources as $src)
                        <option value="{{ $src->id }}" {{ ($filters['source_id'] ?? '') == $src->id ? 'selected' : '' }}>
                            {{ $src->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Quick filters ── --}}
                <div class="flex items-center gap-1.5 flex-wrap">
                    <a href="{{ route('admin.crm.leads.index', array_merge($filters, ['mine' => 1])) }}"
                        class="text-[11px] font-bold px-3 py-1.5 rounded-lg border transition-colors
                        {{ !empty($filters['mine']) ? 'border-brand text-white' : 'border-gray-200 text-gray-500 hover:bg-gray-50' }}"
                        style="{{ !empty($filters['mine']) ? 'background: var(--brand-600); border-color: var(--brand-600)' : '' }}">
                        My Leads
                    </a>
                    <a href="{{ route('admin.crm.leads.index', array_merge($filters, ['overdue' => 1])) }}"
                        class="text-[11px] font-bold px-3 py-1.5 rounded-lg border transition-colors
                        {{ !empty($filters['overdue']) ? 'bg-orange-500 border-orange-500 text-white' : 'border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                        Overdue
                    </a>
                    <a href="{{ route('admin.crm.leads.index', array_merge($filters, ['unassigned' => 1])) }}"
                        class="text-[11px] font-bold px-3 py-1.5 rounded-lg border transition-colors
                        {{ !empty($filters['unassigned']) ? 'bg-gray-700 border-gray-700 text-white' : 'border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                        Unassigned
                    </a>
                    @if(array_filter($filters))
                        <a href="{{ route('admin.crm.leads.index') }}"
                            class="text-[11px] font-bold px-3 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>

                {{-- Search submit ── --}}
                <button type="submit"
                    class="text-[12px] font-bold px-4 py-2 rounded-lg text-white hover:opacity-90 transition-opacity"
                    style="background: var(--brand-600)">
                    Search
                </button>

                {{-- Add lead ── --}}
                <a href="{{ route('admin.crm.leads.create') }}"
                    class="ml-auto inline-flex items-center gap-1.5 text-[12px] font-bold px-4 py-2 rounded-lg text-white hover:opacity-90 transition-opacity"
                    style="background: var(--brand-600)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Lead
                </a>

                <a href="{{ route('admin.crm.leads.import') }}"
                    class="inline-flex items-center gap-1.5 text-[12px] font-bold px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Import
                </a>

                <a href="{{ route('admin.crm.leads.export', request()->query()) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 text-[12px] font-bold px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export
                </a>

            </div>
        </form>
    </div>

    {{-- ════════ TABLE ════════ --}}
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">

        {{-- Table header ── --}}
        <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between flex-wrap gap-2">
            <p class="text-[12px] font-bold text-gray-500">
                {{ $leads->total() }} lead{{ $leads->total() !== 1 ? 's' : '' }}
                @if(array_filter($filters))
                    <span class="text-gray-400 font-medium">— filtered</span>
                @endif
            </p>
            {{-- Sort ── --}}
            <div class="flex items-center gap-2">
                <span class="text-[11px] text-gray-400 font-medium">Sort:</span>
                @foreach(['created_at' => 'Newest', 'score' => 'Score', 'lead_value' => 'Value', 'next_followup_at' => 'Follow-up'] as $field => $label)
                    <a href="{{ route('admin.crm.leads.index', array_merge($filters, ['sort' => $field, 'dir' => ($filters['sort'] ?? '') === $field && ($filters['dir'] ?? '') === 'asc' ? 'desc' : 'asc'])) }}"
                        class="text-[11px] font-bold px-2.5 py-1 rounded-lg transition-colors
                        {{ ($filters['sort'] ?? 'created_at') === $field ? 'text-white' : 'text-gray-400 hover:bg-gray-100' }}"
                        style="{{ ($filters['sort'] ?? 'created_at') === $field ? 'background: var(--brand-600)' : '' }}">
                        {{ $label }}
                        @if(($filters['sort'] ?? 'created_at') === $field)
                            {{ ($filters['dir'] ?? 'desc') === 'desc' ? '↓' : '↑' }}
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        @if($leads->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <p class="font-semibold text-gray-500 mb-1">No leads found</p>
                <p class="text-sm text-gray-400 mb-4">
                    @if(array_filter($filters))
                        Try adjusting your filters
                    @else
                        Start adding leads to your pipeline
                    @endif
                </p>
                <a href="{{ route('admin.crm.leads.create') }}"
                    class="text-sm font-bold px-4 py-2 rounded-xl text-white"
                    style="background: var(--brand-600)">
                    Add First Lead
                </a>
            </div>
        @else

            {{-- Table ── --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider w-[50px]">#</th>
                            <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider w-[260px]">Lead</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Stage</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Source</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Priority</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Score</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Assigned</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Follow-up</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Tasks</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                            @php
                                $pColor = $priorityColors[$lead->priority] ?? $priorityColors['medium'];
                                $initials = strtoupper(substr($lead->name, 0, 1));
                                $avatarColors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4'];
                                $avatarBg = $avatarColors[crc32($lead->name) % count($avatarColors)];
                            @endphp
                            <tr class="table-row">

                                {{-- 🌟 Added Dynamic Continuous Numbering --}}
                                <td class="px-5 py-3 text-[12px] font-bold text-gray-400">
                                    {{ $leads->firstItem() + $loop->index }}
                                </td>

                                {{-- Lead ── --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="lead-avatar" style="background: {{ $avatarBg }}">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.crm.leads.show', $lead->id) }}"
                                                class="text-[13px] font-bold text-gray-900 hover:underline block truncate max-w-[160px]">
                                                {{ $lead->name }}
                                            </a>
                                            <p class="text-[11px] text-gray-400 font-medium truncate">
                                                {{ $lead->phone ?? $lead->email ?? '—' }}
                                            </p>
                                            @if($lead->company_name)
                                                <p class="text-[11px] text-gray-400 truncate max-w-[160px]">
                                                    {{ $lead->company_name }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Stage Update Dropdown --}}
                                <td class="px-3 py-3">
                                    {{-- 1. Added max-w-32 (128px) and title attribute for the tooltip --}}
                                    <div x-data="{ currentStageColor: '{{ $lead->stage->color ?? '#9ca3af' }}' }" 
                                        class="relative inline-block group max-w-[130px]" 
                                        title="{{ $lead->stage->name ?? '' }}">
                                        
                                        <select 
                                            @change="updateLeadStage($event, {{ $lead->id }}, '{{ $lead->crm_stage_id }}')"
                                            {{-- 2. Added 'truncate' and 'pr-6' to handle long text properly --}}
                                            class="appearance-none pl-3 pr-6 py-1 rounded-full text-[11px] font-bold border-none cursor-pointer transition-all focus:ring-0 w-full truncate"
                                            :style="`background: ${currentStageColor}18; color: ${currentStageColor}`"
                                        >
                                            @php
                                                $relevantStages = $stages->where('crm_pipeline_id', $lead->crm_pipeline_id);
                                            @endphp

                                            @foreach($relevantStages as $st)
                                                <option value="{{ $st->id }}" 
                                                    {{ $lead->crm_stage_id == $st->id ? 'selected' : '' }}
                                                    data-color="{{ $st->color }}"
                                                    class="bg-white text-gray-800"
                                                >
                                                    {{ $st->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        
                                        {{-- Custom Dropdown Arrow --}}
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none opacity-50">
                                            <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                        </div>
                                    </div>
                                </td>

                                {{-- Source ── --}}
                                <td class="px-3 py-3">
                                    <span class="text-[12px] text-gray-500 font-medium">
                                        {{ $lead->source?->name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Priority ── --}}
                                <td class="px-3 py-3">
                                    <span class="priority-badge"
                                        style="background: {{ $pColor['bg'] }}; color: {{ $pColor['text'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                            style="background: {{ $pColor['dot'] }}"></span>
                                        {{ ucfirst($lead->priority) }}
                                    </span>
                                </td>

                                {{-- Score ── --}}
                                <td class="px-3 py-3">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[12px] font-black text-gray-700">
                                            {{ $lead->score }}
                                            <span class="text-[10px] font-semibold text-gray-400">
                                                {{ $lead->score_label }}
                                            </span>
                                        </span>
                                        <div class="score-bar">
                                            <div class="score-fill"
                                                style="width: {{ min(100, $lead->score) }}%;
                                                background: {{ $lead->score >= 50 ? '#ef4444' : ($lead->score >= 20 ? '#f59e0b' : '#9ca3af') }}">
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Assigned ── --}}
                                <td class="px-3 py-3">
                                    @php $assignee = $lead->assignees->first(); @endphp
                                    @if($assignee)
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0"
                                                style="background: var(--brand-600)">
                                                {{ strtoupper(substr($assignee->name, 0, 1)) }}
                                            </div>
                                            <span class="text-[11px] font-semibold text-gray-600 truncate max-w-[80px]">
                                                {{ $assignee->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-gray-300 font-medium">Unassigned</span>
                                    @endif
                                </td>

                                {{-- Follow-up ── --}}
                                <td class="px-3 py-3">
                                    @if($lead->next_followup_at)
                                        <span class="text-[11px] font-semibold
                                            {{ $lead->is_overdue ? 'text-red-600' : 'text-gray-600' }}">
                                            {{ $lead->is_overdue ? '⚠ ' : '' }}{{ $lead->next_followup_at->format('d M') }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-sm">—</span>
                                    @endif
                                </td>

                                {{-- Tasks ── --}}
                                <td class="px-3 py-3">
                                    @if($lead->pending_tasks_count > 0)
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-600">
                                            {{ $lead->pending_tasks_count }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-sm">—</span>
                                    @endif
                                </td>

                                {{-- Actions ── --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1 justify-end">

                                        {{-- WhatsApp ── --}}
                                        @if($lead->phone)
                                            <a href="{{ $lead->whatsapp_url }}" target="_blank"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-green-600 hover:bg-green-50 transition-colors"
                                                title="WhatsApp">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                                            </a>
                                        @endif

                                        {{-- View ── --}}
                                        <a href="{{ route('admin.crm.leads.show', $lead->id) }}"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                            title="View">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>

                                        {{-- Edit ── --}}
                                        <a href="{{ route('admin.crm.leads.edit', $lead->id) }}"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                            title="Edit">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>

                                        {{-- Delete ── --}}
                                        <button
                                            @click="deleteLead({{ $lead->id }}, '{{ addslashes($lead->name) }}')"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                            title="Delete">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination ── --}}
            @if($leads->hasPages())
                <div class="px-5 py-4 border-t border-gray-50 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-[12px] text-gray-400 font-medium">
                        Showing {{ $leads->firstItem() }}–{{ $leads->lastItem() }} of {{ $leads->total() }}
                    </p>
                    <div class="flex items-center gap-1">
                        {{-- Prev ── --}}
                        @if($leads->onFirstPage())
                            <span class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-300 cursor-not-allowed">← Prev</span>
                        @else
                            <a href="{{ $leads->previousPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-600 hover:bg-gray-100 transition-colors">← Prev</a>
                        @endif

                        {{-- Pages ── --}}
                        @foreach($leads->getUrlRange(max(1, $leads->currentPage()-2), min($leads->lastPage(), $leads->currentPage()+2)) as $page => $url)
                            <a href="{{ $url }}"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-[12px] font-bold transition-colors
                                {{ $page == $leads->currentPage() ? 'text-white' : 'text-gray-600 hover:bg-gray-100' }}"
                                style="{{ $page == $leads->currentPage() ? 'background: var(--brand-600)' : '' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        {{-- Next ── --}}
                        @if($leads->hasMorePages())
                            <a href="{{ $leads->nextPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-600 hover:bg-gray-100 transition-colors">Next →</a>
                        @else
                            <span class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-300 cursor-not-allowed">Next →</span>
                        @endif
                    </div>
                </div>
            @endif

        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function leadsPage() {
    return {
        init() {
            // nothing dynamic on index — all server-rendered
        },

        async updateLeadStage(event, leadId, oldStageId) {
            const select = event.target;
            const newStageId = select.value;
            const newColor = select.options[select.selectedIndex].dataset.color;
            
            // UI Feedback: Dim the row while saving
            const row = select.closest('tr');
            row.style.opacity = '0.6';

            try {
                const res = await fetch(`/admin/crm/leads/${leadId}/stage`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ 
                        stage_id: newStageId,
                        note: 'Quick stage update from list view' 
                    })
                });

                const data = await res.json();

                if (data.success) {
                    // 1. Update the color in Alpine state
                    // Since we used x-data on the container, we find it via the event
                    const alpineData = Alpine.$data(select.closest('[x-data]'));
                    alpineData.currentStageColor = newColor;

                    // 2. Success Toast
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        icon: 'success',
                        title: data.message,
                    });
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                // Revert on error
                select.value = oldStageId;
                Swal.fire('Update Failed', error.message || 'Server error', 'error');
            } finally {
                row.style.opacity = '1';
            }
        },

        async deleteLead(id, name) {
            const c = await Swal.fire({
                title:             'Delete Lead?',
                text:              `"${name}" will be soft deleted. This can be recovered.`,
                icon:              'warning',
                showCancelButton:  true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText:  'Cancel',
                confirmButtonColor:'#ef4444',
            });

            if (!c.isConfirmed) return;

            try {
                const res  = await fetch(`/admin/crm/leads/${id}`, {
                    method:  'DELETE',
                    headers: {
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await res.json();
                if (data.success) {
                    // Remove row from table
                    const row = document.querySelector(`button[\\@click*="${id}"]`)?.closest('tr');
                    if (row) {
                        row.style.opacity    = '0';
                        row.style.transition = 'opacity 200ms ease';
                        setTimeout(() => row.remove(), 200);
                    }
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch(e) {
                Swal.fire('Error', 'Network error. Please try again.', 'error');
            }
        },
    }
}
</script>
@endpush