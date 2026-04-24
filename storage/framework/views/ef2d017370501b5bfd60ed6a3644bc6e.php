

<?php $__env->startSection('title', 'Reports & Analytics'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Analytics</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $formatAmt = fn($amount) => number_format((float) $amount, 2, '.', ',');
    ?>

    <div class="pb-10" x-data="reportDashboard()">

        
        <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#212538] tracking-tight mb-1">Reports & Analytics</h1>
                <p class="text-sm text-gray-500 font-medium">Overview of your business performance.</p>
            </div>

            
            <form id="filterForm" action="<?php echo e(route('admin.reports.index')); ?>" method="GET" class="flex items-center gap-2">
                <div x-data="{ customDate: '<?php echo e($activeFilter === 'custom'); ?>' }" class="flex items-center gap-2">

                    
                    <select name="date_filter"
                        x-on:change="if($event.target.value !== 'custom') { document.getElementById('filterForm').submit(); } else { customDate = true; }"
                        class="bg-white border border-gray-300 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-bold focus:border-gray-500 outline-none shadow-sm cursor-pointer">
                        <option value="today" <?php echo e($activeFilter == 'today' ? 'selected' : ''); ?>>Today</option>
                        <option value="this_week" <?php echo e($activeFilter == 'this_week' ? 'selected' : ''); ?>>This Week</option>
                        <option value="this_month" <?php echo e($activeFilter == 'this_month' ? 'selected' : ''); ?>>This Month</option>
                        <option value="this_year" <?php echo e($activeFilter == 'this_year' ? 'selected' : ''); ?>>This Year</option>
                        <option value="custom" <?php echo e($activeFilter == 'custom' ? 'selected' : ''); ?>>Custom Range...</option>
                    </select>

                    
                    <template x-if="customDate">
                        <div class="flex items-center gap-2 animate-fade-in">
                            <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>" required
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-500 shadow-sm">
                            <span class="text-gray-400 font-bold">to</span>
                            <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>" required
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-500 shadow-sm">
                            <button type="submit"
                                class="bg-[#212538] text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-black transition-colors shadow-sm">Apply</button>
                            <a href="<?php echo e(route('admin.reports.index')); ?>" class="text-red-500 hover:text-red-700 p-2"><i
                                    data-lucide="x" class="w-4 h-4"></i></a>
                        </div>
                    </template>
                </div>
            </form>
        </div>

        
        <div
            class="mb-6 inline-block bg-[#212538] text-white px-3 py-1 rounded text-xs font-black uppercase tracking-widest shadow-sm">
            Showing Data For: <?php echo e($filterLabel); ?>

        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

            
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-16 h-16 bg-green-50 rounded-bl-full flex items-start justify-end p-3">
                    <i data-lucide="trending-up" class="w-5 h-5 text-[#108c2a]"></i>
                </div>
                <div>
                    <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1">Net Sales</h3>
                    <div class="text-3xl font-black text-gray-900">₹<?php echo e($formatAmt($salesSummary['net_sales'])); ?></div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between text-[12px]">
                    <div><span class="text-gray-500 font-medium">Gross:</span> <span
                            class="font-bold text-gray-800">₹<?php echo e($formatAmt($salesSummary['gross_sales'])); ?></span></div>
                    <div><span class="text-gray-500 font-medium">Returns:</span> <span
                            class="font-bold text-red-500">₹<?php echo e($formatAmt($salesSummary['returns'])); ?></span></div>
                </div>
            </div>

            
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-16 h-16 bg-blue-50 rounded-bl-full flex items-start justify-end p-3">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1">Net Purchases</h3>
                    <div class="text-3xl font-black text-gray-900">₹<?php echo e($formatAmt($purchaseSummary['net_purchases'])); ?>

                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between text-[12px]">
                    <div><span class="text-gray-500 font-medium">Gross:</span> <span
                            class="font-bold text-gray-800">₹<?php echo e($formatAmt($purchaseSummary['total_purchases'])); ?></span>
                    </div>
                    <div><span class="text-gray-500 font-medium">Returns:</span> <span
                            class="font-bold text-red-500">₹<?php echo e($formatAmt($purchaseSummary['purchase_returns'])); ?></span>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
                <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-4">Sales By Source</h3>
                <div class="flex-1 flex flex-col justify-center space-y-3 text-sm">
                    <?php $__empty_1 = true; $__currentLoopData = $salesBySource; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-2 h-2 rounded-full <?php echo e($source->source == 'pos' ? 'bg-orange-500' : ($source->source == 'online' ? 'bg-blue-500' : 'bg-gray-500')); ?>"></span>
                                <span
                                    class="font-bold text-gray-700 uppercase text-[11px] tracking-widest"><?php echo e($source->source); ?></span>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-gray-900">₹<?php echo e($formatAmt($source->total_revenue)); ?></div>
                                <div class="text-[10px] text-gray-400 font-medium"><?php echo e($source->invoice_count); ?> Invoices
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-gray-400 text-xs text-center font-medium italic">No sales data for this period.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                    <i data-lucide="award" class="w-4 h-4 text-[#108c2a]"></i>
                    <h2 class="text-[13px] font-black text-gray-800 uppercase tracking-widest">Top Selling Products</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead
                            class="text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3 text-center">Qty Sold</th>
                                <th class="px-6 py-3 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php $__empty_1 = true; $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="font-bold text-gray-900"><?php echo e($product->product_name); ?></div>
                                        <div class="text-[10px] text-gray-400 font-mono mt-0.5"><?php echo e($product->sku_code); ?>

                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-center font-black text-[#108c2a]">
                                        <?php echo e((float) $product->total_qty_sold); ?></td>
                                    <td class="px-6 py-3 text-right font-black text-gray-900">
                                        ₹<?php echo e($formatAmt($product->total_revenue)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-400 text-xs font-medium">No
                                        sales data found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                    <i data-lucide="trending-down" class="w-4 h-4 text-red-500"></i>
                    <h2 class="text-[13px] font-black text-gray-800 uppercase tracking-widest">Low Selling Products</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead
                            class="text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3 text-center">Qty Sold</th>
                                <th class="px-6 py-3 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php $__empty_1 = true; $__currentLoopData = $lowProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="font-bold text-gray-900"><?php echo e($product->product_name); ?></div>
                                        <div class="text-[10px] text-gray-400 font-mono mt-0.5"><?php echo e($product->sku_code); ?>

                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-center font-black text-red-500">
                                        <?php echo e((float) $product->total_qty_sold); ?></td>
                                    <td class="px-6 py-3 text-right font-black text-gray-900">
                                        ₹<?php echo e($formatAmt($product->total_revenue)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-400 text-xs font-medium">No
                                        sales data found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function reportDashboard() {
            return {
                init() {
                    // Re-initialize Lucide icons if navigating via Livewire/Turbolinks
                    if (typeof lucide !== 'undefined') {
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/reports/analytics.blade.php ENDPATH**/ ?>