<?php $__env->startSection('title', 'Platform Dashboard'); ?>

<?php $__env->startSection('header', 'Platform Dashboard'); ?>

<?php $__env->startSection('content'); ?>

    <div class="space-y-8">

        
        <div class="grid md:grid-cols-4 gap-6">

            <div class="bg-white rounded-xl shadow-sm border p-5">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-gray-500">Total Companies</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">124</p>
                    </div>

                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>

                </div>
            </div>


            <div class="bg-white rounded-xl shadow-sm border p-5">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-gray-500">Active Subscriptions</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">89</p>
                    </div>

                    <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                    </div>

                </div>
            </div>


            <div class="bg-white rounded-xl shadow-sm border p-5">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-gray-500">Monthly Revenue</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">₹1,42,500</p>
                    </div>

                    <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="indian-rupee" class="w-5 h-5"></i>
                    </div>

                </div>
            </div>


            <div class="bg-white rounded-xl shadow-sm border p-5">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-gray-500">Active Plans</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">4</p>
                    </div>

                    <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>

                </div>
            </div>

        </div>


        
        <div class="bg-white rounded-xl border shadow-sm p-6">

            <h3 class="text-lg font-semibold mb-5 text-gray-700">
                Quick Actions
            </h3>

            <div class="grid md:grid-cols-4 gap-4">

                <a href="<?php echo e(route('platform.plans.index')); ?>" class="border rounded-lg p-4 hover:bg-gray-50 transition">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>

                        <div>
                            <p class="font-medium text-gray-700">Manage Plans</p>
                            <p class="text-xs text-gray-400">Edit pricing</p>
                        </div>

                    </div>

                </a>


                <a href="<?php echo e(route('platform.modules.index')); ?>" class="border rounded-lg p-4 hover:bg-gray-50 transition">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 bg-pink-100 text-pink-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="boxes" class="w-5 h-5"></i>
                        </div>

                        <div>
                            <p class="font-medium text-gray-700">Modules</p>
                            <p class="text-xs text-gray-400">Enable features</p>
                        </div>

                    </div>

                </a>


                <a href="<?php echo e(route('platform.subscriptions.index')); ?>"
                    class="border rounded-lg p-4 hover:bg-gray-50 transition">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                        </div>

                        <div>
                            <p class="font-medium text-gray-700">Subscriptions</p>
                            <p class="text-xs text-gray-400">Manage plans</p>
                        </div>

                    </div>

                </a>


                <a href="<?php echo e(route('platform.system.index')); ?>" class="border rounded-lg p-4 hover:bg-gray-50 transition">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="settings" class="w-5 h-5"></i>
                        </div>

                        <div>
                            <p class="font-medium text-gray-700">System Settings</p>
                            <p class="text-xs text-gray-400">Platform config</p>
                        </div>

                    </div>

                </a>

            </div>

        </div>


        
        <div class="bg-white rounded-xl border shadow-sm">

            <div class="p-6 border-b flex items-center justify-between">

                <h3 class="font-semibold text-gray-700">
                    Recent Payments
                </h3>

                <a href="#" class="text-sm text-brand-600 hover:underline">
                    View All
                </a>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead class="bg-gray-50 text-gray-500">

                        <tr>

                            <th class="text-left px-6 py-3 font-medium">
                                Company
                            </th>

                            <th class="text-left px-6 py-3 font-medium">
                                Plan
                            </th>

                            <th class="text-left px-6 py-3 font-medium">
                                Amount
                            </th>

                            <th class="text-left px-6 py-3 font-medium">
                                Status
                            </th>

                            <th class="text-left px-6 py-3 font-medium">
                                Date
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y">

                        <tr>

                            <td class="px-6 py-4">
                                Hardik Fastfood
                            </td>

                            <td class="px-6 py-4">
                                Pro Plan
                            </td>

                            <td class="px-6 py-4 font-medium">
                                ₹999
                            </td>

                            <td class="px-6 py-4">

                                <span class="px-2 py-1 text-xs bg-green-100 text-green-600 rounded">
                                    Paid
                                </span>

                            </td>

                            <td class="px-6 py-4">
                                12 Mar 2026
                            </td>

                        </tr>


                        <tr>

                            <td class="px-6 py-4">
                                Nature Care Nursery
                            </td>

                            <td class="px-6 py-4">
                                Starter Plan
                            </td>

                            <td class="px-6 py-4 font-medium">
                                ₹499
                            </td>

                            <td class="px-6 py-4">

                                <span class="px-2 py-1 text-xs bg-green-100 text-green-600 rounded">
                                    Paid
                                </span>

                            </td>

                            <td class="px-6 py-4">
                                10 Mar 2026
                            </td>

                        </tr>


                        <tr>

                            <td class="px-6 py-4">
                                Foodexo Restaurant
                            </td>

                            <td class="px-6 py-4">
                                Business Plan
                            </td>

                            <td class="px-6 py-4 font-medium">
                                ₹1999
                            </td>

                            <td class="px-6 py-4">

                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-600 rounded">
                                    Pending
                                </span>

                            </td>

                            <td class="px-6 py-4">
                                8 Mar 2026
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>


    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/platform/dashboard.blade.php ENDPATH**/ ?>