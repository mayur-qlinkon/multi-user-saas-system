<?php $__env->startSection('title', 'Attendance Report'); ?>

<?php $__env->startSection('header-title'); ?>
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Attendance Report</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">Filter and review attendance records across your team</p>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] { display: none !important; }
    .filter-input { border: 1.5px solid #e5e7eb; border-radius: 9px; padding: 7px 10px; font-size: 12px; color: #374151; outline: none; background: #fff; font-family: inherit; transition: border-color 150ms; }
    .filter-input:focus { border-color: var(--brand-600); }
    .field-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
    .field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 10px 30px; font-size: 13.5px; outline: none; transition: border-color 150ms ease, box-shadow 150ms ease; font-family: inherit; background: #fff; }
    .field-input:focus { border-color: var(--brand-600); box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-600) 10%, transparent); }
    .field-error { font-size: 11px; color: #dc2626; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
    .status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .method-badge { display: inline-flex; align-items: center; gap: 3px; padding: 1px 6px; border-radius: 6px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: #f0f9ff; color: #0369a1; }
    .override-badge { display: inline-flex; align-items: center; gap: 3px; padding: 1px 6px; border-radius: 6px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: #fef3c7; color: #92400e; }
    .table-row { border-bottom: 1px solid #f8fafc; transition: background 100ms; }
    .table-row:hover { background: #fafbfc; }
    .table-row:last-child { border-bottom: none; }
    .emp-avatar { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; flex-shrink: 0; color: #fff; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php
    $statusColors = \App\Models\Hrm\Attendance::STATUS_COLORS;
    $statusLabels = \App\Models\Hrm\Attendance::STATUS_LABELS;
?>

<div class="pb-10" x-data="attendanceReport()">

    
    <div class="bg-white border border-gray-100 rounded-2xl px-4 py-3 mb-4">
        <form method="GET" action="<?php echo e(route('admin.hrm.attendance.report')); ?>" id="report-filter-form">
            <div class="flex items-center gap-3 flex-wrap">

                
                <div>
                    <input type="date" name="date_from" value="<?php echo e($filters['date_from'] ?? ''); ?>"
                        class="filter-input" placeholder="From Date">
                </div>

                
                <div>
                    <input type="date" name="date_to" value="<?php echo e($filters['date_to'] ?? ''); ?>"
                        class="filter-input" placeholder="To Date">
                </div>

                
                <select name="department_id" class="filter-input">
                    <option value="">All Departments</option>
                    <?php $__currentLoopData = $departments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($dept->id); ?>" <?php echo e(($filters['department_id'] ?? '') == $dept->id ? 'selected' : ''); ?>>
                            <?php echo e($dept->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                
                <select name="store_id" class="filter-input">
                    <option value="">All Stores</option>
                    <?php $__currentLoopData = $stores ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($store->id); ?>" <?php echo e(($filters['store_id'] ?? '') == $store->id ? 'selected' : ''); ?>>
                            <?php echo e($store->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                
                <select name="status" class="filter-input">
                    <option value="">All Status</option>
                    <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php echo e(($filters['status'] ?? '') === $key ? 'selected' : ''); ?>>
                            <?php echo e($label); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                
                <div class="relative flex-1 min-w-[160px]">                    
                    <input type="text" name="q" value="<?php echo e(request('q')); ?>"
                        placeholder="Search employee..."
                        class="filter-input pl-4 w-full">
                </div>

                
                <button type="submit"
                    class="text-[12px] font-bold px-4 py-2 rounded-lg text-white hover:opacity-90 transition-opacity"
                    style="background: var(--brand-600)">
                    <i data-lucide="filter" class="w-3.5 h-3.5 inline-block mr-1"></i>
                    Filter
                </button>

                
                <?php if(request()->hasAny(['date_from', 'date_to', 'department_id', 'store_id', 'status', 'q'])): ?>
                    <a href="<?php echo e(route('admin.hrm.attendance.report')); ?>"
                        class="text-[11px] font-bold px-3 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors">
                        Clear
                    </a>
                <?php endif; ?>

            </div>
        </form>
    </div>

    
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">

        <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between flex-wrap gap-2">
            <p class="text-[12px] font-bold text-gray-500">
                <?php echo e($report->total()); ?> record<?php echo e($report->total() !== 1 ? 's' : ''); ?>

                <?php if(request()->hasAny(['date_from', 'date_to', 'department_id', 'store_id', 'status', 'q'])): ?>
                    <span class="text-gray-400 font-medium">&mdash; filtered</span>
                <?php endif; ?>
            </p>
        </div>

        <?php if($report->isEmpty()): ?>
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                    <i data-lucide="calendar-x" class="w-7 h-7 text-gray-300"></i>
                </div>
                <p class="font-semibold text-gray-500 mb-1">No attendance records found</p>
                <p class="text-sm text-gray-400">
                    <?php if(request()->hasAny(['date_from', 'date_to', 'department_id', 'store_id', 'status', 'q'])): ?>
                        Try adjusting your filters
                    <?php else: ?>
                        Attendance records will appear here
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider w-[50px]">#</th>
                            <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider w-[220px]">Employee</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Check In</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Check Out</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Worked</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Overtime</th>
                            <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $report; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $emp         = $att->employee;
                                $empName     = $emp->user->name ?? 'Unknown';
                                $initials    = strtoupper(substr($empName, 0, 1));
                                $avatarColors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4'];
                                $avatarBg    = $avatarColors[crc32($empName) % count($avatarColors)];
                                $sColor      = $statusColors[$att->status] ?? $statusColors['present'];
                            ?>
                            <tr class="table-row">

                                
                                <td class="px-5 py-3 text-[12px] font-bold text-gray-400">
                                    <?php echo e($report->firstItem() + $loop->index); ?>

                                </td>

                                
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="emp-avatar" style="background: <?php echo e($avatarBg); ?>">
                                            <?php echo e($initials); ?>

                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[13px] font-bold text-gray-900 truncate max-w-[140px]">
                                                <?php echo e($empName); ?>

                                            </p>
                                            <p class="text-[11px] text-gray-400 font-medium truncate">
                                                <?php echo e($emp->employee_code ?? '---'); ?>

                                            </p>
                                        </div>
                                    </div>
                                </td>

                                
                                <td class="px-3 py-3">
                                    <span class="text-[12px] font-bold text-gray-700">
                                        <?php echo e($att->date->format('d M Y')); ?>

                                    </span>
                                    <p class="text-[10px] text-gray-400"><?php echo e($att->date->format('l')); ?></p>
                                </td>

                                
                                <td class="px-3 py-3">
                                    <?php if($att->check_in_time): ?>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[12px] font-bold text-gray-700">
                                                <?php echo e($att->check_in_time->format('h:i A')); ?>

                                            </span>
                                            <?php if($att->check_in_method): ?>
                                                <span class="method-badge"><?php echo e(strtoupper($att->check_in_method)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[12px] text-gray-300">---</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-3 py-3">
                                    <?php if($att->check_out_time): ?>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[12px] font-bold text-gray-700">
                                                <?php echo e($att->check_out_time->format('h:i A')); ?>

                                            </span>
                                            <?php if($att->check_out_method): ?>
                                                <span class="method-badge"><?php echo e(strtoupper($att->check_out_method)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[12px] text-gray-300">---</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-3 py-3">
                                    <span class="text-[12px] font-bold text-gray-700">
                                        <?php echo e($att->worked_hours ? number_format($att->worked_hours, 1) . 'h' : '---'); ?>

                                    </span>
                                </td>

                                
                                <td class="px-3 py-3">
                                    <span class="text-[12px] font-bold <?php echo e($att->overtime_hours > 0 ? 'text-blue-600' : 'text-gray-400'); ?>">
                                        <?php echo e($att->overtime_hours ? number_format($att->overtime_hours, 1) . 'h' : '---'); ?>

                                    </span>
                                </td>

                                
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="status-badge"
                                            style="background: <?php echo e($sColor['bg']); ?>; color: <?php echo e($sColor['text']); ?>">
                                            <span class="w-1.5 h-1.5 rounded-full" style="background: <?php echo e($sColor['dot']); ?>"></span>
                                            <?php echo e($statusLabels[$att->status] ?? ucfirst($att->status)); ?>

                                        </span>
                                        <?php if($att->is_overridden): ?>
                                            <span class="override-badge" title="Overridden: <?php echo e($att->override_reason); ?>">
                                                <i data-lucide="shield-check" class="w-3 h-3"></i>
                                                Override
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1 justify-end">
                                        <button @click="openOverride(<?php echo e($att->id); ?>, '<?php echo e(addslashes($empName)); ?>', '<?php echo e($att->date->format('d M Y')); ?>', '<?php echo e($att->status); ?>', '<?php echo e($att->check_in_time ? $att->check_in_time->format('Y-m-d H:i:s') : ''); ?>', '<?php echo e($att->check_out_time ? $att->check_out_time->format('Y-m-d H:i:s') : ''); ?>', <?php echo e(json_encode($att->override_reason ?? '')); ?>)"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                            title="Override Attendance">
                                            <i data-lucide="pencil-line" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            
            <?php if($report->hasPages()): ?>
                <div class="px-5 py-4 border-t border-gray-50 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-[12px] text-gray-400 font-medium">
                        Showing <?php echo e($report->firstItem()); ?>&ndash;<?php echo e($report->lastItem()); ?> of <?php echo e($report->total()); ?>

                    </p>
                    <div class="flex items-center gap-1">
                        <?php if($report->onFirstPage()): ?>
                            <span class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-300 cursor-not-allowed">&larr; Prev</span>
                        <?php else: ?>
                            <a href="<?php echo e($report->previousPageUrl()); ?>"
                                class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-600 hover:bg-gray-100 transition-colors">&larr; Prev</a>
                        <?php endif; ?>

                        <?php $__currentLoopData = $report->getUrlRange(max(1, $report->currentPage()-2), min($report->lastPage(), $report->currentPage()+2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e($url); ?>"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-[12px] font-bold transition-colors
                                <?php echo e($page == $report->currentPage() ? 'text-white' : 'text-gray-600 hover:bg-gray-100'); ?>"
                                style="<?php echo e($page == $report->currentPage() ? 'background: var(--brand-600)' : ''); ?>">
                                <?php echo e($page); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if($report->hasMorePages()): ?>
                            <a href="<?php echo e($report->nextPageUrl()); ?>"
                                class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-600 hover:bg-gray-100 transition-colors">Next &rarr;</a>
                        <?php else: ?>
                            <span class="px-3 py-1.5 rounded-lg text-[12px] font-bold text-gray-300 cursor-not-allowed">Next &rarr;</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    
    <div x-show="overrideModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl overflow-hidden" @click.away="overrideModal = false"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-black text-gray-800 uppercase tracking-widest text-sm">Override Attendance</h3>
                <button @click="overrideModal = false" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form @submit.prevent="submitOverride()">
                <div class="p-6 space-y-4">

                    
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="text-[12px] text-gray-500">
                            <span class="font-bold text-gray-700" x-text="overrideEmployee"></span>
                            &mdash; <span x-text="overrideDate"></span>
                        </p>
                    </div>

                    
                    <div>
                        <label class="field-label">Status</label>
                        <select x-model="overrideForm.status" class="field-input">
                            <option value="">Keep Current</option>
                            <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Check In Time</label>
                            <input type="datetime-local" x-model="overrideForm.check_in_time" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Check Out Time</label>
                            <input type="datetime-local" x-model="overrideForm.check_out_time" class="field-input">
                        </div>
                    </div>

                    
                    <div>
                        <label class="field-label">Reason <span class="text-red-400">*</span></label>
                        <textarea x-model="overrideForm.reason" class="field-input" rows="3"
                            placeholder="Explain why this attendance is being overridden" required></textarea>
                        <p class="field-error" x-show="overrideErrors.reason" x-text="overrideErrors.reason"></p>
                    </div>

                </div>

                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button type="button" @click="overrideModal = false"
                        class="px-4 py-2 text-[13px] font-bold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="overrideSaving"
                        class="px-5 py-2 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity disabled:opacity-50"
                        style="background: var(--brand-600)">
                        <span x-show="!overrideSaving">Save Override</span>
                        <span x-show="overrideSaving" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
window.attendanceReport = function() {
    return {
        overrideModal: false,
        overrideSaving: false,
        overrideId: null,
        overrideEmployee: '',
        overrideDate: '',
        overrideErrors: {},
        overrideForm: {
            status: '',
            check_in_time: '',
            check_out_time: '',
            reason: '',
        },

        init() {
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        // 🌟 ADD THIS HELPER FUNCTION
        // Converts "2026-04-04 14:30:00" to "2026-04-04T14:30"
        formatDateForInput(dateStr) {
            if (!dateStr) return '';
            return dateStr.replace(' ', 'T').substring(0, 16);
        },

        // 🌟 UPDATE THIS FUNCTION to accept the new time variables
        openOverride(id, empName, date, currentStatus, checkIn, checkOut, existingReason = '') {
            this.overrideId = id;
            this.overrideEmployee = empName;
            this.overrideDate = date;
            this.overrideErrors = {};
            
            this.overrideForm = {
                status: currentStatus || '',
                // Pass the raw dates through our new formatter!
                check_in_time: this.formatDateForInput(checkIn),
                check_out_time: this.formatDateForInput(checkOut),
                reason: existingReason || '',
            };
            
            this.overrideModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async submitOverride() {
            this.overrideSaving = true;
            this.overrideErrors = {};

            const payload = {};
            if (this.overrideForm.status) payload.status = this.overrideForm.status;
            if (this.overrideForm.check_in_time) payload.check_in_time = this.overrideForm.check_in_time.replace('T', ' ') + ':00';
            if (this.overrideForm.check_out_time) payload.check_out_time = this.overrideForm.check_out_time.replace('T', ' ') + ':00';
            payload.reason = this.overrideForm.reason;

            try {
                const res = await fetch(`<?php echo e(url('admin/hrm/attendance')); ?>/${this.overrideId}/override`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        this.overrideErrors = {};
                        for (const [key, messages] of Object.entries(data.errors)) {
                            this.overrideErrors[key] = messages[0];
                        }
                    } else {
                        BizAlert.toast(data.message || 'Failed to override', 'error');
                    }
                    return;
                }

                BizAlert.toast(data.message || 'Attendance overridden successfully', 'success');
                this.overrideModal = false;
                setTimeout(() => window.location.reload(), 600);
            } catch (e) {
                BizAlert.toast('Network error. Please try again.', 'error');
            } finally {
                this.overrideSaving = false;
            }
        },
    };
};
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/hrm/attendance/report.blade.php ENDPATH**/ ?>