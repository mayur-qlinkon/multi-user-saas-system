<?php $__env->startSection('title', 'Edit Product - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Edit / Product</h1>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <div class="space-y-6 pb-10" x-data="productForm()">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                
                <p class="text-sm text-gray-500 mt-1 font-medium">Update inventory item details and pricing.</p>
            </div>
            <a href="<?php echo e(route('admin.products.index')); ?>"
                class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm">
                Back
            </a>
        </div>

        <?php if($errors->any()): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl shadow-sm text-sm">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i> Please fix the following errors:
                </div>
                <ul class="list-disc list-inside ml-6 space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('admin.products.update', $product->id)); ?>" method="POST" enctype="multipart/form-data"
            class="space-y-6" @submit="BizAlert.loading('Updating Product...')">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-5 border-b border-gray-100 pb-2">1. Basic Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Product Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" value="<?php echo e(old('name', $product->name)); ?>" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">HSN Code (Optional)</label>
                        <input type="text" name="hsn_code" value="<?php echo e(old('hsn_code', $product->hsn_code)); ?>"
                            placeholder="e.g., 61091000"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all uppercase">
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Status</label>
                        <select name="is_active"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="1" <?php echo e(old('is_active', $product->is_active) == '1' ? 'selected' : ''); ?>>
                                Active (Visible)</option>
                            <option value="0" <?php echo e(old('is_active', $product->is_active) == '0' ? 'selected' : ''); ?>>
                                Draft (Hidden)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Show in Storefront</label>
                        <select name="show_in_storefront"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="1" <?php echo e(old('show_in_storefront', $product->show_in_storefront) == '1' ? 'selected' : ''); ?>>
                                Yes (Listed publicly)</option>
                            <option value="0" <?php echo e(old('show_in_storefront', $product->show_in_storefront) == '0' ? 'selected' : ''); ?>>
                                No (Hidden from store)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Category <span
                                class="text-red-500">*</span></label>
                        <select name="category_id" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="">Select Category</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cat->id); ?>"
                                    <?php echo e(old('category_id', $product->category_id) == $cat->id ? 'selected' : ''); ?>>
                                    <?php echo e($cat->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Supplier (Optional)</label>
                        <select name="supplier_id"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="">Select Supplier</option>
                            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($sup->id); ?>"
                                    <?php echo e(old('supplier_id', $product->supplier_id) == $sup->id ? 'selected' : ''); ?>>
                                    <?php echo e($sup->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Barcode Symbology <span
                                class="text-red-500">*</span></label>
                        <select name="barcode_symbology" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="CODE128"
                                <?php echo e(old('barcode_symbology', $product->barcode_symbology) == 'CODE128' ? 'selected' : ''); ?>>
                                CODE128</option>
                            <option value="CODE39"
                                <?php echo e(old('barcode_symbology', $product->barcode_symbology) == 'CODE39' ? 'selected' : ''); ?>>
                                CODE39</option>
                            <option value="EAN13"
                                <?php echo e(old('barcode_symbology', $product->barcode_symbology) == 'EAN13' ? 'selected' : ''); ?>>
                                EAN13</option>
                            <option value="UPCA"
                                <?php echo e(old('barcode_symbology', $product->barcode_symbology) == 'UPCA' ? 'selected' : ''); ?>>
                                UPCA</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Max Qty per Order</label>
                        <input type="number" name="quantity_limitation"
                            value="<?php echo e(old('quantity_limitation', $product->quantity_limitation)); ?>" placeholder="No limit"
                            min="1"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all">
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Description (Optional)</label>
                        <textarea name="description" rows="3"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all"><?php echo e(old('description', $product->description)); ?></textarea>
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
                            <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($unit->id); ?>"
                                    <?php echo e(old('product_unit_id', $product->product_unit_id) == $unit->id ? 'selected' : ''); ?>>
                                    <?php echo e($unit->name); ?> (<?php echo e($unit->short_name); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Sale Unit <span
                                class="text-red-500">*</span></label>
                        <select name="sale_unit_id" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="">Select Sale Unit</option>
                            <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($unit->id); ?>"
                                    <?php echo e(old('sale_unit_id', $product->sale_unit_id) == $unit->id ? 'selected' : ''); ?>>
                                    <?php echo e($unit->name); ?> (<?php echo e($unit->short_name); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Purchase Unit <span
                                class="text-red-500">*</span></label>
                        <select name="purchase_unit_id" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                            <option value="">Select Purchase Unit</option>
                            <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($unit->id); ?>"
                                    <?php echo e(old('purchase_unit_id', $product->purchase_unit_id) == $unit->id ? 'selected' : ''); ?>>
                                    <?php echo e($unit->name); ?> (<?php echo e($unit->short_name); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>

            <?php if(has_module('plant_education')): ?>
            
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
            <?php endif; ?>

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

                <?php
                    // Helper to pre-load single product data securely (loads first SKU regardless of current type to prevent data loss on toggle)
                    $singleSku = $product->skus->first();
                ?>

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
                                value="<?php echo e(old('single_price', $singleSku?->price)); ?>"
                                :required="productType === 'single' && catalogMode !== 'catalog'"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2 text-sm focus:border-[#108c2a] outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Purchase Cost (₹) <span
                                    class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="single_cost"
                                value="<?php echo e(old('single_cost', $singleSku?->cost)); ?>"
                                :required="productType === 'single' && catalogMode !== 'catalog'"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2 text-sm focus:border-[#108c2a] outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Tax Type <span
                                    class="text-red-500">*</span></label>
                            <select name="single_tax_type" :required="productType === 'single' && catalogMode !== 'catalog'"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2 text-sm focus:border-[#108c2a] outline-none transition-all bg-white">
                                <option value="exclusive"
                                    <?php echo e(old('single_tax_type', $singleSku?->tax_type) == 'exclusive' ? 'selected' : ''); ?>>
                                    Exclusive</option>
                                <option value="inclusive"
                                    <?php echo e(old('single_tax_type', $singleSku?->tax_type) == 'inclusive' ? 'selected' : ''); ?>>
                                    Inclusive</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Tax (%)</label>
                            <input type="number" step="0.01" name="single_order_tax"
                                value="<?php echo e(old('single_order_tax', $singleSku?->order_tax ?? 0)); ?>"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2 text-sm focus:border-[#108c2a] outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Low Stock Alert</label>
                            <input type="number" name="single_stock_alert"
                                value="<?php echo e(old('single_stock_alert', $singleSku?->stock_alert ?? 0)); ?>"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[13px] font-bold text-gray-700 mb-1.5">HSN Override <span class="text-gray-400 font-normal text-[11px]">(Optional)</span></label>
                            <input type="text" name="single_hsn_code"
                                value="<?php echo e(old('single_hsn_code', $singleSku?->hsn_code ?? '')); ?>"
                                placeholder="e.g., 61091000"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] outline-none transition-all uppercase">
                            <p class="text-[11px] text-gray-400 mt-1">Leave empty to use product HSN</p>
                        </div>
                    </div>
                </div>

                <div x-show="productType === 'variable' && catalogMode !== 'catalog'" x-cloak>
                    <div class="mb-6 bg-blue-50/50 border border-blue-100 p-4 rounded-lg flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-blue-500 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-bold text-blue-900">Managing Variations</p>
                            <p class="text-[13px] text-blue-700 mt-1">If you delete a variation here, it will be
                                soft-deleted to preserve sales history. Stock cannot be added here and must be managed via
                                Purchases or Stock Adjustments.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(variant, index) in variations" :key="variant.id">
                            <div class="relative bg-gray-50 border border-gray-200 rounded-xl p-4 pt-8">

                                <template x-if="variant.is_existing">
                                    <input type="hidden" :name="'variations[' + index + '][id]'" :value="variant.id">
                                </template>

                                <button type="button" @click="removeVariation(index)"
                                    class="absolute top-2 right-2 text-red-400 hover:text-red-600 bg-white rounded p-1 shadow-sm border border-red-100">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>

                                <div
                                    class="absolute top-2 left-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    Variation <span x-text="index + 1"></span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <label class="block text-[12px] font-bold text-gray-600 mb-1">SKU</label>
                                        <div class="flex items-center gap-2">
                                            <div class="relative w-full">
                                                <input type="text" :name="'variations[' + index + '][sku]'"
                                                    x-model="variant.sku" placeholder="Auto-generated if empty"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] focus:ring-2 focus:ring-[#108c2a]/20 outline-none uppercase transition-all pr-9">
                                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300">
                                                    <i data-lucide="barcode" class="w-4 h-4"></i>
                                                </span>
                                            </div>
                                            <button type="button" @click="variant.sku = generateSKU()"
                                                title="Generate SKU"
                                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2 rounded-lg border border-gray-200 flex items-center justify-center transition-colors active:scale-95">
                                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-[12px] font-bold text-gray-600 mb-1">Barcode <span class="text-gray-400 font-normal text-[10px]">(Optional)</span></label>
                                        
                                        <div class="flex items-center gap-2">
                                            <div class="relative w-full">
                                                <input type="text" :name="'variations[' + index + '][barcode]'"
                                                    x-model="variant.barcode" placeholder="Scan or generate"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] focus:ring-2 focus:ring-[#108c2a]/20 outline-none uppercase transition-all pr-9">
                                                
                                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300">
                                                    <i data-lucide="barcode" class="w-4 h-4"></i>
                                                </span>
                                            </div>
                                            
                                            <button type="button" @click="variant.barcode = generateBarcode()"
                                                title="Generate Barcode"
                                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2 rounded-lg border border-gray-200 flex items-center justify-center transition-colors active:scale-95">
                                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-[12px] font-bold text-gray-600 mb-1">MRP (₹)</label>
                                        <input type="number" step="0.01" :name="'variations[' + index + '][mrp]'"
                                            x-model="variant.mrp" placeholder="Optional"
                                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-[#108c2a] outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-bold text-gray-600 mb-1">Selling Price (₹) <span
                                    class="text-red-500">*</span></label>
                                        <input type="number" step="0.01" :name="'variations[' + index + '][price]'"
                                            x-model="variant.price" :required="productType === 'variable' && catalogMode !== 'catalog'"
                                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-[#108c2a] outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-bold text-gray-600 mb-1">Cost (₹) <span
                                    class="text-red-500">*</span></label>
                                        <input type="number" step="0.01" :name="'variations[' + index + '][cost]'"
                                            x-model="variant.cost" :required="productType === 'variable' && catalogMode !== 'catalog'"
                                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-[#108c2a] outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-bold text-gray-600 mb-1">Tax Type <span
                                    class="text-red-500">*</span></label>
                                        <select :name="'variations[' + index + '][tax_type]'" x-model="variant.tax_type"
                                            :required="productType === 'variable' && catalogMode !== 'catalog'"
                                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                            <option value="exclusive">Exclusive</option>
                                            <option value="inclusive">Inclusive</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-bold text-gray-600 mb-1">Tax (%)</label>
                                        <input type="number" step="0.01"
                                            :name="'variations[' + index + '][order_tax]'" x-model="variant.order_tax"
                                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-[#108c2a] outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-bold text-gray-600 mb-1">Stock Alert</label>
                                        <input type="number" :name="'variations[' + index + '][stock_alert]'"
                                            x-model="variant.alert"
                                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-[#108c2a] outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-bold text-gray-600 mb-1">HSN Override</label>
                                        <input type="text" :name="'variations[' + index + '][hsn_code]'"
                                            x-model="variant.hsn_code"
                                            placeholder="Leave empty to use product HSN"
                                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-[#108c2a] outline-none uppercase">
                                    </div>
                                </div>

                                <div class="bg-white p-3 rounded border border-gray-200">
                                    <label
                                        class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Select
                                        Attributes</label>
                                    <div class="flex flex-wrap gap-4">
                                        <?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="flex-1 min-w-[150px]">
                                                <label
                                                    class="block text-[12px] font-bold text-gray-700 mb-1"><?php echo e($attr->name); ?></label>
                                                <select :name="'variations[' + index + '][attrs][<?php echo e($attr->id); ?>]'"
                                                    x-model="variant.attrs[<?php echo e($attr->id); ?>]"
                                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-[#108c2a] outline-none">
                                                    <option value="">Select <?php echo e($attr->name); ?></option>
                                                    <?php $__currentLoopData = $attr->values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($val->id); ?>"><?php echo e($val->value); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addVariation()"
                            class="w-full py-3 border-2 border-dashed border-[#108c2a]/30 text-[#108c2a] hover:bg-[#108c2a]/5 hover:border-[#108c2a] rounded-xl font-bold text-sm flex justify-center items-center gap-2 transition-colors">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Variation
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-2">
                    <h2 class="text-lg font-bold text-gray-800">4. Product Media</h2>
                    <div class="flex gap-2">
                        <button type="button" @click="addMedia('image')"
                            class="text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded flex items-center gap-1 transition-colors">
                            <i data-lucide="image" class="w-3 h-3"></i> Add Image
                        </button>
                        <button type="button" @click="addMedia('youtube')"
                            class="text-xs font-bold bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded flex items-center gap-1 transition-colors">
                            <i data-lucide="youtube" class="w-3 h-3"></i> Add YouTube
                        </button>
                    </div>
                </div>

                <input type="hidden" name="primary_media_index"
                    :value="mediaList.findIndex(m => m.id == primaryMediaId)">

                <div class="space-y-3">
                    <template x-for="(media, index) in mediaList" :key="media.id">
                        <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200 relative transition-all duration-200"
                            draggable="true" @dragstart="dragStart(index, $event)" @dragend="dragEnd()"
                            @dragover="dragOver($event)" @drop="drop(index)"
                            :class="{ 'opacity-40 border-dashed border-gray-400': draggedIndex === index }">

                            <div class="cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 flex-shrink-0"
                                title="Drag to reorder">
                                <i data-lucide="grip-vertical" class="w-5 h-5"></i>
                            </div>

                            <input type="hidden" :name="'media[' + index + '][type]'" :value="media.type">
                            <template x-if="media.is_existing">
                                <input type="hidden" :name="'media[' + index + '][id]'" :value="media.id">
                            </template>

                            <div
                                class="w-10 h-10 flex items-center justify-center rounded bg-white border border-gray-200 flex-shrink-0 overflow-hidden">
                                <template x-if="media.type === 'image'">
                                    <template x-if="media.preview">
                                        <img :src="media.preview" class="w-full h-full object-cover">
                                    </template>
                                </template>
                                <template x-if="media.type === 'image' && !media.preview">
                                    <i data-lucide="image" class="w-5 h-5 text-gray-400"></i>
                                </template>
                                <template x-if="media.type === 'youtube'">
                                    <i data-lucide="youtube" class="w-5 h-5 text-red-500"></i>
                                </template>
                            </div>

                            <div class="flex-1">
                                <template x-if="media.type === 'image'">
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 w-full">

                                        <template x-if="!media.is_existing">
                                            <input type="file" :name="'media[' + index + '][file]'" accept="image/*"
                                                required
                                                class="text-sm w-full text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-white file:border-gray-200 file:border cursor-pointer">
                                        </template>
                                        <template x-if="media.is_existing">
                                            <span class="text-sm font-medium text-gray-600 flex-1">Saved Image (ID: <span
                                                    x-text="media.id"></span>)</span>
                                        </template>

                                        <label
                                            class="flex items-center gap-1.5 cursor-pointer flex-shrink-0 text-sm font-bold text-gray-600">
                                            <input type="radio" :value="media.id" x-model="primaryMediaId"
                                                class="text-[#108c2a] focus:ring-[#108c2a]">
                                            Main Image
                                        </label>
                                    </div>
                                </template>

                                <template x-if="media.type === 'youtube'">
                                    <input type="url" :name="'media[' + index + '][url]'" x-model="media.url"
                                        placeholder="https://www.youtube.com/watch?v=..." required
                                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-red-400 outline-none bg-white">
                                </template>

                                <template x-if="productType === 'variable'">
                                    <div class="mt-2">
                                        <label class="text-[11px] font-bold text-gray-500">Assign to SKU</label>

                                        <select :name="'media[' + index + '][sku_index]'" x-model="media.sku_index"
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs bg-white">
                                            <option value="">Product Default</option>

                                            <template x-for="(variant, vIndex) in variations" :key="variant.id">
                                                <option :value="variant.id"
                                                    x-text="'SKU: ' + (variant.sku || ('Variant ' + (vIndex + 1)))"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>
                            </div>

                            <button type="button" @click="removeMedia(index)"
                                class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors flex-shrink-0">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </template>

                    <div x-show="mediaList.length === 0"
                        class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50">
                        <i data-lucide="film" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                        <p class="text-sm text-gray-500 font-medium">No media added yet.</p>
                        <p class="text-xs text-gray-400 mt-1">Add images or YouTube videos to showcase your product.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">

                <?php if(has_module('plant_education')): ?>

                    
                    <div class="flex items-center gap-2.5 mb-5 border-b border-gray-100 pb-3">
                        <i data-lucide="leaf" class="w-5 h-5 text-green-600 shrink-0"></i>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">5. Product Information <span class="text-gray-400 text-sm font-normal">(Optional)</span></h2>
                            <p class="text-xs text-gray-500 mt-0.5">Provide sunlight and watering care info for this plant.</p>
                        </div>
                    </div>

                    <?php
                        $guideMap = collect($product->product_guide ?? [])->keyBy('title');
                        $sunlightDesc = $guideMap->get('Sunlight')['description'] ?? '';
                        $wateringDesc = $guideMap->get('Watering')['description'] ?? '';
                    ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xl">☀️</span>
                                <label class="text-[13px] font-bold text-amber-800">Sunlight</label>
                            </div>
                            <input type="hidden" name="product_guide[0][title]" value="Sunlight">
                            <input type="text" name="product_guide[0][description]"
                                value="<?php echo e(old('product_guide.0.description', $sunlightDesc)); ?>"
                                placeholder="e.g., 4–6 hours of indirect sunlight"
                                class="w-full border border-amber-200 rounded-lg px-3 py-2 text-sm focus:border-amber-400 outline-none bg-white transition-all">
                        </div>

                        
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xl">💧</span>
                                <label class="text-[13px] font-bold text-blue-800">Watering</label>
                            </div>
                            <input type="hidden" name="product_guide[1][title]" value="Watering">
                            <input type="text" name="product_guide[1][description]"
                                value="<?php echo e(old('product_guide.1.description', $wateringDesc)); ?>"
                                placeholder="e.g., 1–2 times a week"
                                class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400 outline-none bg-white transition-all">
                        </div>

                    </div>

                    
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

                <?php else: ?>

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

                <?php endif; ?>

            </div>

            <div class="flex flex-col sm:flex-row justify-end pt-4 border-t border-gray-200">
                <button type="submit"
                    class="w-full sm:w-auto bg-[#108c2a] hover:bg-[#0c6b1f] text-white px-8 py-3 rounded-xl text-sm font-bold shadow-md flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="save" class="w-4 h-4"></i> Update Product
                </button>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function productForm() {
            return {
                catalogMode: <?php echo json_encode(old('product_type', $product->product_type ?? 'sellable'), 512) ?>,
                productType: <?php echo json_encode(old('type', $product->skus->count() > 1 ? 'variable' : $product->type), 512) ?>,
                singleSku: <?php echo json_encode(old('single_sku', $singleSku ? $singleSku->sku : ''), 512) ?>,
                singleMrp: <?php echo json_encode(old('single_mrp', $singleSku ? $singleSku->mrp : ''), 512) ?>, 
                singleBarcode: <?php echo json_encode(old('single_barcode', $singleSku ? $singleSku->barcode : ''), 512) ?>,
                // 🌟 Pre-load Variations from Database
                <?php
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
                ?>

                variations: <?php echo json_encode($variations, 15, 512) ?>.map((v, i) => ({ ...v, id: v.id || 'var_' + Date.now() + i })),

                // 🌟 Pre-load Media from Database
                <?php
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
                ?>

                mediaList: <?php echo json_encode($mediaList, 15, 512) ?>.map((m, i) => ({ ...m, id: m.id || 'media_' + Date.now() + i })),

                primaryMediaId: <?php echo json_encode(optional($product->media->where('is_primary', true)->first())->id, 512) ?>,

                draggedIndex: null,

                // 🌟 Pre-load Product Guides
                <?php
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
                ?>

                productGuides: <?php echo json_encode($guides, 15, 512) ?>.map((g, i) => ({ ...g, id: g.id || 'guide_' + Date.now() + i })),

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

                generateSKU() {
                    return `PRD-${Date.now().toString().slice(-4)}-${Math.random().toString(36).substring(2,6).toUpperCase()}`;
                },
                generateBarcode() {
                    return Math.floor(100000000000 + Math.random() * 900000000000).toString();
                },

                addVariation() {
                    this.variations.push({
                        id: Date.now(),
                        is_existing: false,
                        sku: '',
                        barcode: '',
                        price: '',
                        cost: '',
                        mrp: '',
                        tax_type: 'exclusive',
                        order_tax: 0,
                        alert: 0,
                        attrs: {}
                    });

                    setTimeout(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }, 50);
                },

                removeVariation(index) {
                    if (this.variations.length > 1) {
                        this.variations.splice(index, 1);
                    } else {
                        BizAlert.toast('You must have at least one variation.', 'error');
                    }
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas\resources\views/admin/products/edit.blade.php ENDPATH**/ ?>