
<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="mb-6 lg:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Welcome back, <?php echo e(explode(' ', $user->name)[0]); ?>!</h1>
        <p class="text-sm text-slate-500 mt-1.5">Manage your orders and account details for <span class="font-semibold text-slate-700"><?php echo e($company->name); ?></span>.</p>
    </div>

    <div class="space-y-6">
        
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-5 transition-shadow hover:shadow-md">
                <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <i data-lucide="shopping-bag" class="w-6 h-6 text-primary"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Orders</p>
                    <p class="text-3xl font-black text-slate-900 leading-none"><?php echo e($totalOrdersCount); ?></p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-5 transition-shadow hover:shadow-md">
                <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
                    <i data-lucide="truck" class="w-6 h-6 text-blue-500"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Active Deliveries</p>
                    <p class="text-3xl font-black text-slate-900 leading-none">0</p>
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                <h2 class="text-base font-bold text-slate-900">Recent Orders</h2>
                <?php if($recentOrders->count() > 0): ?>
                    <a href="<?php echo e(route('storefront.portal.orders', ['slug' => $company->slug])); ?>" class="text-sm font-bold text-primary hover:text-primaryDark transition-colors">View All &rarr;</a>
                <?php endif; ?>
            </div>
            
            <?php if($recentOrders->count() > 0): ?>
                <div class="divide-y divide-slate-50">
                    <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-6 py-5 hover:bg-slate-50/80 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-3 mb-1.5">
                                    <span class="font-mono font-bold text-slate-900"><?php echo e($order->order_number); ?></span>
                                    
                                    
                                    <?php if($order->payment_status === 'paid'): ?>
                                        <span class="px-2.5 py-0.5 rounded-md bg-green-50 text-green-700 text-[10px] font-black uppercase tracking-wider border border-green-100">Paid</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-wider border border-amber-100">Pending</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-slate-500 font-medium">
                                    <?php echo e($order->created_at->format('M d, Y \a\t h:i A')); ?> • 
                                    <span class="text-slate-400"><?php echo e($order->items_count); ?> <?php echo e(Str::plural('item', $order->items_count)); ?></span>
                                </p>
                            </div>
                            
                            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto mt-2 sm:mt-0">
                                <p class="font-black text-slate-900 text-lg tracking-tight">₹<?php echo e(number_format($order->total_amount, 2)); ?></p>
                                <a href="<?php echo e(route('storefront.orders.receipt', ['slug' => $company->slug, 'orderNumber' => $order->order_number])); ?>" 
                                   class="w-9 h-9 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary hover:bg-primary/5 transition-all shadow-sm"
                                   title="Download Receipt" target="_blank">
                                    <i data-lucide="file-down" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="px-6 py-16 text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-5">
                        <i data-lucide="shopping-cart" class="w-8 h-8 text-slate-300"></i>
                    </div>
                    <h3 class="text-slate-900 font-bold text-lg mb-1">No orders yet</h3>
                    <p class="text-sm text-slate-500 mb-8 max-w-sm mx-auto">Looks like you haven't placed any orders with us yet. Start shopping to see them here!</p>
                    <a href="/<?php echo e($company->slug); ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5">
                        <i data-lucide="store" class="w-4 h-4"></i> Start Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.customer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/customer/dashboard.blade.php ENDPATH**/ ?>