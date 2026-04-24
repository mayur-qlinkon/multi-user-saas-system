<?php $__env->startSection('title', 'Credit Notes & Returns'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Sales Returns</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="pb-10">

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
            <form action="<?php echo e(route('admin.invoice-returns.index')); ?>" method="GET" class="flex flex-col sm:flex-row gap-3">

                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                        placeholder="Search CN Number, Customer..."
                        class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-gray-500 focus:ring-1 focus:ring-gray-500 outline-none transition-all placeholder-gray-400">
                </div>

                <div x-data="{ filterOpen: false }" @click.away="filterOpen = false" class="relative">
                    <button type="button" @click="filterOpen = !filterOpen"
                        class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors flex items-center gap-2 h-full">
                        <i data-lucide="filter" class="w-4 h-4 text-gray-500"></i> Filters
                        <?php if(request('status') || request('return_type')): ?>
                            <span class="w-2 h-2 rounded-full bg-red-500 ml-1"></span>
                        <?php endif; ?>
                    </button>

                    <div x-show="filterOpen" x-cloak x-transition
                        class="absolute right-0 mt-2 w-64 bg-white border border-gray-100 rounded-xl shadow-xl z-50 p-4">

                        <div class="mb-3">
                            <label
                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                            <select name="status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-gray-500 outline-none bg-white">
                                <option value="">All Statuses</option>
                                <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                                <option value="confirmed" <?php echo e(request('status') == 'confirmed' ? 'selected' : ''); ?>>Confirmed
                                </option>
                                <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelled
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Return
                                Type</label>
                            <select name="return_type"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-gray-500 outline-none bg-white">
                                <option value="">All Types</option>
                                <option value="refund" <?php echo e(request('return_type') == 'refund' ? 'selected' : ''); ?>>Refund
                                </option>
                                <option value="credit_note" <?php echo e(request('return_type') == 'credit_note' ? 'selected' : ''); ?>>
                                    Credit Note</option>
                                <option value="replacement" <?php echo e(request('return_type') == 'replacement' ? 'selected' : ''); ?>>
                                    Replacement</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="<?php echo e(route('admin.invoice-returns.index')); ?>"
                                class="px-3 py-1.5 text-xs font-bold text-gray-500 hover:text-gray-800 transition-colors">Clear</a>
                            <button type="submit"
                                class="bg-[#212538] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-black transition-colors">Apply</button>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm">
                    Search
                </button>

                <?php if(request()->hasAny(['search', 'status', 'return_type'])): ?>
                    <a href="<?php echo e(route('admin.invoice-returns.index')); ?>"
                        class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2.5 rounded-lg text-sm font-bold transition-colors flex items-center justify-center"
                        title="Clear All Filters">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        
        <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-6 py-4">CN DETAILS</th>
                            <th class="px-6 py-4">ORIGINAL INVOICE</th>
                            <th class="px-6 py-4">CUSTOMER</th>
                            <th class="px-6 py-4 text-center">TYPE</th>
                            <th class="px-6 py-4 text-center">STATUS</th>
                            <th class="px-6 py-4 text-right">TOTAL REFUND</th>
                            <th class="px-6 py-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $returns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 transition-colors group">

                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <a href="<?php echo e(route('admin.invoice-returns.show', $return->id)); ?>"
                                            class="font-extrabold text-gray-900 text-[13px] hover:underline">
                                            <?php echo e($return->credit_note_number); ?>

                                        </a>
                                        <span class="text-[11px] text-gray-500 mt-0.5 font-medium">
                                            <?php echo e(\Carbon\Carbon::parse($return->return_date)->format('d M Y')); ?>

                                        </span>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <a href="<?php echo e(route('admin.invoices.show', $return->invoice_id)); ?>"
                                        class="font-mono text-[12px] font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                        <?php echo e($return->invoice->invoice_number ?? 'Unknown'); ?>

                                    </a>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800 text-[13px]">
                                            <?php echo e($return->customer_name); ?>

                                        </span>
                                        <?php if($return->customer && $return->customer->phone): ?>
                                            <span class="text-[11px] text-gray-400 mt-0.5 font-bold tracking-tighter">
                                                <?php echo e($return->customer->phone); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4 text-center">
                                    <?php
                                        $typeColors = [
                                            'refund' => 'text-purple-600',
                                            'credit_note' => 'text-indigo-600',
                                            'replacement' => 'text-orange-600',
                                        ];
                                        $color = $typeColors[$return->return_type] ?? 'text-gray-600';
                                    ?>
                                    <span class="text-[10px] font-black uppercase tracking-widest <?php echo e($color); ?>">
                                        <?php echo e(str_replace('_', ' ', $return->return_type)); ?>

                                    </span>
                                </td>

                                
                                <td class="px-6 py-4 text-center">
                                    <?php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
                                            'confirmed' => 'bg-green-50 text-green-700 border-green-200',
                                            'cancelled' => 'bg-red-50 text-red-600 border-red-200',
                                        ];
                                        $color = $statusColors[$return->status] ?? $statusColors['draft'];
                                    ?>
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider border <?php echo e($color); ?>">
                                        <?php echo e($return->status); ?>

                                    </span>
                                </td>

                                
                                <td class="px-6 py-4 text-right">
                                    <span
                                        class="font-extrabold text-[#108c2a]">₹<?php echo e(number_format($return->grand_total, 2)); ?></span>
                                    <?php if($return->status === 'confirmed' && $return->restock): ?>
                                        <div class="text-[10px] text-gray-400 font-bold mt-0.5 uppercase tracking-wide">
                                            Stock Updated
                                        </div>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 transition-opacity">

                                        <?php if(has_permission('invoice_returns.view')): ?>
                                        <a href="<?php echo e(route('admin.invoice-returns.show', $return->id)); ?>"
                                            class="w-8 h-8 rounded border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                            title="View Credit Note">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <?php endif; ?>

                                        <?php if($return->status === 'draft'): ?>
                                            <?php if(has_permission('invoice_returns.update')): ?>
                                            <a href="<?php echo e(route('admin.invoice-returns.edit', $return->id)); ?>"
                                                class="w-8 h-8 rounded border border-blue-200 text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors"
                                                title="Edit Draft">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </a>
                                            <?php endif; ?>

                                            <?php if(has_permission('invoice_returns.delete')): ?>
                                            <form action="<?php echo e(route('admin.invoice-returns.destroy', $return->id)); ?>"
                                                method="POST" class="inline-block"
                                                onsubmit="event.preventDefault(); BizAlert.confirm('Delete Draft?', 'Are you sure you want to delete this draft credit note?').then((result) => { if(result.isConfirmed) this.submit(); });">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="w-8 h-8 rounded border border-red-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"
                                                    title="Delete Draft">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="file-x-2" class="w-10 h-10 mb-3 opacity-20"></i>
                                        <p class="text-sm font-medium">No Credit Notes Found</p>
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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/invoice-returns/index.blade.php ENDPATH**/ ?>