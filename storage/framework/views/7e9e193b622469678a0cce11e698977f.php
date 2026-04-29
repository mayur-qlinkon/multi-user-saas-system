<?php $__env->startSection('title', 'Purchase Returns - Qlinkon BIZNESS'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Purchase Returns</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="pb-10" x-data="purchaseReturnIndex()">

        <?php if(session('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('success')); ?>", 'success'));
            </script>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('error')); ?>", 'error'));
            </script>
        <?php endif; ?>

        
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>                
                <p class="text-[13px] text-gray-500 font-medium mt-1">Manage return to vendor records and expected refunds</p>
            </div>
        </div>

        
        <div class="bg-white rounded-t-xl shadow-sm border border-gray-100 p-4 border-b-0">
            <form action="<?php echo e(route('admin.purchase-returns.index')); ?>" method="GET"
                class="flex flex-col sm:flex-row gap-3">

                
                <div class="flex flex-row items-center gap-2 flex-1 max-w-lg w-full">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                            placeholder="Search Return No, PO No, Supplier..."
                            class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all placeholder-gray-400">
                    </div>

                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm shrink-0">
                        Search
                    </button>

                    <?php if(request()->hasAny(['search', 'status', 'payment_status'])): ?>
                        <a href="<?php echo e(route('admin.purchase-returns.index')); ?>" 
                            class="bg-red-50 hover:bg-red-100 text-red-500 w-10 h-10 rounded-lg flex items-center justify-center shrink-0 transition-colors" 
                            title="Clear Filters">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <div x-data="{ filterOpen: false }" @click.away="filterOpen = false" class="relative">
                    <button type="button" @click="filterOpen = !filterOpen"
                        class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors flex items-center gap-2 h-full">
                        <i data-lucide="filter" class="w-4 h-4 text-gray-500"></i> Filters
                        <?php if(request('status') || request('payment_status')): ?>
                            <span class="w-2 h-2 rounded-full bg-red-500 ml-1"></span>
                        <?php endif; ?>
                    </button>

                    <div x-show="filterOpen" x-cloak x-transition
                        class="absolute right-0 mt-2 w-64 bg-white border border-gray-100 rounded-xl shadow-xl z-50 p-4">

                        <div class="mb-3">
                            <label
                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                            <select name="status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                <option value="">All Statuses</option>
                                <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                                <option value="returned" <?php echo e(request('status') == 'returned' ? 'selected' : ''); ?>>Returned
                                </option>
                                <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelled
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Refund
                                Status</label>
                            <select name="payment_status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                <option value="">All Payments</option>
                                <option value="pending" <?php echo e(request('payment_status') == 'pending' ? 'selected' : ''); ?>>
                                    Pending</option>
                                <option value="adjusted" <?php echo e(request('payment_status') == 'adjusted' ? 'selected' : ''); ?>>
                                    Adjusted</option>
                                <option value="refunded" <?php echo e(request('payment_status') == 'refunded' ? 'selected' : ''); ?>>
                                    Refunded</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="<?php echo e(route('admin.purchase-returns.index')); ?>"
                                class="px-3 py-1.5 text-xs font-bold text-gray-500 hover:text-gray-800 transition-colors">Clear</a>
                            <button type="submit"
                                class="bg-[#108c2a] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-[#0c6b1f] transition-colors">Apply</button>
                        </div>
                    </div>
                </div>

               
                <div class="ml-auto flex shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                    <?php if(has_permission('purchase_returns.create')): ?>
                    <a href="<?php echo e(route('admin.purchase-returns.create')); ?>"
                        class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2 whitespace-nowrap">
                        <i data-lucide="plus" class="w-4 h-4"></i> Create Return
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        
        <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            
            
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-6 py-4">RETURN DETAILS</th>
                            <th class="px-6 py-4">SUPPLIER</th>
                            <th class="px-6 py-4">DESTINATION</th>
                            <th class="px-6 py-4 text-center">STATUS</th>
                            <th class="px-6 py-4 text-center">REFUND STATUS</th>
                            <th class="px-6 py-4 text-right">EXPECTED REFUND</th>
                            <th class="px-6 py-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $purchaseReturns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 transition-colors group">

                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <a href="<?php echo e(route('admin.purchase-returns.show', $return->id)); ?>"
                                            class="font-extrabold text-[#108c2a] text-[13px] hover:underline">
                                            <?php echo e($return->return_number); ?>

                                        </a>
                                        <span class="text-[11px] text-gray-500 mt-0.5 font-medium">
                                            <?php echo e($return->return_date->format('d M, Y')); ?>

                                        </span>
                                        <?php if($return->purchase): ?>
                                            <a href="<?php echo e(route('admin.purchases.show', $return->purchase_id)); ?>"
                                                class="text-[10px] text-blue-500 hover:text-blue-700 mt-1 uppercase">
                                                Ref: <?php echo e($return->purchase->purchase_number); ?>

                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-gray-800 text-[13px]"><?php echo e($return->supplier->name ?? 'Unknown'); ?></span>
                                        <?php if($return->supplier_credit_note_number): ?>
                                            <span class="text-[11px] text-gray-400 mt-0.5 font-mono">CN:
                                                <?php echo e($return->supplier_credit_note_number); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-semibold text-gray-700 text-[12px]"><?php echo e($return->warehouse->name ?? 'N/A'); ?></span>
                                        <?php if($return->store): ?>
                                            <span
                                                class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5"><?php echo e($return->store->name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
                                            'returned' => 'bg-green-50 text-green-700 border-green-200',
                                            'cancelled' => 'bg-red-50 text-red-600 border-red-200',
                                        ];
                                        $color = $statusColors[$return->status] ?? $statusColors['draft'];
                                    ?>
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider border <?php echo e($color); ?>">
                                        <?php echo e(str_replace('_', ' ', $return->status)); ?>

                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php
                                        $payColors = [
                                            'pending' => 'bg-orange-50 text-orange-600',
                                            'adjusted' => 'bg-blue-50 text-blue-600',
                                            'refunded' => 'bg-green-50 text-green-700',
                                        ];
                                        $pColor = $payColors[$return->payment_status] ?? $payColors['pending'];
                                    ?>
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider <?php echo e($pColor); ?>">
                                        <?php echo e($return->payment_status); ?>

                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span
                                            class="font-extrabold text-gray-800">₹<?php echo e(number_format($return->total_amount, 2)); ?></span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 transition-opacity">

                                        <?php if(has_permission('purchase_returns.view')): ?>
                                        <a href="<?php echo e(route('admin.purchase-returns.show', $return->id)); ?>"
                                            class="w-8 h-8 rounded border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                            title="View Return">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <?php endif; ?>

                                        <?php if($return->status !== 'cancelled' && has_permission('purchase_returns.add_payment')): ?>
                                            <button type="button"
                                                @click="openPaymentModal(<?php echo e($return->id); ?>, '<?php echo e($return->payment_status); ?>')"
                                                class="w-8 h-8 rounded border border-green-200 text-green-600 hover:bg-green-50 flex items-center justify-center transition-colors"
                                                title="Update Refund Status">
                                                <i data-lucide="indian-rupee" class="w-4 h-4"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if($return->status !== 'returned' && $return->status !== 'cancelled' && has_permission('purchase_returns.update')): ?>
                                            <a href="<?php echo e(route('admin.purchase-returns.edit', $return->id)); ?>"
                                                class="w-8 h-8 rounded border border-blue-200 text-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors"
                                                title="Edit Return">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if($return->status !== 'returned'): ?>
                                            <?php if(has_permission('purchase_returns.delete')): ?>
                                            <form action="<?php echo e(route('admin.purchase-returns.destroy', $return->id)); ?>"
                                                method="POST" @submit.prevent="confirmDelete($event.target)"
                                                class="inline-block">
                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="w-8 h-8 rounded border border-red-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"
                                                    title="Delete Return">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="w-8 h-8 rounded border border-gray-100 text-gray-300 flex items-center justify-center cursor-not-allowed"
                                                title="Cannot delete finalized return">
                                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="corner-up-left" class="w-10 h-10 mb-3 opacity-20"></i>
                                        <p class="text-sm font-medium">No purchase returns found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="md:hidden divide-y divide-gray-50 border-t border-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $purchaseReturns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
                            'returned' => 'bg-green-50 text-green-700 border-green-200',
                            'cancelled' => 'bg-red-50 text-red-600 border-red-200',
                        ];
                        $color = $statusColors[$return->status] ?? $statusColors['draft'];
                        
                        $payColors = [
                            'pending' => 'bg-orange-50 text-orange-600',
                            'adjusted' => 'bg-blue-50 text-blue-600',
                            'refunded' => 'bg-green-50 text-green-700',
                        ];
                        $pColor = $payColors[$return->payment_status] ?? $payColors['pending'];
                    ?>
                    <div class="p-4 hover:bg-gray-50/50 transition-colors flex flex-col gap-3">
                        
                        
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-gray-800 text-[14px] truncate">
                                    <?php echo e($return->supplier->name ?? 'Unknown'); ?>

                                </p>
                                <?php if($return->supplier_credit_note_number): ?>
                                    <p class="text-[11px] text-gray-400 mt-0.5 font-mono truncate">
                                        CN: <?php echo e($return->supplier_credit_note_number); ?>

                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-black text-gray-800 text-[15px]">₹<?php echo e(number_format($return->total_amount, 2)); ?></span>
                            </div>
                        </div>

                        
                        <div class="flex flex-col gap-2 bg-gray-50/80 px-3 py-2.5 rounded-lg border border-gray-100">
                            <div class="flex justify-between items-center">
                                <a href="<?php echo e(route('admin.purchase-returns.show', $return->id)); ?>" class="font-extrabold text-[#108c2a] text-[13px] hover:underline">
                                    <?php echo e($return->return_number); ?>

                                </a>
                                <span class="text-[11px] text-gray-500 font-medium">
                                    <?php echo e($return->return_date->format('d M, Y')); ?>

                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-gray-100/50">
                                <?php if($return->purchase): ?>
                                    <a href="<?php echo e(route('admin.purchases.show', $return->purchase_id)); ?>" class="text-[10px] text-blue-500 hover:text-blue-700 font-bold uppercase tracking-wider">
                                        Ref: <?php echo e($return->purchase->purchase_number); ?>

                                    </a>
                                    <span class="text-gray-300">|</span>
                                <?php endif; ?>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider border <?php echo e($color); ?>">
                                    <?php echo e(str_replace('_', ' ', $return->status)); ?>

                                </span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider <?php echo e($pColor); ?>">
                                    <?php echo e($return->payment_status); ?>

                                </span>
                            </div>
                        </div>

                        
                        <div class="flex items-center justify-between pt-1">
                            <div class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest truncate">
                                <?php echo e($return->warehouse->name ?? 'N/A'); ?>

                                <?php if($return->store): ?>
                                    • <?php echo e($return->store->name); ?>

                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center justify-end gap-2 shrink-0">
                                <?php if(has_permission('purchase_returns.view')): ?>
                                    <a href="<?php echo e(route('admin.purchase-returns.show', $return->id)); ?>" class="w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors" title="View Return">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if($return->status !== 'cancelled' && has_permission('purchase_returns.add_payment')): ?>
                                    <button type="button" @click="openPaymentModal(<?php echo e($return->id); ?>, '<?php echo e($return->payment_status); ?>')" class="w-8 h-8 rounded-lg border border-green-200 text-green-600 hover:bg-green-50 flex items-center justify-center transition-colors" title="Update Refund Status">
                                        <i data-lucide="indian-rupee" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>

                                <?php if($return->status !== 'returned' && $return->status !== 'cancelled' && has_permission('purchase_returns.update')): ?>
                                    <a href="<?php echo e(route('admin.purchase-returns.edit', $return->id)); ?>" class="w-8 h-8 rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors" title="Edit Return">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if($return->status !== 'returned'): ?>
                                    <?php if(has_permission('purchase_returns.delete')): ?>
                                        <form action="<?php echo e(route('admin.purchase-returns.destroy', $return->id)); ?>" method="POST" @submit.prevent="confirmDelete($event.target)" class="inline-block m-0 p-0">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors" title="Delete Return">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-lg border border-gray-100 text-gray-300 flex items-center justify-center cursor-not-allowed" title="Cannot delete finalized return">
                                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 text-center text-sm text-gray-400 bg-white">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="corner-up-left" class="w-10 h-10 mb-3 opacity-20"></i>
                            <p class="font-medium text-gray-500 text-[13px]">No purchase returns found.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($purchaseReturns->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    <?php echo e($purchaseReturns->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div x-show="paymentModalOpen" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all"
                @click.away="paymentModalOpen = false">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Update Refund Status</h3>
                    <button @click="paymentModalOpen = false" class="text-gray-400 hover:text-red-500"><i data-lucide="x"
                            class="w-5 h-5"></i></button>
                </div>

                <form @submit.prevent="submitPaymentForm($event)">
                    <?php echo csrf_field(); ?>
                    <div class="p-6">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Select
                            Status</label>
                        <select name="payment_status" x-model="paymentStatus"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:border-[#108c2a] outline-none font-bold text-gray-700">
                            <option value="pending">Pending (Waiting for Supplier)</option>
                            <option value="adjusted">Adjusted (Credit Note Applied)</option>
                            <option value="refunded">Refunded (Cash/Bank Received)</option>
                        </select>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="paymentModalOpen = false"
                            class="px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-bold text-white bg-[#108c2a] hover:bg-[#0c6b1f] rounded-lg transition-colors shadow-sm">Save
                            Status</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function purchaseReturnIndex() {
            return {
                paymentModalOpen: false,
                selectedReturnId: null,
                paymentStatus: '',

                get paymentActionUrl() {
                    if (!this.selectedReturnId) return '#';
                    return "<?php echo e(route('admin.purchase-returns.index')); ?>/" + this.selectedReturnId + "/payment";
                },

                openPaymentModal(id, status) {
                    this.selectedReturnId = id;
                    this.paymentStatus = status;
                    this.paymentModalOpen = true;
                },

                async submitPaymentForm(e) {
                    BizAlert.loading('Updating Status...');
                    let token = e.target.querySelector('input[name="_token"]').value;

                    try {
                        let response = await fetch(this.paymentActionUrl, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                payment_status: this.paymentStatus
                            })
                        });

                        let data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Something went wrong on the server.');
                        }

                        BizAlert.toast('Status successfully updated!', 'success');
                        setTimeout(() => window.location.reload(), 1000);

                    } catch (error) {
                        BizAlert.toast(error.message, 'error');
                    }
                },

                confirmDelete(form) {
                    BizAlert.confirm(
                        'Delete Purchase Return?',
                        'This action cannot be undone. Any drafted return data will be permanently removed.',
                        'Yes, Delete'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Deleting...');
                            form.submit();
                        }
                    });
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/purchase-returns/index.blade.php ENDPATH**/ ?>