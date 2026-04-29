<?php $__env->startSection('title', 'Tasks'); ?>

<?php $__env->startSection('header-title'); ?>
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Tasks</h1>
        
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] { display: none !important; }

    .field-label {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 6px;
    }

    .field-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 30px;
        font-size: 13.5px;
        outline: none;
        transition: border-color 150ms ease, box-shadow 150ms ease;
        font-family: inherit;
        background: #fff;
    }

    .field-input:focus {
        border-color: var(--brand-600);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-600) 10%, transparent);
    }

    select.field-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }

    .table-row { border-bottom: 1px solid #f8fafc; transition: background 100ms; }
    .table-row:hover { background: #fafbfc; }
    .table-row:last-child { border-bottom: none; }

    .stat-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 14px;
        padding: 14px 16px;
        transition: box-shadow 150ms, border-color 150ms;
    }
    .stat-card:hover { border-color: #e2e8f0; box-shadow: 0 3px 12px rgba(0,0,0,0.06); }

    .avatar-stack { display: flex; }
    .avatar-stack .avatar-item {
        width: 26px; height: 26px;
        border-radius: 50%;
        border: 2px solid #fff;
        background: #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 800;
        color: #374151;
        margin-left: -6px;
        flex-shrink: 0;
        text-transform: uppercase;
    }
    .avatar-stack .avatar-item:first-child { margin-left: 0; }
    .avatar-stack .avatar-more {
        background: #f1f5f9;
        color: #6b7280;
        font-size: 9px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php
    $statusLabels = \App\Models\Hrm\HrmTask::STATUS_LABELS;
    $statusColors = \App\Models\Hrm\HrmTask::STATUS_COLORS;
    $priorityLabels = \App\Models\Hrm\HrmTask::PRIORITY_LABELS;
    $priorityColors = \App\Models\Hrm\HrmTask::PRIORITY_COLORS;

    $totalCount       = $tasks->total();
    $inProgressCount  = $tasks->getCollection()->where('status', 'in_progress')->count();
    $overdueCount     = $tasks->getCollection()->filter(fn($t) => $t->is_overdue)->count();
    $completedCount   = $tasks->getCollection()->where('status', 'completed')->count();
?>

<div class="pb-10">

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total</p>
            <p class="text-2xl font-black text-gray-900"><?php echo e($totalCount); ?></p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">In Progress</p>
            <p class="text-2xl font-black text-blue-600"><?php echo e($inProgressCount); ?></p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Overdue</p>
            <p class="text-2xl font-black text-red-500"><?php echo e($overdueCount); ?></p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Completed</p>
            <p class="text-2xl font-black text-green-600"><?php echo e($completedCount); ?></p>
        </div>
    </div>

    
    <form method="GET" action="<?php echo e(route('admin.hrm.tasks.index')); ?>"
          class="bg-white border border-gray-100 rounded-2xl px-4 py-3 mb-4">
        <div class="flex items-center gap-3 flex-wrap">

            
            <div class="w-full sm:w-auto sm:min-w-[140px]">
                <select name="status" class="field-input !py-2 !text-[13px]" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php echo e(($filters['status'] ?? '') === $val ? 'selected' : ''); ?>>
                            <?php echo e($label); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
            <div class="w-full sm:w-auto sm:min-w-[130px]">
                <select name="priority" class="field-input !py-2 !text-[13px]" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <?php $__currentLoopData = $priorityLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php echo e(($filters['priority'] ?? '') === $val ? 'selected' : ''); ?>>
                            <?php echo e($label); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
            <div class="w-full sm:w-auto sm:min-w-[160px]">
                <select name="employee_id" class="field-input !py-2 !text-[13px]" onchange="this.form.submit()">
                    <option value="">All Employees</option>
                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($emp->id); ?>" <?php echo e(($filters['employee_id'] ?? '') == $emp->id ? 'selected' : ''); ?>>
                            <?php echo e($emp->employee_code); ?> – <?php echo e($emp->user->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
            <div class="relative w-full sm:flex-1 sm:min-w-[180px]">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"></i>
                <input type="text" name="search" value="<?php echo e($filters['search'] ?? ''); ?>"
                    placeholder="Search tasks..."
                    class="field-input pl-9 !py-2 !text-[13px]">
            </div>

            
            <?php if(array_filter($filters)): ?>
                <a href="<?php echo e(route('admin.hrm.tasks.index')); ?>"
                    class="inline-flex items-center gap-1.5 text-[12px] font-bold px-3 py-2 rounded-lg text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 transition-colors">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    Clear
                </a>
            <?php endif; ?>

            
            <?php if(has_permission('hrm_tasks.create')): ?>
                <a href="<?php echo e(route('admin.hrm.tasks.create')); ?>"
                    class="ml-auto inline-flex items-center gap-1.5 text-[12px] font-bold px-4 py-2 rounded-lg text-white hover:opacity-90 transition-opacity"
                    style="background: var(--brand-600)">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Create Task
                </a>
            <?php endif; ?>

        </div>
    </form>

    
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
        
        
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider w-[50px]">#</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Task</th>
                        <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider whitespace-nowrap">Assignees</th>
                        <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider whitespace-nowrap">Priority</th>
                        <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider whitespace-nowrap">Due In</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $sc  = $statusColors[$task->status] ?? $statusColors['pending'];
                            $pc  = $priorityColors[$task->priority] ?? $priorityColors['low'];
                            $isOverdue = $task->is_overdue;
                            $visibleAssignees = $task->assignees->take(3);
                            $extraCount = $task->assignees->count() - $visibleAssignees->count();
                        ?>
                        <tr class="table-row">
                            <td class="px-5 py-3 text-[12px] font-bold text-gray-400"><?php echo e($tasks->firstItem() + $loop->index); ?></td>

                            
                            <td class="px-5 py-3">
                                <div class="flex items-start gap-2">
                                    <?php if($isOverdue): ?>
                                        <span class="mt-1 flex-shrink-0 w-2 h-2 rounded-full bg-red-500" title="Overdue"></span>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-[13px] font-bold text-gray-800"><?php echo e($task->title); ?></p>
                                        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                            <?php if($task->project): ?>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide"><?php echo e($task->project); ?></span>
                                            <?php endif; ?>
                                            <?php if($task->category): ?>
                                                <span class="text-[10px] text-gray-300">·</span>
                                                <span class="text-[10px] text-gray-400"><?php echo e($task->category); ?></span>
                                            <?php endif; ?>
                                            <?php if($task->due_date): ?>
                                                <span class="text-[10px] text-gray-300">·</span>
                                                <span class="text-[10px] <?php echo e($isOverdue ? 'text-red-500 font-semibold' : 'text-gray-400'); ?>">
                                                    Due <?php echo e($task->due_date->format('d M Y')); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            
                            <td class="px-3 py-3 whitespace-nowrap">
                                <?php if($task->assignees->count()): ?>
                                    <div class="avatar-stack">
                                        <?php $__currentLoopData = $visibleAssignees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="avatar-item" title="<?php echo e($emp->user->name); ?>"
                                                style="background: <?php echo e(['#eff6ff','#f0fdf4','#fdf4ff','#fffbeb','#fef2f2'][$loop->index % 5]); ?>">
                                                <?php echo e(strtoupper(substr($emp->user->name, 0, 1))); ?>

                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($extraCount > 0): ?>
                                            <div class="avatar-item avatar-more">+<?php echo e($extraCount); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] text-gray-300">—</span>
                                <?php endif; ?>
                            </td>

                            
                            <td class="px-3 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-md"
                                    style="background: <?php echo e($pc['bg']); ?>; color: <?php echo e($pc['text']); ?>">
                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                        style="background: <?php echo e($pc['dot'] ?? $pc['text']); ?>"></span>
                                    <?php echo e(\App\Models\Hrm\HrmTask::PRIORITY_LABELS[$task->priority] ?? $task->priority); ?>

                                </span>
                            </td>

                            
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-md"
                                    style="background: <?php echo e($sc['bg']); ?>; color: <?php echo e($sc['text']); ?>">
                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                        style="background: <?php echo e($sc['dot'] ?? $sc['text']); ?>"></span>
                                    <?php echo e(\App\Models\Hrm\HrmTask::STATUS_LABELS[$task->status] ?? $task->status); ?>

                                </span>
                            </td>

                            
                            <td class="px-3 py-3 whitespace-nowrap">
                                <?php if($task->due_date): ?>
                                    <?php if(in_array($task->status, ['completed', 'cancelled'])): ?>
                                        <span class="text-[11px] text-gray-400">—</span>
                                    <?php elseif($isOverdue): ?>
                                        <span class="text-[11px] font-bold text-red-500">
                                            <?php echo e($task->due_date->diffForHumans()); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-[11px] text-gray-500">
                                            <?php echo e($task->due_date->diffForHumans()); ?>

                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-[11px] text-gray-300">—</span>
                                <?php endif; ?>
                            </td>

                            
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <?php if(has_permission('hrm_tasks.view')): ?>
                                        <a href="<?php echo e(route('admin.hrm.tasks.show', $task)); ?>"
                                            class="w-[30px] h-[30px] rounded-lg flex items-center justify-center bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors"
                                            title="View">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if(has_permission('hrm_tasks.update')): ?>
                                        <a href="<?php echo e(route('admin.hrm.tasks.edit', $task)); ?>"
                                            class="w-[30px] h-[30px] rounded-lg flex items-center justify-center bg-amber-50 text-amber-500 hover:bg-amber-100 hover:text-amber-700 transition-colors"
                                            title="Edit">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if(has_permission('hrm_tasks.change_status')): ?>
                                        <button onclick="quickStatus(<?php echo e($task->id); ?>, '<?php echo e($task->status); ?>')"
                                            class="w-[30px] h-[30px] rounded-lg flex items-center justify-center bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-700 transition-colors"
                                            title="Update Status">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if(has_permission('hrm_tasks.delete')): ?>
                                        <button onclick="confirmDelete(<?php echo e($task->id); ?>, '<?php echo e(addslashes($task->title)); ?>')"
                                            class="w-[30px] h-[30px] rounded-lg flex items-center justify-center bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 transition-colors"
                                            title="Delete">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7">
                                <div class="flex flex-col items-center justify-center py-20 text-center">
                                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                        <i data-lucide="clipboard" class="w-7 h-7 text-gray-300"></i>
                                    </div>
                                    <p class="font-semibold text-gray-500 mb-1">No tasks found</p>
                                    <p class="text-sm text-gray-400 mb-4">Create your first task to get started</p>
                                    <a href="<?php echo e(route('admin.hrm.tasks.create')); ?>"
                                        class="text-sm font-bold px-4 py-2 rounded-xl text-white" style="background: var(--brand-600)">
                                        Create Task
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="md:hidden divide-y divide-gray-50 border-t border-gray-50 bg-white">
            <?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $sc  = $statusColors[$task->status] ?? $statusColors['pending'];
                    $pc  = $priorityColors[$task->priority] ?? $priorityColors['low'];
                    $isOverdue = $task->is_overdue;
                    $visibleAssignees = $task->assignees->take(3);
                    $extraCount = $task->assignees->count() - $visibleAssignees->count();
                ?>
                <div class="p-4 hover:bg-gray-50/50 transition-colors flex flex-col gap-3">
                    
                    
                    <div class="flex justify-between items-start gap-2">
                        <div class="min-w-0 flex-1">
                            <p class="text-[14px] font-bold text-gray-900 leading-tight"><?php echo e($task->title); ?></p>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <?php if($task->project): ?>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide"><?php echo e($task->project); ?></span>
                                <?php endif; ?>
                                <?php if($task->category): ?>
                                    <?php if($task->project): ?><span class="text-[10px] text-gray-300">·</span><?php endif; ?>
                                    <span class="text-[10px] text-gray-500 font-medium"><?php echo e($task->category); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex items-center gap-1 text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md"
                              style="background: <?php echo e($pc['bg']); ?>; color: <?php echo e($pc['text']); ?>">
                            <span class="w-1.5 h-1.5 rounded-full" style="background: <?php echo e($pc['dot'] ?? $pc['text']); ?>"></span>
                            <?php echo e(\App\Models\Hrm\HrmTask::PRIORITY_LABELS[$task->priority] ?? $task->priority); ?>

                        </span>
                    </div>

                    
                    <div class="flex items-center justify-between bg-gray-50/80 px-3 py-2.5 rounded-lg border border-gray-100">
                        <div class="flex flex-col gap-1.5">
                            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md w-fit border border-white/50"
                                  style="background: <?php echo e($sc['bg']); ?>; color: <?php echo e($sc['text']); ?>">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: <?php echo e($sc['dot'] ?? $sc['text']); ?>"></span>
                                <?php echo e(\App\Models\Hrm\HrmTask::STATUS_LABELS[$task->status] ?? $task->status); ?>

                            </span>
                            <?php if($task->due_date): ?>
                                <div class="text-[10px] <?php echo e($isOverdue && !in_array($task->status, ['completed', 'cancelled']) ? 'text-red-500 font-bold' : 'text-gray-500 font-medium'); ?> flex items-center gap-1 mt-0.5">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    <?php echo e($task->due_date->format('d M Y')); ?>

                                    <?php if($isOverdue && !in_array($task->status, ['completed', 'cancelled'])): ?>
                                        <span class="bg-red-100 text-red-600 px-1 rounded uppercase text-[8px] ml-0.5 tracking-wide">Late</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="shrink-0">
                            <?php if($task->assignees->count()): ?>
                                <div class="avatar-stack justify-end">
                                    <?php $__currentLoopData = $visibleAssignees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="avatar-item" title="<?php echo e($emp->user->name); ?>"
                                            style="background: <?php echo e(['#eff6ff','#f0fdf4','#fdf4ff','#fffbeb','#fef2f2'][$loop->index % 5]); ?>">
                                            <?php echo e(strtoupper(substr($emp->user->name, 0, 1))); ?>

                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($extraCount > 0): ?>
                                        <div class="avatar-item avatar-more">+<?php echo e($extraCount); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-[10px] text-gray-400 italic">Unassigned</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-gray-50 mt-1">
                        <?php if(has_permission('hrm_tasks.view')): ?>
                            <a href="<?php echo e(route('admin.hrm.tasks.show', $task)); ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors" title="View">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if(has_permission('hrm_tasks.change_status')): ?>
                            <button onclick="quickStatus(<?php echo e($task->id); ?>, '<?php echo e($task->status); ?>')" class="w-8 h-8 flex items-center justify-center rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 transition-colors" title="Update Status">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            </button>
                        <?php endif; ?>
                        
                        <?php if(has_permission('hrm_tasks.update')): ?>
                            <a href="<?php echo e(route('admin.hrm.tasks.edit', $task)); ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-amber-200 text-amber-500 hover:bg-amber-50 transition-colors" title="Edit">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if(has_permission('hrm_tasks.delete')): ?>
                            <button onclick="confirmDelete(<?php echo e($task->id); ?>, '<?php echo e(addslashes($task->title)); ?>')" class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="p-8 text-center bg-white">
                    <div class="flex flex-col items-center justify-center py-6 text-center">
                        <div class="w-14 h-14 bg-gray-50 border border-gray-100 rounded-2xl flex items-center justify-center mb-3">
                            <i data-lucide="clipboard" class="w-7 h-7 text-gray-300"></i>
                        </div>
                        <p class="font-bold text-gray-600 mb-1 text-sm">No tasks found</p>
                        <p class="text-xs text-gray-400 mb-4">Create your first task to get started</p>
                        <a href="<?php echo e(route('admin.hrm.tasks.create')); ?>" class="text-xs font-bold px-4 py-2 rounded-lg text-white" style="background: var(--brand-600)">
                            Create Task
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if($tasks->hasPages()): ?>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                <?php echo e($tasks->appends($filters)->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    
    <div id="statusModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm rounded-xl shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-black text-gray-800 uppercase tracking-widest text-sm">Update Status</h3>
                <button onclick="document.getElementById('statusModal').classList.add('hidden')" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 space-y-3">
                <p class="text-[12px] text-gray-500 mb-4">Select the new status for this task:</p>
                <div id="statusOptions" class="grid grid-cols-2 gap-2"></div>
            </div>
        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let _quickStatusTaskId = null;
const STATUS_TRANSITIONS = <?php echo json_encode(\App\Models\Hrm\HrmTask::STATUS_TRANSITIONS, 15, 512) ?>;
const STATUS_LABELS      = <?php echo json_encode(\App\Models\Hrm\HrmTask::STATUS_LABELS, 15, 512) ?>;
const STATUS_COLORS      = <?php echo json_encode(\App\Models\Hrm\HrmTask::STATUS_COLORS, 15, 512) ?>;

function quickStatus(id, currentStatus) {
    _quickStatusTaskId = id;
    const allowed = STATUS_TRANSITIONS[currentStatus] ?? [];
    const container = document.getElementById('statusOptions');
    container.innerHTML = '';

    if (!allowed.length) {
        container.innerHTML = '<p class="col-span-2 text-[12px] text-gray-400 text-center py-2">No transitions available for this status.</p>';
    } else {
        allowed.forEach(s => {
            const c = STATUS_COLORS[s] ?? { bg: '#f3f4f6', text: '#374151' };
            const btn = document.createElement('button');
            btn.className = 'text-[11px] font-bold px-3 py-2 rounded-lg border transition-opacity hover:opacity-80';
            btn.style.background = c.bg;
            btn.style.color = c.text;
            btn.style.borderColor = c.bg;
            btn.textContent = STATUS_LABELS[s] ?? s;
            btn.onclick = () => applyStatus(s);
            container.appendChild(btn);
        });
    }

    document.getElementById('statusModal').classList.remove('hidden');
}

async function applyStatus(newStatus) {
    if (!_quickStatusTaskId) return;
    document.getElementById('statusModal').classList.add('hidden');
    try {
        const res = await fetch(`<?php echo e(url('admin/hrm/tasks')); ?>/${_quickStatusTaskId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: newStatus }),
        });
        const data = await res.json();
        if (!res.ok) { BizAlert.toast(data.message || 'Failed to update status', 'error'); return; }
        BizAlert.toast(data.message, 'success');
        setTimeout(() => window.location.reload(), 600);
    } catch (e) {
        BizAlert.toast('Network error. Please try again.', 'error');
    }
}

function confirmDelete(id, title) {
    BizAlert.confirm('Delete Task', `Are you sure you want to delete "${title}"?`, 'Delete').then(async (result) => {
        if (!result.isConfirmed) return;
        try {
            const res = await fetch(`<?php echo e(url('admin/hrm/tasks')); ?>/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (!res.ok) { BizAlert.toast(data.message || 'Cannot delete', 'error'); return; }
            BizAlert.toast(data.message, 'success');
            setTimeout(() => window.location.reload(), 600);
        } catch (e) {
            BizAlert.toast('Network error. Please try again.', 'error');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/hrm/tasks/index.blade.php ENDPATH**/ ?>