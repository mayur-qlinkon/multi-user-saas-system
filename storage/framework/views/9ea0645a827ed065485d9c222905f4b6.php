<?php $__env->startSection('title', 'Sales Invoices - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Invoices</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="pb-10" x-data="invoiceIndex()">

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

        
        <div class="bg-white rounded-t-xl shadow-sm border border-gray-100 p-4 border-b-0">
            <form action="<?php echo e(route('admin.invoices.index')); ?>" method="GET" class="flex flex-col sm:flex-row gap-3">

                
                <div class="flex flex-row items-center gap-2 flex-1 max-w-lg w-full">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                            placeholder="Search Invoice Number, Customer..."
                            class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all placeholder-gray-400">
                    </div>

                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm shrink-0">
                        Search
                    </button>

                    <?php if(request()->hasAny(['search', 'status', 'payment_status', 'source'])): ?>
                        <a href="<?php echo e(route('admin.invoices.index')); ?>" 
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
                        <?php if(request('status') || request('payment_status') || request('source')): ?>
                            <span class="w-2 h-2 rounded-full bg-red-500 ml-1"></span>
                        <?php endif; ?>
                    </button>

                    <div x-show="filterOpen" x-cloak x-transition
                        class="absolute right-0 mt-2 w-64 bg-white border border-gray-100 rounded-xl shadow-xl z-50 p-4">

                        <div class="mb-3">
                            <label
                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Source</label>
                            <select name="source"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                <option value="">All Sources</option>
                                <option value="pos" <?php echo e(request('source') == 'pos' ? 'selected' : ''); ?>>POS Sale</option>
                                <option value="direct" <?php echo e(request('source') == 'direct' ? 'selected' : ''); ?>>Direct Invoice
                                </option>
                                <option value="online" <?php echo e(request('source') == 'online' ? 'selected' : ''); ?>>Online Order
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label
                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                            <select name="status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                <option value="">All Statuses</option>
                                <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                                <option value="confirmed" <?php echo e(request('status') == 'confirmed' ? 'selected' : ''); ?>>Confirmed
                                </option>
                                <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>
                                    Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label
                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Payment</label>
                            <select name="payment_status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                <option value="">All Payments</option>
                                <option value="unpaid" <?php echo e(request('payment_status') == 'unpaid' ? 'selected' : ''); ?>>Unpaid
                                </option>
                                <option value="partial" <?php echo e(request('payment_status') == 'partial' ? 'selected' : ''); ?>>
                                    Partial</option>
                                <option value="paid" <?php echo e(request('payment_status') == 'paid' ? 'selected' : ''); ?>>Paid
                                </option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="<?php echo e(route('admin.invoices.index')); ?>"
                                class="px-3 py-1.5 text-xs font-bold text-gray-500 hover:text-gray-800 transition-colors">Clear</a>
                            <button type="submit"
                                class="bg-[#108c2a] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-[#0c6b1f] transition-colors">Apply</button>
                        </div>
                    </div>
                </div>

               
               <?php if(has_permission('invoices.create')): ?>
                <div class="ml-auto flex shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                    <a href="<?php echo e(route('admin.invoices.create')); ?>"
                        class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2 whitespace-nowrap">
                        <i data-lucide="plus" class="w-4 h-4"></i> Create Invoice
                    </a>
                </div>
                <?php endif; ?>

            </form>
        </div>

        
        <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            
            
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-6 py-4">INV DETAILS</th>
                            <th class="px-6 py-4">CUSTOMER</th>
                            
                            <th class="px-6 py-4 hidden md:table-cell">SOURCE</th>
                            <th class="px-6 py-4 text-center">STATUS</th>
                            <th class="px-6 py-4 text-center">PAYMENT</th>
                            <th class="px-6 py-4 text-right">TOTAL AMOUNT</th>
                            <th class="px-6 py-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            
                            <?php
                                $paidAmt = $invoice->payments->where('status', 'completed')->sum('amount');
                                $balanceDue = $invoice->grand_total - $paidAmt;
                            ?>

                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <a href="<?php echo e(route('admin.invoices.show', $invoice->id)); ?>"
                                            class="font-extrabold text-[#108c2a] text-[13px] hover:underline">
                                            <?php echo e($invoice->invoice_number); ?>

                                        </a>
                                        <span class="text-[11px] text-gray-500 mt-0.5 font-medium">
                                            <?php echo e($invoice->invoice_date->format('d M, Y')); ?>

                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800 text-[13px]">
                                            <?php echo e($invoice->customer_name ?: $invoice->client->name ?? 'Walk-in Customer'); ?>

                                        </span>
                                        <span class="text-[11px] text-gray-400 mt-0.5 font-bold uppercase tracking-tighter">
                                            <?php echo e($invoice->supply_state); ?>

                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 hidden md:table-cell">
                                    <span
                                        class="text-[10px] font-black uppercase tracking-widest <?php echo e($invoice->source === 'pos' ? 'text-orange-500' : 'text-blue-500'); ?>">
                                        <?php echo e($invoice->source); ?>

                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
                                            'confirmed' => 'bg-green-50 text-green-700 border-green-200',
                                            'cancelled' => 'bg-red-50 text-red-600 border-red-200',
                                        ];
                                        $color = $statusColors[$invoice->status] ?? $statusColors['draft'];
                                    ?>
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider border <?php echo e($color); ?>">
                                        <?php echo e($invoice->status); ?>

                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php
                                        $payColors = [
                                            'unpaid' => 'bg-red-50 text-red-600',
                                            'partial' => 'bg-blue-50 text-blue-600',
                                            'paid' => 'bg-green-50 text-green-700',
                                        ];
                                        $pColor = $payColors[$invoice->payment_status] ?? $payColors['unpaid'];
                                    ?>
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider <?php echo e($pColor); ?>">
                                        <?php echo e($invoice->payment_status); ?>

                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <span
                                        class="font-extrabold text-gray-800">₹<?php echo e(number_format($invoice->grand_total, 2)); ?></span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 transition-opacity">

                                        
                                        <?php if(has_permission('invoices.view')): ?>
                                        <a href="<?php echo e(route('admin.invoices.show', $invoice->id)); ?>"
                                            class="w-8 h-8 rounded border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                            title="View Invoice">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <?php endif; ?>

                                        
                                        <?php if($invoice->status === 'confirmed' && has_permission('invoice_returns.create')): ?>
                                            <a href="<?php echo e(route('admin.invoice-returns.create', $invoice->id)); ?>"
                                                class="w-8 h-8 rounded border border-gray-200 text-gray-600 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors"
                                                title="Create Return">
                                                <i data-lucide="undo-2" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>

                                        
                                        <?php if($invoice->status !== 'cancelled' && $balanceDue > 0 && has_permission('invoices.add_payment')): ?>
                                            <button type="button"
                                                @click="openPaymentModal(<?php echo e($invoice->id); ?>, '<?php echo e($invoice->invoice_number); ?>', <?php echo e($balanceDue); ?>)"
                                                class="w-8 h-8 rounded border border-green-200 text-green-600 hover:bg-green-50 flex items-center justify-center transition-colors"
                                                title="Record Payment">
                                                <i data-lucide="wallet" class="w-4 h-4"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if($invoice->status !== 'cancelled'): ?>
                                            
                                            <?php if($invoice->status !== 'confirmed' && has_permission('invoices.update')): ?>
                                            <a href="<?php echo e(route('admin.invoices.edit', $invoice->id)); ?>"
                                                class="w-8 h-8 rounded border border-blue-200 text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors"
                                                title="Edit Invoice">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </a>
                                            <?php endif; ?>

                                            
                                            <form action="<?php echo e(route('admin.invoices.destroy', $invoice->id)); ?>"
                                                method="POST" @submit.prevent="confirmCancel($event.target)"
                                                class="inline-block">
                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="w-8 h-8 rounded border border-red-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"
                                                    title="Cancel Invoice">
                                                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="shopping-cart" class="w-10 h-10 mb-3 opacity-20"></i>
                                        <p class="text-sm font-medium">No sales invoices found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="md:hidden divide-y divide-gray-50 border-t border-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $paidAmt = $invoice->payments->where('status', 'completed')->sum('amount');
                        $balanceDue = $invoice->grand_total - $paidAmt;
                        
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
                            'confirmed' => 'bg-green-50 text-green-700 border-green-200',
                            'cancelled' => 'bg-red-50 text-red-600 border-red-200',
                        ];
                        $color = $statusColors[$invoice->status] ?? $statusColors['draft'];
                        
                        $payColors = [
                            'unpaid' => 'bg-red-50 text-red-600',
                            'partial' => 'bg-blue-50 text-blue-600',
                            'paid' => 'bg-green-50 text-green-700',
                        ];
                        $pColor = $payColors[$invoice->payment_status] ?? $payColors['unpaid'];
                    ?>
                    <div class="p-4 hover:bg-gray-50/50 transition-colors flex flex-col gap-3">
                        
                        
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0">
                                <p class="font-bold text-gray-800 text-[14px] truncate">
                                    <?php echo e($invoice->customer_name ?: $invoice->client->name ?? 'Walk-in Customer'); ?>

                                </p>
                                <p class="text-[11px] text-gray-400 mt-0.5 font-bold uppercase tracking-tighter">
                                    <?php echo e($invoice->supply_state); ?>

                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-black text-[#108c2a] text-[16px]">₹<?php echo e(number_format($invoice->grand_total, 2)); ?></span>
                            </div>
                        </div>

                        
                        <div class="flex flex-col gap-2 bg-gray-50/80 px-3 py-2.5 rounded-lg border border-gray-100">
                            <div class="flex justify-between items-center">
                                <a href="<?php echo e(route('admin.invoices.show', $invoice->id)); ?>" class="font-extrabold text-[#108c2a] text-[13px] hover:underline">
                                    <?php echo e($invoice->invoice_number); ?>

                                </a>
                                <span class="text-[11px] text-gray-500 font-medium">
                                    <?php echo e($invoice->invoice_date->format('d M, Y')); ?>

                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-gray-100/50">
                                <span class="text-[9px] font-black uppercase tracking-widest <?php echo e($invoice->source === 'pos' ? 'text-orange-500' : 'text-blue-500'); ?>">
                                    <?php echo e($invoice->source); ?>

                                </span>
                                <span class="text-gray-300">|</span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider border <?php echo e($color); ?>">
                                    <?php echo e($invoice->status); ?>

                                </span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider <?php echo e($pColor); ?>">
                                    <?php echo e($invoice->payment_status); ?>

                                </span>
                            </div>
                        </div>

                        
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <?php if(has_permission('invoices.view')): ?>
                                <a href="<?php echo e(route('admin.invoices.show', $invoice->id)); ?>" class="w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors" title="View Invoice">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                            <?php endif; ?>

                            <?php if($invoice->status === 'confirmed' && has_permission('invoice_returns.create')): ?>
                                <a href="<?php echo e(route('admin.invoice-returns.create', $invoice->id)); ?>" class="w-8 h-8 rounded-lg border border-gray-200 text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" title="Create Return">
                                    <i data-lucide="undo-2" class="w-4 h-4"></i>
                                </a>
                            <?php endif; ?>

                            <?php if($invoice->status !== 'cancelled' && $balanceDue > 0 && has_permission('invoices.add_payment')): ?>
                                <button type="button" @click="openPaymentModal(<?php echo e($invoice->id); ?>, '<?php echo e($invoice->invoice_number); ?>', <?php echo e($balanceDue); ?>)" class="w-8 h-8 rounded-lg border border-green-200 text-green-600 hover:bg-green-50 flex items-center justify-center transition-colors" title="Record Payment">
                                    <i data-lucide="wallet" class="w-4 h-4"></i>
                                </button>
                            <?php endif; ?>

                            <?php if($invoice->status !== 'cancelled'): ?>
                                <?php if($invoice->status !== 'confirmed' && has_permission('invoices.update')): ?>
                                    <a href="<?php echo e(route('admin.invoices.edit', $invoice->id)); ?>" class="w-8 h-8 rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors" title="Edit Invoice">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>

                                <form action="<?php echo e(route('admin.invoices.destroy', $invoice->id)); ?>" method="POST" @submit.prevent="confirmCancel($event.target)" class="inline-block">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors" title="Cancel Invoice">
                                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 text-center text-sm text-gray-400 bg-white">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="shopping-cart" class="w-10 h-10 mb-3 opacity-20"></i>
                            <p class="font-medium">No sales invoices found.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($invoices->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    <?php echo e($invoices->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div x-show="isPaymentModalOpen" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
            
            <div class="bg-white w-full max-w-md mx-4 sm:mx-0 rounded-2xl shadow-2xl flex flex-col overflow-hidden"
                x-show="isPaymentModalOpen" x-transition @click.away="closePaymentModal()">

                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-[15px] font-bold text-gray-800">Record Payment - <span
                            x-text="activePayment.invoice_number"></span></h3>
                    <button type="button" @click="closePaymentModal()"
                        class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                
                <form :action="`/admin/invoices/${activePayment.invoice_id}/pay`" method="POST"
                    @submit="BizAlert.loading('Recording Payment...')">
                    <?php echo csrf_field(); ?>
                    <div class="p-6 space-y-5">

                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex justify-between items-center">
                            <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Balance Due</span>
                            <span class="text-xl font-black text-blue-700">₹ <span
                                    x-text="formatCurrency(activePayment.balance_due)"></span></span>
                        </div>

                        <?php if (isset($component)) { $__componentOriginal6074b4137b8006ef4d1b8340d0976388 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6074b4137b8006ef4d1b8340d0976388 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.payment-method-select','data' => ['name' => 'payment_method_id','label' => 'Payment Mode','xModel' => 'activePayment.payment_method_id','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('payment-method-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'payment_method_id','label' => 'Payment Mode','x-model' => 'activePayment.payment_method_id','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6074b4137b8006ef4d1b8340d0976388)): ?>
<?php $attributes = $__attributesOriginal6074b4137b8006ef4d1b8340d0976388; ?>
<?php unset($__attributesOriginal6074b4137b8006ef4d1b8340d0976388); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6074b4137b8006ef4d1b8340d0976388)): ?>
<?php $component = $__componentOriginal6074b4137b8006ef4d1b8340d0976388; ?>
<?php unset($__componentOriginal6074b4137b8006ef4d1b8340d0976388); ?>
<?php endif; ?>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Amount
                                Received (₹) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="amount_paid" x-model="activePayment.amount_paid"
                                required :max="activePayment.balance_due"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm font-black text-[#108c2a] focus:border-[#108c2a] focus:ring-2 focus:ring-green-500/20 outline-none transition-all">
                            <p class="text-[10px] text-gray-400 mt-1.5 font-medium flex items-center gap-1">
                                <i data-lucide="info" class="w-3 h-3"></i> Cannot exceed the balance due amount.
                            </p>
                        </div>
                    </div>

                    <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                        <button type="button" @click="closePaymentModal()"
                            class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold text-sm rounded-xl hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-[#108c2a] text-white font-bold text-sm rounded-xl hover:bg-[#0c6b1f] transition-all shadow-md active:scale-95 flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4"></i> Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
        

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function invoiceIndex() {
            return {
                // 🌟 Add Modal State Variables
                isPaymentModalOpen: false,
                activePayment: {
                    invoice_id: '',
                    invoice_number: '',
                    balance_due: 0,
                    amount_paid: '',
                    payment_method_id: ''
                },

                // 🌟 Open Modal & Auto-fill Balance
                openPaymentModal(id, number, balance) {
                    this.activePayment = {
                        invoice_id: id,
                        invoice_number: number,
                        balance_due: parseFloat(balance),
                        amount_paid: parseFloat(balance), // Default to paying the full remainder
                        payment_method_id: ''
                    };
                    this.isPaymentModalOpen = true;

                    // Optional: Re-initialize icons if your framework requires it
                    setTimeout(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }, 50);
                },

                closePaymentModal() {
                    this.isPaymentModalOpen = false;
                },

                formatCurrency(val) {
                    return parseFloat(val).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                confirmCancel(form) {
                    BizAlert.confirm(
                        'Cancel Invoice?',
                        'This action will void the invoice and reverse any associated stock movements.',
                        'Yes, Cancel it'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Cancelling...');
                            form.submit();
                        }
                    });
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/invoices/index.blade.php ENDPATH**/ ?>