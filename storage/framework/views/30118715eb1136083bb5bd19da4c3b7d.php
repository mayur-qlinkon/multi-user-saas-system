<?php $__env->startSection('title', 'My Attendance'); ?>

<?php $__env->startSection('header-title'); ?>
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">My Attendance</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5"><?php echo e($from->format('d M Y')); ?> — <?php echo e($to->format('d M Y')); ?></p>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] { display: none !important; }
    .field-input {
        border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 8px 14px;
        font-size: 13px; outline: none; background: #fff; font-family: inherit;
        transition: border-color 150ms ease;
    }
    .field-input:focus { border-color: var(--brand-600); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="space-y-5 pb-10">

    
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <?php
        $stats = [
            ['label' => 'Present',  'value' => $summary['present'],  'bg' => '#ecfdf5', 'text' => '#065f46', 'icon' => 'check-circle'],
            ['label' => 'Absent',   'value' => $summary['absent'],   'bg' => '#fef2f2', 'text' => '#991b1b', 'icon' => 'x-circle'],
            ['label' => 'Late',     'value' => $summary['late'],     'bg' => '#fffbeb', 'text' => '#92400e', 'icon' => 'clock'],
            ['label' => 'Half Day', 'value' => $summary['half_day'], 'bg' => '#eff6ff', 'text' => '#1e40af', 'icon' => 'circle-half'],
            ['label' => 'On Leave', 'value' => $summary['on_leave'], 'bg' => '#f5f3ff', 'text' => '#5b21b6', 'icon' => 'calendar-off'],
            ['label' => 'Holiday',  'value' => $summary['holiday'],  'bg' => '#fdf4ff', 'text' => '#86198f', 'icon' => 'sun'],
        ];
        ?>
        <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-bold uppercase tracking-wider" style="color: <?php echo e($s['text']); ?>"><?php echo e($s['label']); ?></p>
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: <?php echo e($s['bg']); ?>">
                    <i data-lucide="<?php echo e($s['icon']); ?>" class="w-3.5 h-3.5" style="color: <?php echo e($s['text']); ?>"></i>
                </div>
            </div>
            <p class="text-[26px] font-black text-gray-900 leading-none"><?php echo e($s['value']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">

        
        <form method="GET" class="flex items-center gap-3 px-5 py-4 border-b border-gray-50 flex-wrap">
            <div class="flex items-center gap-2">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">From</label>
                <input type="date" name="from" value="<?php echo e($from->format('Y-m-d')); ?>" class="field-input">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">To</label>
                <input type="date" name="to" value="<?php echo e($to->format('Y-m-d')); ?>" class="field-input">
            </div>
            <select name="status" class="field-input">
                <option value="">All Status</option>
                <?php $__currentLoopData = \App\Models\Hrm\Attendance::STATUS_LABELS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($val); ?>" <?php echo e(request('status') == $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit"
                class="px-4 py-2 text-[12px] font-bold text-white rounded-lg border-none cursor-pointer"
                style="background: var(--brand-600)">
                Filter
            </button>
            <a href="<?php echo e(route('admin.hrm.my-attendance.index')); ?>"
                class="px-4 py-2 text-[12px] font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                Reset
            </a>
        </form>

        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-50">
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Check In</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Check Out</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Worked</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Overtime</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Method</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__empty_1 = true; $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $sc = \App\Models\Hrm\Attendance::STATUS_COLORS[$att->status] ?? ['bg'=>'#f3f4f6','text'=>'#374151','dot'=>'#9ca3af']; ?>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="text-[13px] font-bold text-gray-800"><?php echo e($att->date->format('d M Y')); ?></p>
                            <p class="text-[11px] text-gray-400"><?php echo e($att->date->format('l')); ?></p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-lg"
                                style="background: <?php echo e($sc['bg']); ?>; color: <?php echo e($sc['text']); ?>">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: <?php echo e($sc['dot']); ?>"></span>
                                <?php echo e(\App\Models\Hrm\Attendance::STATUS_LABELS[$att->status] ?? $att->status); ?>

                            </span>
                            <?php if($att->is_overridden): ?>
                            <span class="ml-1 text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">Edited</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-gray-700">
                            <?php echo e($att->check_in_time ? $att->check_in_time->format('h:i A') : '—'); ?>

                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-gray-700">
                            <?php echo e($att->check_out_time ? $att->check_out_time->format('h:i A') : '—'); ?>

                        </td>
                        <td class="px-5 py-3.5 text-[13px] font-semibold text-gray-700">
                            <?php echo e($att->worked_hours ? number_format($att->worked_hours, 1) . ' hrs' : '—'); ?>

                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-gray-500">
                            <?php echo e($att->overtime_hours > 0 ? number_format($att->overtime_hours, 1) . ' hrs' : '—'); ?>

                        </td>
                        <td class="px-5 py-3.5 text-[12px] text-gray-500 capitalize">
                            <?php echo e($att->check_in_method ?? '—'); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">
                            No attendance records found for selected period.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($attendances->hasPages()): ?>
        <div class="px-5 py-3 border-t border-gray-50"><?php echo e($attendances->links()); ?></div>
        <?php endif; ?>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/hrm/my-attendance/index.blade.php ENDPATH**/ ?>