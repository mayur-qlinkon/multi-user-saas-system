@extends('layouts.admin')

@section('title', 'Settings — Qlinkon BIZNESS')

@section('header-title')
    <div>        
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Settings</h1>
        {{-- <p class="text-xs text-gray-400 font-medium mt-0.5">Company configuration & preferences</p> --}}
    </div>
@endsection

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        input[type="color"] {
            -webkit-appearance: none;
            border: none;
            width: 40px;
            height: 40px;
            padding: 0;
            cursor: pointer;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        }

        input[type="color"]::-webkit-color-swatch-wrapper {
            padding: 0;
        }

        input[type="color"]::-webkit-color-swatch {
            border: none;
            border-radius: 10px;
        }

        .tab-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 20px;
            font-size: 13.5px;
            font-weight: 600;
            color: #6b7280;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
            transition: color 150ms ease, border-color 150ms ease, background 150ms ease;
            cursor: pointer;
            outline: none;
            background: transparent;
            border-top: none;
            border-left: none;
            border-right: none;
        }

        .tab-btn:hover {
            color: var(--brand-600);
            background: #f0fdf4;
        }

        .tab-btn.active {
            color: var(--brand-600);
            border-bottom-color: var(--brand-600);
            background: transparent;
        }

        .tab-btn.active .tab-icon {
            color: var(--brand-600);
        }

        .tab-icon {
            color: #9ca3af;
            transition: color 150ms ease;
        }

        .field-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .field-input {
            width: 100%;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13.5px;
            color: #1f2937;
            outline: none;
            transition: border-color 150ms ease, box-shadow 150ms ease;
            background: #fff;
            font-family: inherit;
        }

        .field-input:focus {
            border-color: var(--brand-600);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-600) 12%, transparent);
        }

        select.field-input {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 36px;
            appearance: none;
        }

        textarea.field-input {
            resize: vertical;
            min-height: 80px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding-bottom: 10px;
            margin-bottom: 20px;
            border-bottom: 1.5px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: var(--brand-600);
        }

        .upload-zone {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 14px;
            border: 2px dashed #e5e7eb;
            border-radius: 12px;
            background: #fafafa;
            transition: border-color 150ms ease;
        }

        .upload-zone:hover {
            border-color: var(--brand-600);
        }

        .toggle-wrap {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            border: 1.5px solid #f3f4f6;
            border-radius: 12px;
            background: #fafafa;
            gap: 14px;
        }

        .color-preview-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
        }
        /* 🌟 Custom Multi-Select Styles */
        .chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f3f4f6; /* gray-100 */
            border: 1px solid #e5e7eb; /* gray-200 */
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #374151; /* gray-700 */
        }
        
        .chip button {
            color: #9ca3af;
            transition: color 150ms;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        
        .chip button:hover {
            color: #ef4444; /* red-500 */
        }

        .multi-select-container {
            position: relative;
            width: 100%;
        }

        .multi-select-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            width: 100%;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 50;
            max-height: 200px;
            overflow-y: auto;
        }

        .multi-select-option {
            padding: 10px 14px;
            cursor: pointer;
            font-size: 13px;
            transition: background 150ms;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .multi-select-option:hover {
            background: #f9fafb; /* gray-50 */
        }

        .save-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--brand-600);
            color: #fff;
            padding: 10px 24px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: background 150ms ease, transform 80ms ease, opacity 150ms ease;
            box-shadow: 0 2px 8px color-mix(in srgb, var(--brand-600) 35%, transparent);
        }

        .save-btn:hover {
            background: var(--brand-700);
        }

        .save-btn:active {
            transform: scale(0.97);
        }

        .save-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .danger-zone {
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            border-radius: 14px;
            padding: 20px;
        }

        .warning-zone {
            background: #fffbeb;
            border: 1.5px solid #fde68a;
            border-radius: 14px;
            padding: 20px;
        }

        .success-zone {
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            border-radius: 14px;
            padding: 20px;
        }

        .banner-link-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            transition: border-color 150ms, box-shadow 150ms;
            text-decoration: none;
        }

        .banner-link-card:hover {
            border-color: var(--brand-600);
            box-shadow: 0 2px 12px color-mix(in srgb, var(--brand-600) 12%, transparent);
        }
    </style>
@endpush

