

<?php $__env->startSection('title', 'Dashboard - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Dashboard</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        /* Hide scrollbar for quick actions but keep it swipeable */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Smooth hover lift for stat cards */
        .stat-card { transition: all 0.2s ease-in-out; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $formatAmt = fn($amount) => number_format((float) $amount, 2, '.', ',');
        
        $hour = now()->format('H');      
        // Prepare continuous 7-day data for the chart
        $chartDates = [];
        $salesData = [];
        $purchasesData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->format('Y-m-d');
            $chartDates[] = now()->subDays($i)->format('d M'); // e.g., "03 Apr"
            
            $salesData[] = $charts['weekly_sales'][$dateStr] ?? 0;
            $purchasesData[] = $charts['weekly_purchases'][$dateStr] ?? 0;
        }

        // Prepare Top Products Data
        $topProductsLabels = [];
        $topProductsSeries = [];
        foreach ($charts['top_products'] as $product) {
            $topProductsLabels[] = $product->product_name;
            $topProductsSeries[] = (float) $product->total_qty; // Casting to float for ApexCharts
        }

        // Prepare Top Customers Data (For later use or stacking)
        $topCustomersLabels = [];
        $topCustomersSeries = [];
        foreach ($charts['top_customers'] as $customer) {
            $topCustomersLabels[] = $customer->name;
            $topCustomersSeries[] = (float) $customer->total;
        }
    ?>

    <div class="pb-12">
        
        <?php if(session('warning')): ?>
            <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 rounded-r-lg mb-6 shadow-sm flex items-start gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5 text-amber-600"></i>
                <div class="text-sm font-medium"><?php echo e(session('warning')); ?></div>
            </div>
        <?php endif; ?>

        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 overflow-hidden">                                
            
            
            <div class="flex overflow-x-auto pb-1 hide-scrollbar gap-2 w-full lg:w-auto">
                <?php if(has_module('pos') && has_permission('pos.access')): ?>
                    <a href="<?php echo e(route('admin.pos.index')); ?>" target="_blank" class="whitespace-nowrap bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shrink-0">
                        <i data-lucide="shopping-cart" class="w-4 h-4 text-blue-600"></i> POS
                    </a>
                <?php endif; ?>
                <?php if(has_module('invoicing')): ?>
                    <?php if(has_permission('invoices.create')): ?>
                        <a href="<?php echo e(route('admin.invoices.create')); ?>" class="whitespace-nowrap bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shrink-0">
                            <i data-lucide="file-plus-2" class="w-4 h-4 text-brand-600"></i> Invoice
                        </a>
                    <?php endif; ?>
                    <?php if(has_permission('quotations.create')): ?>
                        <a href="<?php echo e(route('admin.quotations.create')); ?>" class="whitespace-nowrap bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shrink-0">
                            <i data-lucide="file-plus-2" class="w-4 h-4 text-brand-600"></i> Quotation
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if(has_module('ocr_scanner') && has_permission('ocr_scanner.access')): ?>
                    <a href="<?php echo e(route('admin.ocr-scanner.index')); ?>" class="whitespace-nowrap bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shrink-0">
                        <i data-lucide="scan" class="w-4 h-4 text-brand-600"></i> OCR
                    </a>
                <?php endif; ?>
                <?php if(has_module('challan') && has_permission('challans.create')): ?>
                    <a href="<?php echo e(route('admin.challans.create')); ?>" class="whitespace-nowrap bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shrink-0">
                        <i data-lucide="file-plus-2" class="w-4 h-4 text-brand-600"></i> Challan
                    </a>
                <?php endif; ?>
                <?php if(has_module('purchases') && has_permission('purchases.create')): ?>
                    <a href="<?php echo e(route('admin.purchases.create')); ?>" class="whitespace-nowrap bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shrink-0">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-purple-600"></i> Purchase
                    </a>
                <?php endif; ?>
                <?php if(has_module('crm') && has_permission('crm_leads.create')): ?>
                    <a href="<?php echo e(route('admin.crm.leads.create')); ?>" class="whitespace-nowrap bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 shrink-0">
                        <i data-lucide="user-plus" class="w-4 h-4 text-orange-500"></i> Lead
                    </a>
                <?php endif; ?>
            </div>
        </div>

      
        <?php if($is_owner): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                
                
                <a href="<?php echo e(route('admin.invoices.index')); ?>" class="stat-card bg-gradient-to-r from-cyan-400 to-blue-500 rounded-xl p-5 text-white relative overflow-hidden block">
                    <div class="flex justify-between items-start mb-2 relative z-10">
                        <div class="text-sm font-semibold tracking-wide">Sales (Month)</div>
                        <i data-lucide="shopping-cart" class="w-5 h-5 opacity-80"></i>
                    </div>
                    <div class="relative z-10 w-full overflow-hidden fit-number-wrapper flex items-center pr-12">
                        <div class="text-2xl font-black whitespace-nowrap origin-left inline-block fit-number-text">
                            ₹ <?php echo e($formatAmt($financials['sales_this_month'] ?? 0)); ?>

                        </div>
                    </div>
                    <i data-lucide="shopping-cart" class="w-24 h-24 absolute -right-4 -bottom-4 text-white opacity-10"></i>
                </a>

                <a href="<?php echo e(route('admin.purchases.index')); ?>" class="stat-card bg-gradient-to-r from-purple-500 to-indigo-500 rounded-xl p-5 text-white relative overflow-hidden block">
                    <div class="flex justify-between items-start mb-2 relative z-10">
                        <div class="text-sm font-semibold tracking-wide">Purchases (Month)</div>
                        <i data-lucide="shopping-bag" class="w-5 h-5 opacity-80"></i>
                    </div>
                    <div class="relative z-10 w-full overflow-hidden fit-number-wrapper flex items-center pr-12">
                        <div class="text-2xl font-black whitespace-nowrap origin-left inline-block fit-number-text">
                            ₹ <?php echo e($formatAmt($financials['purchases_this_month'] ?? 0)); ?>

                        </div>
                    </div>
                    <i data-lucide="shopping-bag" class="w-24 h-24 absolute -right-4 -bottom-4 text-white opacity-10"></i>
                </a>

                <a href="<?php echo e(route('admin.invoice-returns.index')); ?>" class="stat-card bg-gradient-to-r from-orange-400 to-orange-500 rounded-xl p-5 text-white relative overflow-hidden block">
                    <div class="flex justify-between items-start mb-2 relative z-10">
                        <div class="text-sm font-semibold tracking-wide">Sales Returns</div>
                        <i data-lucide="arrow-right" class="w-5 h-5 opacity-80"></i>
                    </div>
                    <div class="relative z-10 w-full overflow-hidden fit-number-wrapper flex items-center pr-12">
                        <div class="text-2xl font-black whitespace-nowrap origin-left inline-block fit-number-text">
                            ₹ <?php echo e($formatAmt($financials['sales_returns_month'] ?? 0)); ?>

                        </div>
                    </div>
                    <i data-lucide="arrow-right" class="w-24 h-24 absolute -right-4 -bottom-4 text-white opacity-10"></i>
                </a>

                <a href="<?php echo e(route('admin.purchase-returns.index')); ?>" class="stat-card bg-gradient-to-r from-blue-400 to-cyan-500 rounded-xl p-5 text-white relative overflow-hidden block">
                    <div class="flex justify-between items-start mb-2 relative z-10">
                        <div class="text-sm font-semibold tracking-wide">Purchases Returns</div>
                        <i data-lucide="arrow-left" class="w-5 h-5 opacity-80"></i>
                    </div>
                    <div class="relative z-10 w-full overflow-hidden fit-number-wrapper flex items-center pr-12">
                        <div class="text-2xl font-black whitespace-nowrap origin-left inline-block fit-number-text">
                            ₹ <?php echo e($formatAmt($financials['purchase_returns_month'] ?? 0)); ?>

                        </div>
                    </div>
                    <i data-lucide="arrow-left" class="w-24 h-24 absolute -right-4 -bottom-4 text-white opacity-10"></i>
                </a>

                
                <a href="<?php echo e(route('admin.invoices.index')); ?>" class="stat-card bg-gradient-to-r from-yellow-400 to-amber-500 rounded-xl p-5 text-white relative overflow-hidden block">
                    <div class="flex justify-between items-start mb-2 relative z-10">
                        <div class="text-sm font-semibold tracking-wide">Today Total Sales</div>
                        <i data-lucide="indian-rupee" class="w-5 h-5 opacity-80"></i>
                    </div>
                    <div class="relative z-10 w-full overflow-hidden fit-number-wrapper flex items-center pr-12">
                        <div class="text-2xl font-black whitespace-nowrap origin-left inline-block fit-number-text">
                            ₹ <?php echo e($formatAmt($financials['sales_today'] ?? 0)); ?>

                        </div>
                    </div>
                    <i data-lucide="indian-rupee" class="w-24 h-24 absolute -right-4 -bottom-4 text-white opacity-10"></i>
                </a>

                <a href="#" class="stat-card bg-gradient-to-r from-emerald-400 to-green-500 rounded-xl p-5 text-white relative overflow-hidden block">
                    <div class="flex justify-between items-start mb-2 relative z-10">
                        <div class="text-sm font-semibold tracking-wide">Today Received (Sales)</div>
                        <i data-lucide="banknote" class="w-5 h-5 opacity-80"></i>
                    </div>
                    <div class="relative z-10 w-full overflow-hidden fit-number-wrapper flex items-center pr-12">
                        <div class="text-2xl font-black whitespace-nowrap origin-left inline-block fit-number-text">
                            ₹ <?php echo e($formatAmt($financials['received_today'] ?? 0)); ?>

                        </div>
                    </div>
                    <i data-lucide="banknote" class="w-24 h-24 absolute -right-4 -bottom-4 text-white opacity-10"></i>
                </a>

                <a href="<?php echo e(route('admin.purchases.index')); ?>" class="stat-card bg-gradient-to-r from-red-500 to-rose-600 rounded-xl p-5 text-white relative overflow-hidden block">
                    <div class="flex justify-between items-start mb-2 relative z-10">
                        <div class="text-sm font-semibold tracking-wide">Today Total Purchases</div>
                        <i data-lucide="layers" class="w-5 h-5 opacity-80"></i>
                    </div>
                    <div class="relative z-10 w-full overflow-hidden fit-number-wrapper flex items-center pr-12">
                        <div class="text-2xl font-black whitespace-nowrap origin-left inline-block fit-number-text">
                            ₹ <?php echo e($formatAmt($financials['purchases_today'] ?? 0)); ?>

                        </div>
                    </div>
                    <i data-lucide="layers" class="w-24 h-24 absolute -right-4 -bottom-4 text-white opacity-10"></i>
                </a>

                <a href="<?php echo e(route('admin.expenses.index')); ?>" class="stat-card bg-gradient-to-r from-fuchsia-500 to-purple-600 rounded-xl p-5 text-white relative overflow-hidden block">
                    <div class="flex justify-between items-start mb-2 relative z-10">
                        <div class="text-sm font-semibold tracking-wide">Today Total Expense</div>
                        <i data-lucide="minus-square" class="w-5 h-5 opacity-80"></i>
                    </div>
                    <div class="relative z-10 w-full overflow-hidden fit-number-wrapper flex items-center pr-12">
                        <div class="text-2xl font-black whitespace-nowrap origin-left inline-block fit-number-text">
                            ₹ <?php echo e($formatAmt($financials['expense_today'] ?? 0)); ?>

                        </div>
                    </div>
                    <i data-lucide="minus-square" class="w-24 h-24 absolute -right-4 -bottom-4 text-white opacity-10"></i>
                </a>

            </div>
        <?php endif; ?>
        
       
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            
            
            
            <div class="lg:col-span-2 flex flex-col gap-8">
                
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-sm font-black text-gray-800 uppercase tracking-wider">This Week Sales & Purchases</h2>
                        <i data-lucide="bar-chart-2" class="w-5 h-5 text-gray-400"></i>
                    </div>
                    <div id="weekly-chart" class="w-full h-[300px]"></div>
                </div>

                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-fit">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-black text-gray-800 uppercase tracking-wider">Top Selling Products (<?php echo e(now()->format('F')); ?>)</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="text-[10px] font-black text-gray-400 uppercase tracking-wider bg-white border-b border-gray-50">
                                <tr>
                                    <th class="px-6 py-4">Product</th>
                                    <th class="px-6 py-4 text-center">Quantity</th>
                                    <th class="px-6 py-4 text-right">Grand Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php $__empty_1 = true; $__currentLoopData = $charts['top_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 font-semibold text-gray-600"><?php echo e($product->product_name); ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-500 border border-indigo-100">
                                                <?php echo e((float) $product->total_qty); ?> <?php echo e($product->unit_name); ?>

                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium text-gray-700">₹ <?php echo e($formatAmt($product->total_revenue)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-400 font-medium text-sm">No top products found for this month.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            
            <div class="lg:col-span-1 flex flex-col gap-6">
                
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-sm font-black text-gray-800 uppercase tracking-wider">Top 5 Customers</h2>
                        <i data-lucide="users" class="w-5 h-5 text-gray-400"></i>
                    </div>
                    <?php if(empty($topCustomersSeries)): ?>
                        <div class="h-[220px] flex items-center justify-center text-gray-400 text-xs font-bold">No customer data yet</div>
                    <?php else: ?>
                        <div id="top-customers-chart" class="w-full h-[220px] flex justify-center"></div>
                    <?php endif; ?>
                </div>

                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-sm font-black text-gray-800 uppercase tracking-wider">Top Selling Products</h2>
                        <i data-lucide="package" class="w-5 h-5 text-gray-400"></i>
                    </div>
                    <?php if(empty($topProductsSeries)): ?>
                        <div class="h-[220px] flex items-center justify-center text-gray-400 text-xs font-bold">No product data yet</div>
                    <?php else: ?>
                        <div id="top-products-chart" class="w-full h-[220px] flex justify-center"></div>
                    <?php endif; ?>
                </div>
                

            </div>

        </div>

        

        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-2">
                    <i data-lucide="shopping-cart" class="w-5 h-5 text-gray-400"></i>
                    <h2 class="text-sm font-black text-gray-800 uppercase tracking-wider">Recent Sales</h2>
                </div>
                <a href="<?php echo e(route('admin.invoices.index')); ?>" class="text-xs font-bold text-brand-600 hover:text-brand-700 transition-colors">View All &rarr;</a>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-[10px] font-black text-gray-400 uppercase tracking-wider bg-white border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Reference</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Grand Total</th>
                            <th class="px-6 py-4 text-right">Paid</th>
                            <th class="px-6 py-4 text-right">Due</th>
                            <th class="px-6 py-4 text-center">Payment Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php $__empty_1 = true; $__currentLoopData = $tables['recent_sales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-700"><?php echo e($sale['reference']); ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-600"><?php echo e($sale['customer']); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider 
                                        <?php echo e($sale['status'] === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'); ?>">
                                        <?php echo e($sale['status']); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-gray-900">₹ <?php echo e($formatAmt($sale['grand_total'])); ?></td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-600">₹ <?php echo e($formatAmt($sale['paid'])); ?></td>
                                <td class="px-6 py-4 text-right font-bold <?php echo e($sale['due'] > 0 ? 'text-red-500' : 'text-gray-400'); ?>">
                                    ₹ <?php echo e($formatAmt($sale['due'])); ?>

                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php
                                        $payBadge = match($sale['payment_status']) {
                                            'paid' => 'bg-green-100 text-green-700',
                                            'partial' => 'bg-amber-100 text-amber-700',
                                            default => 'bg-red-100 text-red-700',
                                        };
                                    ?>
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider <?php echo e($payBadge); ?>">
                                        <?php echo e($sale['payment_status']); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-400 font-medium text-sm">No recent sales found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="bg-white rounded-xl shadow-sm border border-red-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-red-100 bg-red-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="siren" class="w-5 h-5 text-red-500"></i>
                    <h2 class="text-sm font-black text-red-800 uppercase tracking-wider">Stock Alerts</h2>
                </div>
                <?php if($tables['low_stock_skus']->count() > 0): ?>
                    <a href="<?php echo e(route('admin.inventory.reports.index')); ?>" class="text-xs font-bold text-red-600 hover:text-red-700 transition-colors">Inventory Report &rarr;</a>
                <?php endif; ?>
            </div>
            
            <div class="overflow-x-auto custom-scrollbar">
                <?php if($tables['low_stock_skus']->isEmpty()): ?>
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <i data-lucide="check-circle-2" class="w-12 h-12 mb-3 text-emerald-400 opacity-50"></i>
                        <span class="text-sm font-bold">All inventory levels are healthy!</span>
                    </div>
                <?php else: ?>
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-[10px] font-black text-gray-400 uppercase tracking-wider bg-white border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4">Code (SKU)</th>
                                <th class="px-6 py-4">Product</th>
                                <th class="px-6 py-4 text-center">Current Quantity</th>
                                <th class="px-6 py-4 text-center">Alert Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php $__currentLoopData = $tables['low_stock_skus']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sku): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-red-50/30 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs font-bold text-gray-500"><?php echo e($sku->sku); ?></td>
                                    <td class="px-6 py-4 font-bold text-gray-800"><?php echo e($sku->product->name ?? 'Unknown'); ?></td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold <?php echo e($sku->current_stock <= 0 ? 'bg-red-100 text-red-700' : 'bg-cyan-100 text-cyan-700'); ?>">
                                            <?php echo e((float) $sku->current_stock); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-rose-100 text-rose-700">
                                            <?php echo e((float) $sku->stock_alert); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('assets/js/apexcharts.min.js')); ?>"></script>

<script>
setTimeout(() => {
    
    (function() {
        var chartEl = document.querySelector("#weekly-chart");
        if (!chartEl) return;

        // Clear any old chart instance before rendering a new one (crucial for SPA)
        chartEl.innerHTML = '';

        var options = {
            series: [{
                name: 'Sales',
                data: <?php echo json_encode($salesData, 15, 512) ?>
            }, {
                name: 'Purchases',
                data: <?php echo json_encode($purchasesData, 15, 512) ?>
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '45%',
                    borderRadius: 4, 
                },
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 3, colors: ['transparent'] },
            xaxis: {
                categories: <?php echo json_encode($chartDates, 15, 512) ?>,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#9ca3af', fontSize: '12px', fontWeight: 500 } }
            },
            yaxis: {
                labels: { 
                    style: { colors: '#9ca3af', fontSize: '12px', fontWeight: 500 },
                    formatter: function (val) { return "₹" + val.toLocaleString(); }
                }
            },
            fill: { opacity: 1 },
            colors: ['#06b6d4', '#6366f1'], 
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                fontWeight: 600,
                markers: { radius: 12 }
            },
            grid: {
                borderColor: '#f3f4f6',
                strokeDashArray: 4,
            },
            tooltip: {
                y: { formatter: function (val) { return "₹ " + val.toLocaleString() } }
            }
        };

        var chart = new ApexCharts(chartEl, options);
        chart.render();
    })();

    // 🥧 Top Customers Pie Chart
    (function() {
        var el = document.querySelector("#top-customers-chart");
        if (!el) return;
        el.innerHTML = ''; 

        var options = {
            series: <?php echo json_encode($topCustomersSeries, 15, 512) ?>,
            labels: <?php echo json_encode($topCustomersLabels, 15, 512) ?>,
            chart: { type: 'pie', height: 250, fontFamily: 'inherit' },
            colors: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#06b6d4'],
            stroke: { width: 2, colors: ['#ffffff'] },
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontWeight: 500, markers: { radius: 12 } },
            tooltip: { y: { formatter: function (val) { return "₹ " + val.toLocaleString() } } }
        };
        new ApexCharts(el, options).render();
    })();

    // 🥧 Top Products Pie Chart
    (function() {
        var el = document.querySelector("#top-products-chart");
        if (!el) return;
        el.innerHTML = ''; 

        var options = {
            series: <?php echo json_encode($topProductsSeries, 15, 512) ?>,
            labels: <?php echo json_encode($topProductsLabels, 15, 512) ?>,
            chart: { type: 'pie', height: 250, fontFamily: 'inherit' },
            colors: ['#3b82f6', '#8b5cf6', '#ec4899', '#f43f5e', '#f97316'],
            stroke: { width: 2, colors: ['#ffffff'] },
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontWeight: 500, markers: { radius: 12 } },
            tooltip: { y: { formatter: function (val) { return val + " Units" } } }
        };
        new ApexCharts(el, options).render();
    })();

    // 🪗 Auto-Shrink Financial Numbers if they get too large
    (function() {
        const adjustTextSize = () => {
            document.querySelectorAll('.fit-number-wrapper').forEach(wrapper => {
                const text = wrapper.querySelector('.fit-number-text');
                if(!text) return;

                // Temporarily reset scale to measure the text's true, uncompressed width
                text.style.transform = 'scale(1)';
                
                let wrapperWidth = wrapper.clientWidth;
                let textWidth = text.scrollWidth;

                // If the text is wider than the wrapper, calculate the exact shrink ratio
                if (textWidth > wrapperWidth) {
                    let scaleValue = wrapperWidth / textWidth;
                    // Apply the scale. origin-left in CSS ensures it stays pinned to the left edge
                    text.style.transform = `scale(${scaleValue})`;
                }
            });
        };

        // Run immediately on load (especially important for SPA)
        adjustTextSize();
        
        // Listen for screen rotations or window resizing to recalculate
        window.addEventListener('resize', adjustTextSize);
        
        // Ensure it runs after SPA transitions are fully injected
        document.addEventListener("spa:page-loaded", adjustTextSize); // Swap this event name with your actual SPA custom event if you have one
    })();

}, 300);
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>