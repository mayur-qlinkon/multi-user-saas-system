@extends('layouts.admin')

@section('title', 'Edit Quotation - ' . $quotation->quotation_number)

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Edit Quotation</h1>
@endsection

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }

        body.item-modal-open {
            overflow: hidden;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
    </style>
@endpush

@section('content')
    {{-- 🌟 Pass the existing quotation into Alpine --}}
    <div class="pb-20" x-data="quotationForm(@js($units ?? []), @js($companyState ?? ''), @js($clients ?? []), @js($quotation))">

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-[1.5rem] font-bold text-[#212538] tracking-tight mb-1">
                    Edit Proposal: <span class="text-[#108c2a]">{{ $quotation->quotation_number }}</span>
                </h1>
            </div>
            {{-- UI Fix: Stack on mobile, side-by-side on md+ --}}
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto mt-2 md:mt-0 justify-end">
                <a href="{{ route('admin.quotations.index') }}"
                    class="text-sm font-bold text-gray-500 hover:text-gray-800 transition-colors order-3 sm:order-1 px-2 py-2 sm:py-0 text-center w-full sm:w-auto">
                    Cancel
                </a>
                <button type="submit" form="mainQuotationForm" name="status" value="draft"
                    class="w-full sm:w-auto bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-3 sm:py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center justify-center gap-2 order-2 sm:order-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Save as Draft
                </button>
                <button type="submit" form="mainQuotationForm" name="status" value="sent"
                    class="w-full sm:w-auto bg-[#108c2a] hover:bg-[#0c6b1f] text-white px-6 py-3 sm:py-2.5 rounded-lg text-sm font-bold transition-all shadow-md flex items-center justify-center gap-2 order-1 sm:order-3">
                    <i data-lucide="send" class="w-4 h-4"></i> Save &amp; Mark as Sent
                </button>
            </div>
        </div>

        @if ($quotation->status === 'draft')
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 mb-6 flex items-start gap-3">
                <i data-lucide="file-edit" class="w-5 h-5 mt-0.5 flex-shrink-0"></i>
                <div class="text-sm">
                    <div class="font-bold">This quotation is a draft</div>
                    <div class="text-xs">Keep editing, or use <strong>Save &amp; Mark as Sent</strong> to record that it was shared with the customer.</div>
                </div>
            </div>
        @endif

        {{-- Validation Error Display --}}
        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200 shadow-sm">
                <div class="font-bold mb-2 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix the following errors:
                </div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    if (typeof Swal !== 'undefined') Swal.close();
                });
            </script>
        @endif

        {{-- 🌟 Note the action URL and the @method('PUT') --}}
        <form id="mainQuotationForm" action="{{ route('admin.quotations.update', $quotation->id) }}" method="POST"
            @submit="BizAlert.loading('Saving Quotation...')">
            @csrf
            @method('PUT')

            {{-- 1. TRANSACTION HEADER --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">

                    {{-- Customer Selection --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Customer <span
                                class="text-red-500">*</span></label>
                        <div class="flex gap-2 items-start">
                            <div class="relative flex-1">
                                {{-- Searchable Input & Button Row --}}
                                <div class="flex gap-1 w-full relative" @click.away="isClientDropdownOpen = false">
                                    
                                    {{-- Visible Search Input --}}
                                    <div class="relative flex-1">
                                        <input type="text" x-model="clientSearchTerm" 
                                            @focus="isClientDropdownOpen = true"
                                            @input="isClientDropdownOpen = true; formData.customer_id = ''"
                                            placeholder="Search customer by name or phone..."
                                            class="w-full border border-gray-300 rounded-l px-3 py-2.5 text-sm focus:border-brand-500 outline-none bg-white font-bold text-gray-700">
                                        <i data-lucide="chevron-down" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                                    </div>

                                    {{-- Hidden Input for Laravel Form Submission --}}
                                    <input type="hidden" name="customer_id" x-model="formData.customer_id" id="customerSelect">

                                    {{-- Quick Add Button --}}
                                    <button type="button" @click="isClientModalOpen = true"
                                        class="bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-600 px-3 rounded-r transition-colors flex items-center justify-center shrink-0"
                                        title="Quick Add Client">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>

                                    {{-- Floating Dropdown List --}}
                                    <ul x-show="isClientDropdownOpen" x-cloak x-transition
                                        class="absolute z-[60] w-[calc(100%-2.5rem)] bg-white border border-gray-200 rounded-lg shadow-2xl mt-1 max-h-60 overflow-y-auto top-full left-0 custom-scrollbar">
                                        
                                        <li x-show="filteredClientList.length === 0" class="px-4 py-4 text-sm text-gray-500 text-center font-medium">
                                            No matching customers found.
                                        </li>

                                        <template x-for="client in filteredClientList" :key="client.id">
                                            <li @click="selectCustomer(client)"
                                                class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                                <div class="font-bold text-[13px] text-gray-800" x-text="client.name"></div>
                                                <div class="text-[11px] text-gray-500 mt-0.5 flex flex-wrap items-center gap-2">
                                                    <span x-show="client.phone" x-text="'📞 ' + client.phone"></span>
                                                    <span x-show="client.gst_number || client.gstin" class="bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200 text-[9px] font-bold text-gray-600" x-text="'GST: ' + (client.gst_number || client.gstin)"></span>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                <div x-show="formData.customer_gstin" x-cloak
                                    class="mt-1.5 pl-1 text-[11px] font-bold text-gray-500">
                                    GSTIN: <span class="text-blue-600" x-text="formData.customer_gstin"></span>
                                </div>
                            </div>
                            <button type="button" @click="toggleGuestMode()"
                                :class="isGuest ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600'"
                                class="px-4 py-4 rounded text-xs font-bold uppercase tracking-widest transition-colors border border-transparent">
                                <span x-text="isGuest ? 'Guest Active' : 'Guest'"></span>
                            </button>
                        </div>
                        <div x-show="isGuest" x-cloak class="mt-3">
                            <input type="text" name="customer_name" x-model="formData.customer_name"
                                placeholder="Enter Guest/Prospect Name..."
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-brand-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Quote
                            Date</label>
                        <input type="date" name="quotation_date" x-model="formData.quotation_date" required
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Valid
                            Until</label>
                        <input type="date" name="valid_until" x-model="formData.valid_until"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">GST
                            Treatment</label>
                        <select name="gst_treatment" x-model="formData.gst_treatment"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none bg-white font-medium">
                            <option value="unregistered">Unregistered Business (B2C)</option>
                            <option value="registered">Registered Business (B2B)</option>
                            <option value="composition">Composition</option>
                            <option value="overseas">Overseas (Export)</option>
                            <option value="sez">SEZ</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Store
                            Branch</label>
                        <select name="store_id" required x-model="formData.store_id" @change="updateStoreData($event); autoSelectWarehouse()"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none bg-white">
                            @foreach ($stores ?? [] as $store)
                                <option value="{{ $store->id }}" data-state="{{ $store->state->name ?? $companyState }}">
                                    {{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Search Warehouse
                            context</label>
                        <select name="warehouse_id" required x-model="formData.warehouse_id"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none bg-white">
                            <option value="">-- Select Warehouse --</option>
                            @foreach ($warehouses ?? [] as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-[-1px]">
                        <x-state-select name="supply_state" label="Place of Supply (State)" x-model="formData.supply_state"
                            @change="calculate()" class="!bg-white !border-gray-300 !font-bold !text-[#108c2a]" />
                    </div>
                </div>
            </div>

            {{-- 2. PRODUCT SEARCH & LINE ITEMS --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 flex flex-col">
                <div
                    class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h2 class="text-lg font-bold text-gray-800 tracking-tight">Proposed Items</h2>

                    <div class="relative w-full sm:w-96" x-data="{ showResults: false }">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        <input type="text" x-model="globalSearch" @input.debounce.300ms="fetchGlobalSkus()"
                            @focus="showResults = true" @click.away="showResults = false"
                            placeholder="Type product name or scan barcode..."
                            class="w-full border border-gray-300 rounded shadow-sm pl-9 pr-4 py-2.5 text-sm focus:border-brand-500 outline-none bg-white">

                        <ul x-show="showResults && globalSearch.length > 1" x-cloak
                            class="absolute z-[60] w-full bg-white border border-gray-200 rounded-lg shadow-2xl mt-1 max-h-60 overflow-y-auto top-full left-0">

                            <li x-show="isSearching"
                                class="px-4 py-3 text-xs text-gray-500 text-center font-medium flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-[#108c2a]" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Searching inventory...
                            </li>

                            <li x-show="!isSearching && globalSearchResults.length === 0" class="px-4 py-4 text-center">
                                <span class="block text-sm font-bold text-gray-700">No products found</span>
                                <span class="block text-[11px] text-gray-400 mt-0.5">Try a different name.</span>
                            </li>

                            <template x-for="result in globalSearchResults" :key="result.product_sku_id">
                                <li @click="addSkuToTable(result); showResults = false"
                                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="text-[13px] font-bold text-gray-800" x-text="result.product_name">
                                            </div>
                                            <div class="text-[10px] text-gray-400 font-mono mt-0.5"
                                                x-text="result.sku_code"></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[12px] font-black text-[#108c2a]"
                                                x-text="result.price ? '₹' + parseFloat(result.price).toFixed(2) : '₹0.00'">
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div class="overflow-x-auto min-h-[200px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-4 min-w-[250px]">PRODUCT DETAILS</th>
                                <th class="px-4 py-4 min-w-[100px] text-center">HSN/SAC</th>
                                <th class="px-4 py-4 min-w-[140px] text-right">UNIT PRICE</th>
                                <th class="px-4 py-4 min-w-[140px] text-center">QTY</th>
                                <th class="px-4 py-4 min-w-[100px] text-right">TAX %</th>
                                <th class="px-5 py-4 min-w-[140px] text-right">LINE TOTAL</th>
                                <th class="px-4 py-4 w-[60px] text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(item, index) in items" :key="item.key">
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    {{-- Product Name & Editing --}}
                                    <td class="px-5 py-3 align-middle">
                                        <div class="text-[13px] font-bold text-gray-800 flex items-center gap-2">
                                            <span x-text="item.product_name"></span>
                                            <button type="button" @click="openItemModal(index)"
                                                class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1 rounded transition-colors"
                                                title="Edit Properties">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[11px] text-gray-500 font-mono" x-text="'SKU: ' + item.sku_code"></span>
                                            <span x-show="item.discount_value > 0"
                                                class="bg-amber-100 text-amber-700 text-[10px] px-1.5 py-0.5 rounded font-bold">
                                                Disc: <span x-text="item.discount_type === 'percentage' ? item.discount_value + '%' : '₹' + item.discount_value"></span>
                                            </span>
                                        </div>

                                        <input type="hidden" :name="'items[' + index + '][product_name]'" :value="item.product_name">
                                        <input type="hidden" :name="'items[' + index + '][tax_type]'" :value="item.tax_type">
                                        <input type="hidden" :name="'items[' + index + '][tax_percent]'" :value="item.tax_percent">
                                        <input type="hidden" :name="'items[' + index + '][discount_type]'" :value="item.discount_type">
                                        <input type="hidden" :name="'items[' + index + '][discount_value]'" :value="item.discount_value">
                                        <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                                        <input type="hidden" :name="'items[' + index + '][product_sku_id]'" :value="item.product_sku_id">
                                        <input type="hidden" :name="'items[' + index + '][unit_id]'" :value="item.unit_id">
                                        <input type="hidden" :name="'items[' + index + '][sku_code]'" :value="item.sku_code">
                                        <input type="hidden" :name="'items[' + index + '][unit_price]'" :value="item.unit_price">
                                        <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                                        <input type="hidden" :name="'items[' + index + '][hsn_code]'" :value="item.hsn_code">
                                    </td>

                                    <td class="px-4 py-3 text-center align-middle">
                                        <span class="text-[12px] font-mono text-gray-600" x-text="item.hsn_code || '-'"></span>
                                    </td>

                                    {{-- Unit Price --}}
                                    <td class="px-4 py-3 align-middle">
                                        <div class="relative w-full min-w-[100px]">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-bold">₹</span>
                                            <input type="number" step="0.01" x-model="item.unit_price"
                                                @input="calculate()"
                                                class="w-full h-10 md:h-9 border border-gray-300 rounded px-2 pl-7 text-sm focus:border-brand-500 outline-none font-bold text-gray-700 text-right shadow-sm transition-all bg-white">
                                        </div>
                                    </td>

                                    {{-- QTY --}}
                                    <td class="px-4 py-3 align-middle">
                                        <div class="flex items-center justify-center min-w-[120px]">
                                            <button type="button"
                                                @click="item.quantity = Math.max(1, parseFloat(item.quantity || 0) - 1); calculate()"
                                                class="w-10 h-10 md:w-8 md:h-9 border border-gray-300 rounded-l flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 active:bg-gray-200 transition-colors">-</button>
                                            <input type="number" step="0.0001" x-model="item.quantity"
                                                @input="calculate()"
                                                class="w-16 h-10 md:h-9 border-y border-x-0 border-gray-300 text-center text-sm font-bold focus:ring-0 focus:border-brand-500 outline-none p-0 text-gray-700 shadow-inner">
                                            <button type="button"
                                                @click="item.quantity = parseFloat(item.quantity || 0) + 1; calculate()"
                                                class="w-10 h-10 md:w-8 md:h-9 border border-gray-300 rounded-r flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 active:bg-gray-200 transition-colors">+</button>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-right align-middle">
                                        <div class="text-[12px] font-bold text-gray-700" x-text="item.tax_percent + '%'"></div>
                                        <div class="text-[9px] text-gray-400 uppercase" x-text="item.tax_type"></div>
                                    </td>

                                    <td class="px-5 py-3 text-right align-middle">
                                        <span class="font-black text-gray-800 text-[14px]" x-text="formatCurrency(item.line_total)"></span>
                                    </td>

                                    <td class="px-4 py-3 text-center align-middle">
                                        <button type="button" @click="removeItem(index)"
                                            class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. SUMMARY SECTION --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

                {{-- Notes & Terms --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col gap-4">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b pb-3">Notes & Conditions
                    </h3>
                    <textarea name="notes" rows="3" placeholder="Internal notes (not visible to customer)..."
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-brand-500 outline-none resize-none">{{ old('notes', $quotation->notes) }}</textarea>
                    <textarea name="terms_conditions" rows="4" placeholder="Terms and conditions for this proposal..."
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-brand-500 outline-none resize-none">{{ old('terms_conditions', $quotation->terms_conditions) }}</textarea>
                </div>

                {{-- FINANCIAL SUMMARY --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3
                        class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-3 mb-4">
                        Financials</h3>

                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">Subtotal (Taxable):</span>
                            <span class="font-bold text-gray-800" x-text="formatCurrency(totals.subtotal)"></span>
                        </div>

                        {{-- Tax Breakdown --}}
                        <template x-if="totals.igst > 0">
                            <div class="flex justify-between items-center text-gray-500">
                                <span>IGST:</span>
                                <span x-text="formatCurrency(totals.igst)"></span>
                            </div>
                        </template>
                        <template x-if="totals.igst <= 0 && totals.tax > 0">
                            <div class="space-y-1">
                                <div class="flex justify-between items-center text-gray-500">
                                    <span
                                        x-text="'CGST (' + (totals.cgst_rate === 'Mixed' ? 'Mixed' : parseFloat(totals.cgst_rate).toFixed(2) + '%') + '):'"></span>
                                    <span x-text="formatCurrency(totals.cgst)"></span>
                                </div>
                                <div class="flex justify-between items-center text-gray-500">
                                    <span
                                        x-text="'SGST (' + (totals.sgst_rate === 'Mixed' ? 'Mixed' : parseFloat(totals.sgst_rate).toFixed(2) + '%') + '):'"></span>
                                    <span x-text="formatCurrency(totals.sgst)"></span>
                                </div>
                            </div>
                        </template>

                        {{-- Global Discount --}}
                        <div class="flex flex-wrap sm:flex-nowrap justify-between items-center pt-2 gap-2">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-600">Discount:</span>
                                <select name="discount_type" x-model="global.discount_type" @change="calculate()"
                                    class="border border-gray-300 rounded px-2 py-0.5 text-[11px] font-bold text-gray-600 focus:border-[#108c2a] outline-none bg-gray-50 cursor-pointer shrink-0">
                                    <option value="fixed">Flat (₹)</option>
                                    <option value="percentage">Percent (%)</option>
                                </select>
                            </div>
                            <input type="number" step="0.01" name="discount_value" x-model="global.discount_value"
                                @input="calculate()"
                                class="w-24 sm:w-32 border border-gray-300 rounded px-3 py-1.5 text-right font-bold text-red-500 focus:border-[#108c2a] outline-none ml-auto shadow-sm"
                                placeholder="0.00">
                        </div>

                        {{-- Shipping --}}
                        <div class="flex flex-wrap sm:flex-nowrap justify-between items-center pt-2 border-b border-gray-100 pb-4 gap-2">
                            <span class="font-semibold whitespace-nowrap">Shipping / Other (₹):</span>
                            <input type="number" step="0.01" name="shipping_charge" x-model="global.shipping"
                                @input="calculate()"
                                class="w-24 sm:w-32 border border-gray-300 rounded px-3 py-1.5 text-right font-bold text-gray-800 focus:border-[#108c2a] outline-none ml-auto shadow-sm">
                        </div>

                        {{-- Final Totals --}}
                        <div class="flex justify-between items-end pt-2">
                            <div>
                                <div class="text-[11px] font-bold text-gray-500 uppercase">Auto Round Off</div>
                                <div class="text-sm font-bold text-gray-600 mt-1" x-text="global.round_off"></div>
                                <input type="hidden" name="round_off" :value="global.round_off">
                            </div>
                            <div class="text-right">
                                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Estimated
                                    Total</div>
                                <div class="text-3xl font-black text-[#108c2a]"
                                    x-text="formatCurrency(totals.grand_total)"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- ITEM SETTINGS MODAL --}}
        <div x-show="isItemModalOpen" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-end bg-black/50 backdrop-blur-sm transition-opacity">
            <div class="bg-white w-full max-w-md h-full shadow-2xl flex flex-col" x-show="isItemModalOpen" x-transition>
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-[15px] font-bold text-gray-800" x-text="activeEditData.product_name"></h3>
                    <button @click="closeItemModal()" class="text-gray-400 hover:text-red-500"><i data-lucide="x"
                            class="w-5 h-5"></i></button>
                </div>
                <div class="p-6 space-y-5 flex-1 overflow-y-auto custom-scrollbar">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Tax
                            Type</label>
                        <select x-model="activeEditData.tax_type"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm outline-none bg-white">
                            <option value="exclusive">Exclusive (Price + Tax)</option>
                            <option value="inclusive">Inclusive (Price includes Tax)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">GST
                            (%)</label>
                        <input type="number" step="0.01" x-model="activeEditData.tax_percent"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Discount
                            Type</label>
                        <select x-model="activeEditData.discount_type"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm outline-none bg-white">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₹)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Discount
                            Value</label>
                        <input type="number" step="0.01" x-model="activeEditData.discount_value"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Unit</label>
                        <select x-model="activeEditData.unit_id"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm outline-none bg-white">
                            <template x-for="u in units" :key="u.id">
                                <option :value="u.id" x-text="u.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="p-5 border-t border-gray-100 bg-white grid grid-cols-2 gap-3">
                    <button @click="closeItemModal()"
                        class="bg-gray-100 text-gray-700 font-bold text-sm py-2.5 rounded-lg">Cancel</button>
                    <button @click="saveItemModal()"
                        class="bg-[#108c2a] text-white font-bold text-sm py-2.5 rounded-lg">Save Changes</button>
                </div>
            </div>
        </div>

        {{-- QUICK CLIENT MODAL --}}
        <x-quick-client-modal :states="$states ?? []" />

    </div>
@endsection

@push('scripts')
    <script>
        function quotationForm(allUnits = [], companyState = '', allClients = [], existingData = null) {

            // 🌟 Determine if we are loading a Guest (Walk-in) from history
            let isGuestInit = existingData ? (!existingData.customer_id ? true : false) : false;

            return {
                clientsList: allClients,
                units: allUnits,
                company_state: companyState,
                items: [],
                itemCounter: 0,
                globalSearch: '',
                isSearching: false,
                globalSearchResults: [],
                isGuest: isGuestInit,

                // 🌟 Searchable Dropdown State (Pre-fills existing customer name)
                clientSearchTerm: existingData?.customer_name || '',
                isClientDropdownOpen: false,

                // 🌟 Store -> Warehouse mapping for Auto-Select
                storeDefaults: @json($warehouses->where('is_default', 1)->pluck('id', 'store_id')),

                // 🌟 Pre-fill the form with existing quotation data
                formData: {
                    customer_id: existingData?.customer_id || '',
                    customer_name: existingData?.customer_name || '',
                    customer_gstin: existingData?.customer_gstin || '',
                    gst_treatment: existingData?.gst_treatment || 'unregistered',
                    supply_state: existingData?.supply_state || companyState,

                    // Laravel dates come as full ISO strings (2025-10-10T00:00:00.000Z), so we split them for the HTML date picker
                    quotation_date: existingData?.quotation_date ? existingData.quotation_date.split('T')[0] : new Date()
                        .toISOString().split('T')[0],
                    valid_until: existingData?.valid_until ? existingData.valid_until.split('T')[0] : '',

                    store_id: existingData?.store_id || "{{ auth()->user()->store_id ?? '' }}",
                    warehouse_id: '', // Used for search context
                },

                // 🌟 Pre-fill Global Financials
                global: {
                    shipping: parseFloat(existingData?.shipping_charge || 0),
                    discount_type: existingData?.discount_type || 'fixed',
                    discount_value: parseFloat(existingData?.discount_value || 0),
                    round_off: parseFloat(existingData?.round_off || 0).toFixed(2)
                },

                totals: {
                    subtotal: 0,
                    tax: 0,
                    cgst: 0,
                    sgst: 0,
                    igst: 0,
                    cgst_rate: 0,
                    sgst_rate: 0,
                    igst_rate: 0,
                    grand_total: 0
                },

                isItemModalOpen: false,
                activeEditIndex: null,
                activeEditData: {},
                get filteredClientList() {
                    if (this.clientSearchTerm.trim() === '') return this.clientsList;
                    const term = this.clientSearchTerm.toLowerCase();
                    return this.clientsList.filter(client => {
                        return client.name.toLowerCase().includes(term) || 
                               (client.phone && client.phone.includes(term));
                    });
                },

                // 🌟 Handle clicking an item in the dropdown
                selectCustomer(client) {
                    this.isGuest = false;
                    this.formData.customer_id = client.id;
                    this.clientSearchTerm = client.name;
                    this.formData.supply_state = client.state_name_only || client.state?.name || this.company_state;
                    this.formData.customer_name = client.name;
                    this.formData.customer_gstin = client.gst_number || client.gstin || '';
                    this.formData.gst_treatment = this.formData.customer_gstin ? 'registered' : 'unregistered';
                    this.isClientDropdownOpen = false;
                    this.calculate();
                },

                autoSelectWarehouse() {
                    let sId = String(this.formData.store_id);
                    if (this.storeDefaults[sId]) {
                        this.formData.warehouse_id = String(this.storeDefaults[sId]);
                    } else {
                        this.formData.warehouse_id = ''; 
                    }
                },

                isClientModalOpen: false,
                newClient: {
                    name: '',
                    phone: '',
                    city: '',
                    state_id: '',
                    registration_type: 'unregistered',
                },

                // 🌟 INITIALIZE THE EDIT SCREEN
                init() {
                    // If we have items from the backend, map them into the Alpine format
                    if (existingData && existingData.items) {
                        this.items = existingData.items.map((item) => ({
                            key: this.itemCounter++,
                            product_id: item.product_id,
                            product_sku_id: item.product_sku_id,
                            unit_id: item.unit_id,
                            product_name: item.product_name,
                            sku_code: item.sku_code,
                            hsn_code: item.hsn_code || '',
                            quantity: parseFloat(item.quantity) || 1,
                            unit_price: parseFloat(item.unit_price) || 0,
                            tax_percent: parseFloat(item.tax_percent) || 0,
                            tax_type: item.tax_type || 'exclusive',
                            discount_type: item.discount_type || 'percentage',
                            discount_value: parseFloat(item.discount_value || 0),
                            line_total: parseFloat(item.total_amount) || 0
                        }));

                        // Force a recalculation so the UI totals match immediately
                        this.calculate();
                    }

                    this.$nextTick(() => {
                        let selectEl = document.querySelector('select[name="store_id"]');
                        if (selectEl && selectEl.value) {
                            this.updateStoreData({ target: selectEl });
                        }
                        
                        // 🌟 Auto-sync warehouse on page load if it's empty
                        if (!this.formData.warehouse_id) {
                            this.autoSelectWarehouse();
                        }
                    });
                },

                async saveQuickClient() {
                    if (!this.newClient.name || !this.newClient.phone || !this.newClient.city || !this.newClient
                        .state_id) {
                        BizAlert.toast('Please fill all required fields', 'error');
                        return;
                    }
                    if (this.newClient.phone.length !== 10) {
                        BizAlert.toast('Phone number must be exactly 10 digits.', 'error');
                        return;
                    }
                    try {
                        BizAlert.loading('Saving Client...');
                        let csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        let response = await fetch("{{ route('admin.clients.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfMeta.content
                            },
                            body: JSON.stringify(this.newClient)
                        });
                        let data = await response.json();
                        if (!response.ok) {
                            let errorMsg = data.message || 'Failed to save client';
                            if (data.errors) errorMsg = Object.values(data.errors)[0][0];
                            BizAlert.toast(errorMsg, 'error');
                            return;
                        }
                        BizAlert.toast('Client added successfully!', 'success');
                        this.isClientModalOpen = false;
                        this.newClient = {
                            name: '',
                            phone: '',
                            city: '',
                            state_id: '',
                            registration_type: 'unregistered'
                        };
                        this.clientsList.push(data.client);
                        this.selectCustomer(data.client);
                    } catch (error) {
                        BizAlert.toast('Network error.', 'error');
                    }
                },

                toggleGuestMode() {
                    this.isGuest = !this.isGuest;
                    if (this.isGuest) {
                        this.formData.customer_id = '';
                        this.formData.customer_name = '';
                        this.clientSearchTerm = '';
                        this.formData.supply_state = this.company_state;
                    }
                    this.calculate();
                },

                updateStoreData(e) {
                    const opt = e.target.options[e.target.selectedIndex];
                    if (opt && opt.dataset.state) {
                        this.company_state = opt.dataset.state;
                    }
                    this.calculate();
                },

                async fetchGlobalSkus() {
                    let warehouseId = this.formData.warehouse_id;
                    if (!warehouseId) {
                        BizAlert.toast('Please select a warehouse first', 'error');
                        this.globalSearch = '';
                        return;
                    }
                    if (this.globalSearch.length < 2) return;
                    this.isSearching = true;
                    try {
                        let response = await fetch(
                            `/admin/api/search-skus?term=${encodeURIComponent(this.globalSearch)}&warehouse_id=${warehouseId}&in_stock_only=1`
                        );
                        if (!response.ok) {
                            BizAlert.toast('Error searching products.', 'error');
                            return;
                        }
                        this.globalSearchResults = await response.json();
                    } catch (error) {
                        console.error(error);
                    } finally {
                        this.isSearching = false;
                    }
                },

                addSkuToTable(result) {
                    this.items.push({
                        key: this.itemCounter++,
                        product_id: result.product_id,
                        product_sku_id: result.product_sku_id,
                        unit_id: result.unit_id,
                        product_name: result.product_name,
                        sku_code: result.sku_code,
                        hsn_code: result.hsn_code || '',
                        quantity: 1,
                        unit_price: parseFloat(result.price) || 0,
                        tax_percent: parseFloat(result.tax_percent) || 18,
                        tax_type: result.tax_type || 'exclusive',
                        discount_type: 'percentage',
                        discount_value: 0,
                        line_total: 0
                    });
                    this.globalSearch = '';
                    this.calculate();
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                    this.calculate();
                },

                openItemModal(index) {
                    this.activeEditIndex = index;
                    this.activeEditData = JSON.parse(JSON.stringify(this.items[index]));
                    this.isItemModalOpen = true;
                },

                saveItemModal() {
                    this.activeEditData.tax_percent = parseFloat(this.activeEditData.tax_percent) || 0;
                    this.activeEditData.discount_value = parseFloat(this.activeEditData.discount_value) || 0;
                    this.items[this.activeEditIndex] = Object.assign(this.items[this.activeEditIndex], this.activeEditData);
                    this.calculate();
                    this.closeItemModal();
                },

                closeItemModal() {
                    this.isItemModalOpen = false;
                },

                calculate() {
                    let subtotalAcc = 0;
                    let taxAcc = 0;

                    this.items.forEach(item => {
                        let qty = parseFloat(item.quantity) || 0;
                        let price = parseFloat(item.unit_price) || 0;
                        let taxPct = parseFloat(item.tax_percent) || 0;
                        let discVal = parseFloat(item.discount_value) || 0;
                        let baseVal = qty * price;

                        let discountAmount = 0;
                        if (item.discount_type === 'percentage') {
                            discountAmount = baseVal * (discVal / 100);
                        } else {
                            discountAmount = discVal;
                        }

                        let afterDiscount = Math.max(0, baseVal - discountAmount);

                        let taxable = 0,
                            tax = 0;
                        if (item.tax_type === 'inclusive') {
                            taxable = afterDiscount / (1 + (taxPct / 100));
                            tax = afterDiscount - taxable;
                        } else {
                            taxable = afterDiscount;
                            tax = taxable * (taxPct / 100);
                        }

                        item.line_total = taxable + tax;
                        subtotalAcc += taxable;
                        taxAcc += tax;
                    });

                    this.totals.subtotal = subtotalAcc;
                    this.totals.tax = taxAcc;

                    let uniqueRates = [...new Set(this.items.map(item => parseFloat(item.tax_percent) || 0))];
                    let isMixed = uniqueRates.length > 1;
                    let baseRate = uniqueRates.length === 1 ? uniqueRates[0] : 0;

                    const isInterState = (this.formData.supply_state || '').trim().toLowerCase() !== (this.company_state ||
                        '').trim().toLowerCase();

                    if (isInterState) {
                        this.totals.igst = taxAcc;
                        this.totals.cgst = 0;
                        this.totals.sgst = 0;
                        this.totals.igst_rate = isMixed ? 'Mixed' : baseRate;
                        this.totals.cgst_rate = 0;
                        this.totals.sgst_rate = 0;
                    } else {
                        this.totals.igst = 0;
                        this.totals.cgst = taxAcc / 2;
                        this.totals.sgst = taxAcc / 2;
                        this.totals.igst_rate = 0;
                        this.totals.cgst_rate = isMixed ? 'Mixed' : (baseRate / 2);
                        this.totals.sgst_rate = isMixed ? 'Mixed' : (baseRate / 2);
                    }

                    let shipping = parseFloat(this.global.shipping) || 0;
                    let globalDiscVal = parseFloat(this.global.discount_value) || 0;
                    let itemsSum = subtotalAcc + taxAcc;

                    let globalDiscountAmount = 0;
                    if (this.global.discount_type === 'percentage') {
                        globalDiscountAmount = itemsSum * (globalDiscVal / 100);
                    } else {
                        globalDiscountAmount = globalDiscVal;
                    }

                    let totalBeforeRound = Math.max(0, itemsSum - globalDiscountAmount + shipping);

                    this.totals.grand_total = Math.round(totalBeforeRound);
                    this.global.round_off = (this.totals.grand_total - totalBeforeRound).toFixed(2);
                },

                formatCurrency(val) {
                    return '₹' + parseFloat(val).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }
        }
    </script>
@endpush
