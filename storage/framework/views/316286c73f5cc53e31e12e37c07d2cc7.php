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
    class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center bg-gray-900/70 backdrop-blur-sm sm:p-4"
    x-transition:enter="transition ease-out duration-300" 
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" 
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" 
    x-transition:leave-end="opacity-0">

    
    <div class="bg-white w-full sm:max-w-2xl rounded-t-3xl sm:rounded-2xl shadow-2xl flex flex-col overflow-hidden max-h-[90vh] sm:max-h-[85vh]"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-8 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
        @click.away="isProductModalOpen = false">

        
        <div class="w-full flex justify-center pt-3 pb-1 sm:hidden shrink-0 bg-white">
            <div class="w-12 h-1.5 bg-gray-200 rounded-full"></div>
        </div>

        
        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100 bg-white shrink-0">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="package-plus" class="w-5 h-5 text-brand-500"></i>
                    Quick Add Product
                </h3>
                <p class="text-[11px] text-gray-400 font-medium hidden sm:block mt-0.5">Enter product details to immediately add to POS</p>
            </div>
            <button @click="isProductModalOpen = false"
                class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-xl hover:bg-red-50 bg-gray-50 sm:bg-transparent">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        
        <div class="p-5 sm:p-6 overflow-y-auto no-scrollbar flex-1 bg-gray-50/30">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">

                
                <div class="col-span-1 sm:col-span-2">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="newProduct.name"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 sm:py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none transition-all bg-white"
                        placeholder="e.g., Ficus Plant">
                </div>

                
                <div class="col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Category <span class="text-red-500">*</span></label>
                    <select x-model="newProduct.category_id"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 sm:py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-white appearance-none cursor-pointer">
                        <option value="">Select Category</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Unit <span class="text-red-500">*</span></label>
                    <select x-model="newProduct.unit_id"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 sm:py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-white appearance-none cursor-pointer">
                        <option value="">Select Unit</option>
                        <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($unit->id); ?>"><?php echo e($unit->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Opening Stock</label>
                    <input type="number" x-model.number="newProduct.opening_stock"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 sm:py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none transition-all bg-white"
                        placeholder="0">
                </div>

                <div class="col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">HSN Code <span class="text-[9px] font-medium text-gray-400">(Optional)</span></label>
                    <input type="text" x-model="newProduct.hsn_code"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 sm:py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none transition-all bg-white"
                        placeholder="e.g., 123456">
                </div>

                
                <div class="col-span-1 sm:col-span-2 h-px bg-gray-200/60 my-1"></div>

                
                <div class="col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Cost Price <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm">₹</span>
                        <input type="number" step="0.01" x-model.number="newProduct.cost"
                            class="w-full border border-gray-200 rounded-xl pl-8 pr-4 py-3 sm:py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none transition-all bg-white"
                            placeholder="0.00">
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Selling Price <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm">₹</span>
                        <input type="number" step="0.01" x-model.number="newProduct.price"
                            class="w-full border border-gray-200 rounded-xl pl-8 pr-4 py-3 sm:py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none transition-all bg-white"
                            placeholder="0.00">
                    </div>
                </div>

                
                <div class="col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tax Percent (%)</label>
                    <input type="number" step="0.01" x-model.number="newProduct.tax_percent"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 sm:py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none transition-all bg-white"
                        placeholder="0.00">
                </div>

                <div class="col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tax Type</label>
                    <select x-model="newProduct.tax_type"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 sm:py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-white appearance-none cursor-pointer">
                        <option value="exclusive">Exclusive</option>
                        <option value="inclusive">Inclusive</option>
                    </select>
                </div>

                
                <div class="col-span-1 sm:col-span-2 mt-2">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Product Image <span class="text-[9px] font-medium text-gray-400">(Optional)</span></label>
                    <input type="file" x-ref="productImageFile" accept="image/*"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white
                        file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-[11px] file:font-bold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 cursor-pointer transition-all">
                </div>
            </div>
        </div>

        
        <div class="p-4 sm:p-5 border-t border-gray-100 bg-white flex flex-col sm:flex-row justify-end gap-3 shrink-0">
            <button @click="isProductModalOpen = false"
                class="w-full sm:w-auto px-6 py-3 sm:py-2.5 text-sm font-bold text-gray-600 bg-gray-50 border border-gray-200 hover:bg-gray-100 rounded-xl transition-colors order-2 sm:order-1">
                Cancel
            </button>
            <button @click="saveQuickProduct()"
                class="w-full sm:w-auto px-6 py-3 sm:py-2.5 text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl shadow-lg shadow-brand-500/20 transition-all flex items-center justify-center gap-2 order-1 sm:order-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i> Save & Add
            </button>
        </div>

    </div>
</div><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/components/quick-product-modal.blade.php ENDPATH**/ ?>