@section('content')
    <div class="pb-10" x-data="settingsApp()">

        {{-- ── Top Bar ── --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>                
                <p class="text-sm text-gray-400 font-medium mt-0.5">Manage legal details, branding, billing and integrations
                </p>
            </div>
            @if(has_permission('settings.update'))
            <button type="button" @click="submitForm()" :disabled="isSaving" class="save-btn">
                <i data-lucide="loader-2" x-show="isSaving" x-cloak class="w-4 h-4 animate-spin"></i>
                <i data-lucide="save" x-show="!isSaving" class="w-4 h-4"></i>
                <span x-text="isSaving ? 'Saving...' : 'Save Changes'"></span>
            </button>
            @endif
        </div>

        {{-- ── Main Card ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Tab Bar --}}
            <div class="flex overflow-x-auto border-b border-gray-100 bg-gray-50/60 hide-scrollbar px-2">
                <template x-for="tab in tabs" :key="tab.id">
                    <button type="button" class="tab-btn" :class="{ active: activeTab === tab.id }"
                        @click="activeTab = tab.id">
                        <i :data-lucide="tab.icon" class="tab-icon w-4 h-4"></i>
                        <span x-text="tab.label"></span>
                    </button>
                </template>
            </div>

            <form id="settings-form" @submit.prevent="submitForm">
                @csrf
                <div class="p-6 sm:p-8">

                    {{-- ════════════════════════════════
                 TAB 1 — COMPANY
            ════════════════════════════════ --}}
                    <div x-show="activeTab === 'company'" x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0">

                        <p class="section-title"><i data-lucide="building-2" class="w-4 h-4"></i> Legal Entity Details</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                            <div class="md:col-span-2">
                                <label class="field-label">Company / Legal Name <span class="text-red-500">*</span></label>
                                <input type="text" name="company_name" value="{{ $company->name ?? '' }}"
                                    placeholder="As registered with ROC / GST" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">GSTIN <span class="text-red-500">*</span></label>
                                <input type="text" name="gst_number" value="{{ $company->gst_number ?? '' }}"
                                    placeholder="15-digit GSTIN" maxlength="15" class="field-input uppercase">
                                <p class="text-[11px] text-gray-400 mt-1.5">Used on all tax invoices and e-way bills</p>
                            </div>
                            <div>
                                <label class="field-label">PAN Number</label>
                                <input type="text" name="pan_number" value="{{ get_setting('pan_number') }}"
                                    placeholder="10-digit PAN" maxlength="10" class="field-input uppercase">
                            </div>
                            <div>
                                <label class="field-label">Registration Type</label>
                                <select name="registration_type" class="field-input">
                                    <option value="regular"
                                        {{ get_setting('registration_type') === 'regular' ? 'selected' : '' }}>Regular
                                    </option>
                                    <option value="composition"
                                        {{ get_setting('registration_type') === 'composition' ? 'selected' : '' }}>
                                        Composition</option>
                                    <option value="unregistered"
                                        {{ get_setting('registration_type') === 'unregistered' ? 'selected' : '' }}>
                                        Unregistered</option>
                                    <option value="sez"
                                        {{ get_setting('registration_type') === 'sez' ? 'selected' : '' }}>SEZ</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Financial Year Start</label>
                                <select name="fy_start" class="field-input">
                                    <option value="april"
                                        {{ get_setting('fy_start', 'april') === 'april' ? 'selected' : '' }}>April (Standard
                                        — Indian FY)</option>
                                    <option value="january" {{ get_setting('fy_start') === 'january' ? 'selected' : '' }}>
                                        January</option>
                                </select>
                                <p class="text-[11px] text-gray-400 mt-1.5">Affects reports and GST return periods</p>
                            </div>
                            <div>
                                <label class="field-label">Company Email</label>
                                <input type="email" name="company_email" value="{{ $company->email ?? '' }}"
                                    placeholder="billing@company.com" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Company Phone</label>
                                <input type="tel" name="company_phone" value="{{ $company->phone ?? '' }}"
                                    placeholder="10-digit number" maxlength="10" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Currency</label>
                                <select name="currency" class="field-input">
                                    <option value="INR" {{ ($company->currency ?? 'INR') === 'INR' ? 'selected' : '' }}>
                                        ₹ INR — Indian Rupee</option>
                                    <option value="USD" {{ ($company->currency ?? '') === 'USD' ? 'selected' : '' }}>$
                                        USD — US Dollar</option>
                                    <option value="EUR" {{ ($company->currency ?? '') === 'EUR' ? 'selected' : '' }}>€
                                        EUR — Euro</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Default State (Place of Supply)</label>
                                <select name="state_id" class="field-input">
                                    <option value="">Select State</option>
                                    @foreach ($states ?? [] as $state)
                                        <option value="{{ $state->id }}"
                                            {{ ($company->state_id ?? '') == $state->id ? 'selected' : '' }}>
                                            {{ $state->name }} ({{ $state->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-gray-400 mt-1.5">Default place of supply on new invoices</p>
                            </div>
                        </div>

                        <p class="section-title"><i data-lucide="map-pin" class="w-4 h-4"></i> Registered Address</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div class="md:col-span-3">
                                <label class="field-label">Street Address</label>
                                <textarea name="address" class="field-input" rows="2"
                                    placeholder="Office / factory address as per GST registration">{{ $company->address ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="field-label">City</label>
                                <input type="text" name="city" value="{{ $company->city ?? '' }}"
                                    placeholder="e.g. Ahmedabad" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">PIN Code</label>
                                <input type="text" name="zip_code" value="{{ $company->zip_code ?? '' }}"
                                    placeholder="6-digit PIN" maxlength="6" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Country</label>
                                <input type="text" name="country" value="{{ $company->country ?? 'India' }}"
                                    class="field-input" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- ════════════════════════════════
                 TAB 2 — BRANDING
            ════════════════════════════════ --}}
                    <div x-show="activeTab === 'branding'" x-cloak x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0">

                        <p class="section-title"><i data-lucide="palette" class="w-4 h-4"></i> Theme Colors</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                            <div>
                                <label class="field-label">Primary Brand Color</label>
                                <div class="color-preview-chip">
                                    <input type="color" name="primary_color" x-model="theme.primary"
                                        @input="livePreviewColors()">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800" x-text="theme.primary"></p>
                                        <p class="text-[11px] text-gray-400">Buttons, active nav, badges</p>
                                    </div>
                                    <div class="ml-auto w-8 h-8 rounded-lg shadow-sm flex-shrink-0"
                                        :style="`background:${theme.primary}`"></div>
                                </div>
                            </div>
                            <div>
                                <label class="field-label">Hover / Accent Color</label>
                                <div class="color-preview-chip">
                                    <input type="color" name="primary_hover_color" x-model="theme.hover"
                                        @input="livePreviewColors()">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800" x-text="theme.hover"></p>
                                        <p class="text-[11px] text-gray-400">Hover states, deep accents</p>
                                    </div>
                                    <div class="ml-auto w-8 h-8 rounded-lg shadow-sm flex-shrink-0"
                                        :style="`background:${theme.hover}`"></div>
                                </div>
                            </div>
                        </div>

                        <div class="success-zone mb-8">
                            <p class="text-xs text-green-700 font-semibold flex items-center gap-2">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Live preview is active — color changes apply instantly to the sidebar and buttons above.
                                Save Changes to persist them.
                            </p>
                        </div>

                        <p class="section-title"><i data-lucide="image" class="w-4 h-4"></i> Identity Assets</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                            {{-- <div>
                                <label class="field-label">Admin Logo <span
                                        class="text-gray-400 normal-case font-normal">(shown in sidebar)</span></label>
                                <div class="upload-zone">
                                    <img :src="previews.logo ||
                                        '{{ get_setting('logo') ? asset('storage/' . get_setting('logo')) : asset('assets/images/placeholder.webp') }}'"
                                        class="w-24 h-12 rounded-lg object-contain border border-gray-200 bg-white flex-shrink-0">
                                    <div class="flex-1">
                                        <input type="file" name="logo" @change="previewFile($event, 'logo')"
                                            accept="image/*"
                                            class="text-xs w-full text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-brand-500 file:text-white cursor-pointer">
                                        <p class="text-[10px] text-gray-400 mt-1.5">PNG/SVG, transparent bg recommended</p>
                                    </div>
                                </div>
                            </div> --}}
                            <div>
                                <label class="field-label">Website Logo <span
                                        class="text-gray-400 normal-case font-normal">(public storefront)</span></label>
                                <div class="upload-zone">
                                    <img :src="previews.icon ||
                                        '{{ get_setting('icon') ? asset('storage/' . get_setting('icon')) : 'https://placehold.co/400x200?text=Logo' }}'"
                                        class="w-24 h-12 rounded-lg object-contain border border-gray-200 bg-white flex-shrink-0">
                                    <div class="flex-1">
                                        <input type="file" name="icon" @change="previewFile($event, 'icon')"
                                            accept="image/*"
                                            class="text-xs w-full text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-brand-500 file:text-white cursor-pointer">
                                        <p class="text-[10px] text-gray-400 mt-1.5">Recommended 400×120px</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="field-label">Browser Favicon</label>
                                <div class="upload-zone">
                                    <img :src="previews.favicon ||
                                        '{{ get_setting('favicon') ? asset('storage/' . get_setting('favicon')) : asset('assets/images/placeholder.webp') }}'"
                                        class="w-10 h-10 rounded-lg object-contain border border-gray-200 bg-white flex-shrink-0">
                                    <div class="flex-1">
                                        <input type="file" name="favicon" @change="previewFile($event, 'favicon')"
                                            accept="image/png,image/x-icon,image/svg+xml"
                                            class="text-xs w-full text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-brand-500 file:text-white cursor-pointer">
                                        <p class="text-[10px] text-gray-400 mt-1.5">ICO or PNG, 32×32px</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="field-label">Authorized Signature <span
                                        class="text-gray-400 normal-case font-normal">(invoice footer)</span></label>
                                <div class="upload-zone">
                                    <img :src="previews.signature ||
                                        '{{ get_setting('signature') ? asset('storage/' . get_setting('signature')) : 'https://placehold.co/400x200?text=Signature' }}'"
                                        class="w-24 h-12 rounded-lg object-contain border border-gray-200 bg-white flex-shrink-0">
                                    <div class="flex-1">
                                        <input type="file" name="signature" @change="previewFile($event, 'signature')"
                                            accept="image/*"
                                            class="text-xs w-full text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-brand-500 file:text-white cursor-pointer">
                                        <p class="text-[10px] text-gray-400 mt-1.5">PNG with transparent background</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                       
                    </div>

                    {{-- ════════════════════════════════
                 TAB 3 — BILLING
            ════════════════════════════════ --}}
                    <div x-show="activeTab === 'billing'" x-cloak x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0">

                        <p class="section-title"><i data-lucide="landmark" class="w-4 h-4"></i> Bank Account Details</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                            <div class="md:col-span-2">
                                <label class="field-label">Bank Name</label>
                                <input type="text" name="bank_name" value="{{ get_setting('bank_name') }}"
                                    placeholder="e.g. HDFC Bank" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Account Holder Name</label>
                                <input type="text" name="bank_holder" value="{{ get_setting('bank_holder') }}"
                                    placeholder="As per bank records" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Account Number</label>
                                <input type="text" name="bank_ac" value="{{ get_setting('bank_ac') }}"
                                    placeholder="Account number" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">IFSC Code</label>
                                <input type="text" name="ifsc" value="{{ get_setting('ifsc') }}"
                                    placeholder="e.g. HDFC0001234" maxlength="11" class="field-input uppercase">
                            </div>
                            <div>
                                <label class="field-label">Branch Name</label>
                                <input type="text" name="bank_branch" value="{{ get_setting('bank_branch') }}"
                                    placeholder="e.g. Navrangpura" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">UPI ID</label>
                                <input type="text" name="upi_id" value="{{ get_setting('upi_id') }}"
                                    placeholder="yourshop@upi" class="field-input">
                                <p class="text-[11px] text-gray-400 mt-1.5">Used to generate QR code on invoices</p>
                            </div>
                        </div>

                        <p class="section-title"><i data-lucide="file-text" class="w-4 h-4"></i> Invoice Configuration
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                            <div>
                                <label class="field-label">Invoice Prefix</label>
                                <input type="text" name="invoice_prefix"
                                    value="{{ get_setting('invoice_prefix', 'INV-') }}" placeholder="e.g. INV-"
                                    class="field-input">
                                <p class="text-[11px] text-gray-400 mt-1.5">e.g. INV-0001</p>
                            </div>
                            <div>
                                <label class="field-label">Next Invoice Number</label>
                                <input type="number" name="invoice_start_number" min="1"
                                    value="{{ get_setting('invoice_start_number', 1) }}" class="field-input">
                                <p class="text-[11px] text-gray-400 mt-1.5">Auto-increments from this number</p>
                            </div>
                            <div>
                                <label class="field-label">Quotation Prefix</label>
                                <input type="text" name="quotation_prefix"
                                    value="{{ get_setting('quotation_prefix', 'QTN-') }}" placeholder="e.g. QTN-"
                                    class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Default Tax Type</label>
                                <select name="default_tax_type" class="field-input">
                                    <option value="cgst_sgst"
                                        {{ get_setting('default_tax_type', 'cgst_sgst') === 'cgst_sgst' ? 'selected' : '' }}>
                                        CGST + SGST (Intra-state)</option>
                                    <option value="igst"
                                        {{ get_setting('default_tax_type') === 'igst' ? 'selected' : '' }}>IGST
                                        (Inter-state)</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Default Payment Terms</label>
                                <select name="payment_terms" class="field-input">
                                    <option value="immediate"
                                        {{ get_setting('payment_terms', 'immediate') === 'immediate' ? 'selected' : '' }}>
                                        Immediate</option>
                                    <option value="net7"
                                        {{ get_setting('payment_terms') === 'net7' ? 'selected' : '' }}>Net 7 Days</option>
                                    <option value="net15"
                                        {{ get_setting('payment_terms') === 'net15' ? 'selected' : '' }}>Net 15 Days
                                    </option>
                                    <option value="net30"
                                        {{ get_setting('payment_terms') === 'net30' ? 'selected' : '' }}>Net 30 Days
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Round Off Amounts</label>
                                <select name="round_off" class="field-input">
                                    <option value="1" {{ get_setting('round_off', '1') === '1' ? 'selected' : '' }}>
                                        Yes — Round to nearest ₹1</option>
                                    <option value="0" {{ get_setting('round_off') === '0' ? 'selected' : '' }}>No —
                                        Keep decimals</option>
                                </select>
                            </div>
                        </div>

                        <p class="section-title"><i data-lucide="file-pen-line" class="w-4 h-4"></i> Default Invoice
                            Content</p>

                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="field-label">Default Invoice Footer Note</label>
                                <textarea name="invoice_footer_note" class="field-input" rows="2"
                                    placeholder="e.g. Thank you for your business! Goods once sold will not be taken back.">{{ get_setting('invoice_footer_note') }}</textarea>
                            </div>
                            <div>
                                <label class="field-label">Default Terms & Conditions</label>
                                <textarea name="default_terms" class="field-input" rows="3"
                                    placeholder="Standard terms printed on every invoice...">{{ get_setting('default_terms') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- ════════════════════════════════
                        TAB 4 — STOREFRONT
                    ════════════════════════════════ --}}
                    <div x-show="activeTab === 'storefront'" x-cloak x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl mb-5 flex gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-blue-500 mt-0.5 shrink-0"></i>
                            <p class="text-xs text-blue-700">These are <strong>company-wide defaults</strong>.
                            Per-branch contact details, WhatsApp, hours, and social links are configured under
                            <a href="{{ route('admin.stores.index') }}" class="underline font-semibold">Stores → Edit Branch → Public Page</a>.</p>
                        </div>

                        <p class="section-title"><i data-lucide="monitor" class="w-4 h-4"></i> Public Shop Status</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                            <div>
                                <label class="field-label">Storefront</label>
                                <div class="toggle-wrap">
                                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                        <input type="checkbox" name="storefront_online" value="1"
                                            {{ get_setting('storefront_online') ? 'checked' : '' }} class="sr-only peer">
                                        <div
                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600">
                                        </div>
                                    </label>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700">Online & Publicly Accessible</p>
                                        <p class="text-[11px] text-gray-400">When off, visitors see a maintenance page</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="field-label">Shop Tagline</label>
                                <input type="text" name="storefront_tagline"
                                    value="{{ get_setting('storefront_tagline') }}"
                                    placeholder="e.g. Fresh Plants, Delivered to Your Door" class="field-input">
                            </div>
                           <div>
                                <label class="field-label">Support Phone</label>
                                <input 
                                    type="tel" 
                                    name="call_number" 
                                    value="{{ get_setting('call_number') }}"
                                    placeholder="10-digit number"
                                    class="field-input"
                                    pattern="[0-9]{10}"
                                    maxlength="10"
                                    minlength="10"
                                    inputmode="numeric"                                    
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                >
                            </div>
                            <div>
                                <label class="field-label">WhatsApp Number</label>
                                <input type="text" name="whatsapp"   
                                    pattern="[0-9]{10}"
                                    maxlength="10"
                                    minlength="10"
                                    inputmode="numeric" value="{{ get_setting('whatsapp') }}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    placeholder="With country code, e.g. 919099001122" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Website URL</label>
                                <input type="url" name="website" value="{{ get_setting('website') }}"
                                    placeholder="https://yourstore.com" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Company Address (Public)</label>
                                <input type="text" name="storefront_address" value="{{ get_setting('storefront_address') }}"
                                <input type="text" name="storefront_address" value="{{ get_setting('storefront_address') }}"
                                    placeholder="Shown on contact page" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Support Email</label>
                                <input type="email" name="support_email" value="{{ get_setting('support_email') }}"
                                    placeholder="support@yourstore.com" class="field-input">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="field-label">Business Hours</label>
                                <textarea name="business_hours" rows="3" placeholder="e.g., Mon-Fri: 9 AM - 6 PM&#10;Sat-Sun: Closed" class="field-input resize-y">{{ get_setting('business_hours') }}</textarea>
                                <p class="text-[10px] text-gray-400 mt-1">These hours will be displayed on your public storefront contact page.</p>
                            </div>
                        </div>

                        <p class="section-title"><i data-lucide="share-2" class="w-4 h-4"></i> Social Profiles</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                            @php
                                $socials = [
                                    [
                                        'name' => 'instagram',
                                        'label' => 'Instagram',
                                        'icon' => '📸',
                                        'placeholder' => 'https://instagram.com/yourpage',
                                    ],
                                    [
                                        'name' => 'facebook',
                                        'label' => 'Facebook',
                                        'icon' => '👥',
                                        'placeholder' => 'https://facebook.com/yourpage',
                                    ],
                                    [
                                        'name' => 'youtube',
                                        'label' => 'YouTube',
                                        'icon' => '▶️',
                                        'placeholder' => 'https://youtube.com/@yourchannel',
                                    ],
                                    [
                                        'name' => 'linkedin',
                                        'label' => 'LinkedIn',
                                        'icon' => '💼',
                                        'placeholder' => 'https://linkedin.com/company/yourcompany',
                                    ],
                                    [
                                        'name' => 'twitter',
                                        'label' => 'Twitter / X',
                                        'icon' => '🐦',
                                        'placeholder' => 'https://x.com/yourhandle',
                                    ],
                                    [
                                        'name' => 'google',
                                        'label' => 'Google Maps',
                                        'icon' => '📍',
                                        'placeholder' => 'https://maps.google.com/?cid=...',
                                    ],
                                ];
                            @endphp
                            @foreach ($socials as $social)
                                <div>
                                    <label class="field-label">{{ $social['icon'] }} {{ $social['label'] }}</label>
                                    <input type="url" name="{{ $social['name'] }}"
                                        value="{{ get_setting($social['name']) }}"
                                        placeholder="{{ $social['placeholder'] }}" class="field-input">
                                </div>
                            @endforeach
                        </div>

                        <p class="section-title"><i data-lucide="search" class="w-4 h-4"></i> SEO & Meta</p>

                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="field-label">Meta Title</label>
                                <input type="text" name="seo_title" value="{{ get_setting('seo_title') }}"
                                    placeholder="60 characters recommended" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Meta Description</label>
                                <textarea name="seo_description" class="field-input" rows="3"
                                    placeholder="160 characters recommended — shown in Google search results">{{ get_setting('seo_description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- ════════════════════════════════
                 TAB 5 — SYSTEM
            ════════════════════════════════ --}}
                    <div x-show="activeTab === 'system'" x-cloak x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0">

                        {{-- ── INVENTORY SETTINGS ── --}}
                        <p class="section-title">
                            <i data-lucide="boxes" class="w-4 h-4"></i>
                            Inventory Configuration
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">

                            <div>
                                <label class="field-label">Batch Tracking</label>

                                <div class="toggle-wrap">
                                    
                                    <!-- Toggle -->
                                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                        <input type="checkbox" name="enable_batch_tracking" value="1"
                                            {{ get_setting('enable_batch_tracking', 0) ? 'checked' : '' }}
                                            class="sr-only peer">

                                        <div class="w-11 h-6 bg-gray-200 rounded-full peer 
                                            peer-checked:bg-brand-600
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                            after:bg-white after:border after:rounded-full after:h-5 after:w-5
                                            after:transition-all peer-checked:after:translate-x-full">
                                        </div>
                                    </label>

                                    <!-- Text -->
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700">
                                            Enable Batch / Lot Tracking
                                        </p>
                                        <p class="text-[11px] text-gray-400">
                                            Track stock by expiry date, batch number and apply FIFO during sales
                                        </p>
                                    </div>

                                </div>

                                <!-- Helper Text -->
                                <p class="text-[11px] text-gray-400 mt-2">
                                    Recommended for medical stores, food products, dairy, and nursery businesses.
                                    Keep OFF for mobile, clothing or electronics shops.
                                </p>
                            </div>

                        </div>

                        {{-- ── STOREFRONT PRICING ── --}}
                        @if(has_module('plant_education'))
                        <p class="section-title">
                            <i data-lucide="tag" class="w-4 h-4"></i>
                            Storefront Pricing
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                            <div>
                                <label class="field-label">Show Pricing</label>
                                <div class="toggle-wrap">
                                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                        <input type="checkbox" name="enable_product_pricing" value="1"
                                            {{ get_setting('enable_product_pricing', 1) ? 'checked' : '' }}
                                            class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 rounded-full peer
                                            peer-checked:bg-brand-600
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                            after:bg-white after:border after:rounded-full after:h-5 after:w-5
                                            after:transition-all peer-checked:after:translate-x-full">
                                        </div>
                                    </label>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700">
                                            Display Prices on Public Storefront
                                        </p>
                                        <p class="text-[11px] text-gray-400">
                                            When disabled, product prices are hidden on all public storefront pages. Useful for inquiry-based businesses.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(has_permission('settings.clear_cache'))
                        <p class="section-title"><i data-lucide="zap" class="w-4 h-4"></i> Performance & Cache</p>

                        <div class="warning-zone mb-6 flex items-start gap-4">
                            <div
                                class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-lucide="refresh-cw" class="w-5 h-5 text-amber-600"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-amber-900">System Cache</h4>
                                <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                                    If logo, colors or banners aren't reflecting on the website after saving,
                                    clear the cache. This does not delete any data.
                                </p>
                                <button type="button" @click="clearCache()" :disabled="isClearingCache"
                                    class="mt-4 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2">
                                    <i data-lucide="loader-2" x-show="isClearingCache"
                                        class="w-3.5 h-3.5 animate-spin"></i>
                                    <i data-lucide="trash-2" x-show="!isClearingCache" class="w-3.5 h-3.5"></i>
                                    <span x-text="isClearingCache ? 'Clearing...' : 'Purge All Cache'"></span>
                                </button>
                            </div>
                        </div>
                        @endif
                        
                        @if(has_permission('settings.audit'))
                        
                        <p class="section-title"><i data-lucide="history" class="w-4 h-4"></i> Audit Trail</p>
                        
                        <a href="{{ route('admin.settings.audit') }}" class="banner-link-card group mb-8 block">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="history" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">Settings Change History</p>
                                        <p class="text-[11px] text-gray-400">View who changed what and when — full audit
                                            trail</p>
                                    </div>
                                </div>
                                <i data-lucide="arrow-right"
                                    class="w-4 h-4 text-gray-300 group-hover:text-brand-600 transition-colors"></i>
                            </div>
                        </a>
                        @endif

                        <p class="section-title"><i data-lucide="info" class="w-4 h-4"></i> System Information</p>

                        <div class="bg-gray-50 rounded-xl border border-gray-100 px-5 py-2 mb-6">
                            <div class="info-row">
                                <span class="text-gray-500 font-medium">Tenant ID</span>
                                <span class="font-mono font-bold text-gray-900">#{{ auth()->user()->company_id }}</span>
                            </div>
                            <div class="info-row">
                                <span class="text-gray-500 font-medium">Environment</span>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                            {{ app()->environment('production') ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ app()->environment() }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="text-gray-500 font-medium">Laravel Version</span>
                                <span class="font-mono text-gray-900 font-bold">{{ app()->version() }}</span>
                            </div>
                            <div class="info-row">
                                <span class="text-gray-500 font-medium">PHP Version</span>
                                <span class="font-mono text-gray-900 font-bold">{{ phpversion() }}</span>
                            </div>
                            <div class="info-row">
                                <span class="text-gray-500 font-medium">Timezone</span>
                                <span class="font-mono text-gray-900 font-bold">{{ config('app.timezone') }}</span>
                            </div>
                        </div>

                        @if(has_permission('settings.reset'))
                        <p class="section-title"><i data-lucide="shield-alert" class="w-4 h-4"></i> Danger Zone</p>

                        <div class="danger-zone">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="text-sm font-bold text-red-800">Reset All Settings</h4>
                                    <p class="text-xs text-red-600 mt-1 leading-relaxed">
                                        This will reset all settings to factory defaults. Company data and transactions are
                                        unaffected.
                                        This action cannot be undone.
                                    </p>
                                </div>
                                <button type="button" @click="confirmReset()"
                                    class="flex-shrink-0 bg-white border border-red-300 text-red-600 hover:bg-red-600 hover:text-white px-4 py-2 rounded-lg text-xs font-bold transition-all">
                                    Reset Settings
                                </button>
                            </div>
                        </div>
                        @endif

                    </div>

                </div>
            </form>

            {{-- ════════════════════════════════
                 TAB: NOTIFICATIONS (own form / save, outside main form)
            ════════════════════════════════ --}}
            <div x-show="activeTab === 'notifications'" x-cloak
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="p-6 sm:p-8">

                <p class="section-title"><i data-lucide="bell" class="w-4 h-4"></i> Notification Recipients</p>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                    Choose who gets notified for each event. Roles and specific users can both be selected — all unique
                    recipients receive the notification. If nothing is configured, the <strong class="text-gray-700">owner</strong>
                    is notified by default.
                </p>

                {{-- Event Card: New Order Inquiry --}}
                <div class="border border-gray-200 rounded-2xl mb-6">
                    <div class="bg-gray-50/80 rounded-t-2xl px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="shopping-cart" class="w-4 h-4 text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">New Order Inquiry</h4>
                            <p class="text-[12px] text-gray-500 mt-0.5">Triggered when a customer places a new order via the storefront</p>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- ── Roles Multi-Select ── --}}
                        <div>
                            <label class="field-label">Notify by Role</label>
                            <div class="relative" @click.outside="msConfig.notify_new_order.roles.open = false; msConfig.notify_new_order.roles.search = ''">

                                {{-- Chip container + search input --}}
                                <div
                                    @click="msConfig.notify_new_order.roles.open = true; $nextTick(() => $refs.rolesSearch.focus())"
                                    class="field-input flex flex-wrap gap-1.5 items-center cursor-text transition-all"
                                    style="min-height:44px; padding:6px 10px;"
                                    :class="msConfig.notify_new_order.roles.open ? 'border-[var(--brand-600)] shadow-[0_0_0_3px_color-mix(in_srgb,var(--brand-600)_12%,transparent)]' : ''"
                                >
                                    {{-- Selected chips --}}
                                    <template x-for="val in notifConfig.notify_new_order.roles" :key="val">
                                        <span class="inline-flex items-center gap-1 text-[12px] font-semibold px-2.5 py-1 rounded-lg leading-none flex-shrink-0"
                                            style="background:color-mix(in srgb,var(--brand-600) 12%,transparent); color:var(--brand-600);">
                                            <span x-text="msLabel('roles', val)" class="capitalize"></span>
                                            <button type="button" @click.stop="msRemove('notify_new_order', 'roles', val)"
                                                class="ml-0.5 opacity-60 hover:opacity-100 hover:text-red-500 transition-all leading-none">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        </span>
                                    </template>

                                    {{-- Search input --}}
                                    <input x-ref="rolesSearch" type="text" x-model="msConfig.notify_new_order.roles.search"
                                        @focus="msConfig.notify_new_order.roles.open = true"
                                        @keydown.escape="msConfig.notify_new_order.roles.open = false; msConfig.notify_new_order.roles.search = ''"
                                        class="flex-1 min-w-[80px] bg-transparent outline-none text-[13.5px] text-gray-700 placeholder-gray-400 py-0.5 px-1"
                                        :placeholder="notifConfig.notify_new_order.roles.length === 0 ? 'Search roles...' : 'Add more...'">

                                    <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-150"
                                        :class="msConfig.notify_new_order.roles.open ? 'rotate-180' : ''"></i>
                                </div>

                                {{-- Dropdown --}}
                                <div x-show="msConfig.notify_new_order.roles.open" x-cloak
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 -translate-y-1 scale-y-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
                                    class="absolute z-30 left-0 right-0 top-full mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl overflow-y-auto"
                                    style="max-height:200px;">
                                    <template x-for="opt in msFiltered('notify_new_order', 'roles')" :key="opt.value">
                                        <div @mousedown.prevent="msSelect('notify_new_order', 'roles', opt.value)"
                                            class="flex items-center gap-2 px-4 py-2.5 cursor-pointer hover:bg-gray-50 transition-colors">
                                            <span class="text-[13.5px] text-gray-700 font-medium capitalize" x-text="opt.label"></span>
                                        </div>
                                    </template>
                                    <div x-show="msFiltered('notify_new_order', 'roles').length === 0" class="px-4 py-3 text-center">
                                        <span class="text-[12px] text-gray-400 italic"
                                            x-text="msConfig.notify_new_order.roles.search ? 'No matching roles.' : 'All roles selected.'"></span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1.5">Selects all users who currently hold this role</p>
                        </div>

                        {{-- ── Users Multi-Select ── --}}
                        <div>
                            <label class="field-label">Notify Specific Users</label>
                            <div class="relative" @click.outside="msConfig.notify_new_order.users.open = false; msConfig.notify_new_order.users.search = ''">

                                {{-- Chip container + search input --}}
                                <div
                                    @click="msConfig.notify_new_order.users.open = true; $nextTick(() => $refs.usersSearch.focus())"
                                    class="field-input flex flex-wrap gap-1.5 items-center cursor-text transition-all"
                                    style="min-height:44px; padding:6px 10px;"
                                    :class="msConfig.notify_new_order.users.open ? 'border-[var(--brand-600)] shadow-[0_0_0_3px_color-mix(in_srgb,var(--brand-600)_12%,transparent)]' : ''"
                                >
                                    {{-- Selected chips --}}
                                    <template x-for="val in notifConfig.notify_new_order.users" :key="val">
                                        <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 text-[12px] font-semibold px-2.5 py-1 rounded-lg leading-none flex-shrink-0">
                                            <span x-text="msLabel('users', val)"></span>
                                            <button type="button" @click.stop="msRemove('notify_new_order', 'users', val)"
                                                class="ml-0.5 opacity-60 hover:opacity-100 hover:text-red-500 transition-all leading-none">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        </span>
                                    </template>

                                    {{-- Search input --}}
                                    <input x-ref="usersSearch" type="text" x-model="msConfig.notify_new_order.users.search"
                                        @focus="msConfig.notify_new_order.users.open = true"
                                        @keydown.escape="msConfig.notify_new_order.users.open = false; msConfig.notify_new_order.users.search = ''"
                                        class="flex-1 min-w-[80px] bg-transparent outline-none text-[13.5px] text-gray-700 placeholder-gray-400 py-0.5 px-1"
                                        :placeholder="notifConfig.notify_new_order.users.length === 0 ? 'Search users...' : 'Add more...'">

                                    <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-150"
                                        :class="msConfig.notify_new_order.users.open ? 'rotate-180' : ''"></i>
                                </div>

                                {{-- Dropdown --}}
                                <div x-show="msConfig.notify_new_order.users.open" x-cloak
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 -translate-y-1 scale-y-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
                                    class="absolute z-30 left-0 right-0 top-full mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl overflow-y-auto"
                                    style="max-height:200px;">
                                    <template x-for="opt in msFiltered('notify_new_order', 'users')" :key="opt.value">
                                        <div @mousedown.prevent="msSelect('notify_new_order', 'users', opt.value)"
                                            class="flex items-center justify-between px-4 py-2.5 cursor-pointer hover:bg-gray-50 transition-colors">
                                            <span class="text-[13.5px] text-gray-700 font-medium" x-text="opt.label"></span>
                                            <span x-show="opt.sub" x-text="opt.sub"
                                                class="text-[11px] text-gray-400 capitalize ml-3 flex-shrink-0"></span>
                                        </div>
                                    </template>
                                    <div x-show="msFiltered('notify_new_order', 'users').length === 0" class="px-4 py-3 text-center">
                                        <span class="text-[12px] text-gray-400 italic"
                                            x-text="msConfig.notify_new_order.users.search ? 'No matching users.' : 'All users selected.'"></span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1.5">Notified regardless of their role</p>
                        </div>

                    </div>
                </div>

                    {{-- Event Card: Leave Requests --}}
                    <div class="border border-gray-200 rounded-2xl mb-6">
                        <div class="bg-gray-50/80 rounded-t-2xl px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="calendar-clock" class="w-4 h-4 text-amber-600"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">Leave Requests</h4>
                                <p class="text-[12px] text-gray-500 mt-0.5">Triggered when an employee submits a new leave/time-off request</p>
                            </div>
                        </div>

                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- ── Roles Multi-Select ── --}}
                            <div>
                                <label class="field-label">Notify by Role</label>
                                <div class="relative" @click.outside="msConfig.notify_leave_request.roles.open = false; msConfig.notify_leave_request.roles.search = ''">

                                    <div @click="msConfig.notify_leave_request.roles.open = true; $nextTick(() => $refs.leaveRolesSearch.focus())"
                                        class="field-input flex flex-wrap gap-1.5 items-center cursor-text transition-all"
                                        style="min-height:44px; padding:6px 10px;"
                                        :class="msConfig.notify_leave_request.roles.open ? 'border-[var(--brand-600)] shadow-[0_0_0_3px_color-mix(in_srgb,var(--brand-600)_12%,transparent)]' : ''">
                                        
                                        <template x-for="val in notifConfig.notify_leave_request.roles" :key="val">
                                            <span class="inline-flex items-center gap-1 text-[12px] font-semibold px-2.5 py-1 rounded-lg leading-none flex-shrink-0"
                                                style="background:color-mix(in srgb,var(--brand-600) 12%,transparent); color:var(--brand-600);">
                                                <span x-text="msLabel('roles', val)" class="capitalize"></span>
                                                <button type="button" @click.stop="msRemove('notify_leave_request', 'roles', val)"
                                                    class="ml-0.5 opacity-60 hover:opacity-100 hover:text-red-500 transition-all leading-none">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                </button>
                                            </span>
                                        </template>

                                        <input x-ref="leaveRolesSearch" type="text" x-model="msConfig.notify_leave_request.roles.search"
                                            @focus="msConfig.notify_leave_request.roles.open = true"
                                            @keydown.escape="msConfig.notify_leave_request.roles.open = false; msConfig.notify_leave_request.roles.search = ''"
                                            class="flex-1 min-w-[80px] bg-transparent outline-none text-[13.5px] text-gray-700 placeholder-gray-400 py-0.5 px-1"
                                            :placeholder="notifConfig.notify_leave_request.roles.length === 0 ? 'Search roles...' : 'Add more...'">

                                        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-150"
                                            :class="msConfig.notify_leave_request.roles.open ? 'rotate-180' : ''"></i>
                                    </div>

                                    <div x-show="msConfig.notify_leave_request.roles.open" x-cloak
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-1 scale-y-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
                                        class="absolute z-30 left-0 right-0 top-full mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl overflow-y-auto"
                                        style="max-height:200px;">
                                        <template x-for="opt in msFiltered('notify_leave_request', 'roles')" :key="opt.value">
                                            <div @mousedown.prevent="msSelect('notify_leave_request', 'roles', opt.value)"
                                                class="flex items-center gap-2 px-4 py-2.5 cursor-pointer hover:bg-gray-50 transition-colors">
                                                <span class="text-[13.5px] text-gray-700 font-medium capitalize" x-text="opt.label"></span>
                                            </div>
                                        </template>
                                        <div x-show="msFiltered('notify_leave_request', 'roles').length === 0" class="px-4 py-3 text-center">
                                            <span class="text-[12px] text-gray-400 italic" x-text="msConfig.notify_leave_request.roles.search ? 'No matching roles.' : 'All roles selected.'"></span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1.5">Selects all users who currently hold this role</p>
                            </div>

                            {{-- ── Users Multi-Select ── --}}
                            <div>
                                <label class="field-label">Notify Specific Users</label>
                                <div class="relative" @click.outside="msConfig.notify_leave_request.users.open = false; msConfig.notify_leave_request.users.search = ''">

                                    <div @click="msConfig.notify_leave_request.users.open = true; $nextTick(() => $refs.leaveUsersSearch.focus())"
                                        class="field-input flex flex-wrap gap-1.5 items-center cursor-text transition-all"
                                        style="min-height:44px; padding:6px 10px;"
                                        :class="msConfig.notify_leave_request.users.open ? 'border-[var(--brand-600)] shadow-[0_0_0_3px_color-mix(in_srgb,var(--brand-600)_12%,transparent)]' : ''">
                                        
                                        <template x-for="val in notifConfig.notify_leave_request.users" :key="val">
                                            <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 text-[12px] font-semibold px-2.5 py-1 rounded-lg leading-none flex-shrink-0">
                                                <span x-text="msLabel('users', val)"></span>
                                                <button type="button" @click.stop="msRemove('notify_leave_request', 'users', val)"
                                                    class="ml-0.5 opacity-60 hover:opacity-100 hover:text-red-500 transition-all leading-none">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                </button>
                                            </span>
                                        </template>

                                        <input x-ref="leaveUsersSearch" type="text" x-model="msConfig.notify_leave_request.users.search"
                                            @focus="msConfig.notify_leave_request.users.open = true"
                                            @keydown.escape="msConfig.notify_leave_request.users.open = false; msConfig.notify_leave_request.users.search = ''"
                                            class="flex-1 min-w-[80px] bg-transparent outline-none text-[13.5px] text-gray-700 placeholder-gray-400 py-0.5 px-1"
                                            :placeholder="notifConfig.notify_leave_request.users.length === 0 ? 'Search users...' : 'Add more...'">

                                        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-150"
                                            :class="msConfig.notify_leave_request.users.open ? 'rotate-180' : ''"></i>
                                    </div>

                                    <div x-show="msConfig.notify_leave_request.users.open" x-cloak
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-1 scale-y-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
                                        class="absolute z-30 left-0 right-0 top-full mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl overflow-y-auto"
                                        style="max-height:200px;">
                                        <template x-for="opt in msFiltered('notify_leave_request', 'users')" :key="opt.value">
                                            <div @mousedown.prevent="msSelect('notify_leave_request', 'users', opt.value)"
                                                class="flex items-center justify-between px-4 py-2.5 cursor-pointer hover:bg-gray-50 transition-colors">
                                                <span class="text-[13.5px] text-gray-700 font-medium" x-text="opt.label"></span>
                                                <span x-show="opt.sub" x-text="opt.sub" class="text-[11px] text-gray-400 capitalize ml-3 flex-shrink-0"></span>
                                            </div>
                                        </template>
                                        <div x-show="msFiltered('notify_leave_request', 'users').length === 0" class="px-4 py-3 text-center">
                                            <span class="text-[12px] text-gray-400 italic" x-text="msConfig.notify_leave_request.users.search ? 'No matching users.' : 'All users selected.'"></span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1.5">Notified regardless of their role</p>
                            </div>

                        </div>
                    </div>

                <div class="flex justify-end">
                    @if(has_permission('settings.update_notifications'))
                    <button type="button" @click="saveNotifications()" :disabled="isSavingNotif" class="save-btn">
                        <i data-lucide="loader-2" x-show="isSavingNotif" x-cloak class="w-4 h-4 animate-spin"></i>
                        <i data-lucide="bell" x-show="!isSavingNotif" class="w-4 h-4"></i>
                        <span x-text="isSavingNotif ? 'Saving...' : 'Save Notification Settings'"></span>
                    </button>
                    @endif
                </div>
            </div>

            {{-- Bottom Save Bar — hidden on the Notifications tab (which has its own save button) --}}
            <div x-show="activeTab !== 'notifications'"
                class="border-t border-gray-100 bg-gray-50/80 px-8 py-4 flex items-center justify-between">
                <p class="text-xs text-gray-400 font-medium flex items-center gap-2">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    Last saved: <span
                        class="font-semibold text-gray-600">{{ get_setting('_last_saved') ?? 'Never' }}</span>
                </p>
                @if(has_permission('settings.update'))
                <button type="button" @click="submitForm()" :disabled="isSaving" class="save-btn">
                    <i data-lucide="loader-2" x-show="isSaving" x-cloak class="w-4 h-4 animate-spin"></i>
                    <i data-lucide="save" x-show="!isSaving" class="w-4 h-4"></i>
                    <span x-text="isSaving ? 'Saving...' : 'Save Changes'"></span>
                </button>
                @endif
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function settingsApp() {
            return {
                activeTab: 'company',
                isSaving: false,
                isClearingCache: false,
                isSavingNotif: false,

                notifConfig: {
                    notify_new_order: {
                        roles: @json(array_values($notificationConfig['notify_new_order']['roles'] ?? ['owner'])),
                        users: @json(array_map('strval', $notificationConfig['notify_new_order']['users'] ?? [])),
                    },
                    notify_leave_request: {
                        roles: @json(array_values($notificationConfig['notify_leave_request']['roles'] ?? ['owner'])),
                        users: @json(array_map('strval', $notificationConfig['notify_leave_request']['users'] ?? [])),
                    },
                },

                // 1. The shared options (Server Data)
                msOptions: {
                    roles: @json($roles->map(fn ($r) => ['value' => $r->slug, 'label' => $r->name])),
                    users: @json($users->map(fn ($u) => ['value' => (string) $u->id, 'label' => $u->name, 'sub' => $u->roles->first()?->name ?? ''])),
                },

                // 2. The UI state, separated per event
                msConfig: {
                    notify_new_order: {
                        roles: { search: '', open: false },
                        users: { search: '', open: false },
                    },
                    notify_leave_request: {
                        roles: { search: '', open: false },
                        users: { search: '', open: false },
                    }
                },

                tabs: [{
                        id: 'company',
                        label: 'Company',
                        icon: 'building-2'
                    },
                    {
                        id: 'branding',
                        label: 'Branding',
                        icon: 'palette'
                    },
                    {
                        id: 'billing',
                        label: 'Billing',
                        icon: 'landmark'
                    },
                    {
                        id: 'storefront',
                        label: 'Storefront',
                        icon: 'monitor'
                    },
                    {
                        id: 'system',
                        label: 'System',
                        icon: 'cpu'
                    },
                    {
                        id: 'notifications',
                        label: 'Notifications',
                        icon: 'bell'
                    },
                ],

                theme: {
                    primary: '{{ get_setting('primary_color', '#008a62') }}',
                    hover: '{{ get_setting('primary_hover_color', '#007050') }}',
                },

                previews: {
                    logo: null,
                    icon: null,
                    favicon: null,
                    signature: null,
                },

                previewFile(event, key) {
                    const file = event.target.files[0];
                    if (file) this.previews[key] = URL.createObjectURL(file);
                },

                livePreviewColors() {
                    const root = document.documentElement;
                    root.style.setProperty('--brand-500', this.theme.primary);
                    root.style.setProperty('--brand-600', this.theme.hover);
                    root.style.setProperty('--brand-700', this.theme.hover);
                },

                async submitForm() {
                    this.isSaving = true;
                    const form = document.getElementById('settings-form');
                    const data = new FormData(form);

                    // Append theme values explicitly (Alpine models don't auto-submit)
                    data.set('primary_color', this.theme.primary);
                    data.set('primary_hover_color', this.theme.hover);

                    try {
                        const res = await fetch("{{ route('admin.settings.update') }}", {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: data,
                        });
                        const result = await res.json();

                        if (result.success) {
                            BizAlert.toast(result.message || 'Settings saved!', 'success');
                            // Reload so layout picks up new colors/logo from DB
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            BizAlert.toast(result.message || 'Error saving settings.', 'error');
                        }
                    } catch (err) {
                        BizAlert.toast('Network error. Please try again.', 'error');
                    } finally {
                        this.isSaving = false;
                    }
                },

                // ── Multi-select helpers ──────────────────────────────────────────────
                // All operate on notifConfig.notify_new_order[key] directly,
                // so saveNotifications() needs zero changes.

                msFiltered(eventKey, field) {
                    const options = this.msOptions[field];
                    const search = this.msConfig[eventKey][field].search;
                    const selected = this.notifConfig[eventKey][field];
                    const q = search.toLowerCase();
                    return options.filter(o =>
                        ! selected.includes(o.value) &&
                        (o.label.toLowerCase().includes(q) || (o.sub ?? '').toLowerCase().includes(q))
                    );
                },

                msSelect(eventKey, field, value) {
                    if (! this.notifConfig[eventKey][field].includes(value)) {
                        this.notifConfig[eventKey][field].push(value);
                    }
                    this.msConfig[eventKey][field].search = ''; // Clear search on select
                },

                msRemove(eventKey, field, value) {
                    this.notifConfig[eventKey][field] = this.notifConfig[eventKey][field].filter(v => v !== value);
                },

                msLabel(field, value) {
                    return this.msOptions[field].find(o => o.value === value)?.label ?? value;
                },
                async saveNotifications() {
                    this.isSavingNotif = true;
                    const formData = new FormData();
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    this.notifConfig.notify_new_order.roles.forEach(r => formData.append('notify_new_order_roles[]', r));
                    this.notifConfig.notify_new_order.users.forEach(u => formData.append('notify_new_order_users[]', u));
                    this.notifConfig.notify_leave_request.roles.forEach(r => formData.append('notify_leave_request_roles[]', r));
                    this.notifConfig.notify_leave_request.users.forEach(u => formData.append('notify_leave_request_users[]', u));

                    try {
                        const res = await fetch("{{ route('admin.settings.notifications.update') }}", {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });
                        const result = await res.json();
                        BizAlert.toast(result.message || 'Saved!', result.success ? 'success' : 'error');
                    } catch {
                        BizAlert.toast('Network error. Please try again.', 'error');
                    } finally {
                        this.isSavingNotif = false;
                    }
                },

                async clearCache() {
                    this.isClearingCache = true;
                    try {
                        const res = await fetch("{{ route('admin.settings.clear-cache') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        });
                        const result = await res.json();
                        BizAlert.toast(result.message || 'Cache cleared!', result.success ? 'success' : 'error');
                    } catch {
                        BizAlert.toast('Failed to clear cache.', 'error');
                    } finally {
                        this.isClearingCache = false;
                    }
                },

                async confirmReset() {
                    const result = await BizAlert.confirm(
                        'Reset All Settings?',
                        'This will restore factory defaults. Your company data, invoices and transactions are NOT affected.',
                        'Yes, Reset Everything'
                    );

                    if (!result.isConfirmed) return;

                    BizAlert.loading('Resetting...');

                    try {
                        const res = await fetch("{{ route('admin.settings.reset') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                        const data = await res.json();

                        if (data.success) {
                            BizAlert.toast(data.message, 'success');
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            BizAlert.toast(data.message, 'error');
                        }
                    } catch {
                        BizAlert.toast('Reset failed. Please try again.', 'error');
                    }
                },

                init() {
                    // Re-render lucide icons when tab changes (dynamic :data-lucide in tabs)
                    this.$watch('activeTab', () => {
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    });
                }
            }
        }
    </script>
@endpush
