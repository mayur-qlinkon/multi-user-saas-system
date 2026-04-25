<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['categories', 'units']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['categories', 'units']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-show="isProductModalOpen" style="display: none;"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/70 backdrop-blur-sm px-4"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl flex flex-col overflow-hidden max-h-[90vh]"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100" @click.away="isProductModalOpen = false">

        
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50 shrink-0">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i data-lucide="package-plus" class="w-5 h-5 text-brand-500"></i>
                Quick Add Product
            </h3>
            <button @click="isProductModalOpen = false"
                class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-red-50">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        
        <div class="p-6 overflow-y-auto no-scrollbar flex-1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="col-span-2 md:col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Product Name
                        *</label>
                    <input type="text" x-model="newProduct.name"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none"
                        placeholder="e.g., Ficus Plant">
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Category
                        *</label>
                    <select x-model="newProduct.category_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none bg-white">
                        <option value="">Select Category</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Unit
                        *</label>
                    <select x-model="newProduct.unit_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none bg-white">
                        <option value="">Select Unit</option>
                        <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($unit->id); ?>"><?php echo e($unit->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Opening
                        Stock</label>
                    <input type="number" x-model.number="newProduct.opening_stock"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none"
                        placeholder="0">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Cost Price
                        *</label>
                    <input type="number" step="0.01" x-model.number="newProduct.cost"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none"
                        placeholder="0.00">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Selling Price
                        *</label>
                    <input type="number" step="0.01" x-model.number="newProduct.price"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none"
                        placeholder="0.00">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tax Percent
                        (%)</label>
                    <input type="number" step="0.01" x-model.number="newProduct.tax_percent"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none"
                        placeholder="0.00">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tax
                        Type</label>
                    <select x-model="newProduct.tax_type"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none bg-white">
                        <option value="exclusive">Exclusive</option>
                        <option value="inclusive">Inclusive</option>
                    </select>
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Barcode
                        (Optional)</label>
                    <input type="text" x-model="newProduct.barcode"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none"
                        placeholder="Scan or type...">
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Product Image
                        (Optional)</label>
                    <input type="file" x-ref="productImageFile" accept="image/*"
                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 cursor-pointer">
                </div>
            </div>
        </div>

        
        <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 shrink-0">
            <button @click="isProductModalOpen = false"
                class="px-5 py-2 text-sm font-bold text-gray-600 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
            <button @click="saveQuickProduct()"
                class="px-6 py-2 text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl shadow-sm transition-colors flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i> Save & Add to Cart
            </button>
        </div>

    </div>
</div>
<?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas\resources\views/components/quick-product-modal.blade.php ENDPATH**/ ?>