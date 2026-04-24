<?php $__env->startSection('title', 'Challan Returns - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">CHALLAN RETURNS</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="pb-10" x-data="{ filterOpen: false }">

        <?php if(session('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('success')); ?>", 'success'));
            </script>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('error')); ?>", 'error'));
            </script>
        <?php endif; ?>    

        
        <div class="bg-white rounded-t-xl shadow-sm border border-gray-100 p-4 border-b-0">
            <form action="<?php echo e(route('admin.challan-returns.index')); ?>" method="GET" class="flex flex-col sm:flex-row gap-3">

                
                <div class="flex flex-row items-center gap-2 flex-1 max-w-lg w-full">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                            placeholder="Search Return No, Challan No, Party..."
                            class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all placeholder-gray-400">
                    </div>

                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm shrink-0">
                        Search
                    </button>

                    <?php if(request()->hasAny(['search', 'condition'])): ?>
                        <a href="<?php echo e(route('admin.challan-returns.index')); ?>" 
                            class="bg-red-50 hover:bg-red-100 text-red-500 w-10 h-10 rounded-lg flex items-center justify-center shrink-0 transition-colors" 
                            title="Clear Filters">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>

                
                <div @click.away="filterOpen = false" class="relative">
                    <button type="button" @click="filterOpen = !filterOpen"
                        class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors flex items-center gap-2 h-full">
                        <i data-lucide="filter" class="w-4 h-4 text-gray-500"></i> Filters
                        <?php if(request('condition')): ?>
                            <span class="w-2 h-2 rounded-full bg-red-500 ml-1"></span>
                        <?php endif; ?>
                    </button>

                    <div x-show="filterOpen" x-cloak x-transition
                        class="absolute right-0 mt-2 w-64 bg-white border border-gray-100 rounded-xl shadow-xl z-50 p-4">

                        <div class="mb-4">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Return Condition</label>
                            <select name="condition" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                <option value="">All Conditions</option>
                                <option value="good" <?php echo e(request('condition') == 'good' ? 'selected' : ''); ?>>Good Condition</option>
                                <option value="partial" <?php echo e(request('condition') == 'partial' ? 'selected' : ''); ?>>Partial Return</option>
                                <option value="damaged" <?php echo e(request('condition') == 'damaged' ? 'selected' : ''); ?>>Damaged</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="<?php echo e(route('admin.challan-returns.index')); ?>"
                                class="px-3 py-1.5 text-xs font-bold text-gray-500 hover:text-gray-800 transition-colors">Clear</a>
                            <button type="submit"
                                class="bg-[#108c2a] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-[#0c6b1f] transition-colors">Apply</button>
                        </div>
                    </div>
                </div>

               
                <div class="ml-auto flex shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                    <?php if(has_permission('challan_returns.create')): ?>
                        <a href="<?php echo e(route('admin.challans.index')); ?>"
                            class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2 whitespace-nowrap">
                            <i data-lucide="undo-2" class="w-4 h-4"></i> Initiate Return
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        
        <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-6 py-4">RETURN DETAILS</th>
                            <th class="px-6 py-4">ORIGINAL CHALLAN</th>
                            <th class="px-6 py-4">PARTY</th>
                            <th class="px-6 py-4 text-center hidden md:table-cell">CONDITION</th>
                            <th class="px-6 py-4 text-center hidden md:table-cell">QTY RETURNED</th>
                            <th class="px-6 py-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $returns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                
                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <a href="<?php echo e(route('admin.challan-returns.show', $return->id)); ?>"
                                            class="font-extrabold text-[#108c2a] text-[13px] hover:underline">
                                            <?php echo e($return->return_number); ?>

                                        </a>
                                        <span class="text-[11px] text-gray-500 mt-0.5 font-medium flex items-center gap-1">
                                            <i data-lucide="calendar" class="w-3 h-3"></i> <?php echo e($return->return_date->format('d M, Y')); ?>

                                        </span>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <?php if($return->challan): ?>
                                            <a href="<?php echo e(route('admin.challans.show', $return->challan_id)); ?>" 
                                               class="font-bold text-gray-800 text-[13px] hover:text-blue-600 transition-colors">
                                                <?php echo e($return->challan->challan_number); ?>

                                            </a>
                                            <span class="text-[10px] text-gray-400 mt-0.5 font-bold uppercase tracking-tighter">
                                                <?php echo e($return->challan->type_label); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="font-bold text-red-400 text-[13px] italic">
                                                Challan Deleted
                                            </span>
                                            <span class="text-[10px] text-gray-400 mt-0.5 font-bold uppercase tracking-tighter">
                                                Ref ID: <?php echo e($return->challan_id); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800 text-[13px] truncate max-w-[200px]">
                                            <?php echo e($return->challan?->party_name ?? 'Unknown (Record Missing)'); ?>

                                        </span>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4 text-center hidden md:table-cell">
                                    <?php
                                        $colorMap = [
                                            'green' => 'bg-green-50 text-green-700 border-green-200',
                                            'red'   => 'bg-red-50 text-red-600 border-red-200',
                                            'amber' => 'bg-amber-50 text-amber-600 border-amber-200',
                                            'gray'  => 'bg-gray-50 text-gray-600 border-gray-200',
                                        ];
                                        $c = $colorMap[$return->condition_color] ?? $colorMap['gray'];
                                    ?>
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider border <?php echo e($c); ?>">
                                        <?php echo e($return->condition_label); ?>

                                    </span>
                                </td>

                                
                                <td class="px-6 py-4 text-center hidden md:table-cell">
                                    <div class="flex flex-col items-center">
                                        <span class="font-black text-gray-800 text-[14px]">
                                            <?php echo e((float) $return->total_qty_returned); ?>

                                        </span>
                                        <?php if($return->total_qty_damaged > 0): ?>
                                            <span class="text-[10px] font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded mt-1">
                                                <?php echo e((float) $return->total_qty_damaged); ?> Damaged
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 transition-opacity">

                                        
                                        <?php if(has_permission('challan_returns.view')): ?>
                                            <a href="<?php echo e(route('admin.challan-returns.show', $return->id)); ?>"
                                                class="w-8 h-8 rounded border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                                title="View Return">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>

                                        
                                        <?php if(has_permission('challan_returns.download_pdf')): ?>
                                            <a href="<?php echo e(route('admin.challan-returns.pdf', $return->id)); ?>" target="_blank"
                                                class="w-8 h-8 rounded border border-indigo-200 text-indigo-600 hover:bg-indigo-50 flex items-center justify-center transition-colors"
                                                title="Download PDF">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>

                                        
                                        <?php if(has_permission('challan_returns.update')): ?>
                                            <a href="<?php echo e(route('admin.challan-returns.edit', $return->id)); ?>"
                                                class="w-8 h-8 rounded border border-blue-200 text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors"
                                                title="Edit Return Notes">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="undo-2" class="w-10 h-10 mb-3 opacity-20"></i>
                                        <p class="text-sm font-medium">No challan returns found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($returns->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    <?php echo e($returns->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/challan-returns/index.blade.php ENDPATH**/ ?>