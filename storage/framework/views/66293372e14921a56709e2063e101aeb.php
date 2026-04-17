<?php $__env->startSection('title', 'Salary Slips'); ?>

<?php $__env->startSection('header-title'); ?>
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Salary Slips</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">Manage employee payroll and salary disbursements</p>
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

    .field-error {
        font-size: 11px;
        color: #dc2626;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .table-row { border-bottom: 1px solid #f8fafc; transition: background 100ms; }
    .table-row td { white-space: nowrap; vertical-align: middle; }
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
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php
    $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
    $currentYear = now()->year;
?>

<div class="pb-10" x-data="salarySlipsPage()">

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Slips</p>
            <p class="text-2xl font-black text-gray-900"><?php echo e($slips->total()); ?></p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Draft</p>
            <p class="text-2xl font-black text-gray-500"><?php echo e($stats['draft'] ?? 0); ?></p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Approved</p>
            <p class="text-2xl font-black text-amber-600"><?php echo e($stats['approved'] ?? 0); ?></p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Paid</p>
            <p class="text-2xl font-black text-green-600"><?php echo e($stats['paid'] ?? 0); ?></p>
        </div>
    </div>

    
    <form method="GET" action="<?php echo e(route('admin.hrm.salary-slips.index')); ?>"
        class="bg-white border border-gray-100 rounded-2xl px-4 py-3 mb-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-wrap">

            <div class="w-full sm:w-[130px]">
                <select name="month" class="field-input !py-2 !text-[13px]">
                    <option value="">All Months</option>
                    <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($num); ?>" <?php echo e(request('month') == $num ? 'selected' : ''); ?>><?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="w-full sm:w-[110px]">
                <select name="year" class="field-input !py-2 !text-[13px]">
                    <option value="">All Years</option>
                    <?php for($y = $currentYear + 1; $y >= $currentYear - 1; $y--): ?>
                        <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="w-full sm:w-[140px]">
                <select name="status" class="field-input !py-2 !text-[13px]">
                    <option value="">All Statuses</option>
                    <?php $__currentLoopData = \App\Models\Hrm\SalarySlip::STATUS_LABELS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php echo e(request('status') == $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="w-full sm:flex-1 sm:min-w-[180px]">
                <select name="employee_id" class="field-input !py-2 !text-[13px]">
                    <option value="">All Employees</option>
                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($emp->id); ?>" <?php echo e(request('employee_id') == $emp->id ? 'selected' : ''); ?>>
                            <?php echo e($emp->user?->name); ?> (<?php echo e($emp->employee_code); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                <button type="submit" class="flex-1 sm:flex-none justify-center inline-flex items-center gap-1.5 text-[12px] font-bold px-4 py-2 rounded-lg text-white hover:opacity-90 transition-opacity" style="background: var(--brand-600)">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i> Search
                </button>

                <a href="<?php echo e(route('admin.hrm.salary-slips.index')); ?>" class="flex-1 sm:flex-none justify-center inline-flex items-center gap-1.5 text-[12px] font-bold px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Clear
                </a>

                <?php if(has_permission('salary_slips.generate')): ?>
                    <button type="button" @click="generateModalOpen = true" class="w-full sm:w-auto sm:ml-auto justify-center inline-flex items-center gap-1.5 text-[12px] font-bold px-4 py-2 rounded-lg text-white hover:opacity-90 transition-opacity" style="background: #10b981">
                        <i data-lucide="file-plus" class="w-3.5 h-3.5"></i> Generate Slips
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </form>

    
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto w-full pb-2">
            <table class="w-full min-w-[1000px]">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider w-[50px]">#</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Employee</th>
                        <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Month / Year</th>
                        <th class="px-3 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-wider">Gross</th>
                        <th class="px-3 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-wider">Deductions</th>
                        <th class="px-3 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-wider">Net Salary</th>
                        <th class="px-3 py-3 text-center text-[10px] font-black text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $slips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $sc = \App\Models\Hrm\SalarySlip::STATUS_COLORS[$slip->status] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
                        $empName = $slip->employee->user?->name ?? '—';
                    ?>
                    <tr class="table-row">
                        <td class="px-5 py-3 text-[12px] font-bold text-gray-400"><?php echo e($slips->firstItem() + $loop->index); ?></td>
                        <td class="px-5 py-3">
                            <div>
                                <p class="text-[13px] font-bold text-gray-800"><?php echo e($empName); ?></p>
                                <p class="text-[11px] text-gray-400 mt-0.5">
                                    <span class="font-bold"><?php echo e($slip->employee->employee_code); ?></span>
                                    <?php if($slip->employee->department): ?>
                                        &nbsp;·&nbsp;<?php echo e($slip->employee->department->name); ?>

                                    <?php endif; ?>
                                </p>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-[13px] text-gray-700">
                            <?php echo e($months[$slip->month] ?? $slip->month); ?> <?php echo e($slip->year); ?>

                        </td>
                        <td class="px-3 py-3 text-right text-[13px] text-gray-700">
                            ₹<?php echo e(number_format($slip->gross_salary, 2)); ?>

                        </td>
                        <td class="px-3 py-3 text-right text-[13px] text-red-600">
                            ₹<?php echo e(number_format($slip->total_deductions, 2)); ?>

                        </td>
                        <td class="px-3 py-3 text-right">
                            <span class="inline-block text-[13px] font-black text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-lg">
                                ₹<?php echo e(number_format($slip->net_salary, 2)); ?>

                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-md"
                                style="background: <?php echo e($sc['bg']); ?>; color: <?php echo e($sc['text']); ?>">
                                <?php echo e(\App\Models\Hrm\SalarySlip::STATUS_LABELS[$slip->status]); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                
                                <?php if(has_permission('salary_slips.view')): ?>
                                <a href="<?php echo e(route('admin.hrm.salary-slips.show', $slip)); ?>"
                                    class="w-[30px] h-[30px] rounded-lg flex items-center justify-center bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-700 transition-colors"
                                    title="View">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </a>
                                <?php endif; ?>

                                
                                <?php if($slip->status === 'generated' && has_permission('salary_slips.approve')): ?>
                                <button @click="approveSlip(<?php echo e($slip->id); ?>)"
                                    class="w-[30px] h-[30px] rounded-lg flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-800 transition-colors"
                                    title="Approve">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                </button>
                                <?php endif; ?>

                                
                                <?php if($slip->status === 'approved'&& has_permission('salary_slips.mark_paid')): ?>
                                <button @click="openPayModal(<?php echo e($slip->id); ?>)"
                                    class="w-[30px] h-[30px] rounded-lg flex items-center justify-center bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-800 transition-colors"
                                    title="Mark Paid">
                                    <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                                </button>
                                <?php endif; ?>

                                
                                <?php if(has_permission('salary_slips.download_pdf')): ?>
                                <a href="<?php echo e(route('admin.hrm.salary-slips.pdf', $slip)); ?>" target="_blank"
                                    class="w-[30px] h-[30px] rounded-lg flex items-center justify-center bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors"
                                    title="Download PDF">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                </a>
                                <?php endif; ?>

                                
                                <?php if($slip->status !== 'paid'&& has_permission('salary_slips.delete')): ?>
                                <button @click="deleteSlip(<?php echo e($slip->id); ?>, $el.closest('tr'))"
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
                        <td colspan="8">
                            <div class="flex flex-col items-center justify-center py-20 text-center">
                                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                    <i data-lucide="file-text" class="w-7 h-7 text-gray-300"></i>
                                </div>
                                <p class="font-semibold text-gray-500 mb-1">No salary slips found</p>
                                <p class="text-sm text-gray-400 mb-4">Generate slips for a pay period to get started</p>
                                <button @click="generateModalOpen = true"
                                    class="text-sm font-bold px-4 py-2 rounded-xl text-white" style="background: var(--brand-600)">
                                    Generate Slips
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($slips->hasPages()): ?>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                <?php echo e($slips->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    
    <div x-show="generateModalOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white w-full max-w-md mx-4 rounded-xl shadow-2xl overflow-hidden" @click.away="generateModalOpen = false"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-black text-gray-800 uppercase tracking-widest text-sm">Generate Salary Slips</h3>
                <button @click="generateModalOpen = false" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form @submit.prevent="submitGenerate()">
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Month <span class="text-red-400">*</span></label>
                            <select x-model="genForm.month" class="field-input" required>
                                <option value="">Select Month</option>
                                <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($num); ?>"><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <p class="field-error" x-show="genErrors.month" x-text="genErrors.month"></p>
                        </div>
                        <div>
                            <label class="field-label">Year <span class="text-red-400">*</span></label>
                            <select x-model="genForm.year" class="field-input" required>
                                <option value="">Select Year</option>
                                <?php for($y = $currentYear + 1; $y >= $currentYear - 1; $y--): ?>
                                    <option value="<?php echo e($y); ?>"><?php echo e($y); ?></option>
                                <?php endfor; ?>
                            </select>
                            <p class="field-error" x-show="genErrors.year" x-text="genErrors.year"></p>
                        </div>
                    </div>

                    <div>
                        <label class="field-label">Employee <span class="text-gray-400 font-normal normal-case">(leave blank for all)</span></label>
                        <select x-model="genForm.employee_id" class="field-input">
                            <option value="">All Employees</option>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>">
                                    <?php echo e($emp->user?->name); ?> (<?php echo e($emp->employee_code); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div x-show="genMessage" x-cloak
                        class="text-[12px] font-semibold px-3 py-2 rounded-lg"
                        :class="genSuccess ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'"
                        x-text="genMessage"></div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button type="button" @click="generateModalOpen = false"
                        class="px-4 py-2 text-[13px] font-bold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="genSaving"
                        class="px-5 py-2 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity disabled:opacity-50"
                        style="background: #10b981">
                        <span x-show="!genSaving">Generate</span>
                        <span x-show="genSaving" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Generating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <div x-show="payModalOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden" @click.away="payModalOpen = false"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-black text-gray-800 uppercase tracking-widest text-sm">Mark as Paid</h3>
                <button @click="payModalOpen = false" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form @submit.prevent="submitPay()">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="field-label">Payment Mode <span class="text-red-400">*</span></label>
                        <select x-model="payForm.payment_mode" class="field-input" required>
                            <option value="">Select Mode</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                        </select>
                        <p class="field-error" x-show="payErrors.payment_mode" x-text="payErrors.payment_mode"></p>
                    </div>
                    <div>
                        <label class="field-label">Payment Reference</label>
                        <input type="text" x-model="payForm.payment_reference" class="field-input"
                            placeholder="Transaction ID, cheque no., etc.">
                        <p class="field-error" x-show="payErrors.payment_reference" x-text="payErrors.payment_reference"></p>
                    </div>
                    <div>
                        <label class="field-label">Payment Date <span class="text-red-400">*</span></label>
                        <input type="date" x-model="payForm.payment_date" class="field-input" required>
                        <p class="field-error" x-show="payErrors.payment_date" x-text="payErrors.payment_date"></p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button type="button" @click="payModalOpen = false"
                        class="px-4 py-2 text-[13px] font-bold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="paySaving"
                        class="px-5 py-2 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity disabled:opacity-50"
                        style="background: #10b981">
                        <span x-show="!paySaving">Mark Paid</span>
                        <span x-show="paySaving" class="flex items-center gap-2">
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
window.salarySlipsPage = function() {
    return {
        generateModalOpen: false,
        genSaving: false,
        genErrors: {},
        genMessage: '',
        genSuccess: false,
        genForm: { month: '', year: '', employee_id: '' },

        payModalOpen: false,
        paySaving: false,
        payErrors: {},
        paySlipId: null,
        payForm: { payment_mode: '', payment_reference: '', payment_date: '' },

        init() {
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openPayModal(id) {
            this.paySlipId = id;
            this.payForm = { payment_mode: '', payment_reference: '', payment_date: '' };
            this.payErrors = {};
            this.payModalOpen = true;
        },

        async approveSlip(id) {
            const result = await BizAlert.confirm('Approve Salary Slip', 'Are you sure you want to approve this salary slip?', 'Approve');
            if (!result.isConfirmed) return;

            try {
                const resp = await fetch(`<?php echo e(url('admin/hrm/salary-slips')); ?>/${id}/approve`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await resp.json();
                if (!resp.ok) { BizAlert.toast(data.message || 'Error approving slip', 'error'); return; }
                BizAlert.toast(data.message, 'success');
                setTimeout(() => window.location.reload(), 600);
            } catch (e) {
                BizAlert.toast('Network error. Please try again.', 'error');
            }
        },

        async submitGenerate() {
            this.genSaving = true;
            this.genErrors = {};
            this.genMessage = '';

            try {
                const resp = await fetch('<?php echo e(route('admin.hrm.salary-slips.generate')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.genForm),
                });
                const data = await resp.json();

                if (!resp.ok) {
                    if (resp.status === 422 && data.errors) {
                        for (const [key, messages] of Object.entries(data.errors)) {
                            this.genErrors[key] = messages[0];
                        }
                    } else {
                        this.genMessage = data.message || 'Something went wrong';
                        this.genSuccess = false;
                    }
                    return;
                }

                this.genMessage = data.message;
                this.genSuccess = true;
                setTimeout(() => { this.generateModalOpen = false; window.location.reload(); }, 900);
            } catch (e) {
                this.genMessage = 'Network error. Please try again.';
                this.genSuccess = false;
            } finally {
                this.genSaving = false;
            }
        },

        async deleteSlip(id, row) {
            const result = await BizAlert.confirm('Delete Salary Slip', 'This will permanently delete the salary slip and all its line items. Continue?');
            if (!result.isConfirmed) return;

            try {
                const resp = await fetch(`<?php echo e(url('admin/hrm/salary-slips')); ?>/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await resp.json();
                if (!resp.ok) { BizAlert.toast(data.message || 'Could not delete slip', 'error'); return; }
                BizAlert.toast(data.message, 'success');
                row.style.transition = 'opacity 300ms';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            } catch (e) {
                BizAlert.toast('Network error. Please try again.', 'error');
            }
        },

        async submitPay() {
            this.paySaving = true;
            this.payErrors = {};

            try {
                const resp = await fetch(`<?php echo e(url('admin/hrm/salary-slips')); ?>/${this.paySlipId}/pay`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.payForm),
                });
                const data = await resp.json();

                if (!resp.ok) {
                    if (resp.status === 422 && data.errors) {
                        for (const [key, messages] of Object.entries(data.errors)) {
                            this.payErrors[key] = messages[0];
                        }
                    } else {
                        BizAlert.toast(data.message || 'Something went wrong', 'error');
                    }
                    return;
                }

                BizAlert.toast(data.message, 'success');
                this.payModalOpen = false;
                setTimeout(() => window.location.reload(), 600);
            } catch (e) {
                BizAlert.toast('Network error. Please try again.', 'error');
            } finally {
                this.paySaving = false;
            }
        },
    };
};
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/hrm/salary-slips/index.blade.php ENDPATH**/ ?>