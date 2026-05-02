@extends('layouts.admin')

@section('title', 'Edit Product - Qlinkon BIZNESS')

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Edit Product</h1>
@endsection


@section('content')
    <div class="space-y-6 pb-10" x-data="productForm()">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                {{-- <h1 class="text-2xl font-bold text-[#212538] tracking-tight">Edit Product</h1> --}}
                <p class="text-sm text-gray-500 mt-1 font-medium">Update inventory item details and pricing.</p>
            </div>
            <a href="{{ route('admin.products.index') }}"
                class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm">
                Back
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl shadow-sm text-sm">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i> Please fix the following errors:
                </div>
                <ul class="list-disc list-inside ml-6 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6" @submit="BizAlert.loading('Updating Product...')">
            @csrf
            @method('PUT')

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-5 border-b border-gray-100 pb-2">1. Basic Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Product Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">HSN Code (Optional)</label>
                        <input type="text" name="hsn_code" value="{{ old('hsn_code', $product->hsn_code) }}"
                            placeholder="e.g., 61091000"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all uppercase">
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Status</label>
                        <select name="is_active"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="1" {{ old('is_active', $product->is_active) == '1' ? 'selected' : '' }}>
                                Active (Visible)</option>
                            <option value="0" {{ old('is_active', $product->is_active) == '0' ? 'selected' : '' }}>
                                Draft (Hidden)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Show in Storefront</label>
                        <select name="show_in_storefront"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="1" {{ old('show_in_storefront', $product->show_in_storefront) == '1' ? 'selected' : '' }}>
                                Yes (Listed publicly)</option>
                            <option value="0" {{ old('show_in_storefront', $product->show_in_storefront) == '0' ? 'selected' : '' }}>
                                No (Hidden from store)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Category <span
                                class="text-red-500">*</span></label>
                        <select name="category_id" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="">Select Category</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Supplier (Optional)</label>
                        <select name="supplier_id"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="">Select Supplier</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->id }}"
                                    {{ old('supplier_id', $product->supplier_id) == $sup->id ? 'selected' : '' }}>
                                    {{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Barcode Symbology <span
                                class="text-red-500">*</span></label>
                        <select name="barcode_symbology" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="CODE128"
                                {{ old('barcode_symbology', $product->barcode_symbology) == 'CODE128' ? 'selected' : '' }}>
                                CODE128</option>
                            <option value="CODE39"
                                {{ old('barcode_symbology', $product->barcode_symbology) == 'CODE39' ? 'selected' : '' }}>
                                CODE39</option>
                            <option value="EAN13"
                                {{ old('barcode_symbology', $product->barcode_symbology) == 'EAN13' ? 'selected' : '' }}>
                                EAN13</option>
                            <option value="UPCA"
                                {{ old('barcode_symbology', $product->barcode_symbology) == 'UPCA' ? 'selected' : '' }}>
                                UPCA</option>
                        </select>
                    </div>
                    {{-- <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Max Qty per Order</label>
                        <input type="number" name="quantity_limitation"
                            value="{{ old('quantity_limitation', $product->quantity_limitation) }}" placeholder="No limit"
                            min="1"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all">
                    </div> --}}
                    <div class="lg:col-span-3">
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Description (Optional)</label>
                        <textarea name="description" rows="3"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-5 border-b border-gray-100 pb-2">2. Units & Measurements</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Product Unit <span
                                class="text-red-500">*</span></label>
                        <select name="product_unit_id" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="">Select Base Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ old('product_unit_id', $product->product_unit_id) == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->short_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Sale Unit <span
                                class="text-red-500">*</span></label>
                        <select name="sale_unit_id" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="">Select Sale Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ old('sale_unit_id', $product->sale_unit_id) == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->short_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Purchase Unit <span
                                class="text-red-500">*</span></label>
                        <select name="purchase_unit_id" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="">Select Purchase Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ old('purchase_unit_id', $product->purchase_unit_id) == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->short_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if(has_module('plant_education'))
            {{-- ── Product Type Selector (module-gated) ── --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Product Purpose</h2>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                    <label class="flex-1 flex items-center gap-3 px-4 py-3 rounded-lg border-2 cursor-pointer transition-all"
                        :class="catalogMode === 'sellable' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 bg-white hover:bg-gray-50'">
                        <input type="radio" name="product_type" value="sellable" x-model="catalogMode" class="hidden">
                        <i data-lucide="shopping-cart" class="w-5 h-5" :class="catalogMode === 'sellable' ? 'text-brand-600' : 'text-gray-400'"></i>
                        <div>
                            <p class="text-sm font-bold" :class="catalogMode === 'sellable' ? 'text-brand-700' : 'text-gray-700'">Sellable</p>
                            <p class="text-[11px] text-gray-400">Normal product with pricing, stock & POS</p>
                        </div>
                    </label>
                    <label class="flex-1 flex items-center gap-2 px-4 py-3 rounded-lg border-2 cursor-pointer transition-all"
                        :class="catalogMode === 'catalog' ? 'border-teal-500 bg-teal-50' : 'border-gray-200 bg-white hover:bg-gray-50'">
                        <input type="radio" name="product_type" value="catalog" x-model="catalogMode" class="hidden">
                        <i data-lucide="book-open" class="w-5 h-5" :class="catalogMode === 'catalog' ? 'text-teal-600' : 'text-gray-400'"></i>
                        <div>
                            <p class="text-sm font-bold" :class="catalogMode === 'catalog' ? 'text-teal-700' : 'text-gray-700'">Catalog</p>
                            <p class="text-[11px] text-gray-400">Informational product — no pricing or stock</p>
                        </div>
                    </label>
                </div>
            </div>
            @endif

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" x-show="catalogMode !== 'catalog'" x-cloak>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 border-b border-gray-100 pb-3">
                    <h2 class="text-base sm:text-lg font-bold text-gray-800">3. Product Pricing & SKUs</h2>

                    <div class="flex items-center bg-gray-100 p-1 rounded-lg w-full sm:w-auto">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="single" x-model="productType" class="peer hidden">
                            <span
                                class="block px-4 py-1.5 text-sm font-bold rounded-md peer-checked:bg-white peer-checked:text-[#108c2a] peer-checked:shadow-sm text-gray-500 transition-all">Single
                                Item</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="variable" x-model="productType"
                                class="peer hidden">
                            <span
                                class="block px-4 py-1.5 text-sm font-bold rounded-md peer-checked:bg-white peer-checked:text-[#108c2a] peer-checked:shadow-sm text-gray-500 transition-all">Variable
                                Product</span>
                        </label>
                    </div>
                </div>

                @php
                    // Helper to pre-load single product data securely (loads first SKU regardless of current type to prevent data loss on toggle)
                    $singleSku = $product->skus->first();
                @endphp

                <div x-show="productType === 'single' && catalogMode !== 'catalog'" x-cloak>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">SKU <span
                                    class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <input type="text" name="single_sku" x-model="singleSku"
                                    :required="productType === 'single' && catalogMode !== 'catalog'"
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all uppercase">
                                <button type="button" @click="singleSku = generateSKU()" title="Generate Random SKU"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2 rounded-md transition-colors border border-gray-200 flex-shrink-0">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        {{-- 🌟 NEW: Barcode Field with Generate Button --}}
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Barcode <span class="text-gray-400 font-normal text-xs">(Optional)</span></label>
                            <div class="flex gap-2">
                                <input type="text" name="single_barcode" x-model="singleBarcode"
                                    placeholder="Scan or auto-generate"
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2 text-sm focus:border-[#108c2a] outline-none transition-all uppercase">
                                
                                <button type="button" @click="singleBarcode = generateBarcode()" title="Generate Random Barcode"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2 rounded-md transition-colors border border-gray-200 flex-shrink-0">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        {{-- 🌟 NEW: MRP Field --}}
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">MRP (₹) <span class="text-gray-400 font-normal text-xs">(Optional)</span></label>
                            <input type="number" step="0.01" name="single_mrp" x-model="singleMrp"
                                placeholder="Original Price"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2 text-sm focus:border-[#108c2a] outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Selling Price (₹) <span
                                    class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="single_price"
                                value="{{ old('single_price', $singleSku?->price) }}"
                                :required="productType === 'single' && catalogMode !== 'catalog'"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2 text-sm focus:border-[#108c2a] outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Purchase Cost (₹) <span
                                    class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="single_cost"
                                value="{{ old('single_cost', $singleSku?->cost) }}"
                                :required="productType === 'single' && catalogMode !== 'catalog'"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2 text-sm focus:border-[#108c2a] outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Tax Type <span
                                    class="text-red-500">*</span></label>
                            <select name="single_tax_type" :required="productType === 'single' && catalogMode !== 'catalog'"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                                <option value="exclusive"
                                    {{ old('single_tax_type', $singleSku?->tax_type) == 'exclusive' ? 'selected' : '' }}>
                                    Exclusive</option>
                                <option value="inclusive"
                                    {{ old('single_tax_type', $singleSku?->tax_type) == 'inclusive' ? 'selected' : '' }}>
                                    Inclusive</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Tax (%)</label>
                            <input type="number" step="0.01" name="single_order_tax"
                                value="{{ old('single_order_tax', $singleSku?->order_tax ?? 0) }}"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2 text-sm focus:border-[#108c2a] outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Low Stock Alert</label>
                            <input type="number" name="single_stock_alert"
                                value="{{ old('single_stock_alert', $singleSku?->stock_alert ?? 0) }}"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">HSN Override <span class="text-gray-400 font-normal text-[11px]">(Optional)</span></label>
                            <input type="text" name="single_hsn_code"
                                value="{{ old('single_hsn_code', $singleSku?->hsn_code ?? '') }}"
                                placeholder="e.g., 61091000"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all uppercase">
                            <p class="text-[11px] text-gray-400 mt-1">Leave empty to use product HSN</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                <i data-lucide="package-plus" class="w-4 h-4 text-[#108c2a]"></i> Add Additional Stock (Optional)
                            </h4>
                            <button type="button" @click="addSingleStock()"
                                class="text-xs font-bold text-[#108c2a] hover:underline flex items-center gap-1">
                                <i data-lucide="plus" class="w-3 h-3"></i> Add Warehouse
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(stock, stockIndex) in singleStocks" :key="stock.id">
                                <div class="flex items-center gap-2">
                                    <select :name="'single_stock[' + stockIndex + '][warehouse_id]'"
                                        x-model="stock.warehouse_id" required
                                        class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:border-[#108c2a] outline-none">
                                        <option value="">Select Warehouse...</option>
                                        @foreach ($warehouses ?? [] as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </select>

                                    <input type="number" :name="'single_stock[' + stockIndex + '][qty]'"
                                        x-model="stock.qty" required placeholder="Qty" min="1"
                                        class="w-24 border border-gray-300 rounded px-3 py-2 text-sm text-center focus:border-[#108c2a] outline-none">

                                    <button type="button" @click="removeSingleStock(stockIndex)" title="Remove"
                                        class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </template>
                            <p x-show="singleStocks.length === 0" class="text-xs text-gray-400 italic">Add stock here to immediately increase warehouse inventory.</p>
                        </div>
                    </div>
                </div>

                
                <div x-show="productType === 'variable' && catalogMode !== 'catalog'" x-cloak>
                    <div class="mb-6 bg-[#108c2a]/5 border border-[#108c2a]/20 p-4 rounded-xl flex items-start gap-3">
                        <i data-lucide="layers" class="w-5 h-5 text-[#108c2a] mt-0.5"></i>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Variant Generator</p>
                            <p class="text-[13px] text-gray-600 mt-1">Select the attributes below and click generate. We will automatically append new combinations to your existing variants without overwriting them.</p>
                        </div>
                    </div>
                    
                    {{-- STEP 1: Attribute Selection --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
                        <h3 class="text-[13px] font-bold text-gray-400 uppercase tracking-wider mb-4">Step 1: Select Attributes</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($attributes as $attr)
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-sm font-bold text-gray-800">{{ $attr->name }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2.5">
                                        @foreach ($attr->values as $val)
                                            <label :class="(selectedValues[{{ $attr->id }}] || []).includes('{{ $val->id }}::{{ $val->value }}') ? 'bg-[#108c2a] border-[#108c2a] text-white shadow-sm' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'" 
                                                   class="relative inline-flex items-center justify-center px-4 py-2 text-[13px] font-bold rounded-lg cursor-pointer transition-all select-none border">
                                                <input type="checkbox"
                                                       x-model="selectedValues[{{ $attr->id }}]"
                                                       value="{{ $val->id }}::{{ $val->value }}"
                                                       class="sr-only">
                                                <span>{{ $val->value }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex flex-wrap gap-3 mt-6 pt-5 border-t border-gray-100">
                            <button type="button" @click="confirmVariantGeneration()"
                                class="bg-[#108c2a] hover:bg-[#0c6b1f] text-white px-6 py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                                <i data-lucide="sparkles" class="w-4 h-4"></i> Generate Missing Combinations
                            </button>
                            <button type="button" @click="clearSelections()"
                                class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-2.5 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
                                <i data-lucide="rotate-ccw" class="w-4 h-4 text-gray-400"></i> Clear Selections
                            </button>
                        </div>
                    </div>

                    {{-- STEP 2: Spreadsheet Table --}}
                    <div x-show="variations.length > 0" x-cloak x-transition>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-[13px] font-bold text-gray-400 uppercase tracking-wider">Step 2: Pricing & Inventory</h3>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 px-3 py-1 rounded-full" 
                                      x-text="variations.length + ' Total Variants'"></span>
                            </div>
                        </div>

                        {{-- 🌟 BULK EDIT PANEL --}}
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-4 shadow-inner">
                            <div class="flex items-center gap-2 mb-3">
                                <i data-lucide="edit-3" class="w-4 h-4 text-gray-500"></i>
                                <h4 class="text-[13px] font-bold text-gray-700">Quick Bulk Edit</h4>
                                <span class="text-[11px] text-gray-400 ml-1">(Leaves empty fields unchanged)</span>
                            </div>
                            
                            <div class="flex flex-wrap items-end gap-3">
                                <div class="w-24">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Price (₹)</label>
                                    <input type="number" step="0.01" x-model="bulk.price" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm outline-none focus:border-[#108c2a]" placeholder="--">
                                </div>
                                <div class="w-24">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Cost (₹)</label>
                                    <input type="number" step="0.01" x-model="bulk.cost" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm outline-none focus:border-[#108c2a]" placeholder="--">
                                </div>
                                <div class="w-28">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Tax Type</label>
                                    <select x-model="bulk.tax_type" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm outline-none focus:border-[#108c2a] bg-white">
                                        <option value="">Leave As Is</option>
                                        <option value="exclusive">Exclusive</option>
                                        <option value="inclusive">Inclusive</option>
                                    </select>
                                </div>
                                <div class="w-20">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Tax (%)</label>
                                    <input type="number" step="0.01" x-model="bulk.tax_percent" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm outline-none focus:border-[#108c2a]" placeholder="--">
                                </div>
                                <div class="w-20">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Alert Qty</label>
                                    <input type="number" x-model="bulk.alert" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm outline-none focus:border-[#108c2a]" placeholder="--">
                                </div>
                                
                                <button type="button" @click="applyBulkEdit()" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-1.5 rounded text-sm font-bold transition-colors shadow-sm ml-auto sm:ml-0 h-[34px]">
                                    Apply to All
                                </button>
                            </div>
                        </div>
                        
                        {{-- 🖥️ DESKTOP VIEW (TABLE) --}}
                        <div class="hidden md:block overflow-x-auto border border-gray-200 rounded-xl shadow-sm bg-white">
                            <table class="w-full text-left border-collapse whitespace-nowrap">
                                <thead class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <tr>
                                        <th class="px-4 py-3 sticky left-0 bg-gray-50 z-10 border-r border-gray-200 shadow-[1px_0_0_0_#e5e7eb]">Variant Name</th>
                                        <th class="px-4 py-3">SKU</th>
                                        <th class="px-4 py-3">Barcode</th>
                                        <th class="px-4 py-3">MRP (₹)</th>
                                        <th class="px-4 py-3">Price (₹) <span class="text-red-500">*</span></th>
                                        <th class="px-4 py-3">Cost (₹) <span class="text-red-500">*</span></th>
                                        <th class="px-4 py-3">Tax Type</th>
                                        <th class="px-4 py-3">Tax (%)</th>
                                        <th class="px-4 py-3">HSN Code</th>
                                        <th class="px-4 py-3">Alert Qty</th>
                                        <th class="px-4 py-3 text-center">Stock (Whs)</th>
                                        <th class="px-4 py-3 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-for="(variant, index) in variations" :key="variant.id">
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            
                                            <td class="px-4 py-2 font-bold text-[13px] text-gray-800 sticky left-0 bg-white shadow-[1px_0_0_0_#f3f4f6] z-10 border-r border-gray-100">
                                                <span x-text="variant.label || ('Variation ' + (index + 1))"></span>
                                                <template x-if="variant.is_existing">
                                                    <span class="ml-2 px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 text-[9px] uppercase tracking-wider">Saved</span>
                                                </template>
                                            </td>
                                            
                                            <td class="px-2 py-2">
                                                <input type="text" :name="'variations['+index+'][sku]'" x-model="variant.sku"
                                                       class="w-32 border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none uppercase bg-gray-50 focus:bg-white transition-colors" placeholder="Auto">
                                            </td>

                                            <td class="px-2 py-2">
                                                <input type="text" :name="'variations['+index+'][barcode]'" x-model="variant.barcode"
                                                       class="w-32 border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="Barcode">
                                            </td>

                                            <td class="px-2 py-2">
                                                <input type="number" step="0.01" :name="'variations['+index+'][mrp]'" x-model="variant.mrp"
                                                       class="w-24 border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="0.00">
                                            </td>

                                            <td class="px-2 py-2">
                                                <input type="number" step="0.01" :name="'variations['+index+'][price]'" x-model="variant.price" required
                                                       class="w-24 border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none font-bold text-gray-800 bg-gray-50 focus:bg-white transition-colors" placeholder="0.00">
                                            </td>

                                            <td class="px-2 py-2">
                                                <input type="number" step="0.01" :name="'variations['+index+'][cost]'" x-model="variant.cost" required
                                                       class="w-24 border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="0.00">
                                            </td>

                                            <td class="px-2 py-2">
                                                <select :name="'variations['+index+'][tax_type]'" x-model="variant.tax_type"
                                                        class="w-24 border border-gray-300 rounded px-2 py-2 text-xs focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors">
                                                    <option value="exclusive">Exclusive</option>
                                                    <option value="inclusive">Inclusive</option>
                                                </select>
                                            </td>

                                            <td class="px-2 py-2">
                                                <input type="number" step="0.01" :name="'variations['+index+'][order_tax]'" x-model="variant.order_tax"
                                                       class="w-20 border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="0">
                                            </td>

                                            <td class="px-2 py-2">
                                                <input type="text" :name="'variations['+index+'][hsn_code]'" x-model="variant.hsn_code"
                                                       class="w-24 border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="HSN">
                                            </td>

                                            <td class="px-2 py-2">
                                                <input type="number" :name="'variations['+index+'][stock_alert]'" x-model="variant.alert"
                                                       class="w-20 border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="0">
                                            </td>

                                            {{-- 🌟 NEW: Stock Management Cell (Only editable for new variations) --}}
                                            <td class="px-2 py-2 text-center">
                                                <template x-if="!variant.is_existing">
                                                    <div>
                                                        <button type="button" @click="openStockModal(index)"
                                                            class="bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors whitespace-nowrap">
                                                            <span x-text="calculateTotalStock(variant) > 0 ? calculateTotalStock(variant) + ' Units' : '+ Add Stock'"></span>
                                                        </button>

                                                        {{-- Hidden Payload for Laravel's processInitialStock --}}
                                                        <template x-for="(stock, stockIdx) in variant.stocks" :key="stock.id">
                                                            <div>
                                                                <input type="hidden" :name="'variations['+index+'][stock]['+stockIdx+'][warehouse_id]'" :value="stock.warehouse_id">
                                                                <input type="hidden" :name="'variations['+index+'][stock]['+stockIdx+'][qty]'" :value="stock.qty">
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="variant.is_existing">
                                                    <span class="text-[10px] text-gray-400 font-bold uppercase whitespace-nowrap" title="Manage via Purchases or Adjustments">Locked</span>
                                                </template>
                                            </td>

                                            <td class="px-3 py-2 text-center">
                                                <button type="button" @click="removeVariation(index)" title="Remove Variant"
                                                        class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </td>

                                            <template x-if="variant.is_existing">
                                                <input type="hidden" :name="'variations[' + index + '][id]'" :value="variant.id">
                                            </template>
                                            
                                            <template x-for="(valueId, attrId) in variant.attrs" :key="attrId">
                                                <input type="hidden" :name="'variations['+index+'][attrs]['+attrId+']'" :value="valueId">
                                            </template>
                                      </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        {{-- 📱 MOBILE VIEW (CARDS) --}}
                        <div class="md:hidden space-y-4 mt-4">
                            <template x-for="(variant, index) in variations" :key="variant.id">
                                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm relative flex flex-col gap-3">
                                    
                                    {{-- Header --}}
                                    <div class="flex justify-between items-start pr-8">
                                        <div>
                                            <div class="font-bold text-[14px] text-gray-800" x-text="variant.label || ('Variation ' + (index + 1))"></div>
                                            <template x-if="variant.is_existing">
                                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 text-[9px] uppercase tracking-wider">Saved</span>
                                            </template>
                                        </div>
                                        <button type="button" @click="typeof removeGeneratedVariant === 'function' ? removeGeneratedVariant(index) : removeVariation(index)" title="Remove Variant"
                                                class="absolute top-4 right-4 text-red-400 hover:text-red-600 bg-red-50 p-1.5 rounded transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>

                                    {{-- Grid for Inputs --}}
                                    <div class="grid grid-cols-2 gap-3 mt-1">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">SKU</label>
                                            <input type="text" :name="'variations['+index+'][sku]'" x-model="variant.sku"
                                                   class="w-full border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none uppercase bg-gray-50 focus:bg-white transition-colors" placeholder="Auto">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Barcode</label>
                                            <input type="text" :name="'variations['+index+'][barcode]'" x-model="variant.barcode"
                                                   class="w-full border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="Barcode">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Cost (₹) <span class="text-red-500">*</span></label>
                                            <input type="number" step="0.01" :name="'variations['+index+'][cost]'" x-model="variant.cost" required
                                                   class="w-full border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Price (₹) <span class="text-red-500">*</span></label>
                                            <input type="number" step="0.01" :name="'variations['+index+'][price]'" x-model="variant.price" required
                                                   class="w-full border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none font-bold text-gray-800 bg-gray-50 focus:bg-white transition-colors" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">MRP (₹)</label>
                                            <input type="number" step="0.01" :name="'variations['+index+'][mrp]'" x-model="variant.mrp"
                                                   class="w-full border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Tax Type</label>
                                            <select :name="'variations['+index+'][tax_type]'" x-model="variant.tax_type"
                                                    class="w-full border border-gray-300 rounded px-2 py-2 text-xs focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors">
                                                <option value="exclusive">Exclusive</option>
                                                <option value="inclusive">Inclusive</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Tax (%)</label>
                                            <input type="number" step="0.01" :name="'variations['+index+'][order_tax]'" x-model="variant.order_tax"
                                                   class="w-full border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="0">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Alert Qty</label>
                                            <input type="number" :name="'variations['+index+'][stock_alert]'" x-model="variant.alert"
                                                   class="w-full border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="0">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">HSN Code</label>
                                            <input type="text" :name="'variations['+index+'][hsn_code]'" x-model="variant.hsn_code"
                                                   class="w-full border border-gray-300 rounded px-2.5 py-2 text-sm focus:border-[#108c2a] outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="HSN">
                                        </div>
                                    </div>

                                    {{-- Stock Button & Logic (Edit specific) --}}
                                    <div class="mt-2 border-t border-gray-100 pt-3 flex justify-between items-center">
                                        <span class="text-[11px] font-bold text-gray-500 uppercase">Stock (Whs)</span>
                                        <template x-if="!variant.is_existing">
                                            <div>
                                                <button type="button" @click="openStockModal(index)"
                                                    class="bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors whitespace-nowrap">
                                                    <span x-text="calculateTotalStock(variant) > 0 ? calculateTotalStock(variant) + ' Units' : '+ Add Stock'"></span>
                                                </button>
                                                <template x-for="(stock, stockIdx) in variant.stocks" :key="stock.id">
                                                    <div>
                                                        <input type="hidden" :name="'variations['+index+'][stock]['+stockIdx+'][warehouse_id]'" :value="stock.warehouse_id">
                                                        <input type="hidden" :name="'variations['+index+'][stock]['+stockIdx+'][qty]'" :value="stock.qty">
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="variant.is_existing">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase whitespace-nowrap bg-gray-100 px-2 py-1 rounded" title="Manage via Purchases or Adjustments">Locked</span>
                                        </template>
                                    </div>

                                    {{-- Hidden Inputs --}}
                                    <template x-if="variant.is_existing">
                                        <input type="hidden" :name="'variations[' + index + '][id]'" :value="variant.id">
                                    </template>
                                    <template x-for="(valueId, attrId) in variant.attrs" :key="attrId">
                                        <input type="hidden" :name="'variations['+index+'][attrs]['+attrId+']'" :value="valueId">
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            
           <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="mb-5 border-b border-gray-100 pb-3">
                    <h2 class="text-lg font-bold text-gray-800">4. Product Media</h2>
                    <p class="text-xs text-gray-500 mt-1">Add images or YouTube videos. Drag to reorder, and click 'Set Main' for your primary thumbnail.</p>
                </div>

                <input type="hidden" name="primary_media_index" :value="mediaList.findIndex(m => m.id == primaryMediaId)">

                {{-- 🌟 NEW: Responsive Grid Container --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    
                    <template x-for="(media, index) in mediaList" :key="media.id">
                        <div class="relative flex flex-col bg-gray-50 rounded-xl border-2 transition-all duration-200 group overflow-hidden shadow-sm"
                             :class="primaryMediaId == media.id ? 'border-[#108c2a] ring-2 ring-[#108c2a]/20' : 'border-gray-200 hover:border-gray-300'"
                             draggable="true" @dragstart="dragStart(index, $event)" @dragend="dragEnd()"
                             @dragover="dragOver($event)" @drop="drop(index)">

                            {{-- Hidden Inputs --}}
                            <input type="hidden" :name="'media[' + index + '][type]'" :value="media.type">
                            <template x-if="media.is_existing">
                                <input type="hidden" :name="'media[' + index + '][id]'" :value="media.id">
                            </template>

                            {{-- 🌟 Visual Main Image Badge --}}
                            <label x-show="media.type === 'image'" class="absolute top-2 left-2 z-20 cursor-pointer transition-opacity" :class="primaryMediaId == media.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                                <input type="radio" :value="media.id" x-model="primaryMediaId" class="hidden">
                                <div :class="primaryMediaId == media.id ? 'bg-[#108c2a] text-white shadow-md' : 'bg-white text-gray-600 shadow border border-gray-200 hover:bg-gray-50'" 
                                     class="px-2 py-1 rounded-md text-[10px] font-bold tracking-wide flex items-center gap-1 transition-colors">
                                    <i data-lucide="star" class="w-3 h-3" :class="primaryMediaId == media.id ? 'fill-current' : ''"></i>
                                    <span x-text="primaryMediaId == media.id ? 'Main' : 'Set Main'"></span>
                                </div>
                            </label>

                            {{-- Delete Button Overlay --}}
                            <button type="button" @click="removeMedia(index)"
                                class="absolute top-2 right-2 z-20 bg-white text-red-500 hover:bg-red-50 p-1.5 rounded-md shadow border border-gray-200 opacity-0 group-hover:opacity-100 transition-opacity">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>

                            {{-- Media Preview Area (Perfectly Square) --}}
                            <div class="relative aspect-square w-full bg-gray-100 flex items-center justify-center border-b border-gray-200 overflow-hidden">
                                
                                {{-- Drag Handle (Overlay on hover) --}}
                                <div class="absolute inset-0 z-10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none bg-black/5">
                                    <div class="bg-white/90 p-1.5 rounded shadow-sm backdrop-blur-sm">
                                        <i data-lucide="grip" class="w-4 h-4 text-gray-500"></i>
                                    </div>
                                </div>

                                {{-- Image Logic --}}
                                <template x-if="media.type === 'image'">
                                    <div class="w-full h-full relative">
                                        {{-- Invisible file input overlaid ONLY for new images --}}
                                        <template x-if="!media.is_existing">
                                            <input type="file" :name="'media[' + index + '][file]'" accept="image/*" required
                                                @change="
                                                    if($event.target.files.length > 0) {
                                                        media.preview = URL.createObjectURL($event.target.files[0]);
                                                    }
                                                "
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                        </template>

                                        {{-- Instant Preview Image (Works for both existing saved images and newly uploaded blobs) --}}
                                        <template x-if="media.preview">
                                            <img :src="media.preview" class="w-full h-full object-cover">
                                        </template>
                                        {{-- Placeholder before selection --}}
                                        <template x-if="!media.preview">
                                            <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 gap-2">
                                                <i data-lucide="image-plus" class="w-6 h-6"></i>
                                                <span class="text-[10px] font-bold uppercase tracking-wider">Click to Browse</span>
                                            </div>
                                        </template>
                                        
                                        {{-- 🌟 Saved Image Indicator Overlay --}}
                                        <template x-if="media.is_existing">
                                            <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-[9px] px-2 py-1 flex justify-between items-center z-10">
                                                <span>Saved in DB</span>
                                                <span x-text="'ID: ' + media.id"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- YouTube Logo --}}
                                <template x-if="media.type === 'youtube'">
                                    <div class="w-full h-full flex flex-col items-center justify-center text-red-500 gap-2 bg-red-50/50">
                                        <i data-lucide="youtube" class="w-8 h-8"></i>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-red-700">Video</span>
                                    </div>
                                </template>
                            </div>

                            {{-- Footer Controls (Inputs nested cleanly at bottom of card) --}}
                            <div class="p-2.5 flex flex-col gap-2 bg-white flex-1 justify-end relative z-20">
                                
                                {{-- YouTube URL Input --}}
                                <template x-if="media.type === 'youtube'">
                                    <div>
                                        <input type="url" :name="'media[' + index + '][url]'" x-model="media.url" placeholder="Paste YouTube URL..." required
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-[11px] focus:border-red-400 outline-none transition-colors">
                                    </div>
                                </template>

                                {{-- Unified Variant Dropdown (edit.blade uses variant.id) --}}
                                <template x-if="productType === 'variable'">
                                    <div>
                                        <select :name="'media[' + index + '][sku_index]'" x-model="media.sku_index"
                                            class="w-full border border-gray-200 rounded px-2 py-1.5 text-[10px] text-gray-600 bg-gray-50 focus:bg-white outline-none focus:border-[#108c2a] transition-colors">
                                            <option value="">All Variants</option>
                                            <template x-for="(variant, vIndex) in variations" :key="variant.id">
                                                <option :value="variant.id"
                                                    x-text="variant.sku ? 'SKU: ' + variant.sku : 'Variant ' + (vIndex + 1)"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- 🌟 The "Add Media" Action Cards --}}
                    <button type="button" @click="addMedia('image')"
                        class="relative aspect-square flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl hover:bg-[#108c2a]/5 hover:border-[#108c2a] transition-colors text-gray-400 hover:text-[#108c2a] group">
                        <div class="bg-gray-100 group-hover:bg-[#108c2a]/10 p-3 rounded-full mb-2 transition-colors">
                            <i data-lucide="image-plus" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Add Image</span>
                    </button>

                    <button type="button" @click="addMedia('youtube')"
                        class="relative aspect-square flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl hover:bg-red-50 hover:border-red-400 transition-colors text-gray-400 hover:text-red-500 group">
                        <div class="bg-gray-100 group-hover:bg-red-100 p-3 rounded-full mb-2 transition-colors">
                            <i data-lucide="youtube" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Add Video</span>
                    </button>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">

                @if(has_module('plant_education'))

                    {{-- ── Plant Education: 2 fixed care fields ── --}}
                    <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-3">
                        <i data-lucide="leaf" class="w-5 h-5 text-green-600 shrink-0"></i>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">5. Product Information <span class="text-gray-400 text-sm font-normal">(Optional)</span></h2>
                            <p class="text-xs text-gray-500 mt-0.5">Provide sunlight and watering care info for this plant.</p>
                        </div>
                    </div>

                    @php
                        $guideMap = collect($product->product_guide ?? [])->keyBy('title');
                        $sunlightDesc = $guideMap->get('Sunlight')['description'] ?? '';
                        $wateringDesc = $guideMap->get('Watering')['description'] ?? '';
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Sunlight --}}
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xl">☀️</span>
                                <label class="text-[13px] font-bold text-amber-800">Sunlight</label>
                            </div>
                            <input type="hidden" name="product_guide[0][title]" value="Sunlight">
                            <input type="text" name="product_guide[0][description]"
                                value="{{ old('product_guide.0.description', $sunlightDesc) }}"
                                placeholder="e.g., 4–6 hours of indirect sunlight"
                                class="w-full border border-amber-200 rounded-lg px-3 py-2 text-sm focus:border-amber-400 outline-none bg-white transition-all">
                        </div>

                        {{-- Watering --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xl">💧</span>
                                <label class="text-[13px] font-bold text-blue-800">Watering</label>
                            </div>
                            <input type="hidden" name="product_guide[1][title]" value="Watering">
                            <input type="text" name="product_guide[1][description]"
                                value="{{ old('product_guide.1.description', $wateringDesc) }}"
                                placeholder="e.g., 1–2 times a week"
                                class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 outline-none bg-white transition-all">
                        </div>

                    </div>

                    {{-- Additional Info sections (optional) — indices start at 2 after Sunlight+Watering --}}
                    <div class="mt-5 pt-4 border-t border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <p class="text-sm font-bold text-gray-700">Additional Info <span class="text-gray-400 text-xs font-normal">(Optional)</span></p>
                                <p class="text-xs text-gray-500 mt-0.5">Add extra care tips, fertilizing notes, repotting info, etc.</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(guide, index) in productGuides" :key="guide.id">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 relative">
                                    <button type="button" @click="removeGuide(index)"
                                        class="absolute top-3 right-3 text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded transition-colors"
                                        title="Remove">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                    <div class="grid grid-cols-1 gap-3 pr-10">
                                        <div>
                                            <label class="block text-[12px] font-bold text-gray-700 mb-1">Section Title <span class="text-red-500">*</span></label>
                                            <input type="text" :name="'product_guide[' + (index + 2) + '][title]'"
                                                x-model="guide.title" required placeholder="e.g., Fertilizing Tips"
                                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-[12px] font-bold text-gray-700 mb-1">Details <span class="text-red-500">*</span></label>
                                            <textarea :name="'product_guide[' + (index + 2) + '][description]'" x-model="guide.description" required rows="2"
                                                placeholder="e.g., Feed monthly during the growing season."
                                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-[#108c2a] outline-none transition-all resize-y bg-white"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <button type="button" @click="addGuide()"
                            class="text-xs font-bold bg-[#108c2a]/10 hover:bg-[#108c2a]/20 text-[#108c2a] px-3 py-1.5 rounded flex items-center gap-1 transition-colors mt-4 w-fit">
                            <i data-lucide="plus-circle" class="w-3 h-3"></i> Add Section
                        </button>
                    </div>

                @else

                    <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-2">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">5. Product Information <span
                                    class="text-gray-400 text-sm font-normal">(Optional)</span></h2>
                            <p class="text-xs text-gray-500 mt-1">Add care instructions, setup guides, or educational info for
                                your customers.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(guide, index) in productGuides" :key="guide.id">
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 relative">

                                <button type="button" @click="removeGuide(index)"
                                    class="absolute top-3 right-3 text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded transition-colors"
                                    title="Remove Guide">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>

                                <div class="grid grid-cols-1 gap-4 pr-10">
                                    <div>
                                        <label class="block text-[12px] font-bold text-gray-700 mb-1">Section Title <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" :name="'product_guide[' + index + '][title]'"
                                            x-model="guide.title" required placeholder="e.g., Washing Instructions"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-bold text-gray-700 mb-1">Details <span
                                                class="text-red-500">*</span></label>
                                        <textarea :name="'product_guide[' + index + '][description]'" x-model="guide.description" required rows="2"
                                            placeholder="e.g., Machine wash cold."
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-[#108c2a] outline-none transition-all resize-y bg-white"></textarea>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addGuide()"
                        class="text-xs font-bold bg-[#108c2a]/10 hover:bg-[#108c2a]/20 text-[#108c2a] px-3 py-1.5 rounded flex items-center gap-1 transition-colors mt-4 w-fit">
                        <i data-lucide="plus-circle" class="w-3 h-3"></i> Add Section
                    </button>

                @endif

            </div>

            <div class="flex flex-col sm:flex-row justify-end pt-4 border-t border-gray-200">
                <button type="submit"
                    class="w-full sm:w-auto bg-[#108c2a] hover:bg-[#0c6b1f] text-white px-8 py-3 rounded-xl text-sm font-bold shadow-md flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="save" class="w-4 h-4"></i> Update Product
                </button>
            </div>

            {{-- 🌟 VARIANT STOCK MODAL --}}
            <div x-show="isStockModalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="closeStockModal()">
                <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl flex flex-col overflow-hidden" x-show="isStockModalOpen" x-transition.scale.origin.bottom>
                    
                    {{-- Modal Header --}}
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <div>
                            <h3 class="text-[15px] font-bold text-gray-800">Adjust Opening Stock</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Assign physical stock to warehouses</p>
                        </div>
                        <button type="button" @click="closeStockModal()" class="text-gray-400 hover:text-red-500 bg-white rounded-md p-1 shadow-sm border border-gray-200"><i data-lucide="x" class="w-5 h-5"></i></button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 overflow-y-auto max-h-[60vh] bg-white">
                        <template x-if="activeVariantIndex !== null && getActiveVariant()">
                            <div class="space-y-3">
                                <template x-for="(stock, stockIndex) in getActiveVariant().stocks" :key="stock.id">
                                    <div class="flex items-center gap-3 bg-gray-50 p-2 rounded-lg border border-gray-200">
                                        <select x-model="stock.warehouse_id" class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                            <option value="">Select Warehouse...</option>
                                            @foreach ($warehouses ?? [] as $wh)
                                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                            @endforeach
                                        </select>

                                        <input type="number" x-model="stock.qty" placeholder="Qty" min="1"
                                            class="w-24 border border-gray-300 rounded-md px-3 py-2 text-sm text-center font-bold text-gray-800 focus:border-[#108c2a] outline-none">

                                        <button type="button" @click="removeActiveVariantStock(stockIndex)" title="Remove" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </template>
                                
                                <div class="pt-2">
                                    <button type="button" @click="addActiveVariantStock()" class="text-xs font-bold text-[#108c2a] hover:bg-[#108c2a]/10 px-3 py-2 rounded-lg flex items-center gap-1.5 transition-colors border border-[#108c2a]/20 border-dashed w-full justify-center">
                                        <i data-lucide="plus" class="w-4 h-4"></i> Add Warehouse Allocation
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end">
                        <button type="button" @click="closeStockModal()" class="bg-[#108c2a] text-white font-bold text-sm px-8 py-2.5 rounded-xl shadow-sm hover:bg-[#0c6b1f] transition-all">Done</button>
                    </div>
                </div>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function productForm() {
            return {
                catalogMode: @json(old('product_type', $product->product_type ?? 'sellable')),
                productType: @json(old('type', $product->skus->count() > 1 ? 'variable' : $product->type)),
                singleSku: @json(old('single_sku', $singleSku ? $singleSku->sku : '')),
                singleMrp: @json(old('single_mrp', $singleSku ? $singleSku->mrp : '')), 
                singleBarcode: @json(old('single_barcode', $singleSku ? $singleSku->barcode : '')),
                // 🌟 Pre-load Variations from Database
                @php
                    $variations = old(
                        'variations',
                        $product->skus->count() > 0
                            ? $product->skus
                                ->map(function ($sku) {
                                    return [
                                        'id' => $sku->id,
                                        'is_existing' => true,
                                        'sku' => $sku->sku,
                                        'barcode' => $sku->barcode,
                                        'price' => $sku->price,
                                        'cost' => $sku->cost,
                                        'mrp' => $sku->mrp,
                                        'tax_type' => $sku->tax_type,
                                        'order_tax' => $sku->order_tax,
                                        'alert' => $sku->stock_alert,
                                        'hsn_code' => $sku->hsn_code ?? '',
                                        'attrs' => (object) $sku->skuValues->pluck('attribute_value_id', 'attribute_id')->toArray(),
                                    ];
                                })
                                ->values()
                                ->toArray()
                            : [
                                [
                                    'id' => 'temp_' . time(),
                                    'is_existing' => false,
                                    'sku' => '',
                                    'barcode' => '',
                                    'price' => '',
                                    'cost' => '',
                                    'mrp' => '',
                                    'tax_type' => 'exclusive',
                                    'order_tax' => 0,
                                    'alert' => 0,
                                    'hsn_code' => '',
                                    'attrs' => (object) [],
                                ],
                            ],
                    );
                @endphp

                variations: @json($variations).map((v, i) => ({ ...v, id: v.id || 'var_' + Date.now() + i })),

                // 🌟 Pre-load Media from Database
                @php
                    $skuIndexMap = collect($variations)
                        ->pluck('id')
                        ->filter(fn ($id) => is_numeric($id))
                        ->mapWithKeys(fn ($id, $index) => [(int) $id => $index])
                        ->all();

                    $mediaList = old(
                        'media',
                        $product->media
                            ->sortBy('sort_order')
                            ->map(function ($m) use ($skuIndexMap) {
                                return [
                                    'id' => $m->id,
                                    'is_existing' => true,
                                    'type' => $m->media_type,
                                    'url' => $m->media_type === 'youtube' ? $m->media_path : '',
                                    'preview' => $m->media_type === 'image' ? asset('storage/' . $m->media_path) : '',
                                    'sku_index' => $m->product_sku_id !== null ? ($skuIndexMap[$m->product_sku_id] ?? '') : '',
                                ];
                            })
                            ->values()
                            ->toArray(),
                    );
                @endphp

                mediaList: @json($mediaList).map((m, i) => ({ ...m, id: m.id || 'media_' + Date.now() + i })),

                primaryMediaId: @json(optional($product->media->where('is_primary', true)->first())->id),

                draggedIndex: null,

                // 🌟 Pre-load Product Guides
                @php
                    // When plant_education is active, Sunlight & Watering are fixed inputs (index 0 & 1).
                    // Only load remaining additional guides into the dynamic Alpine array.
                    $isPlantModule = has_module('plant_education');
                    $guides = old(
                        'product_guide',
                        collect($product->product_guide ?? [])
                            ->when($isPlantModule, fn ($c) => $c->filter(
                                fn ($g) => ! in_array($g['title'] ?? '', ['Sunlight', 'Watering'])
                            ))
                            ->map(function ($g) {
                                return [
                                    'id' => 'guide_' . uniqid(),
                                    'title' => $g['title'] ?? '',
                                    'description' => $g['description'] ?? '',
                                ];
                            })
                            ->values()
                            ->toArray(),
                    );
                @endphp

                productGuides: @json($guides).map((g, i) => ({ ...g, id: g.id || 'guide_' + Date.now() + i })),

                addGuide() {
                    if (this.productGuides.length >= 15) {
                        BizAlert.toast('Maximum 15 guidance sections allowed.', 'error');
                        return;
                    }

                    this.productGuides.push({
                        id: Date.now(),
                        title: '',
                        description: ''
                    });

                    setTimeout(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }, 50);
                },

                removeGuide(index) {
                    this.productGuides.splice(index, 1);
                },

                addMedia(type) {
                    if (this.mediaList.length >= 10) {
                        return BizAlert.toast('Maximum 10 media items allowed.', 'error');
                    }

                    const newItem = {
                        id: Date.now(),
                        is_existing: false,
                        type: type,
                        url: '',
                        preview: '',
                        sku_index: ''
                    };

                    this.mediaList.push(newItem);

                    if (type === 'image' && !this.primaryMediaId) {
                        this.primaryMediaId = newItem.id;
                    }

                    setTimeout(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }, 50);
                },

                removeMedia(index) {
                    const removedId = this.mediaList[index].id;
                    this.mediaList.splice(index, 1);

                    // THE FIX: Use == so string "8" matches integer 8
                    if (this.primaryMediaId == removedId) {
                        const nextImage = this.mediaList.find(m => m.type === 'image');
                        this.primaryMediaId = nextImage ? nextImage.id : null;
                    }
                },

                dragStart(index, event) {
                    this.draggedIndex = index;
                    event.dataTransfer.effectAllowed = 'move';
                },

                dragEnd() {
                    this.draggedIndex = null;
                },

                dragOver(event) {
                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                },

                drop(index) {
                    if (this.draggedIndex === null || this.draggedIndex === index) return;

                    const draggedItem = this.mediaList.splice(this.draggedIndex, 1)[0];
                    this.mediaList.splice(index, 0, draggedItem);

                    this.draggedIndex = null;
                },

                // 🌟 Intelligent Formatting for Attributes
                formatSkuAttribute(val) {
                    let cleanVal = val.toUpperCase().trim();
                    const sizeMap = { 'SMALL': 'S', 'MEDIUM': 'M', 'LARGE': 'L', 'EXTRA LARGE': 'XL', 'EXTRA SMALL': 'XS' };
                    if (sizeMap[cleanVal]) return sizeMap[cleanVal];
                    return cleanVal.substring(0, 3);
                },
                generateSKU(length = 8, prefix = 'SKU-') {
                    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                    let result = prefix;

                    // Use crypto for better randomness
                    const randomValues = new Uint32Array(length);
                    crypto.getRandomValues(randomValues);

                    for (let i = 0; i < length; i++) {
                        result += chars[randomValues[i] % chars.length];
                    }

                    return result;
                },

                // 🌟 New Intelligent SKU Builder
                generateIntelligentSku(comboNames, usedSkus) {
                    let nameInput = document.querySelector('input[name="name"]');
                    let pName = nameInput && nameInput.value.trim() ? nameInput.value.trim() : 'PRD';
                    let prefix = pName.substring(0, 3).toUpperCase();
                    let attrCodes = comboNames.map(name => this.formatSkuAttribute(name));
                    let baseSku = [prefix, ...attrCodes].join('-');
                    let finalSku = baseSku;
                    let counter = 1;
                    
                    while (usedSkus.has(finalSku.toUpperCase())) {
                        finalSku = `${baseSku}-${counter.toString().padStart(2, '0')}`;
                        counter++;
                    }
                    usedSkus.add(finalSku.toUpperCase());
                    return finalSku;
                },

                calculateCombinations() {
                    let total = 1;
                    let hasSelection = false;
                    for (const key in this.selectedValues) {
                        if (this.selectedValues[key] && this.selectedValues[key].length > 0) {
                            total *= this.selectedValues[key].length;
                            hasSelection = true;
                        }
                    }
                    return hasSelection ? total : 0;
                },

                confirmVariantGeneration() {
                    const total = this.calculateCombinations();
                    if (total === 0) {
                        return BizAlert.toast('Please select at least one attribute.', 'error');
                    }
                    if (total > 100) {
                        return BizAlert.toast(`Blocked: Attempting to generate ${total} variants. Maximum allowed is 100.`, 'error');
                    }
                    if (total > 50) {
                        if (!confirm(`Warning: You are about to generate ${total} variants. Continue?`)) return;
                    }
                    this.generateVariants();
                },

                applyBulkEdit() {
                    if(this.variations.length === 0) return;
                    let applied = false;
                    this.variations.forEach(variant => {
                        if (this.bulk.price !== '') { variant.price = this.bulk.price; applied = true; }
                        if (this.bulk.cost !== '') { variant.cost = this.bulk.cost; applied = true; }
                        if (this.bulk.tax_type !== '') { variant.tax_type = this.bulk.tax_type; applied = true; }
                        if (this.bulk.tax_percent !== '') { variant.order_tax = this.bulk.tax_percent; applied = true; }
                        if (this.bulk.alert !== '') { variant.alert = this.bulk.alert; applied = true; }
                    });
                    if(applied) BizAlert.toast(`Bulk updated ${this.variations.length} variants successfully!`, 'success');
                    this.bulk = { price: '', cost: '', tax_type: '', tax_percent: '', alert: '' };
                },

                generateVariants() {
                    let arraysToCombine = [];
                    let attrKeys = [];

                    for (const [attrId, values] of Object.entries(this.selectedValues)) {
                        if (values && values.length > 0) {
                            arraysToCombine.push(values);
                            attrKeys.push(attrId);
                        }
                    }

                    const combine = (arrs) => arrs.reduce((a, b) => a.flatMap(d => b.map(e => [...d, e])), [[]]);
                    const combinations = combine(arraysToCombine);

                    let usedSkus = new Set();
                    let existingSignatures = new Set();

                    // Track existing items so we don't accidentally overwrite or duplicate them
                    this.variations.forEach(v => {
                        if (v.sku) usedSkus.add(v.sku.toUpperCase());
                        if (v.attrs) existingSignatures.add(JSON.stringify(v.attrs));
                    });

                    let addedCount = 0;

                    combinations.forEach((combo) => {
                        let labelParts = [];
                        let attrsPayload = {};

                        combo.forEach((valString, index) => {
                            let [valId, valName] = valString.split('::');
                            labelParts.push(valName);
                            attrsPayload[attrKeys[index]] = valId;
                        });

                        // Only push to the table if this exact attribute combination doesn't already exist
                        if (!existingSignatures.has(JSON.stringify(attrsPayload))) {
                            this.variations.push({
                                id: Date.now() + Math.random().toString(36).substr(2, 9),
                                is_existing: false,
                                label: labelParts.join(' / '),
                                attrs: attrsPayload,
                                sku: this.generateIntelligentSku(labelParts, usedSkus),
                                barcode: '',
                                mrp: this.singleMrp || '',
                                price: '',
                                cost: '',
                                tax_type: 'exclusive',
                                order_tax: '',
                                hsn_code: '',
                                alert: 0,
                                stocks: [] // 🌟 CRITICAL: Init empty stock array
                            });
                            addedCount++;
                            existingSignatures.add(JSON.stringify(attrsPayload));
                        }
                    });

                    if (addedCount > 0) {
                        BizAlert.toast(`${addedCount} new variants added!`, 'success');
                    } else {
                        BizAlert.toast(`Combinations already exist. No new variants added.`, 'info');
                    }
                    
                    setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 50);
                },

                clearSelections() {
                    for (let key in this.selectedValues) {
                        this.selectedValues[key] = [];
                    }
                },

                removeVariation(index) {
                    if (this.variations.length > 1) {
                        this.variations.splice(index, 1);
                    } else {
                        BizAlert.toast('You must have at least one variation.', 'error');
                    }
                },

                // 🌟 Initialize array for every attribute ID on page load
                selectedValues: {
                    @foreach ($attributes as $attr)
                        '{{ $attr->id }}': [],
                    @endforeach
                },
                
                // 🌟 Bulk Edit Tracker
                bulk: {
                    price: '',
                    cost: '',
                    tax_type: '',
                    tax_percent: '',
                    alert: ''
                },

                singleStocks: [],
                
                addSingleStock() {
                    this.singleStocks.push({
                        id: Date.now() + Math.random(),
                        warehouse_id: '',
                        qty: ''
                    });
                    setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 50);
                },
                removeSingleStock(index) {
                    this.singleStocks.splice(index, 1);
                },

                // 🌟 NEW: Advanced Modal Stock Management
                isStockModalOpen: false,
                activeVariantIndex: null,

                getActiveVariant() {
                    return this.variations[this.activeVariantIndex];
                },

                openStockModal(index) {
                    this.activeVariantIndex = index;
                    let variant = this.getActiveVariant();
                    
                    if (!variant.stocks) {
                        variant.stocks = [];
                    }
                    
                    // Auto-add first row if empty to save a click
                    if(variant.stocks.length === 0) {
                        this.addActiveVariantStock();
                    }
                    
                    this.isStockModalOpen = true;
                },

                closeStockModal() {
                    // Filter out any blank rows before closing
                    let variant = this.getActiveVariant();
                    if (variant && variant.stocks) {
                        variant.stocks = variant.stocks.filter(s => s.warehouse_id !== '' && s.qty !== '');
                    }
                    
                    this.isStockModalOpen = false;
                    this.activeVariantIndex = null;
                },

                addActiveVariantStock() {
                    let variant = this.getActiveVariant();
                    if (!variant.stocks) variant.stocks = [];
                    variant.stocks.push({
                        id: Date.now() + Math.random(),
                        warehouse_id: '',
                        qty: ''
                    });
                    setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 50);
                },

                removeActiveVariantStock(stockIndex) {
                    this.getActiveVariant().stocks.splice(stockIndex, 1);
                },

                calculateTotalStock(variant) {
                    if(!variant || !variant.stocks) return 0;
                    return variant.stocks.reduce((sum, stock) => sum + (parseFloat(stock.qty) || 0), 0);
                }
            }
        }
    </script>
@endpush
