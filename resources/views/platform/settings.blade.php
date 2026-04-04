
@extends('layouts.app')

@section('title', 'System Settings - Qlinkon')
@section('header', 'System Settings')

@section('styles')
    <style>
        /* Custom Toggle Switch Styles */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #0f766e; /* brand-500 */
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #0f766e;
        }
    </style>
@endsection

@section('content')
    <div class="pb-10" x-data="{ tab: 'general' }">
        
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Platform Configuration</h1>
                <p class="text-sm text-gray-500 mt-1">Manage global application settings, billing gateways, and external APIs.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" form="settings-form" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Save All Settings
                </button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            
            {{-- Sidebar Navigation Tabs --}}
            <aside class="w-full lg:w-64 shrink-0">
                <nav class="flex flex-row lg:flex-col gap-2 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0 custom-scrollbar">
                    <button @click="tab = 'general'" :class="tab === 'general' ? 'bg-white text-brand-600 shadow-sm border-brand-200' : 'text-gray-600 hover:bg-gray-200 border-transparent'" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold border transition-all whitespace-nowrap lg:whitespace-normal text-left">
                        <i data-lucide="sliders" class="w-5 h-5"></i> General
                    </button>
                    <button @click="tab = 'branding'" :class="tab === 'branding' ? 'bg-white text-brand-600 shadow-sm border-brand-200' : 'text-gray-600 hover:bg-gray-200 border-transparent'" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold border transition-all whitespace-nowrap lg:whitespace-normal text-left">
                        <i data-lucide="palette" class="w-5 h-5"></i> Branding
                    </button>
                    <button @click="tab = 'security'" :class="tab === 'security' ? 'bg-white text-brand-600 shadow-sm border-brand-200' : 'text-gray-600 hover:bg-gray-200 border-transparent'" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold border transition-all whitespace-nowrap lg:whitespace-normal text-left">
                        <i data-lucide="shield-check" class="w-5 h-5"></i> Auth & Security
                    </button>
                    <button @click="tab = 'billing'" :class="tab === 'billing' ? 'bg-white text-brand-600 shadow-sm border-brand-200' : 'text-gray-600 hover:bg-gray-200 border-transparent'" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold border transition-all whitespace-nowrap lg:whitespace-normal text-left">
                        <i data-lucide="credit-card" class="w-5 h-5"></i> Billing Gateways
                    </button>
                    <button @click="tab = 'mail'" :class="tab === 'mail' ? 'bg-white text-brand-600 shadow-sm border-brand-200' : 'text-gray-600 hover:bg-gray-200 border-transparent'" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold border transition-all whitespace-nowrap lg:whitespace-normal text-left">
                        <i data-lucide="mail" class="w-5 h-5"></i> SMTP & Mail
                    </button>
                </nav>
            </aside>

            {{-- Form Content Area --}}
            <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <form id="settings-form" action="#" method="POST" enctype="multipart/form-data">
                    
                    {{-- 1. GENERAL TAB --}}
                    <div x-show="tab === 'general'" x-cloak class="p-6 space-y-8">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-lg font-bold text-gray-800">General Information</h2>
                            <p class="text-xs text-gray-500">Basic details about your SaaS platform.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Application Name</label>
                                <input type="text" name="app_name" value="Qlinkon" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Support Email</label>
                                <input type="email" name="support_email" value="support@qlinkon.com" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Support Phone</label>
                                <input type="text" name="support_phone" value="+91 9876543210" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Default Timezone</label>
                                <select name="timezone" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none bg-white">
                                    <option value="Asia/Kolkata" selected>Asia/Kolkata (IST)</option>
                                    <option value="UTC">UTC</option>
                                </select>
                            </div>
                        </div>

                        <div class="p-5 bg-orange-50 rounded-xl border border-orange-100 flex items-center justify-between mt-6">
                            <div>
                                <h4 class="font-bold text-orange-800 text-sm">Platform Maintenance Mode</h4>
                                <p class="text-xs text-orange-600 mt-0.5">Take the entire SaaS offline for updates. Super Admins can still access the system.</p>
                            </div>
                            <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="maintenance_mode" id="maintenance_toggle" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-all duration-300 z-10 top-0 left-0"/>
                                <label for="maintenance_toggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer transition-colors duration-300"></label>
                            </div>
                        </div>
                    </div>

                    {{-- 2. BRANDING TAB --}}
                    <div x-show="tab === 'branding'" x-cloak class="p-6 space-y-8">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-lg font-bold text-gray-800">Visual Branding</h2>
                            <p class="text-xs text-gray-500">Customize the look and feel of the platform interface.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="block text-[12px] font-bold text-gray-700">Light Mode Logo</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:bg-gray-50 transition-colors">
                                    <i data-lucide="image" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                                    <span class="text-xs font-medium text-brand-600">Click to upload</span>
                                    <input type="file" class="hidden">
                                </div>
                            </div>
                            <div class="space-y-4">
                                <label class="block text-[12px] font-bold text-gray-700">Favicon</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:bg-gray-50 transition-colors">
                                    <i data-lucide="square-dashed" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                                    <span class="text-xs font-medium text-brand-600">Click to upload</span>
                                    <input type="file" class="hidden">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Primary Brand Color (Hex)</label>
                                <div class="flex gap-2">
                                    <input type="color" value="#0f766e" class="h-10 w-12 rounded cursor-pointer border-0 p-0">
                                    <input type="text" name="primary_color" value="#0f766e" class="flex-1 border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:border-brand-500 outline-none uppercase font-mono">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. SECURITY TAB --}}
                    <div x-show="tab === 'security'" x-cloak class="p-6 space-y-8">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-lg font-bold text-gray-800">Authentication & Security</h2>
                            <p class="text-xs text-gray-500">Control how users register and secure their accounts.</p>
                        </div>
                        
                        <div class="space-y-5">
                            <label class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Allow Public Registration</p>
                                    <p class="text-xs text-gray-500 mt-0.5">If disabled, new companies can only be added by Super Admins via invitations.</p>
                                </div>
                                <div class="relative inline-block w-12 align-middle select-none">
                                    <input type="checkbox" checked class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer z-10 top-0 left-0"/>
                                    <label class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </label>

                            <label class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Force Email Verification</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Users must verify their email address before accessing the dashboard.</p>
                                </div>
                                <div class="relative inline-block w-12 align-middle select-none">
                                    <input type="checkbox" checked class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer z-10 top-0 left-0"/>
                                    <label class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- 4. BILLING TAB --}}
                    <div x-show="tab === 'billing'" x-cloak class="p-6 space-y-8">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-lg font-bold text-gray-800">Billing & Gateways</h2>
                            <p class="text-xs text-gray-500">Configure how you collect SaaS subscription payments.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Platform Currency</label>
                                <select class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white">
                                    <option value="INR" selected>INR - Indian Rupee (₹)</option>
                                    <option value="USD">USD - US Dollar ($)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Active Payment Gateway</label>
                                <select class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white">
                                    <option value="razorpay" selected>Razorpay</option>
                                    <option value="stripe">Stripe</option>
                                    <option value="offline">Offline / Manual Bank Transfer</option>
                                </select>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 space-y-5">
                            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2"><i data-lucide="key" class="w-4 h-4"></i> Razorpay API Keys</h3>
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Key ID</label>
                                <input type="text" placeholder="rzp_live_xxxxxxxxxxxx" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Key Secret</label>
                                <input type="password" placeholder="••••••••••••••••••••••••" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm outline-none font-mono">
                            </div>
                        </div>
                    </div>

                    {{-- 5. MAIL TAB --}}
                    <div x-show="tab === 'mail'" x-cloak class="p-6 space-y-8">
                        <div class="border-b border-gray-100 pb-4">
                            <h2 class="text-lg font-bold text-gray-800">SMTP & Mail</h2>
                            <p class="text-xs text-gray-500">Configure email delivery for invoices, resets, and alerts.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Mail Driver</label>
                                <select class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white">
                                    <option value="smtp" selected>SMTP</option>
                                    <option value="mailgun">Mailgun</option>
                                    <option value="ses">Amazon SES</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Mail Host</label>
                                <input type="text" placeholder="smtp.mailtrap.io" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Mail Port</label>
                                <input type="text" placeholder="587" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Encryption</label>
                                <select class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white">
                                    <option value="tls" selected>TLS</option>
                                    <option value="ssl">SSL</option>
                                </select>
                            </div>
                            <div class="md:col-span-2 grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Username</label>
                                    <input type="text" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Password</label>
                                    <input type="password" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection