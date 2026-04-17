@extends('layouts.admin')

@section('title', 'Create Purchase Return')
@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">CREATE / Purchase Returns</h1>
@endsection
@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="pb-10" x-data="purchaseReturnForm(@js($originalPurchase ?? null), @js($units ?? []))">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-sm font-bold text-gray-500 uppercase tracking-wides">Create Purchase Return</h1>
                <p class="text-[13px] text-gray-500 font-medium">Select a completed Purchase Order to process a return</p>
            </div>
            <a href="{{ route('admin.purchase-returns.index') }}"
                class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm">
                Cancel
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200">
                <div class="font-bold mb-2">Please fix the following errors:</div>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.purchase-returns.store') }}" method="POST"
            @submit="BizAlert.loading('Processing Return...')">
            @csrf

            <input type="hidden" name="supplier_id" x-model="header.supplier_id">
            <input type="hidden" name="warehouse_id" x-model="header.warehouse_id">
            <input type="hidden" name="store_id" x-model="header.store_id">
            <input type="hidden" name="tax_type" x-model="header.tax_type">

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">

                    <div class="md:col-span-2 relative" @click.away="showPoResults = false">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Original Purchase
                            Order <span class="text-red-500">*</span></label>

                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="search" class="w-4 h-4 text-gray-400" x-show="!isSearchingPo"></i>
                                <svg x-show="isSearchingPo" class="animate-spin h-4 w-4 text-[#108c2a]"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>

                            <input type="text" x-model="poSearchTerm" @input.debounce.300ms="searchPOs()"
                                @focus="showPoResults = true; if(poSearchResults.length === 0) searchPOs()"
                                placeholder="Search PO Number or Supplier Name..."
                                class="w-full border border-gray-300 rounded pl-9 pr-4 py-2.5 text-sm focus:border-brand-500 outline-none transition-colors"
                                :disabled="header.purchase_id !== ''">

                            <button type="button" x-show="header.purchase_id !== ''" @click="clearPO()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <ul x-show="showPoResults" x-cloak
                            class="absolute z-[60] w-full bg-white border border-gray-200 rounded-lg shadow-2xl mt-1 max-h-60 overflow-y-auto top-full left-0">

                            <li x-show="!isSearchingPo && poSearchTerm.length === 0 && poSearchResults.length > 0"
                                class="px-4 py-2 bg-gray-50 border-b border-gray-100 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Recent Purchase Orders
                            </li>

                            <li x-show="!isSearchingPo && poSearchResults.length === 0" class="px-4 py-4 text-center">
                                <span class="block text-sm font-bold text-gray-700">No Purchase Orders found</span>
                                <span class="block text-[11px] text-gray-400 mt-0.5">Ensure the PO is marked as
                                    'Received'.</span>
                            </li>

                            <template x-for="po in poSearchResults" :key="po.id">
                                <li @click="selectPO(po)"
                                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors flex justify-between items-center">
                                    <div>
                                        <div class="text-[13px] font-bold text-[#108c2a]" x-text="po.purchase_number"></div>
                                        <div class="text-[11px] text-gray-500 font-medium"
                                            x-text="po.supplier?.name || 'Unknown Supplier'"></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[12px] font-bold text-gray-700" x-text="'₹' + po.total_amount">
                                        </div>
                                        <div class="text-[10px] text-gray-400"
                                            x-text="new Date(po.purchase_date).toLocaleDateString('en-GB')"></div>
                                    </div>
                                </li>
                            </template>
                        </ul>

                        <input type="hidden" name="purchase_id" x-model="header.purchase_id" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Return Date <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" required
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Status <span
                                class="text-red-500">*</span></label>
                        <select name="status" required
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none bg-white">
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="returned" {{ old('status') == 'returned' ? 'selected' : '' }}>Returned (Deducts
                                Stock)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 flex flex-col" x-show="items.length > 0"
                x-cloak>
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800 tracking-tight">Items to Return</h2>
                    <span class="text-xs font-medium text-gray-500">Only check the items you are returning.</span>
                </div>

                <div class="overflow-x-auto min-h-[200px]">
                    <table class="w-full text-left border-collapse">
                        <thead
                            class="hidden md:table-header-group bg-gray-50 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-4 w-[50px] text-center">Inc?</th>
                                <th class="px-5 py-4 min-w-[250px]">PRODUCT</th>
                                <th class="px-4 py-4 w-[120px] text-right">UNIT COST</th>
                                <th class="px-4 py-4 w-[100px] text-center">MAX QTY</th>
                                <th class="px-4 py-4 w-[140px]">RETURN QTY</th>
                                <th class="px-4 py-4 w-[180px]">REASON</th>
                                <th class="px-5 py-4 w-[140px] text-right">SUBTOTAL</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(item, index) in items" :key="item.purchase_item_id">
                                <tr class="hover:bg-gray-50/50 transition-colors flex flex-col md:table-row border-b border-gray-200 md:border-none p-4 md:p-0 relative"
                                    :class="item.is_included ? 'bg-blue-50/20' : 'opacity-60'">

                                    <td class="px-4 md:py-3 py-2 flex items-center justify-between md:table-cell absolute top-4 right-4 md:static">
                                        <span class="md:hidden text-xs font-bold text-gray-500 uppercase">Include</span>
                                        <input type="checkbox" x-model="item.is_included" @change="calculate()"
                                            class="w-5 h-5 md:w-4 md:h-4 text-[#108c2a] rounded border-gray-300 focus:ring-[#108c2a]">
                                    </td>

                                    <td class="px-5 md:py-3 py-2 flex flex-col md:table-cell pr-16 md:pr-5">
                                        <div class="text-[13px] font-bold text-gray-800" x-text="item.product_name"></div>
                                        <div class="text-[10px] text-gray-500 mt-0.5 font-mono" x-text="item.sku_code">
                                        </div>

                                        <template x-if="item.is_included">
                                            <div>
                                                <input type="hidden" :name="'items[' + index + '][purchase_item_id]'"
                                                    :value="item.purchase_item_id">
                                                <input type="hidden" :name="'items[' + index + '][product_id]'"
                                                    :value="item.product_id">
                                                <input type="hidden" :name="'items[' + index + '][product_sku_id]'"
                                                    :value="item.product_sku_id">
                                                <input type="hidden" :name="'items[' + index + '][unit_id]'"
                                                    :value="item.unit_id">
                                                <input type="hidden" :name="'items[' + index + '][unit_cost]'"
                                                    :value="item.unit_cost">
                                                <input type="hidden" :name="'items[' + index + '][tax_percent]'"
                                                    :value="item.tax_percent">
                                            </div>
                                        </template>
                                    </td>

                                    <td class="px-4 md:py-3 py-2 flex items-center justify-between md:table-cell text-left md:text-right text-[13px] font-semibold text-gray-600">
                                        <span class="md:hidden text-xs font-bold text-gray-500 uppercase">Unit Cost</span>
                                        <div>₹<span x-text="formatCurrency(item.unit_cost)"></span></div>
                                    </td>

                                    <td class="px-4 md:py-3 py-2 flex items-center justify-between md:table-cell text-left md:text-center text-[13px] font-bold text-gray-800">
                                        <span class="md:hidden text-xs font-bold text-gray-500 uppercase">Max Qty</span>
                                        <span x-text="item.max_qty"></span>
                                    </td>

                                    <td class="px-4 md:py-3 py-2 flex items-center justify-between md:table-cell">
                                        <span class="md:hidden text-xs font-bold text-gray-500 uppercase">Return Qty</span>
                                        <div class="flex items-center gap-1.5 md:w-[130px] justify-center"
                                            :class="!item.is_included ? 'opacity-50' : ''">

                                            <button type="button" tabindex="-1"
                                                class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed border border-gray-200 transition-colors shadow-sm"
                                                :disabled="!item.is_included || item.quantity <= 0"
                                                @click="item.quantity = Math.max(0, parseFloat(item.quantity || 0) - 1); calculate()">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="5" y1="12" x2="19" y2="12">
                                                    </line>
                                                </svg>
                                            </button>

                                            <input type="number" step="0.0001" min="0.0001" :max="item.max_qty"
                                                x-model="item.quantity" @input="calculate()"
                                                :disabled="!item.is_included"
                                                :name="item.is_included ? 'items[' + index + '][quantity]' : ''"
                                                class="w-16 text-center py-1 text-sm border border-gray-300 rounded focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none disabled:bg-gray-50 font-bold text-gray-800 m-0 p-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none shadow-inner"
                                                style="-moz-appearance: textfield;">

                                            <button type="button" tabindex="-1"
                                                class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed border border-gray-200 transition-colors shadow-sm"
                                                :disabled="!item.is_included || item.quantity >= item.max_qty"
                                                @click="item.quantity = Math.min(item.max_qty, parseFloat(item.quantity || 0) + 1); calculate()">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="12" y1="5" x2="12" y2="19">
                                                    </line>
                                                    <line x1="5" y1="12" x2="19" y2="12">
                                                    </line>
                                                </svg>
                                            </button>

                                        </div>
                                    </td>

                                    <td class="px-4 md:py-3 py-2 flex flex-col md:table-cell gap-1.5 md:gap-0">
                                        <span class="md:hidden text-xs font-bold text-gray-500 uppercase">Return Reason</span>
                                        <select x-model="item.return_reason" :disabled="!item.is_included"
                                            :name="item.is_included ? 'items[' + index + '][return_reason]' : ''"
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-[12px] focus:border-brand-500 outline-none disabled:bg-gray-100">
                                            <option value="damaged">Damaged</option>
                                            <option value="wrong_item">Wrong Item</option>
                                            <option value="excess_quantity">Excess Qty</option>
                                            <option value="quality_issue">Quality Issue</option>
                                            <option value="expired">Expired</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </td>

                                    <td class="px-5 md:py-3 py-3 flex items-center justify-between md:table-cell text-left md:text-right border-t border-gray-100 md:border-none mt-2 md:mt-0">
                                        <span class="md:hidden text-[13px] font-extrabold text-gray-700 uppercase">Subtotal</span>
                                        <span class="font-bold text-gray-800 text-[14px]"
                                            x-text="formatCurrency(item.line_total)"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6" x-show="items.length > 0" x-cloak>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col gap-5">

                    <div class="flex flex-col h-full">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">
                            Reason for Return
                        </label>
                        <textarea name="reason" rows="3"
                            class="w-full border border-gray-300 rounded p-3 text-sm focus:border-[#108c2a] outline-none resize-y transition-colors"
                            placeholder="Provide a detailed reason for the return...">{{ old('reason') }}</textarea>
                    </div>

                    <div class="flex flex-col h-full">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">
                            Supplier Credit Note Ref (Optional)
                        </label>
                        <textarea name="supplier_credit_note_number" rows="2"
                            class="w-full border border-gray-300 rounded p-3 text-sm focus:border-[#108c2a] outline-none resize-y transition-colors"
                            placeholder="Enter credit note references, terms, or supplier communication details...">{{ old('supplier_credit_note_number') }}</textarea>
                    </div>

                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3
                        class="text-sm font-bold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-3 mb-4">
                        Refund Summary</h3>

                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">Subtotal (Taxable):</span>
                            <span class="font-bold text-gray-800" x-text="'₹' + formatCurrency(totals.subtotal)"></span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="font-semibold">Total Tax Return:</span>
                            <span class="font-bold text-gray-800" x-text="'₹' + formatCurrency(totals.tax)"></span>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t border-gray-100 text-lg">
                            <span class="font-extrabold text-gray-800">Total Refund Expected:</span>
                            <span class="font-extrabold text-[#108c2a]"
                                x-text="'₹' + formatCurrency(totals.grand_total)"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 p-5 rounded-lg flex justify-end gap-4 shadow-sm"
                x-show="items.length > 0" x-cloak>
                <button type="submit"
                    class="bg-gray-800 text-white px-8 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-900 shadow-md transition-all active:scale-95">
                    SUBMIT
                </button>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function purchaseReturnForm(initialPurchase = null, allUnits = []) {
            return {
                // PO Search State
                poSearchTerm: '',
                poSearchResults: [],
                showPoResults: false,
                isSearchingPo: false,

                header: {
                    purchase_id: '',
                    purchase_number: '',
                    supplier_id: '',
                    warehouse_id: '',
                    store_id: '',
                    tax_type: 'cgst_sgst',
                },

                items: [],
                totals: {
                    subtotal: 0,
                    tax: 0,
                    grand_total: 0
                },

                init() {
                    if (initialPurchase) {
                        this.poSearchTerm = initialPurchase.purchase_number;
                        this.loadPurchaseData(initialPurchase);
                    }
                },

                // --- AJAX SEARCH ---                
                async searchPOs() {
                    let term = this.poSearchTerm.trim();

                    this.isSearchingPo = true;
                    this.showPoResults = true; // Force dropdown to stay open while loading

                    try {
                        let response = await fetch(`/admin/api/purchases/search?term=${encodeURIComponent(term)}`);
                        if (!response.ok) throw new Error("Search failed");
                        this.poSearchResults = await response.json();
                    } catch (error) {
                        console.error(error);
                        this.poSearchResults = [];
                    } finally {
                        this.isSearchingPo = false;
                    }
                },

                // User clicked a PO from the dropdown
                async selectPO(po) {
                    this.poSearchTerm = po.purchase_number; // Set input to PO number
                    this.showPoResults = false;
                    BizAlert.loading('Loading PO details...');

                    try {
                        // Fetch the full details (items, units, etc.) using your existing endpoint
                        let response = await fetch(`/admin/api/purchases/${po.id}/for-return`);
                        if (!response.ok) throw new Error('PO Not Found');
                        let data = await response.json();
                        this.loadPurchaseData(data);
                    } catch (error) {
                        BizAlert.toast('Failed to load PO details.', 'error');
                        this.clearPO();
                    }
                },

                // Allow user to reset and pick a different PO
                clearPO() {
                    this.poSearchTerm = '';
                    this.header.purchase_id = '';
                    this.header.purchase_number = '';
                    this.items = [];
                    this.calculate();
                },



                async fetchPurchaseDetails() {
                    if (!this.searchPoId) return;

                    this.isLoading = true;
                    try {
                        let response = await fetch(`/admin/api/purchases/${this.searchPoId}/for-return`);
                        if (!response.ok) throw new Error('PO Not Found');
                        let data = await response.json();
                        this.loadPurchaseData(data);
                    } catch (error) {
                        BizAlert.toast('Purchase Order not found or access denied.', 'error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                loadPurchaseData(data) {
                    this.header = {
                        purchase_id: data.id,
                        purchase_number: data.purchase_number,
                        supplier_id: data.supplier_id,
                        warehouse_id: data.warehouse_id,
                        store_id: data.store_id,
                        tax_type: data.tax_type,
                    };

                    // Map items but set return quantity to 0 initially
                    this.items = data.items.map(item => ({
                        is_included: false,
                        purchase_item_id: item.id,
                        product_id: item.product_id,
                        product_sku_id: item.product_sku_id,
                        unit_id: item.unit_id,
                        product_name: item.product?.name || 'Unknown',
                        sku_code: item.product_sku?.sku || 'Unknown',

                        unit_cost: parseFloat(item.unit_cost),
                        tax_percent: parseFloat(item.tax_percent),
                        tax_type: data.tax_type,

                        max_qty: parseFloat(item.available_qty !== undefined ? item.available_qty : item
                            .quantity),
                        quantity: 0,
                        return_reason: 'damaged',
                        line_total: 0
                    }));

                    this.calculate();
                    BizAlert.toast('Purchase Order loaded successfully!', 'success');
                },

                calculate() {
                    let subtotalAcc = 0;
                    let taxAcc = 0;

                    this.items.forEach(item => {
                        if (!item.is_included) {
                            item.line_total = 0;
                            return;
                        }

                        let qty = parseFloat(item.quantity) || 0;
                        if (qty > item.max_qty) qty = item.max_qty; // Prevent over-returning frontend side
                        item.quantity = qty;

                        let cost = parseFloat(item.unit_cost) || 0;
                        let taxPct = parseFloat(item.tax_percent) || 0;
                        let isInclusive = item.tax_type === 'inclusive';

                        let baseVal = qty * cost;
                        let taxable = 0;
                        let tax = 0;

                        if (isInclusive) {
                            taxable = baseVal / (1 + (taxPct / 100));
                            tax = baseVal - taxable;
                        } else {
                            taxable = baseVal;
                            tax = taxable * (taxPct / 100);
                        }

                        item.line_total = taxable + tax;
                        subtotalAcc += taxable;
                        taxAcc += tax;
                    });

                    this.totals.subtotal = subtotalAcc;
                    this.totals.tax = taxAcc;
                    this.totals.grand_total = subtotalAcc + taxAcc;
                },

                formatCurrency(value) {
                    return parseFloat(value).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }
        }
    </script>
@endpush
