<?php $__env->startSection('title', 'Warehouse Details - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-xs sm:text-sm font-bold text-gray-400 uppercase tracking-widest">Inventory / Warehouse Details</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="w-full mx-auto space-y-6 pb-10">

        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-[#212538] tracking-tight"><?php echo e($warehouse->name); ?></h2>
                <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs sm:text-sm text-gray-500 font-medium">
                    <span class="flex items-center gap-1"><i data-lucide="store" class="w-4 h-4"></i> <?php echo e($warehouse->store->name ?? 'Primary Store'); ?></span>
                    <span class="hidden sm:inline text-gray-300">•</span>
                    <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-4 h-4"></i> <?php echo e($warehouse->city ?? 'Location not set'); ?></span>
                </div>
            </div>
            <a href="<?php echo e(route('admin.warehouses.index')); ?>"
                class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm shrink-0 flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to List
            </a>
        </div>

        <?php
            $isBatchEnabled = function_exists('batch_enabled') && batch_enabled();
        ?>

        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            
            
            <div class="px-5 sm:px-6 py-4 flex justify-between items-center border-b border-gray-100 bg-[#f8fafc]">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">PRODUCT</span>
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">QUANTITY</span>
            </div>

            
            <div class="divide-y divide-gray-100 bg-white">
                <?php $__empty_1 = true; $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        // Resolve image
                        $imagePath = $stock->sku->product->media->first()?->media_path;
                        $imageUrl = $imagePath ? asset('storage/' . $imagePath) : null;
                        
                        // Bulletproof unit resolution
                        $unitName = $stock->sku->unit->name 
                                    ?? $stock->sku->product->saleUnit->name 
                                    ?? $stock->sku->product->productUnit->name 
                                    ?? 'Unit';

                        // Check if this specific row has batches to display
                        $hasActiveBatches = $isBatchEnabled && $stock->sku->relationLoaded('batches') && $stock->sku->batches->isNotEmpty();
                    ?>

                    
                    <div x-data="{ expanded: false }" class="flex flex-col transition-colors duration-200" :class="expanded ? 'bg-gray-50/50' : 'hover:bg-gray-50/30'">
                        
                        
                        <div class="px-5 sm:px-6 py-4 flex items-center justify-between gap-4 <?php echo e($hasActiveBatches ? 'cursor-pointer' : ''); ?>"
                             <?php if($hasActiveBatches): ?> @click="expanded = !expanded" <?php endif; ?>>
                            
                            
                            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                                
                                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gray-100 border border-gray-200 overflow-hidden shrink-0 flex items-center justify-center">
                                    <?php if($imageUrl): ?>
                                        <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($stock->sku->product->name); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <i data-lucide="package" class="w-5 h-5 text-gray-400"></i>
                                    <?php endif; ?>
                                </div>
                                
                                
                                <div class="flex flex-col min-w-0">
                                    <span class="font-bold text-gray-700 text-sm sm:text-[15px] truncate"><?php echo e($stock->sku->product->name); ?></span>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[10px] sm:text-[11px] text-gray-400 font-mono tracking-widest uppercase"><?php echo e($stock->sku->sku); ?></span>
                                        <?php if($stock->sku->skuValues->isNotEmpty()): ?>
                                            <span class="text-[10px] sm:text-[11px] text-brand-500 font-medium bg-brand-50 px-1.5 rounded truncate">
                                                <?php echo e($stock->sku->skuValues->map(fn($v) => $v->attributeValue->value)->implode(' / ')); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                                
                                <span class="bg-[#e0f2fe] text-[#0284c7] px-2.5 py-1 rounded-md text-xs sm:text-[13px] font-black shadow-sm">
                                    <?php echo e(number_format($stock->qty, 0)); ?>

                                </span>
                                
                                
                                <span class="bg-[#dcfce7] text-[#16a34a] px-2 sm:px-3 py-1 rounded-md text-[10px] sm:text-xs font-bold lowercase tracking-wide shadow-sm hidden xs:inline-block">
                                    <?php echo e($unitName); ?>

                                </span>

                                
                                <?php if($hasActiveBatches): ?>
                                    <button class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-transform duration-300 ml-1" :class="expanded ? 'rotate-180' : ''">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <?php if($hasActiveBatches): ?>
                            <div x-show="expanded" x-collapse x-cloak>
                                
                                <div class="px-3 sm:px-16 pb-4 pt-2 bg-gray-50/50">
                                    
                                    
                                    <div class="border border-brand-100 rounded-lg overflow-x-auto bg-white shadow-sm">
                                        
                                        
                                        <table class="w-full text-left text-[11px] sm:text-xs whitespace-nowrap min-w-[380px]">
                                            
                                            <thead class="bg-brand-50 text-brand-700 font-bold border-b border-brand-100">
                                                <tr>
                                                    <th class="px-3 sm:px-4 py-2.5">Batch No.</th>
                                                    <th class="px-3 sm:px-4 py-2.5 hidden sm:table-cell">Mfg Date</th>
                                                    <th class="px-3 sm:px-4 py-2.5">Expiry Date</th>
                                                    <th class="px-3 sm:px-4 py-2.5 text-right">Available Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 text-gray-600">
                                                <?php $__currentLoopData = $stock->sku->batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr class="hover:bg-gray-50 transition-colors">
                                                        <td class="px-3 sm:px-4 py-2.5 font-mono font-bold text-gray-800"><?php echo e($batch->batch_number ?? 'N/A'); ?></td>
                                                        <td class="px-3 sm:px-4 py-2.5 hidden sm:table-cell"><?php echo e($batch->manufacturing_date ? $batch->manufacturing_date->format('M d, Y') : '-'); ?></td>
                                                        <td class="px-3 sm:px-4 py-2.5">
                                                            <?php if($batch->expiry_date): ?>
                                                                <?php
                                                                    $daysToExpiry = now()->startOfDay()->diffInDays($batch->expiry_date->startOfDay(), false);
                                                                    $expiryClass = $daysToExpiry <= 30 ? 'text-red-500 font-bold' : 'text-gray-600';
                                                                ?>
                                                                <span class="<?php echo e($expiryClass); ?>">
                                                                    <?php echo e($batch->expiry_date->format('M d, Y')); ?>

                                                                </span>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="px-3 sm:px-4 py-2.5 text-right font-black text-[#0284c7]"><?php echo e(number_format($batch->remaining_qty, 0)); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i data-lucide="package-open" class="w-12 h-12 mb-3 opacity-20"></i>
                            <p class="font-medium text-sm text-gray-500">This warehouse is currently empty.</p>
                            <p class="text-xs mt-1">Stock will appear here when purchases or transfers are made.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            
            <?php if($stocks->hasPages()): ?>
                <div class="px-5 sm:px-6 py-4 border-t border-gray-100 bg-white">
                    <?php echo e($stocks->links()); ?>

                </div>
            <?php endif; ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('alpine:init', () => {
            // Re-initialize Lucide icons when Alpine components expand/collapse
            Alpine.effect(() => {
                setTimeout(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }, 50);
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/warehouses/show.blade.php ENDPATH**/ ?>