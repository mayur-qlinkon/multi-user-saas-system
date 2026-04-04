<?php $__env->startSection('title', 'Announcements'); ?>

<?php $__env->startSection('header-title'); ?>
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">HRM / Announcements</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">Manage and broadcast company-wide updates.</p>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] { display: none !important; }
    .table-header th { font-size: 10px; font-weight: 900; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; padding: 12px 24px; border-bottom: 1px solid #f1f5f9; background: #fdfdfd; }
    .table-cell { padding: 16px 24px; vertical-align: top; border-bottom: 1px solid #f1f5f9; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="pb-12" x-data="announcementIndex()">

    
    <?php if(session('success')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('success')); ?>", 'success'));
        </script>
    <?php endif; ?>

    
    <div class="bg-white rounded-t-xl shadow-sm border border-gray-100 p-4 border-b-0 mb-0">
        <form id="filterForm" method="GET" action="<?php echo e(route('admin.hrm.announcements.index')); ?>" class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            
            
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 w-full lg:w-auto flex-1">
                
                
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search title or content..."
                        class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder-gray-400">
                </div>

                
                <select name="status" onchange="document.getElementById('filterForm').submit()" 
                    class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none bg-white cursor-pointer min-w-[130px]">
                    <option value="">All Statuses</option>
                    <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php echo e(($filters['status'] ?? '') === $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                
                <select name="type" onchange="document.getElementById('filterForm').submit()" 
                    class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none bg-white cursor-pointer min-w-[130px]">
                    <option value="">All Types</option>
                    <?php $__currentLoopData = $typeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php echo e(($filters['type'] ?? '') === $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                
                <div class="flex gap-2">
                    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors">
                        Filter
                    </button>
                    <?php if(array_filter($filters)): ?>
                        <a href="<?php echo e(route('admin.hrm.announcements.index')); ?>" title="Clear Filters"
                            class="bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-500 w-10 flex items-center justify-center rounded-lg transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="flex items-center gap-2 w-full lg:w-auto justify-end shrink-0">
                <a href="<?php echo e(route('admin.hrm.announcements.create')); ?>"
                    class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-all flex items-center gap-2 whitespace-nowrap active:scale-95">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create Announcement
                </a>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap min-w-[800px]">
                <thead class="table-header">
                    <tr>
                        <th class="w-[40%]">Announcement Info</th>
                        <th class="w-[15%]">Type & Priority</th>
                        <th class="w-[15%]">Target</th>
                        <th class="w-[15%]">Publish / Expire</th>
                        <th class="w-[10%] text-center">Status</th>
                        <th class="w-[5%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            
                            
                            <td class="table-cell">
                                <div class="flex items-start gap-3">
                                    <?php if($announcement->is_pinned): ?>
                                        <i data-lucide="pin" class="w-4 h-4 text-yellow-500 shrink-0 mt-0.5"></i>
                                    <?php else: ?>
                                        <i data-lucide="megaphone" class="w-4 h-4 text-gray-300 shrink-0 mt-0.5"></i>
                                    <?php endif; ?>
                                    <div class="min-w-0">
                                        <a href="<?php echo e(route('admin.hrm.announcements.show', $announcement)); ?>" class="font-bold text-[14px] text-brand-600 hover:text-brand-700 hover:underline truncate block max-w-md">
                                            <?php echo e($announcement->title); ?>

                                        </a>
                                        <p class="text-[12px] text-gray-500 truncate max-w-md mt-0.5">
                                            <?php echo e(Str::limit(strip_tags($announcement->content), 60)); ?>

                                        </p>
                                    </div>
                                </div>
                            </td>

                            
                            <td class="table-cell">
                                <?php
                                    $typeBadge = match($announcement->type) {
                                        'policy' => 'bg-blue-50 text-blue-700',
                                        'event' => 'bg-purple-50 text-purple-700',
                                        'urgent' => 'bg-orange-50 text-orange-700',
                                        'celebration' => 'bg-green-50 text-green-700',
                                        default => 'bg-gray-50 text-gray-600',
                                    };
                                    $priorityColor = match($announcement->priority) {
                                        'critical' => 'text-red-500',
                                        'high' => 'text-amber-500',
                                        default => 'text-gray-400',
                                    };
                                ?>
                                <div class="flex flex-col items-start gap-1.5">
                                    <span class="inline-flex text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded <?php echo e($typeBadge); ?>">
                                        <?php echo e($announcement->type_label); ?>

                                    </span>
                                    <span class="text-[11px] font-bold <?php echo e($priorityColor); ?> flex items-center gap-1">
                                        <i data-lucide="flag" class="w-3 h-3"></i> <?php echo e($announcement->priority_label); ?>

                                    </span>
                                </div>
                            </td>

                            
                            <td class="table-cell">
                                <span class="text-[12px] font-bold text-gray-700">
                                    <?php echo e(\App\Models\Hrm\Announcement::TARGET_LABELS[$announcement->target_audience] ?? ucfirst($announcement->target_audience)); ?>

                                </span>
                                <?php if($announcement->requires_acknowledgement): ?>
                                    <div class="text-[10px] text-gray-400 font-medium mt-1 flex items-center gap-1">
                                        <i data-lucide="check-square" class="w-3 h-3"></i> Req. Ack
                                    </div>
                                <?php endif; ?>
                            </td>

                            
                            <td class="table-cell">
                                <div class="text-[12px] font-bold text-gray-800">
                                    <?php echo e($announcement->publish_at ? $announcement->publish_at->format('d M Y') : 'Draft'); ?>

                                </div>
                                <div class="text-[11px] text-gray-400 mt-0.5">
                                    Exp: <?php echo e($announcement->expire_at ? $announcement->expire_at->format('d M Y') : 'Never'); ?>

                                </div>
                            </td>

                            
                            <td class="table-cell text-center">
                                <?php
                                    $statusBadge = match($announcement->status) {
                                        'published' => 'bg-green-50 text-green-700 border-green-200',
                                        'scheduled' => 'bg-sky-50 text-sky-700 border-sky-200',
                                        'expired'   => 'bg-red-50 text-red-600 border-red-200',
                                        default     => 'bg-gray-50 text-gray-500 border-gray-200',
                                    };
                                ?>
                                <span class="inline-flex text-[10px] font-extrabold uppercase tracking-wider border px-2 py-0.5 rounded <?php echo e($statusBadge); ?>">
                                    <?php echo e($announcement->status_label); ?>

                                </span>
                            </td>

                            
                            <td class="table-cell text-right">
                                <div class="flex items-center justify-end gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                    
                                    <?php if(!$announcement->is_published): ?>
                                        <button @click="publishAnnouncement(<?php echo e($announcement->id); ?>, '<?php echo e(addslashes($announcement->title)); ?>')" title="Publish Now"
                                            class="w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition-colors">
                                            <i data-lucide="send" class="w-4 h-4 text-green-600"></i>
                                        </button>
                                    <?php endif; ?>
                                    <a href="<?php echo e(route('admin.hrm.announcements.show', $announcement)); ?>" title="View"
                                        class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.hrm.announcements.edit', $announcement)); ?>" title="Edit"
                                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <button @click="confirmDelete(<?php echo e($announcement->id); ?>, '<?php echo e(addslashes($announcement->title)); ?>')" title="Delete"
                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 font-medium">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                        <i data-lucide="megaphone" class="w-8 h-8 text-gray-300"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500">No Announcements Found</p>
                                    <p class="text-xs mt-1">Adjust your filters or create a new announcement to get started.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if($announcements->hasPages()): ?>
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                <?php echo e($announcements->links()); ?>

            </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
window.announcementIndex = function() {
    return {
        init() {
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        // --- NEW PUBLISH FUNCTION ---
        publishAnnouncement(id, title) {
            BizAlert.confirm('Publish Announcement', `Are you sure you want to publish "${title}" immediately?`, 'Yes, Publish').then(async r => {
                if (!r.isConfirmed) return;
                
                try {
                    const response = await fetch(`<?php echo e(url('admin/hrm/announcements')); ?>/${id}/publish`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    });
                    const res = await response.json();
                    
                    if (res.success) { // Assuming your controller returns a boolean 'success'
                        BizAlert.toast(res.message || 'Announcement published successfully!', 'success');
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        BizAlert.toast(res.message || 'Failed to publish announcement.', 'error');
                    }
                } catch {
                    BizAlert.toast('Network error while publishing.', 'error');
                }
            });
        },

        // --- EXISTING DELETE FUNCTION ---
        confirmDelete(id, title) {
            BizAlert.confirm('Delete Announcement', `Are you sure you want to delete "${title}"?`, 'Delete').then(async r => {
                if (!r.isConfirmed) return;
                
                try {
                    const response = await fetch(`<?php echo e(url('admin/hrm/announcements')); ?>/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const res = await response.json();
                    
                    if (res.success) {
                        BizAlert.toast(res.message, 'success');
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        BizAlert.toast(res.message || 'Cannot delete.', 'error');
                    }
                } catch {
                    BizAlert.toast('Network error.', 'error');
                }
            });
        }
    };
};
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/hrm/announcements/index.blade.php ENDPATH**/ ?>