

<?php $__env->startSection('title', 'My Orders'); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6 lg:space-y-8">
        
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Order History</h1>
                <p class="text-slate-500 text-sm mt-1.5">Track your recent orders, view details, and download receipts.</p>
            </div>
        </div>

        
        <div class="space-y-6">
            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    // Map your DB statuses to semantic UI colors
                    $statusColor = match ($order->status) {
                        'delivered'  => 'emerald',
                        'shipped'    => 'blue',
                        'cancelled'  => 'red',
                        'processing' => 'indigo',
                        'confirmed'  => 'teal',
                        default      => 'amber', // 'inquiry', 'pending'
                    };

                    // Logical Timeline Progression for Storefront
                    $steps = ['inquiry', 'confirmed', 'processing', 'shipped', 'delivered'];
                    $currentStepIndex = array_search($order->status, $steps);
                    
                    if ($order->status === 'cancelled') {
                        $currentStepIndex = -1; // Cancelled breaks the timeline
                    } elseif ($currentStepIndex === false) {
                        $currentStepIndex = 0; // Fallback
                    }
                ?>

                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-md transition-shadow duration-300">

                    
                    <div class="bg-slate-50/80 px-5 sm:px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100">
                        <div class="flex flex-wrap sm:flex-nowrap items-center gap-x-6 gap-y-3">
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Order ID</span>
                                <p class="font-black text-slate-900 text-base sm:text-lg tracking-tight">#<?php echo e($order->order_number); ?></p>
                            </div>
                            <div class="hidden sm:block w-px h-8 bg-slate-200"></div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Date Placed</span>
                                <p class="font-semibold text-slate-700 text-sm sm:text-base"><?php echo e($order->created_at->format('M d, Y')); ?></p>
                            </div>
                            <div class="hidden sm:block w-px h-8 bg-slate-200"></div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Total Amount</span>
                                <p class="font-black text-slate-900 text-sm sm:text-base">₹<?php echo e(number_format($order->total_amount, 2)); ?></p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between md:justify-end gap-4 w-full md:w-auto mt-2 md:mt-0">
                            
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-[11px] font-black uppercase bg-<?php echo e($statusColor); ?>-50 text-<?php echo e($statusColor); ?>-700 border border-<?php echo e($statusColor); ?>-100 tracking-wide">
                                <?php if(!in_array($order->status, ['delivered', 'cancelled'])): ?>
                                    <span class="w-1.5 h-1.5 rounded-full bg-<?php echo e($statusColor); ?>-500 animate-pulse"></span>
                                <?php endif; ?>
                                <?php echo e($order->status === 'inquiry' ? 'Pending Confirmation' : ucfirst($order->status)); ?>

                            </span>

                            
                            <a href="<?php echo e(route('storefront.orders.receipt', ['slug' => $company->slug, 'orderNumber' => $order->order_number])); ?>"
                                target="_blank"
                                class="group flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 uppercase tracking-wide hover:border-primary hover:text-primary hover:bg-primary/5 transition-all shadow-sm"
                                title="Download Receipt">
                                <i data-lucide="file-down" class="w-4 h-4 text-slate-400 group-hover:text-primary transition-colors"></i>
                                <span class="hidden sm:inline">Receipt</span>
                            </a>
                        </div>
                    </div>

                    
                    <div class="p-5 sm:p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">

                        
                        <div class="lg:col-span-1 space-y-6">

                            
                            <?php if($order->status !== 'cancelled'): ?>
                                <div>
                                    <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Tracking Status</h4>
                                    <div class="relative flex flex-col gap-4 border-l-2 border-slate-100 pl-5 ml-2.5">
                                        <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="relative">
                                                <div class="absolute -left-[27px] top-0.5 w-2.5 h-2.5 rounded-full ring-4 ring-white <?php echo e($index <= $currentStepIndex ? "bg-{$statusColor}-500" : 'bg-slate-200'); ?>"></div>
                                                <p class="text-xs font-bold <?php echo e($index <= $currentStepIndex ? 'text-slate-900' : 'text-slate-400'); ?> uppercase tracking-wide">
                                                    <?php echo e($step === 'inquiry' ? 'Placed' : ucfirst($step)); ?>

                                                </p>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="bg-red-50 border border-red-100 rounded-xl p-4">
                                    <h4 class="text-[11px] font-black text-red-600 uppercase tracking-widest mb-1">Order Cancelled</h4>
                                    <p class="text-xs text-red-500 font-medium">This order was cancelled and will not be fulfilled.</p>
                                </div>
                            <?php endif; ?>

                            
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-slate-500">Payment Status</span>
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-md uppercase tracking-wide <?php echo e($order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'); ?>">
                                        <?php echo e(ucfirst($order->payment_status)); ?>

                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-slate-500">Method</span>
                                    <span class="text-xs font-bold text-slate-700 uppercase"><?php echo e($order->payment_method); ?></span>
                                </div>
                                <?php if($order->coupon_code): ?>
                                    <div class="flex items-center justify-between pt-2 mt-2 border-t border-slate-200">
                                        <span class="text-xs font-semibold text-slate-500">Coupon</span>
                                        <span class="text-xs font-mono font-black text-green-600"><?php echo e($order->coupon_code); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <div class="lg:col-span-2">
                            <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3">
                                Items (<?php echo e($order->items->count()); ?>)
                            </h4>
                            <div class="space-y-2">
                                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-white border border-slate-100 shadow-sm hover:border-primary/30 transition-colors group">
                                        <div class="flex items-center gap-4">
                                            
                                            <?php if($item->product_image): ?>
                                                <img src="<?php echo e(asset('storage/' . $item->product_image)); ?>" alt="Product" class="w-12 h-12 object-cover rounded-lg bg-slate-50 border border-slate-100 shrink-0">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-slate-50 rounded-lg border border-slate-100 flex items-center justify-center text-slate-300 shrink-0">
                                                    <i data-lucide="package" class="w-5 h-5"></i>
                                                </div>
                                            <?php endif; ?>

                                            <div>
                                                <p class="font-bold text-slate-900 text-sm group-hover:text-primary transition-colors line-clamp-1">
                                                    <?php echo e($item->product_name); ?>

                                                </p>
                                                <?php if($item->sku_label): ?>
                                                    <p class="text-[11px] text-slate-400 font-medium mt-0.5"><?php echo e($item->sku_label); ?></p>
                                                <?php endif; ?>
                                                <p class="text-xs font-semibold text-slate-500 mt-0.5">Qty: <?php echo e($item->qty); ?></p>
                                            </div>
                                        </div>

                                        <div class="font-black text-slate-900 text-sm shrink-0">
                                            ₹<?php echo e(number_format($item->line_total, 2)); ?>

                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-slate-50/80 px-5 sm:px-6 py-3 border-t border-slate-100 flex justify-between items-center">
                        <p class="text-xs font-semibold text-slate-500">Need help with this order?</p>
                        <?php
                            $whatsapp = get_setting('whatsapp', null, $order->company_id);
                        ?>
                        <?php if($whatsapp): ?>
                            <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $whatsapp)); ?>?text=Hi, I need help with my order #<?php echo e($order->order_number); ?>" target="_blank"
                                class="text-xs font-bold text-primary hover:text-primaryDark transition-colors flex items-center gap-1.5">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Support
                            </a>
                        <?php else: ?>
                            <span class="text-xs font-medium text-slate-400">Contact Store Admin</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                
                <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-slate-200 shadow-sm">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="shopping-bag" class="w-10 h-10 text-primary"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-900">No orders found</h3>
                    <p class="text-slate-500 mt-2 max-w-sm mx-auto font-medium text-sm">It looks like you haven't placed any orders yet. Start shopping to fill this page!</p>
                    <a href="/<?php echo e($company->slug); ?>"
                        class="inline-flex items-center gap-2 mt-8 bg-primary hover:bg-primaryDark text-white px-6 py-3 rounded-xl font-bold transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5">
                        <i data-lucide="store" class="w-4 h-4"></i> Start Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>

        
        <?php if($orders->hasPages()): ?>
            <div class="mt-8 pt-4">
                <?php echo e($orders->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.customer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/customer/orders.blade.php ENDPATH**/ ?>