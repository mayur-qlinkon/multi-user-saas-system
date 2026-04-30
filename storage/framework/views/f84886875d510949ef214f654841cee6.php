<?php $__env->startSection('title', 'Leave Balances'); ?>

<?php $__env->startSection('header-title'); ?>
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Leave Balances</h1>
        
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] { display: none !important; }

    .filter-input {
        border: 1.5px solid #e5e7eb;
        border-radius: 9px;
        padding: 7px 10px;
        font-size: 12px;
        color: #374151;
        outline: none;
        background: #fff;
        transition: border-color 150ms;
    }
    .filter-input:focus { border-color: var(--brand-500, #6366f1); }

    .balance-table th {
        background: #f8fafc;
        padding: 10px 14px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        border-bottom: 1.5px solid #f1f5f9;
        white-space: nowrap;
    }
    .balance-table td {
        padding: 10px 14px;
        font-size: 13px;
        color: #374151;
        border-bottom: 1px solid #f8fafc;
        vertical-align: middle;
        white-space: nowrap;
    }
    .balance-table tr:hover td { background: #fafbff; }

    .avail-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }
    .avail-positive { background: #ecfdf5; color: #065f46; }
    .avail-zero     { background: #fef2f2; color: #991b1b; }
    .avail-low      { background: #fffbeb; color: #92400e; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div
    x-data="leaveBalancePage()"
    x-init="init()"
    class="space-y-5"
>

    
    <?php if(session('success')): ?>
        <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 text-sm font-medium px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        
        <form method="GET" action="<?php echo e(route('admin.hrm.leave-balances.index')); ?>" class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <select name="year" class="filter-input pr-8" onchange="this.form.submit()">
                    <?php $__currentLoopData = range(date('Y') + 1, date('Y') - 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <select name="employee_id" class="filter-input pr-8" onchange="this.form.submit()">
                <option value="">All Employees</option>
                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($emp->id); ?>" <?php echo e($employeeId == $emp->id ? 'selected' : ''); ?>>
                        <?php echo e($emp->user->name ?? ""); ?> (<?php echo e($emp->employee_code ?? ""); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="leave_type_id" class="filter-input pr-8" onchange="this.form.submit()">
                <option value="">All Leave Types</option>
                <?php $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($lt->id); ?>" <?php echo e($leaveTypeId == $lt->id ? 'selected' : ''); ?>><?php echo e($lt->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php if($employeeId || $leaveTypeId): ?>
                <a href="<?php echo e(route('admin.hrm.leave-balances.index', ['year' => $year])); ?>" class="text-xs text-gray-400 hover:text-gray-600 underline">Clear</a>
            <?php endif; ?>
        </form>

        
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0 w-full sm:w-auto mt-3 sm:mt-0">
            <button
                @click="confirmCarryForward()"
                class="inline-flex justify-center items-center gap-1.5 bg-white border border-gray-200 hover:border-blue-400 text-gray-600 hover:text-blue-600 text-xs font-semibold px-3 py-2 rounded-lg transition-colors w-full sm:w-auto"
                title="Carry forward unused days from <?php echo e($year - 1); ?> to <?php echo e($year); ?> (carry-forward leave types only)"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline stroke-linecap="round" stroke-linejoin="round" points="1 4 1 10 7 10"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.51 15a9 9 0 1 0 .49-3.62"/></svg>
                Carry Forward from <?php echo e($year - 1); ?>

            </button>
            <button
                @click="initializeBalances()"
                class="inline-flex justify-center items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-colors w-full sm:w-auto"
                title="Create balance records for all active employees × all active leave types for <?php echo e($year); ?>"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Initialize <?php echo e($year); ?> Balances
            </button>
        </div>
    </div>

    
    <?php if($balances->isEmpty()): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
                <p class="text-sm font-semibold text-amber-800">No balances found for <?php echo e($year); ?></p>
                <p class="text-xs text-amber-600 mt-0.5">
                    Click <strong>"Initialize <?php echo e($year); ?> Balances"</strong> to automatically create leave balance records
                    for all active employees using the default days configured in each leave type.
                </p>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if($balances->isNotEmpty()): ?>
        <?php
            $totalEmployees = $balances->pluck('employee_id')->unique()->count();
            $totalAllocated = $balances->sum('allocated');
            $totalUsed      = $balances->sum('used');
            $totalAvailable = $balances->sum(fn($b) => $b->available);
        ?>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3">
                <p class="text-xs text-gray-400 font-medium">Employees</p>
                <p class="text-xl font-bold text-gray-800 mt-0.5"><?php echo e($totalEmployees); ?></p>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3">
                <p class="text-xs text-gray-400 font-medium">Total Allocated</p>
                <p class="text-xl font-bold text-blue-600 mt-0.5"><?php echo e(number_format($totalAllocated, 1)); ?> <span class="text-xs font-normal text-gray-400">days</span></p>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3">
                <p class="text-xs text-gray-400 font-medium">Total Used</p>
                <p class="text-xl font-bold text-red-500 mt-0.5"><?php echo e(number_format($totalUsed, 1)); ?> <span class="text-xs font-normal text-gray-400">days</span></p>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl px-4 py-3">
                <p class="text-xs text-gray-400 font-medium">Total Available</p>
                <p class="text-xl font-bold text-green-600 mt-0.5"><?php echo e(number_format($totalAvailable, 1)); ?> <span class="text-xs font-normal text-gray-400">days</span></p>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if($balances->isNotEmpty()): ?>
    
    
    <div class="hidden md:block bg-white border border-gray-100 rounded-2xl shadow-sm overflow-x-auto w-full pb-1">
        <table class="w-full min-w-[800px] balance-table">
            <thead>
                <tr>
                    <th class="text-left">Employee</th>
                    <th class="text-left">Leave Type</th>
                    <th class="text-center">Allocated</th>
                    <th class="text-center hidden sm:table-cell">Carry Fwd</th>
                    <th class="text-center hidden sm:table-cell">Adjustment</th>
                    <th class="text-center hidden sm:table-cell">Used</th>
                    <th class="text-center">Available</th>
                    <th class="text-right">Edit</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $balances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $balance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $available = $balance->available;
                        $chipClass = $available <= 0 ? 'avail-zero' : ($available <= 3 ? 'avail-low' : 'avail-positive');
                    ?>
                    <tr x-data="{ rowId: <?php echo e($balance->id); ?> }">
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-brand-50 text-brand-600 font-bold text-xs flex items-center justify-center shrink-0">
                                    <?php echo e(strtoupper(substr($balance->employee->user->name ?? '?', 0, 1))); ?>

                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-xs leading-tight"><?php echo e($balance->employee->user->name ?? '—'); ?></p>
                                    <p class="text-[10px] text-gray-400"><?php echo e($balance->employee->employee_code??""); ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-xs font-semibold text-gray-700"><?php echo e($balance->leaveType->name); ?></span>
                            <span class="ml-1 text-[10px] text-gray-400 font-mono"><?php echo e($balance->leaveType->code); ?></span>
                        </td>
                        <td class="text-center font-semibold text-gray-700"><?php echo e(number_format($balance->allocated, 1)); ?></td>
                        <td class="text-center text-gray-500 hidden sm:table-cell"><?php echo e(number_format($balance->carried_forward, 1)); ?></td>
                        <td class="text-center hidden sm:table-cell">
                            <?php if($balance->adjustment != 0): ?>
                                <span class="font-semibold <?php echo e($balance->adjustment > 0 ? 'text-green-600' : 'text-red-500'); ?>">
                                    <?php echo e($balance->adjustment > 0 ? '+' : ''); ?><?php echo e(number_format($balance->adjustment, 1)); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-gray-400">0.0</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center font-semibold text-red-500 hidden sm:table-cell"><?php echo e(number_format($balance->used, 1)); ?></td>
                        <td class="text-center">
                            <span class="avail-chip <?php echo e($chipClass); ?>"><?php echo e(number_format($available, 1)); ?></span>
                        </td>
                        <td class="text-right">
                            <button
                                @click="openEdit(<?php echo e($balance->id); ?>, <?php echo e($balance->allocated); ?>, <?php echo e($balance->adjustment); ?>, '<?php echo e(addslashes($balance->employee->user->name ?? '')); ?>', '<?php echo e(addslashes($balance->leaveType->name)); ?>')"
                                class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                title="Edit balance"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    
    <div class="md:hidden divide-y divide-gray-50 border border-gray-100 rounded-2xl bg-white mt-4 shadow-sm">
        <?php $__currentLoopData = $balances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $balance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $available = $balance->available;
                $chipClass = $available <= 0 ? 'avail-zero' : ($available <= 3 ? 'avail-low' : 'avail-positive');
            ?>
            <div class="p-4 flex flex-col gap-3 hover:bg-gray-50/50 transition-colors">
                
                
                <div class="flex justify-between items-start gap-2">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-600 font-bold text-sm flex items-center justify-center shrink-0">
                            <?php echo e(strtoupper(substr($balance->employee->user->name ?? '?', 0, 1))); ?>

                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-[14px] text-gray-900 truncate"><?php echo e($balance->employee->user->name ?? '—'); ?></p>
                            <p class="text-[11px] text-gray-500 mt-0.5 truncate"><?php echo e($balance->employee->employee_code ?? ""); ?></p>
                        </div>
                    </div>
                    <div class="shrink-0 flex flex-col items-end">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Available</span>
                        <span class="avail-chip <?php echo e($chipClass); ?> !text-[12px] !px-2 !py-0.5"><?php echo e(number_format($available, 1)); ?></span>
                    </div>
                </div>

                
                <div class="flex flex-col gap-2 bg-gray-50/80 px-3 py-2.5 rounded-lg border border-gray-100">
                    <div class="flex justify-between items-center border-b border-gray-100/50 pb-1.5 mb-0.5">
                        <span class="text-[12px] font-bold text-gray-800"><?php echo e($balance->leaveType->name); ?> <span class="text-gray-400 text-[10px] font-mono ml-1"><?php echo e($balance->leaveType->code); ?></span></span>
                    </div>
                    
                    <div class="grid grid-cols-4 gap-2 text-center pt-1">
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase">Alloc</p>
                            <p class="text-[11px] font-semibold text-gray-700"><?php echo e(number_format($balance->allocated, 1)); ?></p>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase">C.Fwd</p>
                            <p class="text-[11px] font-semibold text-gray-500"><?php echo e(number_format($balance->carried_forward, 1)); ?></p>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase">Adj</p>
                            <?php if($balance->adjustment != 0): ?>
                                <p class="text-[11px] font-semibold <?php echo e($balance->adjustment > 0 ? 'text-green-600' : 'text-red-500'); ?>">
                                    <?php echo e($balance->adjustment > 0 ? '+' : ''); ?><?php echo e(number_format($balance->adjustment, 1)); ?>

                                </p>
                            <?php else: ?>
                                <p class="text-[11px] font-semibold text-gray-400">0.0</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase">Used</p>
                            <p class="text-[11px] font-semibold text-red-500"><?php echo e(number_format($balance->used, 1)); ?></p>
                        </div>
                    </div>
                </div>

                
                <div class="pt-1">
                    <button @click="openEdit(<?php echo e($balance->id); ?>, <?php echo e($balance->allocated); ?>, <?php echo e($balance->adjustment); ?>, '<?php echo e(addslashes($balance->employee->user->name ?? '')); ?>', '<?php echo e(addslashes($balance->leaveType->name)); ?>')"
                            class="flex items-center justify-center gap-1.5 w-full bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg px-3 py-2.5 transition-colors text-[11px] font-bold uppercase tracking-wider"
                            title="Edit balance">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Balance
                    </button>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <div
        x-show="editModal.open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @keydown.escape.window="editModal.open = false"
    >
        <div class="absolute inset-0 bg-black/40" @click="editModal.open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6" @click.stop>

            <h3 class="text-sm font-bold text-gray-800">Edit Leave Balance</h3>
            <p class="text-xs text-gray-400 mt-0.5 mb-4">
                <span x-text="editModal.employeeName"></span> — <span x-text="editModal.leaveTypeName"></span>
            </p>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Allocated Days</label>
                    <input
                        x-model.number="editModal.allocated"
                        type="number" step="0.5" min="0" max="365"
                        class="w-full filter-input"
                        placeholder="e.g. 20"
                    >
                    <p class="text-[10px] text-gray-400 mt-1">Total days granted for the year (per leave type default)</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Adjustment</label>
                    <input
                        x-model.number="editModal.adjustment"
                        type="number" step="0.5" min="-365" max="365"
                        class="w-full filter-input"
                        placeholder="e.g. +2 or -1"
                    >
                    <p class="text-[10px] text-gray-400 mt-1">Use positive for bonus days, negative for penalty/deduction</p>
                </div>
                <div class="bg-gray-50 rounded-xl px-4 py-3 text-xs">
                    <div class="flex justify-between text-gray-500">
                        <span>Allocated</span><span x-text="editModal.allocated + ' days'"></span>
                    </div>
                    <div class="flex justify-between text-gray-500 mt-1">
                        <span>Adjustment</span><span x-text="(editModal.adjustment >= 0 ? '+' : '') + editModal.adjustment + ' days'"></span>
                    </div>
                    <div class="flex justify-between font-bold text-gray-800 border-t border-gray-200 mt-2 pt-2">
                        <span>New Available (approx)</span>
                        <span x-text="(parseFloat(editModal.allocated || 0) + parseFloat(editModal.adjustment || 0)).toFixed(1) + ' days'"></span>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">* Actual = allocated + carry_forward + adjustment − used</p>
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <button @click="editModal.open = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold py-2 rounded-xl transition-colors">
                    Cancel
                </button>
                <button
                    @click="saveBalance()"
                    :disabled="editModal.saving"
                    class="flex-1 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-2 rounded-xl transition-colors disabled:opacity-60"
                >
                    <span x-show="!editModal.saving">Save Changes</span>
                    <span x-show="editModal.saving">Saving…</span>
                </button>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function leaveBalancePage() {
    return {
        editModal: {
            open: false,
            saving: false,
            balanceId: null,
            allocated: 0,
            adjustment: 0,
            employeeName: '',
            leaveTypeName: '',
        },

        init() {},

        openEdit(id, allocated, adjustment, employeeName, leaveTypeName) {
            this.editModal.balanceId    = id;
            this.editModal.allocated    = allocated;
            this.editModal.adjustment   = adjustment;
            this.editModal.employeeName = employeeName;
            this.editModal.leaveTypeName = leaveTypeName;
            this.editModal.open         = true;
        },

        saveBalance() {
            this.editModal.saving = true;

            fetch(`/admin/hrm/leave-balances/${this.editModal.balanceId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    allocated:  this.editModal.allocated,
                    adjustment: this.editModal.adjustment,
                }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Updated!', text: data.message, timer: 1800, showConfirmButton: false });
                    this.editModal.open = false;
                    setTimeout(() => location.reload(), 1900);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Update failed.' });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.' }))
            .finally(() => { this.editModal.saving = false; });
        },

        initializeBalances() {
            const year = <?php echo e($year); ?>;

            Swal.fire({
                title: `Initialize ${year} Balances?`,
                html: `<p style="font-size:13px;color:#4b5563;margin-top:6px;">
                    This will create leave balance records for all <strong>active employees</strong>
                    × all <strong>active leave types</strong> using each type's default days.<br><br>
                    <em style="color:#6b7280;">Employees who already have balances for ${year} will be skipped.</em>
                </p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6b7280',
                confirmButtonText: `Yes, Initialize ${year}`,
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (!result.isConfirmed) return;

                Swal.fire({ title: 'Initializing…', text: 'Creating balance records…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch('<?php echo e(route("admin.hrm.leave-balances.initialize")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ year }),
                })
                .then(r => r.json())
                .then(data => {
                    Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Done!' : 'Error', text: data.message, timer: 2500, showConfirmButton: false });
                    if (data.success) setTimeout(() => location.reload(), 2600);
                })
                .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.' }));
            });
        },

        confirmCarryForward() {
            const toYear   = <?php echo e($year); ?>;
            const fromYear = toYear - 1;

            Swal.fire({
                title: `Carry Forward from ${fromYear}?`,
                html: `<p style="font-size:13px;color:#4b5563;margin-top:6px;">
                    Unused leave days from <strong>${fromYear}</strong> will be added as carry-forward
                    to <strong>${toYear}</strong> balances.<br><br>
                    Only leave types with <strong>"Carry Forward"</strong> enabled are included.<br>
                    <em style="color:#6b7280;">Employees who already have ${toYear} records will have their carry_forward amount updated.</em>
                </p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: `Yes, Carry Forward`,
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (!result.isConfirmed) return;

                Swal.fire({ title: 'Processing…', text: 'Calculating carry-forward…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch('<?php echo e(route("admin.hrm.leave-balances.carry-forward")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ to_year: toYear }),
                })
                .then(r => r.json())
                .then(data => {
                    Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Done!' : 'Error', text: data.message, timer: 2500, showConfirmButton: false });
                    if (data.success) setTimeout(() => location.reload(), 2600);
                })
                .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.' }));
            });
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/hrm/leave-balances/index.blade.php ENDPATH**/ ?>