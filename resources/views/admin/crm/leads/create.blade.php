@extends('layouts.admin')

@section('title', 'Add New Lead')

@section('header-title')
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.crm.leads.index') }}"
            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        </a>
        <div>
            <h1 class="text-[17px] font-bold text-gray-800 leading-none">Add New Lead</h1>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Create a new CRM lead</p>
        </div>
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .form-section {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .section-head {
        padding: 13px 18px;
        border-bottom: 1px solid #f8fafc;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-icon {
        width: 28px; height: 28px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .section-title {
        font-size: 12px;
        font-weight: 800;
        color: #374151;
        letter-spacing: 0.03em;
    }

    .section-body { padding: 18px; }

    .field-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 5px;
    }

    .field-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 9px 13px;
        font-size: 13px;
        color: #1f2937;
        outline: none;
        font-family: inherit;
        background: #fff;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }

    .field-input:focus {
        border-color: var(--brand-600);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-600) 10%, transparent);
    }
    select.field-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }

    .field-input.has-error { border-color: #f43f5e; }

    .field-error {
        font-size: 11px;
        font-weight: 600;
        color: #e11d48; /* Slightly deeper, less neon red */
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .field-error::before {
        content: "•";
        font-size: 14px;
        line-height: 1;
    }

    .priority-btn {
        flex: 1;
        padding: 8px 4px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
        border: 1.5px solid #e5e7eb;
        background: #fff;
        color: #9ca3af;
        cursor: pointer;
        text-align: center;
        transition: all 120ms;
    }

    .priority-btn.selected-hot    { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
    .priority-btn.selected-high   { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .priority-btn.selected-medium { background: #fefce8; color: #a16207; border-color: #fef08a; }
    .priority-btn.selected-low    { background: #f9fafb; color: #6b7280; border-color: #e5e7eb; }

    .tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        border: 1.5px solid transparent;
        cursor: pointer;
        transition: all 100ms;
        user-select: none;
    }

    .tag-chip.selected { border-color: currentColor; }
    .tag-chip:not(.selected) { opacity: 0.45; }
    .tag-chip:not(.selected):hover { opacity: 0.75; }
</style>
@endpush

@section('content')

<div class="pb-10" x-data="createLeadPage()" x-init="init()">

    {{-- ── Server error banner ── --}}
    <template x-if="serverError">
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-start gap-3">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <p class="text-sm font-semibold text-red-700" x-text="serverError"></p>
        </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-5 xl:grid-cols-3 gap-5">

        {{-- ═══════════════════════════════
             LEFT — Core Info (2/3 width)
        ═══════════════════════════════ --}}
        <div class="lg:col-span-3 xl:col-span-2 space-y-4">

            {{-- ── Basic Info ── --}}
            <div class="form-section">
                <div class="section-head">
                    <div class="section-icon" style="background: var(--brand-50)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="color: var(--brand-600)"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <span class="section-title">Basic Information</span>
                </div>
                <div class="section-body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-4">

                        {{-- Name ── --}}
                        <div class="sm:col-span-2">
                            <label class="field-label">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                x-model="form.name"
                                placeholder="Lead's full name"
                                class="field-input"
                                :class="errors.name ? 'has-error' : ''">
                            <template x-if="errors.name">
                                <p class="field-error" x-text="errors.name[0]"></p>
                            </template>
                        </div>

                        {{-- Phone ── --}}
                        <div>
                            <label class="field-label">Phone</label>
                            <input type="tel"
                                x-model="form.phone"
                                pattern="[0-9]{10}"
                                maxlength="10"
                                minlength="10"
                                inputmode="numeric"                                
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                placeholder="Mobile number"
                                class="field-input"
                                :class="errors.phone ? 'has-error' : ''">
                            <template x-if="errors.phone">
                                <p class="field-error" x-text="errors.phone[0]"></p>
                            </template>
                        </div>

                        {{-- Email ── --}}
                        <div>
                            <label class="field-label">Email</label>
                            <input type="email"
                                x-model="form.email"
                                placeholder="Email address"
                                class="field-input"
                                :class="errors.email ? 'has-error' : ''">
                            <template x-if="errors.email">
                                <p class="field-error" x-text="errors.email[0]"></p>
                            </template>
                        </div>

                        {{-- Company ── --}}
                        <div>
                            <label class="field-label">Company Name</label>
                            <input type="text"
                                x-model="form.company_name"
                                placeholder="Business / company name"
                                class="field-input">
                        </div>

                        {{-- Lead Value ── --}}
                        <div>
                            <label class="field-label">Estimated Value (₹)</label>
                            <input type="number"
                                x-model="form.lead_value"
                                placeholder="0.00"
                                min="0"
                                class="field-input"
                                :class="errors.lead_value ? 'has-error' : ''">
                            <template x-if="errors.lead_value">
                                <p class="field-error" x-text="errors.lead_value[0]"></p>
                            </template>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Pipeline & Source ── --}}
            <div class="form-section">
                <div class="section-head">
                    <div class="section-icon" style="background: #eff6ff">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <span class="section-title">Pipeline & Source</span>
                </div>
                <div class="section-body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Pipeline ── --}}
                        <div>
                            <label class="field-label">Pipeline</label>
                            <select x-model="form.crm_pipeline_id"
                                @change="loadStages()"
                                class="field-input w-full"
                                :class="errors.crm_pipeline_id ? 'has-error' : ''">
                                <option value="">Select pipeline</option>
                                @foreach($pipelines as $pipeline)
                                    <option value="{{ $pipeline->id }}"
                                        {{ $pipeline->is_default ? 'selected' : '' }}>
                                        {{ $pipeline->name }}
                                        {{ $pipeline->is_default ? '(Default)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Stage ── --}}
                        <div>
                            <label class="field-label">Stage</label>
                            <select x-model="form.crm_stage_id"
                                class="field-input w-full"
                                :class="errors.crm_stage_id ? 'has-error' : ''"
                                :disabled="availableStages.length === 0">
                                <option value=""
                                    x-text="availableStages.length === 0 ? 'Select pipeline first' : 'Select stage'">
                                </option>
                                <template x-for="stage in availableStages" :key="stage.id">
                                    <option :value="stage.id" x-text="stage.name"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Source ── --}}
                        <div>
                            <label class="field-label">Lead Source</label>
                            <select x-model="form.crm_lead_source_id" class="field-input w-full">
                                <option value="">Select source</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Assigned To ── --}}
                        <div>
                            <label class="field-label">Assign To</label>
                            <select x-model="form.assigned_to" class="field-input w-full">
                                <option value="">Assign to user...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Address ── --}}
            <div class="form-section">
                <div class="section-head">
                    <div class="section-icon" style="background: #f0fdf4">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <span class="section-title">Address
                        <span class="text-gray-400 font-normal text-[10px] normal-case tracking-normal ml-1">(optional)</span>
                    </span>
                </div>
                <div class="section-body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div class="sm:col-span-2">
                            <label class="field-label">Street Address</label>
                            <textarea x-model="form.address" rows="2"
                                placeholder="House/flat no, street, area, landmark"
                                class="field-input resize-none"></textarea>
                        </div>

                        <div>
                            <label class="field-label">City</label>
                            <input type="text" x-model="form.city"
                                placeholder="City" class="field-input">
                        </div>

                        <div>
                            <label class="field-label">State</label>
                            <input type="text" x-model="form.state"
                                placeholder="State" class="field-input">
                        </div>

                        <div>
                            <label class="field-label">PIN Code</label>
                            <input type="text" x-model="form.zip_code"
                                placeholder="6-digit PIN" maxlength="6" class="field-input">
                        </div>

                        <div>
                            <label class="field-label">Country</label>
                            <input type="text" x-model="form.country"
                                placeholder="Country" class="field-input">
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Social ── --}}
            <div class="form-section">
                <div class="section-head">
                    <div class="section-icon" style="background: #fdf4ff">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2" stroke-linecap="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    </div>
                    <span class="section-title">Social & Web
                        <span class="text-gray-400 font-normal text-[10px] normal-case tracking-normal ml-1">(optional)</span>
                    </span>
                </div>
                <div class="section-body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="field-label">Instagram ID</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[13px] font-semibold">@</span>
                                <input type="text" x-model="form.instagram_id" style="padding-left:25px;"
                                    placeholder="username" class="field-input">
                            </div>
                        </div>

                        <div>
                            <label class="field-label">Facebook ID</label>
                            <input type="text" x-model="form.facebook_id"
                                placeholder="Facebook profile" class="field-input">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="field-label">Website</label>
                            <input type="url" x-model="form.website"
                                placeholder="https://example.com" class="field-input"
                                :class="errors.website ? 'has-error' : ''">
                            <template x-if="errors.website">
                                <p class="field-error" x-text="errors.website[0]"></p>
                            </template>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Notes ── --}}
            <div class="form-section">
                <div class="section-head">
                    <div class="section-icon" style="background: #fefce8">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <span class="section-title">Notes
                        <span class="text-gray-400 font-normal text-[10px] normal-case tracking-normal ml-1">(optional)</span>
                    </span>
                </div>
                <div class="section-body">
                    <textarea x-model="form.description" rows="3"
                        placeholder="Any additional context about this lead — how they reached out, what they're interested in..."
                        class="field-input resize-none w-full"></textarea>
                </div>
            </div>

        </div>

        {{-- ═══════════════════════════════
             RIGHT — Priority, Tags, Followup, Submit
        ═══════════════════════════════ --}}
        <div class="lg:col-span-2 xl:col-span-1 space-y-4 lg:sticky lg:top-5">


            {{-- ── Priority ── --}}
            <div class="form-section">
                <div class="section-head">
                    <div class="section-icon" style="background: #fef2f2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <span class="section-title">Priority</span>
                </div>
                <div class="section-body">
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4 gap-2">
                        @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'hot' => '🔥 Hot'] as $val => $label)
                            <button type="button"
                                @click="form.priority = '{{ $val }}'"
                                class="priority-btn"
                                :class="form.priority === '{{ $val }}' ? 'selected-{{ $val }}' : ''">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── Tags ── --}}
            @if($tags->isNotEmpty())
                <div class="form-section">
                    <div class="section-head">
                        <div class="section-icon" style="background: #f0fdf4">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        </div>
                        <span class="section-title">Tags</span>
                    </div>
                    <div class="section-body">
                        <div class="flex flex-wrap gap-2">
                            @foreach($tags as $tag)
                                <button type="button"
                                    @click="toggleTag({{ $tag->id }})"
                                    class="tag-chip"
                                    :class="form.tags.includes({{ $tag->id }}) ? 'selected' : ''"
                                    style="background: {{ $tag->color }}18; color: {{ $tag->color }}">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        style="background: {{ $tag->color }}"></span>
                                    {{ $tag->name }}
                                </button>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2 font-medium">
                            Click to select multiple tags
                        </p>
                    </div>
                </div>
            @endif

            {{-- ── Follow-up Date ── --}}
            <div class="form-section">
                <div class="section-head">
                    <div class="section-icon" style="background: #eff6ff">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <span class="section-title">Follow-up Date</span>
                </div>
                <div class="section-body">
                    <input type="datetime-local"
                        x-model="form.next_followup_at"
                        class="field-input">
                    <p class="text-[11px] text-gray-400 mt-1.5 font-medium">
                        Scheduler will send reminder before this date
                    </p>
                </div>
            </div>

            {{-- ── Submit ── --}}
            <div class="form-section">
                <div class="section-body flex flex-col gap-3">

                    <button type="button"
                        @click="submit()"
                        :disabled="saving || !form.name.trim()"
                        class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl text-[14px] font-bold text-white transition-opacity"
                        style="background: var(--brand-600)"
                        :class="(saving || !form.name.trim()) ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'">
                        <svg x-show="saving" class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        <svg x-show="!saving" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        <span x-text="saving ? 'Saving...' : 'Create Lead'"></span>
                    </button>

                    <a href="{{ route('admin.crm.leads.index') }}"
                        class="w-full flex items-center justify-center py-2.5 rounded-xl text-[13px] font-bold text-gray-600 border border-gray-200 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>

                    {{-- Field count hint ── --}}
                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl px-4 py-3">
                        <p class="text-[11px] text-blue-700 font-semibold">
                            <svg class="inline w-3 h-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            Only Name is required. All other fields are optional and can be filled later.
                        </p>
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
@php
    $allStagesJson = $pipelines->map(fn($p) => [
        'pipeline_id' => $p->id,
        'stages'      => $p->stages->map(fn($s) => [
            'id'   => $s->id,
            'name' => $s->name,
        ])->values(),
    ])->values()->toJson();

    $defaultPipelineId = $pipelines->firstWhere('is_default', true)?->id
        ?? $pipelines->first()?->id
        ?? '';
