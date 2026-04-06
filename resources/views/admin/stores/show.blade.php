@extends('layouts.admin')

@section('title', $store->name . ' - Store Details')

@section('header-title')
    <h1 class="text-xs sm:text-sm font-bold text-gray-400 uppercase tracking-widest">Stores / View Branch</h1>
@endsection

@section('content')
    <div class="w-full mx-auto space-y-4 sm:space-y-6 pb-10">

        {{-- 🌟 HEADER & ACTIONS --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl border border-gray-200 overflow-hidden bg-white shadow-sm shrink-0">
                    <img src="{{ $store->logo_url }}" alt="Logo" class="w-full h-full object-cover">
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg sm:text-2xl font-black text-gray-800 tracking-tight">{{ $store->name }}</h2>
                        @if ($store->is_active)
                            <span class="bg-[#dcfce7] text-[#16a34a] px-2 py-0.5 rounded-md font-bold text-[9px] sm:text-[10px] uppercase tracking-wider">Active</span>
                        @else
                            <span class="bg-gray-100 text-gray-400 px-2 py-0.5 rounded-md font-bold text-[9px] sm:text-[10px] uppercase tracking-wider">Inactive</span>
                        @endif
                    </div>
                    <p class="text-[11px] sm:text-xs text-gray-500 font-medium mt-0.5 flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3 h-3"></i> Registered on {{ $store->created_at->format('M d, Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                <a href="{{ route('admin.stores.index') }}"
                    class="flex-1 sm:flex-none bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold transition-colors shadow-sm text-center flex items-center justify-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                </a>
                <a href="{{ route('admin.stores.edit', $store->id) }}"
                    class="flex-1 sm:flex-none bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md active:scale-95 text-center flex items-center justify-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4"></i> Edit Branch
                </a>
            </div>
        </div>

        {{-- 🌟 SECTION 1: IDENTITY & LOCATION (Always Visible) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            
            {{-- Contact Info --}}
            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-sm sm:text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <i data-lucide="phone-call" class="w-4 h-4 text-brand-500"></i> Contact Information
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Official Email</span>
                        <span class="block text-[13px] sm:text-sm font-bold text-gray-800">{{ $store->email ?? 'Not provided' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Contact Phone</span>
                        <span class="block text-[13px] sm:text-sm font-bold text-gray-800">{{ $store->phone ?? 'Not provided' }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Unique Branch Slug</span>
                        <span class="inline-block bg-gray-50 border border-gray-200 text-gray-600 px-2 py-1 rounded text-xs font-mono">{{ $store->slug }}</span>
                    </div>
                </div>
            </div>

            {{-- Location Info --}}
            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-sm sm:text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-brand-500"></i> Location Details
                </h3>
                <div class="grid grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-5">
                    <div>
                        <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">City</span>
                        <span class="block text-[13px] sm:text-sm font-bold text-gray-800">{{ $store->city ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">State</span>
                        <span class="block text-[13px] sm:text-sm font-bold text-gray-800">{{ $store->state->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Pincode / Zip</span>
                        <span class="block text-[13px] sm:text-sm font-bold text-gray-800">{{ $store->zip_code ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Country</span>
                        <span class="block text-[13px] sm:text-sm font-bold text-gray-800">{{ $store->country ?? 'India' }}</span>
                    </div>
                </div>
                <div>
                    <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Full Street Address</span>
                    <p class="text-[13px] sm:text-sm font-bold text-gray-800 bg-gray-50 p-3 rounded-xl border border-gray-100 leading-relaxed">
                        {{ $store->address ?? 'No detailed address provided.' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- 🌟 SECTION 2: MULTI-STORE BILLING OVERRIDES --}}
        @if($isMultiStore)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                
                {{-- Billing & Compliance --}}
                <div class="lg:col-span-2 bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-sm sm:text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-brand-500"></i> Billing & Compliance
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 sm:gap-6">
                        <div>
                            <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">GSTIN</span>
                            <span class="block text-[13px] sm:text-sm font-bold font-mono text-gray-800">{{ $store->getRawOriginal('gst_number') ?: 'Using Global' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Store UPI ID</span>
                            <span class="block text-[13px] sm:text-sm font-bold text-gray-800">{{ $store->getRawOriginal('upi_id') ?: 'Using Global' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Currency</span>
                            <span class="block text-[13px] sm:text-sm font-bold text-gray-800">{{ $store->currency }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Invoice Prefix</span>
                            <span class="block text-[13px] sm:text-sm font-bold font-mono text-gray-800">{{ $store->getRawOriginal('invoice_prefix') ?: 'Using Global (' . $store->invoice_prefix . ')' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Quotation Prefix</span>
                            <span class="block text-[13px] sm:text-sm font-bold font-mono text-gray-800">{{ $store->getRawOriginal('quotation_prefix') ?: 'Using Global (' . $store->quotation_prefix . ')' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Bank Account Details --}}
                <div class="lg:col-span-1 bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-sm sm:text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                        <i data-lucide="building-2" class="w-4 h-4 text-brand-500"></i> Bank Details
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Bank Name</span>
                            <span class="block text-[13px] sm:text-sm font-bold text-gray-800">{{ $store->getRawOriginal('bank_name') ?: 'Using Global Settings' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Account Number</span>
                            <span class="block text-[13px] sm:text-sm font-bold font-mono text-gray-800">{{ $store->getRawOriginal('account_number') ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">IFSC & Branch</span>
                            <span class="block text-[13px] sm:text-sm font-bold text-gray-800">{{ $store->getRawOriginal('ifsc_code') ?: '-' }} • {{ $store->getRawOriginal('branch_name') ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Invoice Content & Signature --}}
                <div class="lg:col-span-3 bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-sm sm:text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-brand-500"></i> Invoice Layout & Signatures
                    </h3>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-5">
                            <div>
                                <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Footer Note</span>
                                <div class="text-[12px] sm:text-[13px] text-gray-700 bg-gray-50 p-3 rounded-xl border border-gray-100 italic">
                                    {{ $store->getRawOriginal('invoice_footer_note') ?: 'Using global footer note. Set locally to override.' }}
                                </div>
                            </div>
                            <div>
                                <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Terms & Conditions</span>
                                <div class="text-[12px] sm:text-[13px] text-gray-700 bg-gray-50 p-3 rounded-xl border border-gray-100 whitespace-pre-line">
                                    {{ $store->getRawOriginal('invoice_terms') ?: 'Using global terms and conditions. Set locally to override.' }}
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-1 border-t lg:border-t-0 lg:border-l border-gray-100 pt-5 lg:pt-0 lg:pl-6 flex flex-col items-start lg:items-center justify-center">
                            <span class="block text-[10px] sm:text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-3">Authorized Signature</span>
                            @if ($store->signature_url)
                                <div class="w-48 h-20 border border-gray-200 rounded-xl p-2 bg-white shadow-sm flex items-center justify-center">
                                    <img src="{{ $store->signature_url }}" alt="Signature" class="max-w-full max-h-full object-contain">
                                </div>
                                @if(!$store->getRawOriginal('signature'))
                                    <span class="text-[10px] text-gray-400 mt-2 font-medium">(Falling back to Global Signature)</span>
                                @endif
                            @else
                                <div class="w-48 h-20 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50 flex flex-col items-center justify-center text-gray-400">
                                    <i data-lucide="pen-tool" class="w-5 h-5 mb-1"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">No Signature</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        @else
            {{-- Single Store Notice --}}
            <div class="bg-blue-50/50 border border-blue-100 p-5 rounded-2xl flex gap-4 items-start">
                <div class="bg-blue-100 p-2 rounded-full shrink-0 mt-0.5">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-blue-900">Billing & Configuration Managed Globally</h4>
                    <p class="text-xs text-blue-700 mt-1 leading-relaxed max-w-3xl">
                        Because you are currently on a single-store subscription plan, your Bank Details, GST, and Invoice Settings are securely managed at the Global Company level to ensure consistency.
                    </p>
                </div>
            </div>
        @endif

    </div>
@endsection