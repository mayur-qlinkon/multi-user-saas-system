<?php $__env->startSection('title', 'Contact Inquiries'); ?>
<?php $__env->startSection('header', 'Contact Inquiries'); ?>

<?php $__env->startSection('content'); ?>
    <div class="pb-10">

        
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Contact Inquiries</h1>
                <p class="text-sm text-gray-500 mt-1">Messages submitted via the public landing page.</p>
            </div>
            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full">
                <?php echo e($inquiries->total()); ?> total
            </span>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <?php if($inquiries->isEmpty()): ?>
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <i data-lucide="inbox" class="w-10 h-10 mb-3"></i>
                    <p class="text-sm font-medium">No inquiries yet</p>
                </div>
            <?php else: ?>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-5 py-3.5 font-semibold text-xs text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-xs text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-xs text-gray-500 uppercase tracking-wider hidden md:table-cell">Message</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-xs text-gray-500 uppercase tracking-wider hidden lg:table-cell">Date</th>
                            <th class="px-5 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $inquiries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inquiry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 transition-colors <?php echo e($inquiry->is_read ? '' : 'bg-blue-50/40'); ?>">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <?php if(! $inquiry->is_read): ?>
                                            <span class="w-2 h-2 rounded-full bg-brand-600 shrink-0"></span>
                                        <?php else: ?>
                                            <span class="w-2 h-2 shrink-0"></span>
                                        <?php endif; ?>
                                        <span class="font-semibold text-gray-800"><?php echo e($inquiry->name); ?></span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-gray-600"><?php echo e($inquiry->email); ?></td>
                                <td class="px-5 py-4 text-gray-500 hidden md:table-cell max-w-xs truncate">
                                    <?php echo e(Str::limit($inquiry->message, 80)); ?>

                                </td>
                                <td class="px-5 py-4 text-gray-400 text-xs hidden lg:table-cell whitespace-nowrap">
                                    <?php echo e($inquiry->created_at->format('d M Y, h:i A')); ?>

                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="<?php echo e(route('platform.inquiries.show', $inquiry)); ?>"
                                        class="text-brand-600 hover:text-brand-700 font-semibold text-xs">
                                        View →
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>

                <?php if($inquiries->hasPages()): ?>
                    <div class="px-5 py-4 border-t border-gray-100">
                        <?php echo e($inquiries->links()); ?>

                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/platform/inquiries/index.blade.php ENDPATH**/ ?>