<?php $__env->startSection('title', 'Expenses'); ?>

<?php $__env->startSection('header-title'); ?>
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Expenses</h1>        
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .filter-input {
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 13px;
            color: #1f2937;
            outline: none;
            background: #fff;
            transition: border-color 150ms ease;
            height: 38px;
        }

        .filter-input:focus {
            border-color: var(--brand-600);
        }

        select.filter-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 32px;
        }

        .table-row {
            transition: background 150ms ease;
        }

        .table-row:hover {
            background: #f8fafc;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

    <?php
        // Status color mapping
        $statusColors = [
            'draft' => ['bg' => '#f3f4f6', 'text' => '#4b5563', 'dot' => '#9ca3af'],
            'pending_approval' => ['bg' => '#fef3c7', 'text' => '#b45309', 'dot' => '#f59e0b'],
            'approved' => ['bg' => '#e0f2fe', 'text' => '#0369a1', 'dot' => '#0ea5e9'],
            'reimbursed' => ['bg' => '#dcfce3', 'text' => '#166534', 'dot' => '#22c55e'],
            'rejected' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'dot' => '#ef4444'],
        ];

        $paymentColors = [
            'unpaid' => ['bg' => '#fee2e2', 'text' => '#b91c1c'],
            'partial' => ['bg' => '#fef3c7', 'text' => '#b45309'],
            'paid' => ['bg' => '#dcfce3', 'text' => '#166534'],
        ];
    ?>

    <div class="pb-10 w-full" x-data="expensesIndex()">

        
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-5">

            
            <form method="GET" action="<?php echo e(route('admin.expenses.index')); ?>"
                class="flex flex-col sm:flex-row sm:flex-wrap gap-3 flex-1 w-full" x-ref="filterForm">

                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" style="padding-left: 30px;"
                        placeholder="Search merchant, invoice..." class="filter-input pl-9 w-full">
                </div>

                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm shrink-0">
                        Search
                    </button>

                <select name="status" class="filter-input w-full sm:w-auto sm:min-w-[140px]" @change="$refs.filterForm.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending_approval" <?php echo e(request('status') === 'pending_approval' ? 'selected' : ''); ?>>
                        Pending Approval</option>
                    <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>>Approved</option>
                    <option value="reimbursed" <?php echo e(request('status') === 'reimbursed' ? 'selected' : ''); ?>>Reimbursed
                    </option>
                    <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                    <option value="draft" <?php echo e(request('status') === 'draft' ? 'selected' : ''); ?>>Draft</option>
                </select>

                <select name="category_id" class="filter-input w-full sm:w-auto sm:min-w-[160px]" @change="$refs.filterForm.submit()">
                    <option value="">All Categories</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category_id') == $cat->id ? 'selected' : ''); ?>

                            class="font-bold">
                            <?php echo e($cat->name); ?>

                        </option>
                        <?php $__currentLoopData = $cat->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($child->id); ?>"
                                <?php echo e(request('category_id') == $child->id ? 'selected' : ''); ?>>
                                &nbsp;&nbsp;— <?php echo e($child->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <?php if(request()->hasAny(['search', 'status', 'payment_status', 'source'])): ?>
                        <a href="<?php echo e(route('admin.expenses.index')); ?>" 
                            class="bg-red-50 hover:bg-red-100 text-red-500 w-10 h-10 rounded-lg flex items-center justify-center shrink-0 transition-colors" 
                            title="Clear Filters">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
            </form>

            
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <?php if(has_permission('expenses.create')): ?>
                <a href="<?php echo e(route('admin.expenses.create')); ?>"
                    class="inline-flex justify-center items-center gap-2 px-4 py-2.5 w-full sm:w-auto bg-brand-500 rounded-xl text-sm font-bold text-white hover:bg-brand-600 transition-opacity">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Log Expense
                </a>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if(session('success')): ?>
            <div class="mb-4 bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex items-center gap-3">
                <i data-lucide="check-circle" class="w-4 h-4 text-green-600 flex-shrink-0"></i>
                <p class="text-sm font-semibold text-green-800"><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 flex-shrink-0"></i>
                <p class="text-sm font-semibold text-red-700"><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?>

        
        <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">

            <?php if($expenses->isEmpty()): ?>
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div
                        class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mb-3 border border-gray-100">
                        <i data-lucide="indian-rupee" class="w-6 h-6 text-gray-300"></i>
                    </div>
                    <p class="font-semibold text-gray-600 mb-1">No expenses found</p>
                    <p class="text-sm text-gray-400 mb-4">
                        <?php if(request()->hasAny(['search', 'status', 'category_id'])): ?>
                            Try adjusting your filters to find what you're looking for.
                        <?php else: ?>
                            Start tracking your company expenditures by logging an expense.
                        <?php endif; ?>
                    </p>
                    <?php if(!request()->hasAny(['search', 'status', 'category_id'])): ?>
                        <a href="<?php echo e(route('admin.expenses.create')); ?>"
                            class="text-sm font-bold px-4 py-2 rounded-xl text-white shadow-sm hover:opacity-90"
                            style="background: var(--brand-600)">
                            Log First Expense
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[950px]">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50">
                                <th
                                    class="px-5 py-3 text-[10px] font-black text-gray-400 uppercase tracking-wider w-[50px]">
                                    #</th>
                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-wider">Expense
                                    Details</th>
                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-wider">Category
                                </th>
                                <th
                                    class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-wider text-right">
                                    Amount</th>
                                <th
                                    class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-wider text-center">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-wider text-center">
                                    Payment Status</th>
                                <th
                                    class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase tracking-wider text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $statusConf = $statusColors[$expense->status] ?? $statusColors['draft'];
                                    $payConf = $paymentColors[$expense->payment_status] ?? $paymentColors['unpaid'];
                                    $isFinalized = in_array($expense->status, ['approved', 'reimbursed']);
                                ?>
                                <tr class="table-row group">

                                    
                                    <td class="px-5 py-3.5 text-[12px] font-bold text-gray-400">
                                        <?php echo e($expenses->firstItem() + $loop->index); ?>

                                    </td>

                                    
                                    <td class="px-3 py-3 sm:px-4 sm:py-3.5">
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                                                style="background: var(--brand-50)">
                                                <i data-lucide="indian-rupee" class="w-4 h-4"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <a href="<?php echo e(route('admin.expenses.show', $expense->id)); ?>"
                                                    class="text-[13px] font-bold text-gray-900 hover:text-blue-600 hover:underline block break-words sm:truncate">
                                                    <?php echo e($expense->merchant_name); ?>

                                                </a>
                                                <div
                                                    class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-0.5 text-[11px] text-gray-500 font-medium">
                                                    <span><?php echo e($expense->expense_number); ?></span>
                                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                                    <span><?php echo e($expense->expense_date->format('d M Y')); ?></span>
                                                </div>
                                                <?php if($expense->merchant_gstin): ?>
                                                    <p class="text-[10px] text-gray-400 mt-0.5">GSTIN:
                                                        <?php echo e($expense->merchant_gstin); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>

                                    
                                    <td class="px-3 py-3 sm:px-4 sm:py-3.5">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-600">
                                            <?php echo e($expense->category->name ?? 'Uncategorized'); ?>

                                        </span>
                                        <?php if($expense->is_reimbursable): ?>
                                            <p class="text-[10px] font-bold text-[#b45309] mt-1">Reimbursable</p>
                                        <?php endif; ?>
                                    </td>

                                    
                                    <td class="px-3 py-3 sm:px-4 sm:py-3.5 text-right">
                                        <p class="text-[14px] font-black text-gray-900 whitespace-nowrap">
                                            &#8377;<?php echo e(number_format($expense->total_amount, 2)); ?>

                                        </p>
                                        
                                    </td>

                                    
                                    <td class="px-3 py-3 sm:px-4 sm:py-3.5 text-center">
                                        <span class="status-badge whitespace-nowrap"
                                            style="background: <?php echo e($statusConf['bg']); ?>; color: <?php echo e($statusConf['text']); ?>">
                                            <span class="w-1.5 h-1.5 rounded-full"
                                                style="background: <?php echo e($statusConf['dot']); ?>"></span>
                                            <?php echo e(str_replace('_', ' ', $expense->status)); ?>

                                        </span>
                                    </td>

                                    
                                    <td class="px-3 py-3 sm:px-4 sm:py-3.5 text-center">
                                        <?php if($expense->payment_status === 'paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php elseif($expense->payment_status === 'partial'): ?>
                                            <span class="badge bg-warning">Partial</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Unpaid</span>
                                        <?php endif; ?>
                                    </td>
                                    

                                    
                                    <td class="px-3 py-3 sm:px-4 sm:py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1">

                                            <?php if(has_permission('expenses.view')): ?>
                                            <a href="<?php echo e(route('admin.expenses.show', $expense->id)); ?>"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                title="View Details">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            <?php endif; ?>

                                            <?php if(!$isFinalized): ?>
                                                <?php if(has_permission('expenses.update')): ?>
                                                <a href="<?php echo e(route('admin.expenses.edit', $expense->id)); ?>"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                                    title="Edit">
                                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if(has_permission('expenses.delete')): ?>
                                                <button type="button"
                                                    @click="deleteExpense(<?php echo e($expense->id); ?>, '<?php echo e($expense->expense_number); ?>')"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                    title="Delete">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                                <?php endif; ?> 

                                            <?php else: ?>
                                                
                                                <div class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-200 cursor-not-allowed"
                                                    title="Locked (Finalized)">
                                                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="md:hidden divide-y divide-gray-50 border-t border-gray-50">
                    <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $statusConf = $statusColors[$expense->status] ?? $statusColors['draft'];
                            $payConf = $paymentColors[$expense->payment_status] ?? $paymentColors['unpaid'];
                            $isFinalized = in_array($expense->status, ['approved', 'reimbursed']);
                        ?>
                        <div class="p-4 hover:bg-gray-50/50 transition-colors flex flex-col gap-3">
                            
                            
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5" style="background: var(--brand-50)">
                                        <i data-lucide="indian-rupee" class="w-4 h-4" style="color: var(--brand-600)"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <a href="<?php echo e(route('admin.expenses.show', $expense->id)); ?>" class="text-[13px] font-bold text-gray-900 hover:text-blue-600 hover:underline block truncate">
                                            <?php echo e($expense->merchant_name); ?>

                                        </a>
                                        <div class="text-[11px] text-gray-500 font-medium mt-0.5">
                                            <?php echo e($expense->expense_number); ?> • <?php echo e($expense->expense_date->format('d M Y')); ?>

                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-[15px] font-black text-gray-900"><?php echo e($expense->currency_code); ?> <?php echo e(number_format($expense->total_amount, 2)); ?></p>
                                    <?php if($expense->tax_amount > 0): ?>
                                        <p class="text-[9px] font-bold text-gray-400 mt-0.5 uppercase tracking-wider">Incl. Tax</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            
                            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-gray-100/50 mt-1">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">
                                    <?php echo e($expense->category->name ?? 'Uncategorized'); ?>

                                </span>
                                <?php if($expense->is_reimbursable): ?>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold text-[#b45309] bg-amber-50 border border-amber-100 tracking-wide">
                                        Reimbursable
                                    </span>
                                <?php endif; ?>
                                <span class="ml-auto inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider border" style="background: <?php echo e($statusConf['bg']); ?>; color: <?php echo e($statusConf['text']); ?>; border-color: <?php echo e($statusConf['bg']); ?>">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background: <?php echo e($statusConf['dot']); ?>"></span>
                                    <?php echo e(str_replace('_', ' ', $expense->status)); ?>

                                </span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider" style="background: <?php echo e($payConf['bg']); ?>; color: <?php echo e($payConf['text']); ?>">
                                    <?php echo e($expense->payment_status); ?>

                                </span>
                            </div>

                            
                            <div class="flex items-center justify-end gap-2 pt-1">
                                <?php if(has_permission('expenses.view')): ?>
                                    <a href="<?php echo e(route('admin.expenses.show', $expense->id)); ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="View Details">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if(!$isFinalized): ?>
                                    <?php if(has_permission('expenses.update')): ?>
                                        <a href="<?php echo e(route('admin.expenses.edit', $expense->id)); ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-amber-200 text-amber-600 hover:bg-amber-50 transition-colors" title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if(has_permission('expenses.delete')): ?>
                                        <button type="button" @click="deleteExpense(<?php echo e($expense->id); ?>, '<?php echo e($expense->expense_number); ?>')" class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    <?php endif; ?> 
                                <?php else: ?>
                                    <div class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-100 text-gray-300 cursor-not-allowed" title="Locked (Finalized)">
                                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <?php if($expenses->hasPages()): ?>
                    <div
                        class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-4 bg-white text-center sm:text-left">
                        <p class="text-[12px] text-gray-400 font-medium">
                            Showing <span class="font-bold text-gray-600"><?php echo e($expenses->firstItem()); ?></span> to <span
                                class="font-bold text-gray-600"><?php echo e($expenses->lastItem()); ?></span> of <span
                                class="font-bold text-gray-600"><?php echo e($expenses->total()); ?></span> expenses
                        </p>
                        <div>
                            <?php echo e($expenses->links('pagination::tailwind')); ?>

                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function expensesIndex() {
            return {
                async deleteExpense(id, number) {
                    const confirmed = await Swal.fire({
                        title: 'Delete Expense?',
                        text: `Are you sure you want to delete ${number}? This action cannot be undone.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#ef4444',
                    });

                    if (confirmed.isConfirmed) {
                        // Create a dynamic form to submit the DELETE request
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/admin/expenses/${id}`;

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';

                        form.appendChild(csrfInput);
                        form.appendChild(methodInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/expenses/index.blade.php ENDPATH**/ ?>