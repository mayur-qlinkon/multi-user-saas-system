<?php $__env->startSection('title', 'Quotations - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Quotations</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="pb-10" x-data="quotationIndex()">

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

        
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                
                <p class="text-sm text-gray-500 font-medium">Manage and track your customer proposals.</p>
            </div>
            <div class="flex items-center gap-2">
                <?php if(has_permission('quotations.create')): ?>
                <a href="<?php echo e(route('admin.quotations.create')); ?>"
                    class="bg-brand-500  hover:bg-brand-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create Quotation
                </a>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="bg-white rounded-t-xl shadow-sm border border-gray-100 p-4 border-b-0">
            <form action="<?php echo e(route('admin.quotations.index')); ?>" method="GET" class="flex flex-col sm:flex-row gap-3">

                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                        placeholder="Search Quotation Number, Customer..."
                        class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all placeholder-gray-400">
                </div>

                <div x-data="{ filterOpen: false }" @click.away="filterOpen = false" class="relative">
                    <button type="button" @click="filterOpen = !filterOpen"
                        class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors flex items-center gap-2 h-full">
                        <i data-lucide="filter" class="w-4 h-4 text-gray-500"></i> Filters
                        <?php if(request('status')): ?>
                            <span class="w-2 h-2 rounded-full bg-red-500 ml-1"></span>
                        <?php endif; ?>
                    </button>

                    <div x-show="filterOpen" x-cloak x-transition
                        class="absolute right-0 mt-2 w-64 bg-white border border-gray-100 rounded-xl shadow-xl z-50 p-4">

                        <div class="mb-4">
                            <label
                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                            <select name="status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                <option value="">All Statuses</option>
                                <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                                <option value="sent" <?php echo e(request('status') == 'sent' ? 'selected' : ''); ?>>Sent</option>
                                <option value="accepted" <?php echo e(request('status') == 'accepted' ? 'selected' : ''); ?>>Accepted
                                </option>
                                <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected
                                </option>
                                <option value="expired" <?php echo e(request('status') == 'expired' ? 'selected' : ''); ?>>Expired
                                </option>
                                <option value="converted" <?php echo e(request('status') == 'converted' ? 'selected' : ''); ?>>
                                    Converted to Invoice</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="<?php echo e(route('admin.quotations.index')); ?>"
                                class="px-3 py-1.5 text-xs font-bold text-gray-500 hover:text-gray-800 transition-colors">Clear</a>
                            <button type="submit"
                                class="bg-[#108c2a] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-[#0c6b1f] transition-colors">Apply</button>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm">
                    Search
                </button>

                <?php if(request()->hasAny(['search', 'status'])): ?>
                    <a href="<?php echo e(route('admin.quotations.index')); ?>"
                        class="bg-red-50 hover:bg-red-100 text-red-500 px-3 py-2.5 rounded-lg text-sm font-bold transition-colors flex items-center justify-center"
                        title="Clear All Filters">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        
        <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            
            
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-6 py-4">QT DETAILS</th>
                            <th class="px-6 py-4">CUSTOMER</th>
                            <th class="px-6 py-4">VALIDITY</th>
                            <th class="px-6 py-4 text-center">STATUS</th>
                            <th class="px-6 py-4 text-right">TOTAL AMOUNT</th>
                            <th class="px-6 py-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quotation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 transition-colors group">

                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">

                                        <a href="
                                        <?php if(has_permission('quotations.view')): ?>
                                            <?php echo e(route('admin.quotations.show', $quotation->id)); ?>

                                         <?php endif; ?>
                                         "
                                            class="font-extrabold text-[#108c2a] text-[13px] hover:underline">
                                            <?php echo e($quotation->quotation_number); ?>

                                        </a>

                                        <span class="text-[11px] text-gray-500 mt-0.5 font-medium">
                                            <?php echo e($quotation->quotation_date->format('d M, Y')); ?>

                                        </span>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800 text-[13px]">
                                            <?php echo e($quotation->display_name); ?>

                                        </span>
                                        <span class="text-[11px] text-gray-400 mt-0.5 font-bold uppercase tracking-tighter">
                                            <?php echo e($quotation->supply_state ?? 'State N/A'); ?>

                                        </span>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <?php if($quotation->valid_until): ?>
                                        <span
                                            class="text-[12px] font-semibold <?php echo e($quotation->is_expired && $quotation->status !== 'converted' ? 'text-red-500' : 'text-gray-600'); ?>">
                                            <?php echo e($quotation->valid_until->format('d M, Y')); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-[12px] text-gray-400 font-medium">N/A</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4 text-center">
                                    <?php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
                                            'sent' => 'bg-blue-50 text-blue-600 border-blue-200',
                                            'accepted' => 'bg-green-50 text-green-700 border-green-200',
                                            'rejected' => 'bg-red-50 text-red-600 border-red-200',
                                            'expired' => 'bg-orange-50 text-orange-600 border-orange-200',
                                            'converted' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        ];
                                        $color = $statusColors[$quotation->status] ?? $statusColors['draft'];
                                    ?>
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider border <?php echo e($color); ?>">
                                        <?php echo e($quotation->status); ?>

                                    </span>
                                </td>

                                
                                <td class="px-6 py-4 text-right">
                                    <span
                                        class="font-extrabold text-gray-800">₹<?php echo e(number_format($quotation->grand_total, 2)); ?></span>
                                </td>

                                
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 transition-opacity">

                                        
                                        <?php if(has_permission('quotations.view')): ?>
                                        <a href="<?php echo e(route('admin.quotations.show', $quotation->id)); ?>"
                                            class="w-8 h-8 rounded border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                            title="View Quotation">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <?php endif; ?>
                                      

                                        <?php if($quotation->status !== 'converted'): ?>
                                            
                                            <?php if($quotation->status === 'draft' && has_permission('quotations.mark_sent')): ?>
                                                <form action="<?php echo e(route('admin.quotations.mark_sent', $quotation->id)); ?>"
                                                    method="POST" class="inline-block">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit"
                                                        class="w-8 h-8 rounded border border-blue-200 text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors"
                                                        title="Mark as Sent">
                                                        <i data-lucide="send" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            
                                            <?php if(has_permission('quotations.update')): ?>
                                            <a href="<?php echo e(route('admin.quotations.edit', $quotation->id)); ?>"
                                                class="w-8 h-8 rounded border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                                title="Edit Quotation">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </a>
                                            <?php endif; ?>



                                            
                                            <?php if(has_permission('quotations.convert')): ?>
                                            <form action="<?php echo e(route('admin.quotations.convert', $quotation->id)); ?>"
                                                method="POST" @submit.prevent="confirmConvert($event.target)"
                                                class="inline-block">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="w-8 h-8 rounded border border-green-200 text-green-600 hover:bg-green-50 flex items-center justify-center transition-colors"
                                                    title="Convert to Invoice">
                                                    <i data-lucide="file-check-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>

                                            <?php if(has_permission('quotations.download_pdf')): ?>
                                            <a href="<?php echo e(route('admin.quotations.pdf', $quotation->id)); ?>" target="_blank"
                                                class="w-8 h-8 rounded border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                                title="Download Quotation">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                            </a>
                                            <?php endif; ?>

                                            
                                            <?php if(has_permission('quotations.delete')): ?>
                                            <form action="<?php echo e(route('admin.quotations.destroy', $quotation->id)); ?>"
                                                method="POST" @submit.prevent="confirmArchive($event.target)"
                                                class="inline-block">
                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="w-8 h-8 rounded border border-red-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"
                                                    title="Archive Quotation">
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
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="file-signature" class="w-10 h-10 mb-3 opacity-20"></i>
                                        <p class="text-sm font-medium">No quotations found.</p>
                                        <p class="text-xs mt-1">Create your first proposal to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="md:hidden divide-y divide-gray-50 border-t border-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quotation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
                            'sent' => 'bg-blue-50 text-blue-600 border-blue-200',
                            'accepted' => 'bg-green-50 text-green-700 border-green-200',
                            'rejected' => 'bg-red-50 text-red-600 border-red-200',
                            'expired' => 'bg-orange-50 text-orange-600 border-orange-200',
                            'converted' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        ];
                        $color = $statusColors[$quotation->status] ?? $statusColors['draft'];
                    ?>
                    <div class="p-4 hover:bg-gray-50/50 transition-colors flex flex-col gap-3">
                        
                        
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0">
                                <p class="font-bold text-gray-800 text-[14px] truncate">
                                    <?php echo e($quotation->display_name); ?>

                                </p>
                                <p class="text-[11px] text-gray-400 mt-0.5 font-bold uppercase tracking-tighter">
                                    <?php echo e($quotation->supply_state ?? 'State N/A'); ?>

                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-black text-[#108c2a] text-[16px]">₹<?php echo e(number_format($quotation->grand_total, 2)); ?></span>
                            </div>
                        </div>

                        
                        <div class="flex flex-col gap-2 bg-gray-50/80 px-3 py-2.5 rounded-lg border border-gray-100">
                            <div class="flex justify-between items-center">
                                <?php if(has_permission('quotations.view')): ?>
                                    <a href="<?php echo e(route('admin.quotations.show', $quotation->id)); ?>" class="font-extrabold text-[#108c2a] text-[13px] hover:underline">
                                        <?php echo e($quotation->quotation_number); ?>

                                    </a>
                                <?php else: ?>
                                    <span class="font-extrabold text-[#108c2a] text-[13px]"><?php echo e($quotation->quotation_number); ?></span>
                                <?php endif; ?>
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider border <?php echo e($color); ?>">
                                    <?php echo e($quotation->status); ?>

                                </span>
                            </div>
                            <div class="flex justify-between items-center pt-1 border-t border-gray-100/50">
                                <span class="text-[11px] text-gray-500 font-medium">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase">Date:</span> <?php echo e($quotation->quotation_date->format('d M, Y')); ?>

                                </span>
                                <span class="text-[11px] font-medium <?php echo e($quotation->is_expired && $quotation->status !== 'converted' ? 'text-red-500 font-bold' : 'text-gray-500'); ?>">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase">Valid:</span> <?php echo e($quotation->valid_until ? $quotation->valid_until->format('d M, Y') : 'N/A'); ?>

                                </span>
                            </div>
                        </div>

                        
                        <div class="flex items-center justify-end gap-2 pt-1 flex-wrap">
                            <?php if(has_permission('quotations.view')): ?>
                                <a href="<?php echo e(route('admin.quotations.show', $quotation->id)); ?>" class="w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors" title="View Quotation">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                            <?php endif; ?>

                            <?php if(has_permission('quotations.download_pdf')): ?>
                                <a href="<?php echo e(route('admin.quotations.pdf', $quotation->id)); ?>" class="w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors" title="Download Quotation">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>
                            <?php endif; ?>

                            <?php if($quotation->status !== 'converted'): ?>
                                <?php if($quotation->status === 'draft' && has_permission('quotations.mark_sent')): ?>
                                    <form action="<?php echo e(route('admin.quotations.mark_sent', $quotation->id)); ?>" method="POST" class="inline-block">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="w-8 h-8 rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors" title="Mark as Sent">
                                            <i data-lucide="send" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if(has_permission('quotations.update')): ?>
                                    <a href="<?php echo e(route('admin.quotations.edit', $quotation->id)); ?>" class="w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors" title="Edit Quotation">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if(has_permission('quotations.convert')): ?>
                                    <form action="<?php echo e(route('admin.quotations.convert', $quotation->id)); ?>" method="POST" @submit.prevent="confirmConvert($event.target)" class="inline-block">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="w-8 h-8 rounded-lg border border-green-200 text-green-600 hover:bg-green-50 flex items-center justify-center transition-colors" title="Convert to Invoice">
                                            <i data-lucide="file-check-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if(has_permission('quotations.delete')): ?>
                                    <form action="<?php echo e(route('admin.quotations.destroy', $quotation->id)); ?>" method="POST" @submit.prevent="confirmArchive($event.target)" class="inline-block">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors" title="Archive Quotation">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 text-center text-sm text-gray-400 bg-white">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="file-signature" class="w-10 h-10 mb-3 opacity-20"></i>
                            <p class="font-medium text-gray-500 text-[13px]">No quotations found.</p>
                            <p class="text-xs mt-1">Create your first proposal to get started.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($quotations->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    <?php echo e($quotations->links()); ?>

                </div>
            <?php endif; ?>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function quotationIndex() {
            return {
                confirmArchive(form) {
                    BizAlert.confirm(
                        'Archive Quotation?',
                        'Are you sure you want to delete this quotation? This cannot be undone.',
                        'Yes, Archive it',
                    ).then((result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Archiving...');
                            form.submit();
                        }
                    });
                },

                confirmConvert(form) {
                    BizAlert.confirm(
                        'Convert to Invoice?',
                        'This will generate a Draft Invoice with the exact details of this quotation. The quotation will be locked.',
                        'Yes, Convert it',
                    ).then((result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Converting to Invoice...');
                            form.submit();
                        }
                    });
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/quotations/index.blade.php ENDPATH**/ ?>