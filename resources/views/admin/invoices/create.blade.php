@extends('layouts.admin')

@section('title', 'Create Sales Invoice')

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Create Invoice</h1>
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
    {{-- 🌟 Pass the clients into Alpine --}}
    <div class="pb-20" x-data="invoiceForm(@js($units ?? []), @js($companyState), @js($clients ?? []), @js($challanPrefillJs ?? null))">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-[1.5rem] font-bold text-gray-500 uppercase tracking-widest">Create Invoice</h1>
            </div>
            {{-- UI Fix: Make buttons full width on mobile, auto width on screens sm+ --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0 sm:justify-end">
                <a href="{{ route('admin.invoices.index') }}"
                    class="text-sm font-bold text-gray-500 hover:text-gray-800 transition-colors px-2 self-center sm:self-auto">Cancel</a>
                <button type="submit" form="mainInvoiceForm" name="status" value="draft"
                    class="w-full sm:w-auto bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-3 sm:py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Save as Draft
                </button>
                <button type="submit" form="mainInvoiceForm" name="status" value="confirmed"
                    class="w-full sm:w-auto bg-[#108c2a] hover:bg-[#0c6b1f] text-white px-8 py-3 sm:py-2.5 rounded-lg text-sm font-bold transition-all shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Save &amp; Confirm
                </button>
            </div>
        </div>
        {{-- 🚨 ADD THIS: Validation Error Display --}}
        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200 shadow-sm">
                <div class="font-bold mb-2 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    Please fix the following errors:
                </div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <script>
                // Auto-close the loading alert if validation fails
                document.addEventListener('DOMContentLoaded', () => {
                    if (typeof Swal !== 'undefined') Swal.close();
                });
            </script>
        @endif

        <form id="mainInvoiceForm" action="{{ route('admin.invoices.store') }}" method="POST"
            @submit="BizAlert.loading('Generating Invoice...')">
            @csrf

            {{-- HIDDEN SOURCE TRACKING --}}
            <input type="hidden" name="source" value="direct">

            @if($challanPrefill)
                {{-- Challan Conversion: Pass the challan ID so InvoiceService updates qty_invoiced --}}
                <input type="hidden" name="challan_id" value="{{ $challanPrefill->id }}">

                {{-- Conversion Banner --}}
                {{-- UI Fix: Stack elements on mobile, side-by-side on sm+ --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <div class="bg-blue-600 p-2 rounded-lg text-white flex-shrink-0">
                        <i data-lucide="file-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-blue-800">Converting from Delivery Challan</h4>
                        <p class="text-xs text-blue-600 font-medium">
                            Challan <span class="font-black">{{ $challanPrefill->challan_number }}</span> —
                            {{ $challanPrefill->items->sum('qty_pending') }} pending unit(s) pre-loaded below.
                            Quantities are locked to the pending amount.
                        </p>
                    </div>
                    <a href="{{ route('admin.challans.show', $challanPrefill->id) }}"
                        class="ml-auto text-xs text-blue-600 hover:underline font-bold flex-shrink-0">
                        View Challan →
                    </a>
                </div>
            @endif

            {{-- 1. TRANSACTION HEADER --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Customer Selection --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Customer <span
                                class="text-red-500">*</span></label>
                        <div class="flex gap-2 items-start">
                            <div class="relative flex-1">
                                {{-- Select & Button Row --}}
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
                                        class="absolute z-[60] w-[calc(100%-2.5rem)] bg-white border border-gray-200 rounded-lg shadow-2xl mt-1 max-h-60 overflow-y-auto overscroll-contain top-full left-0 custom-scrollbar">
                                        
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

                                {{-- 🌟 GSTIN Drops cleanly below the row --}}
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
                        {{-- Manual Name for Guests --}}
                        <div x-show="isGuest" x-cloak class="mt-3">
                            <input type="text" name="customer_name" x-model="formData.customer_name"
                                placeholder="Enter Guest Name..."
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-brand-500 outline-none">
                        </div>
                    </div>



                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Invoice
                            Date</label>
                        <input type="date" name="invoice_date" x-model="formData.invoice_date" required
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Due Date</label>
                        <input type="date" name="due_date" x-model="formData.due_date"
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
                        {{-- 🌟 Added @change to update the origin state --}}
                        <select name="store_id" required x-model="formData.store_id" @change="updateStoreData($event); autoSelectWarehouse()"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none bg-white">
                            @foreach ($stores ?? [] as $store)
                                {{-- 🌟 Pass the store's state safely into the option --}}
                                <option value="{{ $store->id }}" data-state="{{ $store->state->name ?? $companyState }}">
                                    {{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Stock
                            Warehouse</label>
                        <select name="warehouse_id" required x-model="formData.warehouse_id"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none bg-white">

                            <option value="">-- Select Warehouse --</option>

                            @foreach ($warehouses ?? [] as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-[-1px]"> {{-- Slight margin tweak to align with other inputs --}}
                        <x-state-select name="supply_state" label="Place of Supply (State)" x-model="formData.supply_state"
                            @change="calculate()" class="!bg-white !border-gray-300 !font-bold !text-[#108c2a]" />
                    </div>
                </div>
            </div>

            {{-- 2. PRODUCT SEARCH & LINE ITEMS --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 flex flex-col">
                <div
                    class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h2 class="text-lg font-bold text-gray-800 tracking-tight">Billing Items</h2>

                    {{-- Global SKU Search (Exactly like Purchase Module) --}}
                    <div class="relative w-full sm:w-96" x-data="{ showResults: false }">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        <input type="text" x-model="globalSearch" @input.debounce.300ms="fetchGlobalSkus()"
                            @focus="showResults = true" @click.away="showResults = false"
                            placeholder="Type product name or scan barcode..."
                            class="w-full border border-gray-300 rounded shadow-sm pl-9 pr-4 py-2.5 text-sm focus:border-brand-500 outline-none bg-white">

                        <ul x-show="showResults && globalSearch.length > 1" x-cloak
                            class="absolute z-[60] w-full bg-white border border-gray-200 rounded-lg shadow-2xl mt-1 max-h-60 overflow-y-auto overscroll-contain top-full left-0 custom-scrollbar">

                            {{-- 🌟 Searching Indicator --}}
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

                            {{-- 🌟 No Results Indicator --}}
                            <li x-show="!isSearching && globalSearchResults.length === 0" class="px-4 py-4 text-center">
                                <span class="block text-sm font-bold text-gray-700">No products found in this
                                    warehouse</span>
                                <span class="block text-[11px] text-gray-400 mt-0.5">Try a different name or ensure the
                                    item has stock.</span>
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
                                        <div class="text-[10px] text-gray-500" x-text="'Stock: ' + (result.stock || 0)">
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
                            {{-- Updated: Added min-w-[140px] --}}
                            <th class="px-4 py-4 min-w-[140px] text-right">UNIT PRICE</th>
                            {{-- Updated: Added min-w-[140px] --}}
                            <th class="px-4 py-4 min-w-[140px] text-center">QTY</th>
                            <th class="px-4 py-4 min-w-[100px] text-right">TAX %</th>
                            <th class="px-5 py-4 min-w-[120px] text-right">LINE TOTAL</th>
                            <th class="px-4 py-4 w-[60px] text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in items" :key="item.key">
                            <tr class="hover:bg-gray-50/50 transition-colors">

                                {{-- 1. Product Details & Actions --}}
                                <td class="px-5 py-3">
                                    <div class="text-[13px] font-bold text-gray-800 flex items-center gap-2">
                                        <span x-text="item.product_name"></span>
                                        <button type="button" @click="openItemModal(index)"
                                            class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1 rounded transition-colors"
                                            title="Edit Tax, Discount & Unit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[11px] text-gray-500 font-mono"
                                            x-text="'SKU: ' + item.sku_code"></span>

                                        <span x-show="item.discount_value > 0"
                                            class="bg-amber-100 text-amber-700 text-[10px] px-1.5 py-0.5 rounded font-bold">
                                            Disc: <span
                                                x-text="item.discount_type === 'percentage' ? item.discount_value + '%' : '₹' + item.discount_value"></span>
                                        </span>
                                    </div>

                                    {{-- Hidden Inputs for Laravel --}}
                                    <input type="hidden" :name="'items[' + index + '][tax_type]'" :value="item.tax_type">
                                    <input type="hidden" :name="'items[' + index + '][tax_percent]'"
                                        :value="item.tax_percent">
                                    <input type="hidden" :name="'items[' + index + '][discount_type]'"
                                        :value="item.discount_type">
                                    <input type="hidden" :name="'items[' + index + '][discount_value]'"
                                        :value="item.discount_value">
                                    <input type="hidden" :name="'items[' + index + '][product_sku_id]'"
                                        :value="item.product_sku_id">
                                    <input type="hidden" :name="'items[' + index + '][unit_id]'" :value="item.unit_id">
                                    <input type="hidden" :name="'items[' + index + '][unit_price]'"
                                        :value="item.unit_price">
                                    <input type="hidden" :name="'items[' + index + '][quantity]'"
                                        :value="item.quantity">
                                    <input type="hidden" :name="'items[' + index + '][hsn_code]'"
                                        :value="item.hsn_code">
                                    {{-- Challan conversion fields --}}
                                    <input type="hidden" :name="'items[' + index + '][challan_item_id]'"
                                        :value="item.challan_item_id ?? ''">
                                    <input type="hidden" :name="'items[' + index + '][batch_id]'"
                                        :value="item.batch_id ?? ''">
                                    <input type="hidden" :name="'items[' + index + '][batch_number]'"
                                        :value="item.batch_number ?? ''">
                                </td>

                                {{-- 2. HSN/SAC Code --}}
                                <td class="px-4 py-3 text-center">
                                    <span class="text-[12px] font-mono text-gray-600"
                                        x-text="item.hsn_code || '-'"></span>
                                </td>

                                {{-- 3. Base Unit Price Input --}}
                                <td class="px-4 py-3 align-middle">
                                    <div class="relative w-full min-w-[100px]">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-bold">₹</span>
                                        {{-- UI Fix: Replaced py-2 with explicit h-10 / md:h-9 to match QTY perfectly --}}
                                        <input type="number" step="0.01" x-model="item.unit_price"
                                            @input="calculate()"
                                            class="w-full h-10 md:h-9 border border-gray-300 rounded px-2 pl-7 text-sm focus:border-brand-500 outline-none font-bold text-gray-700 text-right shadow-sm transition-all">
                                    </div>
                                </td>

                                {{-- 4. Quantity Input --}}
                                <td class="px-4 py-3 align-middle">
                                    <template x-if="item.challan_item_id">
                                        <div class="text-center font-black text-gray-800 text-[14px]" x-text="item.quantity"></div>
                                    </template>
                                    <template x-if="!item.challan_item_id">
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
                                    </template>
                                </td>

                                {{-- 5. Tax Percentage Display --}}
                                <td class="px-4 py-3 text-right">
                                    <div class="text-[12px] font-bold text-gray-700" x-text="item.tax_percent + '%'">
                                    </div>
                                    <div class="text-[9px] text-gray-400 uppercase" x-text="item.tax_type"></div>
                                </td>

                                {{-- 6. Line Total --}}
                                <td class="px-5 py-3 text-right">
                                    <span class="font-black text-gray-800 text-[14px]"
                                        x-text="formatCurrency(item.line_total)"></span>
                                </td>

                                {{-- 7. Remove Action --}}
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="removeItem(index)"
                                        class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded transition-colors"
                                        title="Remove Item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                            </path>
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

    {{-- 3. SUMMARY & PAYMENT --}}    
    {{-- UI Fix: 2 columns on iPad (md), 3 columns on Desktop (xl) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-10">
        {{-- UI Fix: Notes takes full width on iPad, 1 column on Desktop --}}
        <div class="md:col-span-2 xl:col-span-1 bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col gap-4">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b pb-3">Notes & Terms</h3>
            <textarea name="notes" rows="2" placeholder="Internal notes..."
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-brand-500 outline-none resize-none"></textarea>
            <textarea name="terms_conditions" rows="2" placeholder="Customer terms..."
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-brand-500 outline-none resize-none"></textarea>
        </div>

        {{-- PAYMENT & RECONCILIATION --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b pb-3 mb-4">Payment Receipt
            </h3>
            <div class="space-y-4">
                <x-payment-method-select name="payment_method_id" label="Payment Mode"
                    x-model="formData.payment_method_id" ::required="global.amount_paid > 0" />

                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Amount
                        Received (₹)</label>
                    <input type="number" step="0.01" name="amount_paid" x-model="global.amount_paid"
                        @input="calculate()"
                        class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm font-black text-[#108c2a] focus:border-[#108c2a] outline-none">
                </div>

                <div class="pt-3 border-t border-gray-50 flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-400 uppercase"
                        x-text="balance.is_change ? 'Return Change:' : 'Due Amount:'"></span>
                    <span :class="balance.is_change ? 'text-blue-600' : 'text-red-600'" class="text-lg font-black"
                        x-text="formatCurrency(balance.value)"></span>
                </div>
            </div>
        </div>

        {{-- FINANCIAL SUMMARY --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-3 mb-4">
                Financials
            </h3>

            <div class="space-y-3 text-sm text-gray-600">
                <div class="flex justify-between items-center">
                    <span class="font-semibold">Subtotal (Taxable):</span>
                    <span class="font-bold text-gray-800" x-text="formatCurrency(totals.subtotal)"></span>
                </div>

                {{-- 🌟 Dynamic Tax Breakdown --}}
                <template x-if="totals.igst > 0">
                    <div class="flex justify-between items-center text-gray-500">
                        <span>IGST:</span>
                        <span x-text="formatCurrency(totals.igst)"></span>
                    </div>
                </template>
                <template x-if="totals.igst <= 0 && totals.tax > 0">
                    <div class="space-y-1">
                        <div class="flex justify-between items-center text-gray-500">
                            {{-- 🌟 Safely handle 'Mixed' vs a Number --}}
                            <span
                                x-text="'CGST (' + (totals.cgst_rate === 'Mixed' ? 'Mixed' : parseFloat(totals.cgst_rate).toFixed(2) + '%') + '):'"></span>
                            <span x-text="formatCurrency(totals.cgst)"></span>
                        </div>
                        <div class="flex justify-between items-center text-gray-500">
                            {{-- 🌟 Safely handle 'Mixed' vs a Number --}}
                            <span
                                x-text="'SGST (' + (totals.sgst_rate === 'Mixed' ? 'Mixed' : parseFloat(totals.sgst_rate).toFixed(2) + '%') + '):'"></span>
                            <span x-text="formatCurrency(totals.sgst)"></span>
                        </div>
                    </div>
                </template>

                {{-- Global Discount --}}
                {{-- UI Fix: Added flex-wrap and responsive input widths --}}
                <div class="flex flex-wrap xl:flex-nowrap justify-between items-center pt-2 gap-2">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-600">Discount:</span>
                        <select name="discount_type" x-model="global.discount_type" @change="calculate()"
                            class="border border-gray-300 rounded px-2 py-0.5 text-[11px] font-bold text-gray-600 focus:border-[#108c2a] outline-none bg-gray-50 cursor-pointer">
                            <option value="fixed">Flat (₹)</option>
                            <option value="percentage">Percent (%)</option>
                        </select>
                    </div>
                    <input type="number" step="0.01" name="discount_value" x-model="global.discount_value"
                        @input="calculate()"
                        class="w-24 sm:w-32 border border-gray-300 rounded px-3 py-1 text-right font-bold text-red-500 focus:border-[#108c2a] outline-none ml-auto"
                        placeholder="0.00">
                </div>

                {{-- Shipping --}}
                <div class="flex flex-wrap xl:flex-nowrap justify-between items-center pt-2 border-b border-gray-100 pb-4 gap-2">
                    <span class="font-semibold">Shipping Cost (₹):</span>
                    <input type="number" step="0.01" name="shipping_charge" x-model="global.shipping"
                        @input="calculate()"
                        class="w-24 sm:w-32 border border-gray-300 rounded px-3 py-1 text-right font-bold text-gray-800 focus:border-[#108c2a] outline-none ml-auto">
                </div>

                {{-- Final Totals --}}
                <div class="flex justify-between items-end pt-2">
                    <div>
                        <div class="text-[11px] font-bold text-gray-500 uppercase">Auto Round Off</div>
                        <div class="text-sm font-bold text-gray-600 mt-1" x-text="global.round_off"></div>
                        <input type="hidden" name="round_off" :value="global.round_off">
                    </div>
                    <div class="text-right">
                        <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Net Payable</div>
                        <div class="text-2xl font-black text-[#108c2a]" x-text="formatCurrency(totals.grand_total)"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>

    {{-- ITEM SETTINGS MODAL (一致性 with Purchase Order) --}}
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
                    <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Sales
                        Unit</label>
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
                <button @click="saveItemModal()" class="bg-[#108c2a] text-white font-bold text-sm py-2.5 rounded-lg">Save
                    Changes</button>
            </div>
        </div>
    </div>

    {{-- 🌟 QUICK CLIENT MODAL COMPONENT --}}
    <x-quick-client-modal :states="$states" />


    </div>
@endsection

@push('scripts')
    <script>
        // 🌟 ADD allClients and challanItems to the signature
        function invoiceForm(allUnits = [], companyState = '', allClients = [], challanItems = null) {
            return {
                clientsList: allClients, // 🌟 Store clients here for dynamic rendering
                units: allUnits,
                company_state: companyState,
                items: [],
                itemCounter: 0,
                globalSearch: '',
                isSearching: false,
                globalSearchResults: [],
                isGuest: false,

                // 🌟 NEW: Searchable Dropdown State
                clientSearchTerm: '',
                isClientDropdownOpen: false,
                // 🌟 1. Inject the magical JSON map for Store -> Warehouse
                storeDefaults: @json($warehouses->where('is_default', 1)->pluck('id', 'store_id')),

                formData: {
                    customer_id: '',
                    customer_name: '',
                    customer_gstin: '', // 🌟 Track GSTIN
                    gst_treatment: 'unregistered', // 🌟 Track GST Treatment
                    supply_state: companyState,
                    payment_method_id: '',
                    invoice_date: new Date().toISOString().split('T')[0],
                    due_date: new Date(new Date().setDate(new Date().getDate() + 7)).toISOString().split('T')[
                        0], // 🌟 Auto +7 Days
                    store_id: "{{ session('store_id', auth()->user()->store_id ?? '') }}",
                    // 🌟 2. Only use old() so we don't overwrite validation errors
                    warehouse_id: "{{ old('warehouse_id') }}",
                },

                global: {
                    shipping: 0,
                    amount_paid: 0,
                    discount_type: 'fixed', // 🌟 Default to flat amount
                    discount_value: 0, // 🌟 Default to 0
                    round_off: '0.00'
                },
                totals: {
                    subtotal: 0,
                    tax: 0,
                    cgst: 0,
                    sgst: 0,
                    igst: 0,
                    cgst_rate: 0, // 🌟 Track dynamic rates
                    sgst_rate: 0,
                    igst_rate: 0,
                    grand_total: 0
                },
                balance: {
                    value: 0,
                    is_change: false
                },

                // 🌟 NEW: Filter the list based on what the user types
                get filteredClientList() {
                    if (this.clientSearchTerm.trim() === '') {
                        return this.clientsList;
                    }
                    const term = this.clientSearchTerm.toLowerCase();
                    return this.clientsList.filter(client => {
                        return client.name.toLowerCase().includes(term) || 
                               (client.phone && client.phone.includes(term));
                    });
                },

                // 🌟 ADD THIS INIT BLOCK
                init() {
                    // Pre-populate items from Challan conversion
                    if (challanItems && challanItems.length > 0) {
                        this.items = challanItems.map(item => ({
                            key: this.itemCounter++,
                            challan_item_id: item.challan_item_id,
                            product_id:      item.product_id,
                            product_sku_id:  item.product_sku_id,
                            unit_id:         item.unit_id,
                            product_name:    item.product_name,
                            sku_code:        item.sku_code,
                            hsn_code:        item.hsn_code || '',
                            quantity:        item.quantity,
                            unit_price:      item.unit_price,
                            tax_percent:     item.tax_percent,
                            tax_type:        item.tax_type || 'exclusive',
                            discount_type:   item.discount_type || 'fixed',
                            discount_value:  item.discount_value || 0,
                            batch_id:        item.batch_id || null,
                            batch_number:    item.batch_number || '',
                            line_total:      0
                        }));
                        this.calculate();
                    }

                    this.$nextTick(() => {
                        let selectEl = document.querySelector('select[name="store_id"]');
                        if (selectEl && selectEl.value) {
                            this.updateStoreData({ target: selectEl });
                        }

                        // 🌟 3. Auto-sync warehouse on page load if it's empty
                        if (!this.formData.warehouse_id) {
                            this.autoSelectWarehouse();
                        }
                    });
                },
                // 🌟 4. The strict type-safe switcher logic
                autoSelectWarehouse() {
                    let sId = String(this.formData.store_id);
                    if (this.storeDefaults[sId]) {
                        this.formData.warehouse_id = String(this.storeDefaults[sId]);
                    } else {
                        this.formData.warehouse_id = ''; 
                    }
                },

                isItemModalOpen: false,
                activeEditIndex: null,
                activeEditData: {},
                // 🌟 QUICK CLIENT MODAL STATE
                isClientModalOpen: false,
                newClient: {
                    name: '',
                    phone: '',
                    city: '',
                    state_id: '',
                    registration_type: 'unregistered',
                },

                async saveQuickClient() {
                    // 1. Check for empty fields
                    if (!this.newClient.name || !this.newClient.phone || !this.newClient.city || !this.newClient
                        .state_id) {
                        BizAlert.toast('Please fill all required fields', 'error');
                        return;
                    }

                    // 🌟 2. Strict Phone Check (Exactly 10 digits)
                    if (this.newClient.phone.length !== 10) {
                        BizAlert.toast('Phone number must be exactly 10 digits.', 'error');
                        return;
                    }

                    // 🌟 3. Strict City Check (Must have at least 2 characters)
                    if (this.newClient.city.trim().length < 2) {
                        BizAlert.toast('Please enter a valid city name.', 'error');
                        return;
                    }

                    try {
                        BizAlert.loading('Saving Client...');

                        // 1. Safely check for CSRF token
                        let csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        if (!csrfMeta) {
                            BizAlert.toast('Security Error: CSRF token missing in layout.', 'error');
                            return;
                        }

                        // 2. Use Laravel's route helper to guarantee the correct URL
                        let response = await fetch("{{ route('admin.clients.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfMeta.content
                            },
                            body: JSON.stringify(this.newClient)
                        });

                        // 3. Safely attempt to parse JSON. If Laravel threw a 500 Whoops page, this catches it!
                        let data;
                        try {
                            data = await response.json();
                        } catch (parseError) {
                            console.error("Server did not return JSON. It returned HTML. Check Laravel Logs.");
                            BizAlert.toast('Server Error (500). Please check Laravel logs.', 'error');
                            return;
                        }

                        // 4. Handle 422 Validation Errors from the backend
                        if (!response.ok) {
                            let errorMsg = data.message || 'Failed to save client';
                            if (data.errors) {
                                errorMsg = Object.values(data.errors)[0][
                                    0
                                ]; // Grab the exact validation message (e.g. "Phone must be unique")
                            }
                            BizAlert.toast(errorMsg, 'error');
                            return;
                        }

                        // 5. Success! Close modal and reset form
                        BizAlert.toast('Client added successfully!', 'success');
                        this.isClientModalOpen = false;
                        this.newClient = {
                            name: '',
                            phone: '',
                            city: '',
                            state_id: '',
                            registration_type: 'unregistered'
                        };

                        // Push to the array so the dropdown updates instantly
                        this.clientsList.push(data.client);

                       // Auto-select the newly created client
                        this.selectCustomer(data.client);

                    } catch (error) {
                        console.error("Fetch Execution Error:", error);
                        BizAlert.toast('Network error. Check console for details.', 'error');
                    }
                },
                toggleGuestMode() {
                    this.isGuest = !this.isGuest;
                    if (this.isGuest) {
                        this.formData.customer_id = '';
                        this.formData.customer_name = '';
                        this.clientSearchTerm = '';
                        this.formData.supply_state = this.company_state; // This is now a clean string
                    }
                    this.calculate();
                },
                // 🌟 NEW: Handle clicking an item in the dropdown
                selectCustomer(client) {
                    this.isGuest = false;
                    
                    // Update Alpine State
                    this.formData.customer_id = client.id;
                    this.clientSearchTerm = client.name; // Put name in the search box
                    
                    // Update Snapshot Data
                    this.formData.supply_state = client.state_name_only || client.state?.name || this.company_state;
                    this.formData.customer_name = client.name;
                    this.formData.customer_gstin = client.gst_number || client.gstin || '';

                    // Auto-set GST treatment based on GSTIN presence
                    this.formData.gst_treatment = this.formData.customer_gstin ? 'registered' : 'unregistered';

                    // Close Dropdown & Recalculate
                    this.isClientDropdownOpen = false;
                    this.calculate();
                },

                // 🌟 Add this new function to dynamically change Origin state when Store changes!
                updateStoreData(e) {
                    const opt = e.target.options[e.target.selectedIndex];
                    if (opt && opt.dataset.state) {
                        this.company_state = opt.dataset.state;
                    }
                    this.calculate(); // Re-trigger the GST split math!
                },

                async fetchGlobalSkus() {
                    let warehouseId = this.formData.warehouse_id;

                    if (!warehouseId) {
                        BizAlert.toast('Please select a warehouse first', 'error');
                        this.globalSearch = '';
                        return;
                    }

                    if (this.globalSearch.length < 2) return;

                    this.isSearching = true; // 🌟 Trigger loading state

                    try {
                        // 🌟 ADDED in_stock_only=1 to block 0 stock items
                        let response = await fetch(
                            `/admin/api/search-skus?term=${encodeURIComponent(this.globalSearch)}&warehouse_id=${warehouseId}&in_stock_only=1`
                        );

                        if (!response.ok) {
                            let errorData = await response.text();
                            console.error("Backend Error:", errorData);
                            BizAlert.toast('Error searching products. Check console.', 'error');
                            return;
                        }

                        this.globalSearchResults = await response.json();
                    } catch (error) {
                        console.error("Network or Parsing Error:", error);
                    } finally {
                        this.isSearching = false; // 🌟 Turn off loading state
                    }
                },

                addSkuToTable(result) {
                    this.items.push({
                        key: this.itemCounter++,
                        challan_item_id: null,
                        batch_id:        null,
                        batch_number:    '',
                        product_id:      result.product_id,
                        product_sku_id:  result.product_sku_id,
                        unit_id:         result.unit_id,
                        product_name:    result.product_name,
                        sku_code:        result.sku_code,
                        hsn_code:        result.hsn_code || '',
                        quantity:        1,
                        unit_price:      parseFloat(result.price) || 0,
                        tax_percent:     parseFloat(result.tax_percent) || 18,
                        tax_type:        result.tax_type || 'exclusive',
                        discount_type:   'fixed',
                        discount_value:  0,
                        line_total:      0
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
                    // 🌟 Ensure inputs are stored as numbers
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

                        // 🌟 1. Apply Line Discount First
                        let discountAmount = 0;
                        if (item.discount_type === 'percentage' || item.discount_type === 'percent') {
                            discountAmount = baseVal * (discVal / 100);
                        } else {
                            discountAmount = discVal; // Flat amount deduction
                        }

                        // Prevent negative totals
                        let afterDiscount = Math.max(0, baseVal - discountAmount);

                        // 🌟 2. Calculate Taxes on the Discounted Amount
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

                    // 🌟 Calculate the Effective GST Percentage (Total Tax / Taxable Amount)
                    // 🌟 Find all unique tax percentages in the cart
                    let uniqueRates = [...new Set(this.items.map(item => parseFloat(item.tax_percent) || 0))];
                    let isMixed = uniqueRates.length > 1; // True if we have 5% and 18% mixed
                    let baseRate = uniqueRates.length === 1 ? uniqueRates[0] : 0; // The single rate, if uniform

                    // 🌟 GST Split Logic based on State
                    const isInterState = (this.formData.supply_state || '').trim().toLowerCase() !== (this.company_state ||
                        '').trim().toLowerCase();

                    if (isInterState) {
                        this.totals.igst = taxAcc;
                        this.totals.cgst = 0;
                        this.totals.sgst = 0;

                        // Pass 'Mixed' string if mixed, otherwise pass the number
                        this.totals.igst_rate = isMixed ? 'Mixed' : baseRate;
                        this.totals.cgst_rate = 0;
                        this.totals.sgst_rate = 0;
                    } else {
                        this.totals.igst = 0;
                        this.totals.cgst = taxAcc / 2;
                        this.totals.sgst = taxAcc / 2;

                        // Pass 'Mixed' string if mixed, otherwise pass the half-number
                        this.totals.igst_rate = 0;
                        this.totals.cgst_rate = isMixed ? 'Mixed' : (baseRate / 2);
                        this.totals.sgst_rate = isMixed ? 'Mixed' : (baseRate / 2);
                    }

                    let shipping = parseFloat(this.global.shipping) || 0;
                    let globalDiscVal = parseFloat(this.global.discount_value) || 0;

                    // Base sum of all items (Taxable + Tax)
                    let itemsSum = subtotalAcc + taxAcc;

                    // 🌟 Calculate Global Discount
                    let globalDiscountAmount = 0;
                    if (this.global.discount_type === 'percent' || this.global.discount_type === 'percentage') {
                        globalDiscountAmount = itemsSum * (globalDiscVal / 100);
                    } else {
                        globalDiscountAmount = globalDiscVal;
                    }

                    // 🌟 Calculate final total (Prevent it from dropping below 0)
                    let totalBeforeRound = Math.max(0, itemsSum - globalDiscountAmount + shipping);

                    this.totals.grand_total = Math.round(totalBeforeRound);
                    this.global.round_off = (this.totals.grand_total - totalBeforeRound).toFixed(2);

                    // Reconciliation logic
                    let paid = parseFloat(this.global.amount_paid) || 0;
                    let diff = paid - this.totals.grand_total;
                    this.balance.is_change = diff > 0;
                    this.balance.value = Math.abs(diff);
                },

                formatCurrency(val) {
                    return '₹' + parseFloat(val).toLocaleString('en-IN', {
                        minimumFractionDigits: 2
                    });
                }
            }
        }
    </script>
@endpush
