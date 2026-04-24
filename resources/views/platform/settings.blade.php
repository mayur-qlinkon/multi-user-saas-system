@extends('layouts.platform')

@section('title', 'System Settings')
@section('header', 'System Settings')

@section('styles')
    <style>
        .toggle-checkbox:checked { right: 0; border-color: #0f766e; }
        .toggle-checkbox:checked + .toggle-label { background-color: #0f766e; }
    </style>
@endsection

@section('content')
    <div class="pb-10" x-data="{ tab: 'general' }">

        {{-- Page Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Platform Configuration</h1>
                <p class="text-sm text-gray-500 mt-1">Manage global application settings, SMTP, branding, and security.</p>
            </div>
            <button type="submit" form="settings-form"
                class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2 self-start">
                <i data-lucide="save" class="w-4 h-4"></i> Save Settings
            </button>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->has('error'))
            <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                {{ $errors->first('error') }}
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-6">

            {{-- Sidebar Tabs --}}
            <aside class="w-full lg:w-56 shrink-0">
                <nav class="flex flex-row lg:flex-col gap-2 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0">
                    @foreach ([
                        ['key' => 'general',  'icon' => 'sliders',       'label' => 'General'],
                        ['key' => 'mail',     'icon' => 'mail',          'label' => 'SMTP & Mail'],
                        ['key' => 'security', 'icon' => 'shield-check',  'label' => 'Security'],
                        ['key' => 'system',   'icon' => 'monitor',       'label' => 'System & Branding'],
                    ] as $t)
                        <button
                            @click="tab = '{{ $t['key'] }}'"
                            :class="tab === '{{ $t['key'] }}' ? 'bg-white text-brand-600 shadow-sm border-brand-200' : 'text-gray-600 hover:bg-gray-100 border-transparent'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold border transition-all whitespace-nowrap text-left w-full">
                            <i data-lucide="{{ $t['icon'] }}" class="w-4 h-4 shrink-0"></i>
                            {{ $t['label'] }}
                        </button>
                    @endforeach
                </nav>
            </aside>

            {{-- Form --}}
            <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <form id="settings-form"
                    action="{{ route('platform.system.update') }}"
                    method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- ── 1. GENERAL ── --}}
                    <div x-show="tab === 'general'" x-cloak class="p-6 space-y-6">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-base font-bold text-gray-800">General Information</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Basic details shown across the platform.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Application Name</label>
                                <input type="text" name="app_name"
                                    value="{{ $flatSettings['app_name'] ?? 'Qlinkon' }}"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Support Email</label>
                                <input type="email" name="support_email"
                                    value="{{ $flatSettings['support_email'] ?? '' }}"
                                    placeholder="support@example.com"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Support Phone</label>
                                <input type="text" name="support_phone"
                                    value="{{ $flatSettings['support_phone'] ?? '' }}"
                                    placeholder="+91 9876543210"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Default Timezone</label>
                                <select name="timezone"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none bg-white">
                                    @foreach (['Asia/Kolkata' => 'Asia/Kolkata (IST)', 'UTC' => 'UTC', 'Asia/Dubai' => 'Asia/Dubai (GST)', 'America/New_York' => 'America/New_York (EST)'] as $tz => $label)
                                        <option value="{{ $tz }}" @selected(($flatSettings['timezone'] ?? 'Asia/Kolkata') === $tz)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Maintenance Mode --}}
                        <div class="p-4 bg-orange-50 rounded-xl border border-orange-100 flex items-center justify-between">
                            <div>
                                <p class="font-bold text-orange-800 text-sm">Maintenance Mode</p>
                                <p class="text-xs text-orange-600 mt-0.5">Takes the public landing page offline. Admin and platform routes are unaffected.</p>
                            </div>
                            <div class="relative inline-block w-12 align-middle select-none shrink-0">
                                <input type="checkbox" name="maintenance_mode" id="maintenance_toggle"
                                    class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer z-10 top-0 left-0"
                                    @checked(filter_var($flatSettings['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN))>
                                <label for="maintenance_toggle"
                                    class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                            </div>
                        </div>
                    </div>

                    {{-- ── 2. SMTP & MAIL ── --}}
                    <div x-show="tab === 'mail'" x-cloak class="p-6 space-y-6">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-base font-bold text-gray-800">SMTP & Mail</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Used by EmailService for all outbound emails. Sender identity is always enforced from here.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Mail Driver</label>
                                <select name="mail_driver"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                                    @foreach (['smtp' => 'SMTP', 'mailgun' => 'Mailgun', 'ses' => 'Amazon SES', 'log' => 'Log (Testing)'] as $val => $label)
                                        <option value="{{ $val }}" @selected(($flatSettings['mail_driver'] ?? 'smtp') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Encryption</label>
                                <select name="mail_encryption"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                                    @foreach (['tls' => 'TLS (Recommended)', 'ssl' => 'SSL', '' => 'None'] as $val => $label)
                                        <option value="{{ $val }}" @selected(($flatSettings['mail_encryption'] ?? 'tls') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">SMTP Host</label>
                                <input type="text" name="mail_host"
                                    value="{{ $flatSettings['mail_host'] ?? '' }}"
                                    placeholder="smtp.mailtrap.io"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">SMTP Port</label>
                                <input type="number" name="mail_port"
                                    value="{{ $flatSettings['mail_port'] ?? 587 }}"
                                    placeholder="587"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Username</label>
                                <input type="text" name="mail_username"
                                    value="{{ $flatSettings['mail_username'] ?? '' }}"
                                    placeholder="your@email.com"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Password
                                    @if (!empty($flatSettings['mail_password']))
                                        <span class="ml-1 text-green-600 font-normal">(saved — leave blank to keep)</span>
                                    @endif
                                </label>
                                <input type="password" name="mail_password"
                                    placeholder="Leave blank to keep existing password"
                                    autocomplete="new-password"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">From Email Address</label>
                                <input type="email" name="mail_from_email"
                                    value="{{ $flatSettings['mail_from_email'] ?? '' }}"
                                    placeholder="no-reply@example.com"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">From Name</label>
                                <input type="text" name="mail_from_name"
                                    value="{{ $flatSettings['mail_from_name'] ?? '' }}"
                                    placeholder="Qlinkon Platform"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                            </div>
                        </div>

                        <p class="text-xs text-gray-400 flex items-center gap-1.5">
                            <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                            These values override <code class="font-mono bg-gray-100 px-1 rounded">.env</code> mail settings at runtime. Tenant emails always use these credentials.
                        </p>
                    </div>

                    {{-- ── 3. SECURITY ── --}}
                    <div x-show="tab === 'security'" x-cloak class="p-6 space-y-6">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-base font-bold text-gray-800">Authentication & Security</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Control registration, verification, and token behaviour.</p>
                        </div>

                        <div class="space-y-4">
                            @foreach ([
                                ['key' => 'allow_public_registration', 'label' => 'Allow Public Registration',  'desc' => 'New companies can self-register. If disabled, only Super Admins can create companies.'],
                                ['key' => 'force_email_verification',  'label' => 'Force Email Verification',   'desc' => 'Users must verify their email address before accessing the dashboard.'],
                                ['key' => 'enable_2fa',                'label' => 'Enable Two-Factor Auth (2FA)', 'desc' => 'Require TOTP-based 2FA for all accounts.'],
                            ] as $toggle)
                                <label class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">{{ $toggle['label'] }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $toggle['desc'] }}</p>
                                    </div>
                                    <div class="relative inline-block w-12 align-middle select-none ml-4 shrink-0">
                                        <input type="checkbox" name="{{ $toggle['key'] }}" id="{{ $toggle['key'] }}"
                                            class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer z-10 top-0 left-0"
                                            @checked(filter_var($flatSettings[$toggle['key']] ?? false, FILTER_VALIDATE_BOOLEAN))>
                                        <label for="{{ $toggle['key'] }}"
                                            class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Password Reset Expiry (minutes)</label>
                                <input type="number" name="password_reset_expiry_minutes"
                                    value="{{ $flatSettings['password_reset_expiry_minutes'] ?? 60 }}"
                                    min="5" max="1440"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                                <p class="text-xs text-gray-400 mt-1">How long a reset link stays valid. Default: 60 minutes.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">OTP Length</label>
                                <input type="number" name="otp_length"
                                    value="{{ $flatSettings['otp_length'] ?? 6 }}"
                                    min="4" max="8"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                                <p class="text-xs text-gray-400 mt-1">Number of digits in OTPs sent for verification. Default: 6.</p>
                            </div>
                        </div>
                    </div>

                    {{-- ── 4. SYSTEM & BRANDING ── --}}
                    <div x-show="tab === 'system'" x-cloak class="p-6 space-y-6">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-base font-bold text-gray-800">System & Branding</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Upload the platform logo and favicon shown on the landing page.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- App Logo --}}
                            <div x-data="{ preview: '{{ !empty($flatSettings['app_logo']) ? asset('storage/'.$flatSettings['app_logo']) : '' }}' }">
                                <label class="block text-xs font-bold text-gray-700 mb-2">App Logo</label>
                                @if (!empty($flatSettings['app_logo']))
                                    <img :src="preview" x-show="preview"
                                        class="h-12 mb-3 object-contain rounded border border-gray-200 p-1 bg-gray-50" alt="Current Logo">
                                @else
                                    <img :src="preview" x-show="preview"
                                        class="h-12 mb-3 object-contain rounded border border-gray-200 p-1 bg-gray-50" alt="Preview" style="display:none">
                                @endif
                                <label class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:bg-gray-50 transition-colors cursor-pointer block">
                                    <i data-lucide="image" class="w-7 h-7 text-gray-400 mx-auto mb-2"></i>
                                    <span class="text-xs font-semibold text-brand-600">Click to upload</span>
                                    <span class="block text-xs text-gray-400 mt-1">PNG, JPG, SVG · Recommended: 200×60px</span>
                                    <input type="file" name="app_logo" accept="image/*" class="hidden"
                                        @change="preview = URL.createObjectURL($event.target.files[0])">
                                </label>
                            </div>

                            {{-- Favicon --}}
                            <div x-data="{ preview: '{{ !empty($flatSettings['app_favicon']) ? asset('storage/'.$flatSettings['app_favicon']) : '' }}' }">
                                <label class="block text-xs font-bold text-gray-700 mb-2">Favicon</label>
                                @if (!empty($flatSettings['app_favicon']))
                                    <img :src="preview" x-show="preview"
                                        class="h-12 mb-3 object-contain rounded border border-gray-200 p-1 bg-gray-50" alt="Current Favicon">
                                @else
                                    <img :src="preview" x-show="preview"
                                        class="h-12 mb-3 object-contain rounded border border-gray-200 p-1 bg-gray-50" alt="Preview" style="display:none">
                                @endif
                                <label class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:bg-gray-50 transition-colors cursor-pointer block">
                                    <i data-lucide="square-dashed" class="w-7 h-7 text-gray-400 mx-auto mb-2"></i>
                                    <span class="text-xs font-semibold text-brand-600">Click to upload</span>
                                    <span class="block text-xs text-gray-400 mt-1">ICO, PNG · Recommended: 32×32px</span>
                                    <input type="file" name="app_favicon" accept="image/*,.ico" class="hidden"
                                        @change="preview = URL.createObjectURL($event.target.files[0])">
                                </label>
                            </div>
                        </div>

                        <p class="text-xs text-gray-400 flex items-center gap-1.5">
                            <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                            Images are automatically converted to WebP for optimal loading speed.
                        </p>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