@endphp
<script>
function createLeadPage() {
    return {
        saving:      false,
        serverError: null,
        errors:      {},

        // ── All pipeline stages for dynamic stage dropdown ──
        allStages: {!! $allStagesJson !!},

        availableStages: [],

        form: {
            name:               '',
            phone:              '',
            email:              '',
            company_name:       '',
            lead_value:         '',
            crm_pipeline_id: '{{ $defaultPipelineId }}',
            crm_stage_id:       '',
            crm_lead_source_id: '',
            assigned_to:        '',
            address:            '',
            city:               '',
            state:              '',
            country:            'India',
            zip_code:           '',
            instagram_id:       '',
            facebook_id:        '',
            google_profile:     '',
            website:            '',
            priority:           'medium',
            description:        '',
            next_followup_at:   '',
            tags:               [],
        },

        init() {
            // Load stages for default pipeline on page load
            if (this.form.crm_pipeline_id) {
                this.loadStages();
            }
        },

        loadStages() {
            const pipelineId   = parseInt(this.form.crm_pipeline_id);
            const pipelineData = this.allStages.find(p => p.pipeline_id === pipelineId);
            this.availableStages   = pipelineData?.stages ?? [];
            // Auto-select first stage
            this.form.crm_stage_id = this.availableStages[0]?.id ?? '';
        },

        toggleTag(tagId) {
            const idx = this.form.tags.indexOf(tagId);
            if (idx === -1) this.form.tags.push(tagId);
            else            this.form.tags.splice(idx, 1);
        },

        async submit() {
            if (!this.form.name.trim() || this.saving) return;

            this.saving      = true;
            this.serverError = null;
            this.errors      = {};

            // Clean empty strings to null for cleaner payload
            const payload = Object.fromEntries(
                Object.entries(this.form).map(([k, v]) => [k, v === '' ? null : v])
            );

            try {
                const res  = await fetch('{{ route("admin.crm.leads.store") }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (res.status === 422) {
                    this.errors = data.errors ?? {};
                    this.serverError = 'We need a little more info. Please check the highlighted fields below.';
                    
                    if (typeof BizAlert !== 'undefined') {
                        BizAlert.toast('Please check the highlighted fields.', 'error');
                    }

                    // UX Win: Scroll to and focus the first field with an error
                    this.$nextTick(() => {
                        const firstErrorNode = document.querySelector('.has-error');
                        if (firstErrorNode) {
                            firstErrorNode.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstErrorNode.focus();
                        } else {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    });
                    return;
                }

                if (!res.ok || (data && !data.success)) {
                    this.serverError = data?.message || 'Oops! Something went wrong on our end. Please try again.';
                    
                    if (typeof BizAlert !== 'undefined') {
                        BizAlert.toast(this.serverError, 'error');
                    }
                    
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }

                // ── Success → Toast & Redirect ──
                if (typeof BizAlert !== 'undefined') {
                    BizAlert.toast('Lead created successfully!', 'success');
                }

                // Slight delay so the user can read the success toast before the page disappears
                setTimeout(() => {
                    window.location.href = data.redirect || '{{ route("admin.crm.leads.index") }}';
                }, 800);

            } catch (e) {
                console.error('[CreateLead] Submit error:', e);
                this.serverError = 'We lost connection to the server. Please check your internet and try again.';
                if (typeof BizAlert !== 'undefined') BizAlert.toast('Network error occurred.', 'error');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } finally {
                this.saving = false;
            }
        },
    }
}
</script>
@endpush