

<?php $__env->startSection('title', 'System Audit Logs'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Settings / Audit Logs</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="pb-10" x-data="auditLogViewer()">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-[#212538] tracking-tight">System Audit Trail</h1>
            <p class="text-sm text-gray-500 font-medium">Immutable record of all system creations, updates, and deletions.
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-900 text-white text-[11px] font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4 rounded-tl-lg">Timestamp</th>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Module</th>
                            <th class="px-6 py-4">Action</th>
                            <th class="px-6 py-4 text-right rounded-tr-lg">Changes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3">
                                    <div class="font-bold text-gray-900"><?php echo e($log->created_at->format('d M Y')); ?></div>
                                    <div class="text-[11px] text-gray-500"><?php echo e($log->created_at->format('h:i:s A')); ?></div>
                                </td>
                                <td class="px-6 py-3 font-bold text-gray-800">
                                    <?php echo e($log->causer->name ?? 'System / Auto'); ?>

                                </td>
                                <td class="px-6 py-3">
                                    <span
                                        class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-bold uppercase tracking-wider">
                                        <?php echo e(class_basename($log->subject_type)); ?> #<?php echo e($log->subject_id); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <?php
                                        $color = match ($log->event) {
                                            'created' => 'text-green-600',
                                            'updated' => 'text-blue-600',
                                            'deleted' => 'text-red-600',
                                            default => 'text-gray-600',
                                        };
                                    ?>
                                    <span class="font-black uppercase text-[11px] <?php echo e($color); ?>">
                                        <?php echo e($log->description); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <?php if(isset($log->properties['old']) || isset($log->properties['attributes'])): ?>
                                        <button
                                            @click="viewChanges(<?php echo e(json_encode($log->properties)); ?>, '<?php echo e($log->description); ?>')"
                                            class="text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                            View Data
                                        </button>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs italic">No payload</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 font-medium">No audit logs
                                    found. Make a change in the system to see it here!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($logs->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    <?php echo e($logs->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div x-show="isOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden" @click.away="isOpen = false">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-black text-gray-800 uppercase tracking-widest text-sm" x-text="modalTitle"></h3>
                    <button @click="isOpen = false" class="text-gray-400 hover:text-red-500"><i data-lucide="x"
                            class="w-5 h-5"></i></button>
                </div>

                <div class="p-6 grid grid-cols-2 gap-4 max-h-[60vh] overflow-y-auto">
                    <div>
                        <h4 class="text-xs font-bold text-red-500 uppercase mb-2 border-b border-red-100 pb-1">Old Values
                        </h4>
                        <pre class="text-[11px] bg-red-50 text-red-900 p-3 rounded overflow-x-auto font-mono"
                            x-text="JSON.stringify(properties.old || {}, null, 2)"></pre>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-green-500 uppercase mb-2 border-b border-green-100 pb-1">New
                            Values</h4>
                        <pre class="text-[11px] bg-green-50 text-green-900 p-3 rounded overflow-x-auto font-mono"
                            x-text="JSON.stringify(properties.attributes || {}, null, 2)"></pre>
                    </div>
                </div>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('auditLogViewer', () => ({
                isOpen: false,
                properties: {},
                modalTitle: '',

                viewChanges(props, title) {
                    this.properties = props;
                    this.modalTitle = title;
                    this.isOpen = true;
                }
            }));
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/audit-logs/index.blade.php ENDPATH**/ ?>