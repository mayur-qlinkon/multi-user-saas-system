<?php $__env->startSection('title', $employee->full_name . ' — Employee Profile'); ?>

<?php $__env->startSection('header-title'); ?>
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('admin.hrm.employees.index')); ?>"
            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        </a>
        <div>
            <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Employee Profile</h1>
            <p class="text-xs text-gray-400 font-medium mt-0.5"><?php echo e($employee->employee_code); ?></p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .detail-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        overflow: hidden;
    }

    .card-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 10px;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        padding: 14px 18px;
        border-bottom: 1px solid #f8fafc;
    }

    .card-header svg { width: 14px; height: 14px; stroke: #94a3b8; }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 9px 18px;
        border-bottom: 1px solid #f8fafc;
        font-size: 13px;
    }

    .info-row:last-child { border-bottom: none; }
    .info-key { color: #94a3b8; font-weight: 500; flex-shrink: 0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.03em; }
    .info-val { color: #1e293b; font-weight: 600; text-align: right; word-break: break-word; max-width: 65%; font-size: 13px; }
    .info-val.muted { color: #cbd5e1; font-weight: 500; }

    .profile-avatar {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 800;
        color: #fff;
        flex-shrink: 0;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        border: 1.5px solid #e5e7eb;
        color: #374151;
        background: #fff;
        cursor: pointer;
        transition: all 120ms;
        text-decoration: none;
    }

    .action-btn:hover { border-color: #d1d5db; background: #f9fafb; }
    .action-btn.danger { color: #dc2626; border-color: #fecaca; }
    .action-btn.danger:hover { background: #fef2f2; border-color: #f87171; }

    .subordinate-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 18px;
        border-bottom: 1px solid #f8fafc;
        transition: background 100ms;
    }

    .subordinate-item:hover { background: #fafbfc; }
    .subordinate-item:last-child { border-bottom: none; }

    .sub-avatar {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        color: #fff;
        flex-shrink: 0;
    }
</style>
<?php $__env->stopPush(); ?>

<?php
    $statusColors = \App\Models\Hrm\Employee::STATUS_COLORS[$employee->status] ?? ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#9ca3af'];
    $statusLabel  = \App\Models\Hrm\Employee::STATUS_LABELS[$employee->status] ?? ucfirst($employee->status);
    $typeLabel    = \App\Models\Hrm\Employee::TYPE_LABELS[$employee->employment_type] ?? ucfirst(str_replace('_', ' ', $employee->employment_type ?? ''));

    $name     = $employee->user?->name ?? 'Unknown';
    $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');
    $avatarBg = ['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981','#3b82f6','#ef4444'][crc32($name) % 7];

    $maskedAadhaar = $employee->aadhaar_number
        ? 'XXXX-XXXX-' . substr($employee->aadhaar_number, -4)
        : null;
?>

<?php $__env->startSection('content'); ?>
<div class="w-full space-y-5">

    
    <div class="detail-card">
        <div class="p-5">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                
                <?php if($employee->photo): ?>
                    <img src="<?php echo e(Storage::url($employee->photo)); ?>" alt="<?php echo e($name); ?>" class="profile-avatar object-cover shadow-sm border border-gray-100">
                <?php else: ?>
                    <div class="profile-avatar" style="background: <?php echo e($avatarBg); ?>">
                        <?php echo e($initials); ?>

                    </div>
                <?php endif; ?>

                
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl font-bold text-gray-800 leading-tight"><?php echo e($name); ?></h2>
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold tracking-wide"
                              style="background: #f1f5f9; color: #64748b;">
                            <?php echo e($employee->employee_code); ?>

                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                              style="background: <?php echo e($statusColors['bg']); ?>; color: <?php echo e($statusColors['text']); ?>;">
                            <span class="w-1.5 h-1.5 rounded-full" style="background: <?php echo e($statusColors['dot']); ?>"></span>
                            <?php echo e($statusLabel); ?>

                        </span>
                    </div>
                    <p class="text-[13px] text-gray-400 font-medium mt-1">
                        <?php echo e($employee->designation?->name ?? '—'); ?>

                        <?php if($employee->department?->name): ?>
                            <span class="text-gray-300 mx-1">/</span> <?php echo e($employee->department->name); ?>

                        <?php endif; ?>
                    </p>
                </div>

                
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="<?php echo e(route('admin.hrm.employees.edit', $employee)); ?>" class="action-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        Edit
                    </a>
                    <form action="<?php echo e(route('admin.hrm.employees.destroy', $employee)); ?>" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this employee?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="action-btn danger">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        
        <div class="space-y-5">

            
            <div class="detail-card">
                <div class="card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    Employment Details
                </div>
                <div>
                    <div class="info-row">
                        <span class="info-key">Joining Date</span>
                        <span class="info-val <?php echo e(!$employee->date_of_joining ? 'muted' : ''); ?>">
                            <?php echo e($employee->date_of_joining?->format('d M Y') ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Employment Type</span>
                        <span class="info-val <?php echo e(!$employee->employment_type ? 'muted' : ''); ?>">
                            <?php echo e($typeLabel ?: '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Store</span>
                        <span class="info-val <?php echo e(!$employee->store ? 'muted' : ''); ?>">
                            <?php echo e($employee->store?->name ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Reporting Manager</span>
                        <span class="info-val <?php echo e(!$employee->reportingManager ? 'muted' : ''); ?>">
                            <?php echo e($employee->reportingManager?->user?->name ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Probation End</span>
                        <span class="info-val <?php echo e(!$employee->probation_end_date ? 'muted' : ''); ?>">
                            <?php echo e($employee->probation_end_date?->format('d M Y') ?? '—'); ?>

                        </span>
                    </div>
                </div>
            </div>

            
            <div class="detail-card">
                <div class="card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Personal Details
                </div>
                <div>
                    <div class="info-row">
                        <span class="info-key">Date of Birth</span>
                        <span class="info-val <?php echo e(!$employee->date_of_birth ? 'muted' : ''); ?>">
                            <?php echo e($employee->date_of_birth?->format('d M Y') ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Gender</span>
                        <span class="info-val <?php echo e(!$employee->gender ? 'muted' : ''); ?>">
                            <?php echo e($employee->gender ? ucfirst($employee->gender) : '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Marital Status</span>
                        <span class="info-val <?php echo e(!$employee->marital_status ? 'muted' : ''); ?>">
                            <?php echo e($employee->marital_status ? ucfirst($employee->marital_status) : '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Blood Group</span>
                        <span class="info-val <?php echo e(!$employee->blood_group ? 'muted' : ''); ?>">
                            <?php echo e($employee->blood_group ?? '—'); ?>

                        </span>
                    </div>
                </div>
            </div>

            
            <div class="detail-card">
                <div class="card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    Address
                </div>
                <div>
                    <div class="info-row">
                        <span class="info-key">Current Address</span>
                        <span class="info-val <?php echo e(!$employee->current_address ? 'muted' : ''); ?>">
                            <?php echo e($employee->current_address ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Permanent Address</span>
                        <span class="info-val <?php echo e(!$employee->permanent_address ? 'muted' : ''); ?>">
                            <?php echo e($employee->permanent_address ?? '—'); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="space-y-5">

            
            <div class="detail-card" x-data="salaryStructure()">
                <div class="card-header" style="justify-content: space-between;">
                    <span class="flex items-center gap-2">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        Salary Structure
                    </span>
                    <button @click="showForm = !showForm"
                        class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-md text-white transition-opacity hover:opacity-80"
                        style="background: var(--brand-600)">
                        <svg x-show="!showForm" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <svg x-show="showForm" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span x-text="showForm ? 'Cancel' : 'Add Component'"></span>
                    </button>
                </div>

                
                <div class="info-row">
                    <span class="info-key">Basic Salary</span>
                    <span class="info-val <?php echo e(!$employee->basic_salary ? 'muted' : ''); ?>">
                        <?php echo e($employee->basic_salary ? '₹' . number_format($employee->basic_salary, 2) : '—'); ?>

                        <?php if($employee->salary_type): ?>
                            <span class="text-[10px] font-normal text-gray-400 ml-1">(<?php echo e(ucfirst($employee->salary_type)); ?>)</span>
                        <?php endif; ?>
                    </span>
                </div>

                
                <div x-show="showForm" x-transition class="px-4 py-3 border-b border-gray-50 bg-gray-50/60">
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Component <span class="text-red-400">*</span></label>
                            <select x-model="form.salary_component_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px] focus:outline-none focus:border-blue-400">
                                <option value="">Select component...</option>
                                <?php $__currentLoopData = $salaryComponents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($comp->id); ?>">[<?php echo e(strtoupper($comp->type)); ?>] <?php echo e($comp->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Calculation Type <span class="text-red-400">*</span></label>
                            <select x-model="form.calculation_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px] focus:outline-none focus:border-blue-400">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">% of Basic</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div x-show="form.calculation_type === 'fixed'">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Amount (₹) <span class="text-red-400">*</span></label>
                            <input type="number" x-model="form.amount" min="0" step="0.01" placeholder="e.g. 10000"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px] focus:outline-none focus:border-blue-400">
                        </div>
                        <div x-show="form.calculation_type === 'percentage'">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Percentage (%) <span class="text-red-400">*</span></label>
                            <input type="number" x-model="form.amount" min="0" max="100" step="0.01" placeholder="e.g. 40"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px] focus:outline-none focus:border-blue-400">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Effective From</label>
                            <input type="date" x-model="form.effective_from"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px] focus:outline-none focus:border-blue-400">
                        </div>
                    </div>
                    <p x-show="formError" x-text="formError" class="text-[11px] text-red-500 mb-2"></p>
                    <button @click="addComponent()" :disabled="saving"
                        class="inline-flex items-center gap-1.5 text-[12px] font-bold px-4 py-2 rounded-lg text-white disabled:opacity-50 hover:opacity-90 transition"
                        style="background: var(--brand-600)">
                        <span x-show="!saving">Add to Structure</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                </div>

                
                <div>
                    <template x-if="structures.length === 0">
                        <div class="flex flex-col items-center py-6 text-center">
                            <svg class="w-8 h-8 text-gray-200 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            <p class="text-[12px] text-gray-400 font-medium">No salary components assigned</p>
                            <p class="text-[11px] text-gray-300">Click "Add Component" to set up this employee's salary</p>
                        </div>
                    </template>
                    <template x-for="s in structures" :key="s.id">
                        <div class="info-row">
                            <div class="flex flex-col min-w-0">
                                <span class="text-[13px] font-semibold text-gray-800" x-text="s.salary_component?.name"></span>
                                <span class="text-[10px] font-bold uppercase tracking-wider mt-0.5"
                                    :style="s.salary_component?.type === 'earning' ? 'color:#16a34a' : 'color:#dc2626'"
                                    x-text="s.salary_component?.type"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <span class="text-[13px] font-bold text-gray-800"
                                        x-text="s.calculation_type === 'fixed' ? '₹' + Number(s.amount).toLocaleString('en-IN') : s.amount + '% of Basic'">
                                    </span>
                                    <p x-show="s.effective_from" class="text-[10px] text-gray-400"
                                        x-text="'From ' + (s.effective_from ? new Date(s.effective_from).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : '')">
                                    </p>
                                </div>
                                <button @click="removeComponent(s.id)"
                                    class="w-6 h-6 flex items-center justify-center rounded-md text-red-400 hover:bg-red-50 hover:text-red-600 transition flex-shrink-0">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                
                <template x-if="structures.length > 0">
                    <div class="px-4 py-3 bg-gray-50/60 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Estimated Net Salary</span>
                        <span class="text-[14px] font-black text-gray-800" x-text="'₹' + estimatedNet().toLocaleString('en-IN')"></span>
                    </div>
                </template>
            </div>

            
            <div class="detail-card">
                <div class="card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    Bank Details
                </div>
                <div>
                    <div class="info-row">
                        <span class="info-key">Bank Name</span>
                        <span class="info-val <?php echo e(!$employee->bank_name ? 'muted' : ''); ?>">
                            <?php echo e($employee->bank_name ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Account Number</span>
                        <span class="info-val <?php echo e(!$employee->bank_account_number ? 'muted' : ''); ?>">
                            <?php echo e($employee->bank_account_number ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">IFSC Code</span>
                        <span class="info-val <?php echo e(!$employee->bank_ifsc ? 'muted' : ''); ?>">
                            <?php echo e($employee->bank_ifsc ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Branch</span>
                        <span class="info-val <?php echo e(!$employee->bank_branch ? 'muted' : ''); ?>">
                            <?php echo e($employee->bank_branch ?? '—'); ?>

                        </span>
                    </div>
                </div>
            </div>

            
            <div class="detail-card">
                <div class="card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Statutory Details
                </div>
                <div>
                    <div class="info-row">
                        <span class="info-key">PAN</span>
                        <span class="info-val <?php echo e(!$employee->pan_number ? 'muted' : ''); ?>">
                            <?php echo e($employee->pan_number ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Aadhaar</span>
                        <span class="info-val <?php echo e(!$employee->aadhaar_number ? 'muted' : ''); ?>">
                            <?php echo e($maskedAadhaar ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">UAN</span>
                        <span class="info-val <?php echo e(!$employee->uan_number ? 'muted' : ''); ?>">
                            <?php echo e($employee->uan_number ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">ESI Number</span>
                        <span class="info-val <?php echo e(!$employee->esi_number ? 'muted' : ''); ?>">
                            <?php echo e($employee->esi_number ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">PF Number</span>
                        <span class="info-val <?php echo e(!$employee->pf_number ? 'muted' : ''); ?>">
                            <?php echo e($employee->pf_number ?? '—'); ?>

                        </span>
                    </div>
                </div>
            </div>

            
            <div class="detail-card">
                <div class="card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Uploaded Documents
                </div>
                <div>
                    <div class="info-row">
                        <span class="info-key">ID Proof</span>
                        <span class="info-val <?php echo e(!$employee->id_proof ? 'muted' : ''); ?>">
                            <?php if($employee->id_proof): ?>
                                <a href="<?php echo e(Storage::url($employee->id_proof)); ?>" target="_blank" class="text-blue-600 hover:underline flex items-center justify-end gap-1.5">
                                    View File <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Address Proof</span>
                        <span class="info-val <?php echo e(!$employee->address_proof ? 'muted' : ''); ?>">
                            <?php if($employee->address_proof): ?>
                                <a href="<?php echo e(Storage::url($employee->address_proof)); ?>" target="_blank" class="text-blue-600 hover:underline flex items-center justify-end gap-1.5">
                                    View File <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            
            <div class="detail-card">
                <div class="card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Emergency Contact
                </div>
                <div>
                    <div class="info-row">
                        <span class="info-key">Name</span>
                        <span class="info-val <?php echo e(!$employee->emergency_contact_name ? 'muted' : ''); ?>">
                            <?php echo e($employee->emergency_contact_name ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Phone</span>
                        <span class="info-val <?php echo e(!$employee->emergency_contact_phone ? 'muted' : ''); ?>">
                            <?php echo e($employee->emergency_contact_phone ?? '—'); ?>

                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Relation</span>
                        <span class="info-val <?php echo e(!$employee->emergency_contact_relation ? 'muted' : ''); ?>">
                            <?php echo e($employee->emergency_contact_relation ? ucfirst($employee->emergency_contact_relation) : '—'); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <?php if($employee->subordinates->count()): ?>
    <div class="detail-card">
        <div class="card-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Direct Reports (<?php echo e($employee->subordinates->count()); ?>)
        </div>
        <div>
            <?php $__currentLoopData = $employee->subordinates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $subName     = $sub->user?->name ?? 'Unknown';
                    $subInitials = collect(explode(' ', $subName))->map(fn($w) => strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');
                    $subBg       = ['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981','#3b82f6','#ef4444'][crc32($subName) % 7];
                    $subStatus   = \App\Models\Hrm\Employee::STATUS_COLORS[$sub->status] ?? ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#9ca3af'];
                ?>
                <a href="<?php echo e(route('admin.hrm.employees.show', $sub)); ?>" class="subordinate-item" style="text-decoration: none;">
                    <div class="sub-avatar" style="background: <?php echo e($subBg); ?>"><?php echo e($subInitials); ?></div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[13px] font-semibold text-gray-800 truncate"><?php echo e($subName); ?></div>
                        <div class="text-[11px] text-gray-400 font-medium"><?php echo e($sub->designation?->name ?? '—'); ?></div>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                          style="background: <?php echo e($subStatus['bg']); ?>; color: <?php echo e($subStatus['text']); ?>;">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: <?php echo e($subStatus['dot']); ?>"></span>
                        <?php echo e(\App\Models\Hrm\Employee::STATUS_LABELS[$sub->status] ?? ucfirst($sub->status)); ?>

                    </span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php
    $structuresJson = $salaryStructures->map(function ($s) {
        return [
            'id'                  => $s->id,
            'salary_component_id' => $s->salary_component_id,
            'calculation_type'    => $s->calculation_type,
            'amount'              => $s->amount,
            'effective_from'      => $s->effective_from?->toDateString(),
            'is_active'           => $s->is_active,
            'salary_component'    => $s->salaryComponent ? [
                'id'   => $s->salaryComponent->id,
                'name' => $s->salaryComponent->name,
                'type' => $s->salaryComponent->type,
                'code' => $s->salaryComponent->code,
            ] : null,
        ];
    });
?>
<script>
window.salaryStructure = function() {
    return {
        structures: <?php echo json_encode($structuresJson, 15, 512) ?>,
        basicSalary: <?php echo e((float) ($employee->basic_salary ?? 0)); ?>,
        showForm: false,
        saving: false,
        formError: '',
        form: { salary_component_id: '', calculation_type: 'fixed', amount: '', effective_from: '' },

        addComponent() {
            this.formError = '';
            if (!this.form.salary_component_id) { this.formError = 'Please select a component.'; return; }
            if (!this.form.amount || this.form.amount <= 0) { this.formError = 'Enter a valid amount.'; return; }

            this.saving = true;
            fetch('<?php echo e(route('admin.hrm.employees.salary-structures.store', $employee)); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(this.form),
            })
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
            .then(({ ok, data }) => {
                if (!ok) { this.formError = data.message || 'Failed to add component.'; return; }
                this.structures = this.structures.filter(s => s.salary_component_id != data.data.salary_component_id);
                this.structures.push(data.data);
                this.form = { salary_component_id: '', calculation_type: 'fixed', amount: '', effective_from: '' };
                this.showForm = false;
                BizAlert.toast(data.message, 'success');
            })
            .catch(() => { this.formError = 'Network error. Please try again.'; })
            .finally(() => { this.saving = false; });
        },

        removeComponent(id) {
            BizAlert.confirm('Remove Component', 'Remove this salary component from the structure?', 'Remove').then(r => {
                if (!r.isConfirmed) return;
                fetch('<?php echo e(url('admin/hrm/employees/' . $employee->id . '/salary-structures')); ?>/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(r => r.json())
                .then(data => {
                    this.structures = this.structures.filter(s => s.id !== id);
                    BizAlert.toast(data.message, 'success');
                })
                .catch(() => BizAlert.toast('Network error.', 'error'));
            });
        },

        estimatedNet() {
            let earnings = 0, deductions = 0;
            this.structures.forEach(s => {
                if (!s.is_active) return;
                const amt = s.calculation_type === 'fixed'
                    ? parseFloat(s.amount)
                    : (parseFloat(s.amount) / 100) * this.basicSalary;
                if (s.salary_component?.type === 'earning') earnings += amt;
                else deductions += amt;
            });
            return Math.round(earnings - deductions);
        },
    };
};
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/hrm/employees/show.blade.php ENDPATH**/ ?>