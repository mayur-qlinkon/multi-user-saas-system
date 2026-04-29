

<?php $__env->startSection('title', $product->name . ' - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6 pb-10">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-[#212538] tracking-tight"><?php echo e($product->name); ?></h1>
                    <?php if($product->is_active): ?>
                        <span
                            class="bg-green-100 text-green-700 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider">Active</span>
                    <?php else: ?>
                        <span
                            class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider">Draft</span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-gray-500 mt-1 font-medium">Added on <?php echo e($product->created_at->format('M d, Y')); ?></p>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo e(route('admin.products.index')); ?>"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                </a>
                <a href="<?php echo e(route('admin.products.edit', $product->id)); ?>"
                    class="bg-[#108c2a] hover:bg-[#0c6b1f] text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm flex items-center gap-2 transition-all">
                    <i data-lucide="edit" class="w-4 h-4"></i> Edit Product
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-1 bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col"
                x-data="{
                    mainMedia: '<?php echo e($product->media->where('is_primary', true)->first()?->media_type === 'youtube' ? $product->media->where('is_primary', true)->first()->media_path : asset('storage/' . ($product->media->where('is_primary', true)->first()?->media_path ?? 'default.png'))); ?>',
                    mainType: '<?php echo e($product->media->where('is_primary', true)->first()?->media_type ?? 'image'); ?>'
                }">

                <div
                    class="w-full aspect-square rounded-lg border border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center mb-4 relative group">
                    <template x-if="mainType === 'image'">
                        <img :src="mainMedia" class="w-full h-full object-contain p-2" loading="eager"
                                onerror="this.onerror=null; this.src='<?php echo e(asset('assets/images/placeholder.webp')); ?>'">
                    </template>
                    <template x-if="mainType === 'youtube'">
                        <div class="flex flex-col items-center text-red-500">
                            <i data-lucide="youtube" class="w-12 h-12 mb-2"></i>
                            <span class="text-sm font-bold">YouTube Video</span>
                            <a :href="mainMedia" target="_blank"
                                class="text-xs text-blue-500 hover:underline mt-1">Watch Link</a>
                        </div>
                    </template>
                </div>

                <?php if($product->media->count() > 0): ?>
                    <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                        <?php $__currentLoopData = $product->media->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isImg = $media->media_type === 'image';
                                $path = $isImg ? asset('storage/' . $media->media_path) : $media->media_path;
                            ?>
                            <button type="button"
                                @click="mainMedia = '<?php echo e($path); ?>'; mainType = '<?php echo e($media->media_type); ?>'"
                                class="w-16 h-16 rounded-md border-2 overflow-hidden flex-shrink-0 transition-all focus:outline-none"
                                :class="mainMedia === '<?php echo e($path); ?>' ? 'border-[#108c2a]' :
                                    'border-gray-200 hover:border-gray-300'">
                                <?php if($isImg): ?>
                                    <img src="<?php echo e($path); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gray-100 flex items-center justify-center text-red-400">
                                        <i data-lucide="youtube" class="w-6 h-6"></i>
                                    </div>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-gray-400 text-center italic mt-auto">No media uploaded.</p>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Product Details</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-8">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Category</p>
                        <p class="text-sm font-bold text-gray-800 mt-0.5"><?php echo e($product->category->name ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Supplier</p>
                        <p class="text-sm font-bold text-gray-800 mt-0.5"><?php echo e($product->supplier->name ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">HSN Code</p>
                        <p class="text-sm font-bold text-gray-800 mt-0.5"><?php echo e($product->hsn_code ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Product Type</p>
                        <span
                            class="inline-flex mt-1 items-center gap-1 <?php echo e($product->type === 'variable' ? 'text-purple-600 bg-purple-50 border-purple-100' : 'text-blue-600 bg-blue-50 border-blue-100'); ?> border font-bold px-2 py-0.5 rounded text-[11px]">
                            <i data-lucide="<?php echo e($product->type === 'variable' ? 'layers' : 'box'); ?>" class="w-3 h-3"></i>
                            <?php echo e(ucfirst($product->type)); ?> Product
                        </span>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Barcode Symbology</p>
                        <p class="text-sm font-bold text-gray-800 mt-0.5 font-mono"><?php echo e($product->barcode_symbology); ?></p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Max Order Qty</p>
                        <p class="text-sm font-bold text-gray-800 mt-0.5"><?php echo e($product->quantity_limitation ?? 'No Limit'); ?>

                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Unit Configuration</p>
                    <div class="flex flex-wrap gap-3">
                        <span
                            class="bg-gray-50 border border-gray-200 text-gray-600 px-3 py-1.5 rounded-md text-xs font-bold">
                            Base: <span class="text-[#108c2a]"><?php echo e($product->productUnit->name ?? 'N/A'); ?></span>
                        </span>
                        <span
                            class="bg-gray-50 border border-gray-200 text-gray-600 px-3 py-1.5 rounded-md text-xs font-bold">
                            Sale: <span class="text-[#108c2a]"><?php echo e($product->saleUnit->name ?? 'N/A'); ?></span>
                        </span>
                        <span
                            class="bg-gray-50 border border-gray-200 text-gray-600 px-3 py-1.5 rounded-md text-xs font-bold">
                            Purchase: <span class="text-[#108c2a]"><?php echo e($product->purchaseUnit->name ?? 'N/A'); ?></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Description</h2>
                <?php if($product->description): ?>
                    <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line"><?php echo e($product->description); ?></p>
                <?php else: ?>
                    <p class="text-sm text-gray-400 italic">No description provided.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Product Guidance</h2>
                <?php if($product->product_guide && count($product->product_guide) > 0): ?>
                    <div class="space-y-4">
                        <?php $__currentLoopData = $product->product_guide; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $guide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <h4 class="text-[13px] font-bold text-gray-800"><?php echo e($guide['title'] ?? 'Guide'); ?></h4>
                                <p class="text-[13px] text-gray-600 mt-1 leading-relaxed"><?php echo e($guide['description'] ?? ''); ?>

                                </p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-6 border-2 border-dashed border-gray-100 rounded-lg">
                        <i data-lucide="book-open" class="w-6 h-6 text-gray-300 mx-auto mb-2"></i>
                        <p class="text-sm text-gray-400 italic">No guidance sections added.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800">Inventory & Pricing (SKUs)</h2>
                <span class="bg-blue-100 text-blue-700 px-2.5 py-1 rounded-md text-[11px] font-bold">Total SKUs:
                    <?php echo e($product->skus->count()); ?></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-white">
                        <tr>
                            <th class="px-6 py-4">SKU Details</th>
                            <?php if($product->type === 'variable'): ?>
                                <th class="px-6 py-4">Attributes</th>
                            <?php endif; ?>
                            <th class="px-6 py-4">Pricing & Tax</th>
                            <th class="px-6 py-4">Live Stock</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php $__currentLoopData = $product->skus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sku): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 transition-colors group">

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="barcode" class="w-4 h-4 text-gray-400"></i>
                                        <span class="font-bold text-[#212538] font-mono"><?php echo e($sku->sku); ?></span>
                                    </div>
                                    <?php if($sku->barcode): ?>
                                        <p class="text-[11px] text-gray-400 mt-1">Barcode: <?php echo e($sku->barcode); ?></p>
                                    <?php endif; ?>
                                </td>

                                <?php if($product->type === 'variable'): ?>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            <?php $__empty_1 = true; $__currentLoopData = $sku->skuValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <span
                                                    class="bg-purple-50 border border-purple-100 text-purple-700 px-2 py-0.5 rounded text-[10px] font-bold">
                                                    <?php echo e($val->attribute->name); ?>: <?php echo e($val->attributeValue->value); ?>

                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <span class="text-gray-400 text-xs italic">No attributes</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2 text-[13px]">
                                            <span class="text-gray-500 w-10">Price:</span>
                                            <span
                                                class="font-bold text-[#108c2a]">₹<?php echo e(number_format($sku->price, 2)); ?></span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[13px]">
                                            <span class="text-gray-500 w-10">Cost:</span>
                                            <span
                                                class="font-medium text-gray-700">₹<?php echo e(number_format($sku->cost, 2)); ?></span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-gray-400 font-medium mt-0.5">
                                            Tax: <?php echo e($sku->order_tax); ?>% (<?php echo e(ucfirst($sku->tax_type)); ?>)
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <?php
                                        // Calculate total stock for this specific SKU across all warehouses
                                        $totalStock = $sku->stocks->sum('qty');
                                    ?>

                                    <div class="flex flex-col items-start">
                                        <span
                                            class="font-bold text-[14px] <?php echo e($totalStock <= $sku->stock_alert ? 'text-red-500' : 'text-gray-800'); ?>">
                                            <?php echo e($totalStock); ?> units
                                        </span>

                                        <?php if($sku->stocks->count() > 0): ?>
                                            <div class="flex flex-col gap-1 mt-2">
                                                <?php $__currentLoopData = $sku->stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <span
                                                        class="text-[10px] font-medium text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200 flex items-center gap-1">
                                                        <i data-lucide="building-2" class="w-2.5 h-2.5"></i>
                                                        <?php echo e($stock->warehouse->name ?? 'Unknown'); ?>: <?php echo e($stock->qty); ?>

                                                    </span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if($sku->stock_alert > 0): ?>
                                            <p class="text-[10px] text-orange-400 mt-1 font-bold">Alert at:
                                                <?php echo e($sku->stock_alert); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <button type="button"
                                        onclick="BizAlert.toast('Stock Adjustments coming soon!', 'info')"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1.5 rounded text-xs font-bold transition-colors">
                                        Adjust Stock
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/products/show.blade.php ENDPATH**/ ?>