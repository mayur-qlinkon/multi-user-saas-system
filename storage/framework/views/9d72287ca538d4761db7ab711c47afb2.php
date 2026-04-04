

<?php $__env->startSection('title', 'Products Management - Qlinkon BIZNESS'); ?>
<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Catalog / Products</h1>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="space-y-6 pb-10" x-data="productTable()">

        <?php if(session('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('success')); ?>", 'success'));
            </script>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row justify-end items-center gap-3 mb-2">
            <form action="<?php echo e(route('admin.products.index')); ?>" method="GET"
                class="flex flex-col md:flex-row w-full md:w-auto gap-3">

                <div class="w-full md:w-80 relative">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                        placeholder="Search Product Name or SKU..."
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none transition-all placeholder:text-gray-400 bg-white shadow-sm">
                </div>

                <div class="w-full md:w-64 relative">
                    <select name="category_id" onchange="this.form.submit()"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-600 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none appearance-none cursor-pointer bg-white shadow-sm font-bold">
                        <option value="">All Categories</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category_id') == $cat->id ? 'selected' : ''); ?>>
                                <?php echo e($cat->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <i data-lucide="chevron-down"
                        class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>

                <a href="<?php echo e(route('admin.products.index')); ?>" title="Reset Filters"
                    class="w-full md:w-auto bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-lg text-sm font-bold flex items-center justify-center transition-colors shadow-sm">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">

            <div
                class="px-6 py-4 flex flex-col sm:flex-row sm:justify-between sm:items-center border-b border-gray-100 gap-4 bg-white">
                <h2 class="text-[1.1rem] font-bold text-[#212538] tracking-tight">
                    Product Catalog <span class="text-gray-400 font-medium text-sm ml-1">(<?php echo e($products->total()); ?>

                        items)</span>
                </h2>

                <div class="flex items-center gap-2.5">
                    <button
                        class="bg-[#ef4444] hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-1.5 transition-colors shadow-sm hidden">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Bulk Delete
                    </button>

                    <a href="<?php echo e(route('admin.products.create')); ?>"
                        class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-1.5 transition-colors shadow-sm">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Product
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto min-h-[400px]">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-[#f8fafc]">
                        <tr
                            class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-[#f8fafc]">
                            <th class="px-6 py-4 w-10">
                                <input type="checkbox"
                                    class="rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] w-4 h-4 cursor-pointer">
                            </th>
                            <th class="px-6 py-4">PRODUCT</th>
                            <th class="px-6 py-4">NAME</th>
                            <th class="px-6 py-4">VARIANTS</th>
                            <th class="px-6 py-4">CATEGORY</th>
                            <th class="px-6 py-4">PRICE</th>
                            <th class="px-6 py-4">PRODUCT UNIT</th>
                            <th class="px-6 py-4">IN STOCK</th>
                            <th class="px-6 py-4">CREATED ON</th>
                            <th class="px-6 py-4 text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr
                                class="hover:bg-gray-50/50 transition-colors border-b border-gray-50 text-[13px] text-gray-600">
                                
                                <td class="px-6 py-4">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] w-4 h-4 cursor-pointer">
                                </td>

                                
                                <td class="px-6 py-4">
                                    <div
                                        class="w-10 h-10 rounded border border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center">
                                        <img src="<?php echo e($product->primary_image_url); ?>" alt="Img"
                                            class="w-full h-full object-cover">
                                    </div>
                                </td>

                                
                                <?php
                                    $minPrice = $product->skus->min('price') ?? 0;
                                    $maxPrice = $product->skus->max('price') ?? 0;
                                    $totalVariants = $product->skus->count();
                                    $totalStock = $product->skus->sum('total_stock');
                                ?>

                                
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    <?php echo e($product->name); ?>

                                </td>

                                
                                <td class="px-6 py-4 text-gray-500">
                                    <?php echo e($product->type === 'variable' ? $totalVariants . ' Variants' : 'Single'); ?>

                                </td>

                                
                                <td class="px-6 py-4 text-gray-500">
                                    <?php echo e($product->category->name ?? 'N/A'); ?>

                                </td>

                                
                                <td class="px-6 py-4 font-medium text-gray-700">
                                    <?php if($minPrice == $maxPrice): ?>
                                        ₹<?php echo e(number_format($minPrice, 2)); ?>

                                    <?php else: ?>
                                        ₹<?php echo e(number_format($minPrice, 2)); ?> - ₹<?php echo e(number_format($maxPrice, 2)); ?>

                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4 text-gray-500">
                                    <?php echo e($product->productUnit->short_name ?? 'Pc'); ?>

                                </td>

                                
                                <td
                                    class="px-6 py-4 font-medium <?php echo e($totalStock > 0 ? 'text-[#108c2a]' : 'text-red-500'); ?>">
                                    <?php echo e($totalStock); ?>

                                </td>

                                
                                <td class="px-6 py-4 text-gray-500">
                                    <?php echo e($product->created_at->format('d M, Y')); ?>

                                </td>

                                
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3 text-gray-400">
                                        <a href="<?php echo e(route('admin.products.show', $product->id)); ?>"
                                            class="hover:text-indigo-500 transition-colors" title="View">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?php echo e(route('admin.products.edit', $product->id)); ?>"
                                            class="hover:text-blue-500 transition-colors" title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <form action="<?php echo e(route('admin.products.duplicate', $product->id)); ?>"
                                                method="POST" class="inline-block m-0 p-0">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="hover:text-amber-500 transition-colors" title="Duplicate">
                                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                                </button>
                                        </form>
                                        <form action="<?php echo e(route('admin.products.destroy', $product->id)); ?>" method="POST"
                                            @submit.prevent="confirmDelete($event.target)" class="inline-block m-0 p-0">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="hover:text-red-500 transition-colors"
                                                title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="package-open" class="w-12 h-12 mb-3 opacity-20"></i>
                                        <p class="font-medium text-gray-500">No products found in your inventory.</p>
                                        <a href="<?php echo e(route('admin.products.create')); ?>"
                                            class="text-[#108c2a] font-bold mt-2 hover:underline">Add your first
                                            product</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if(method_exists($products, 'links') && $products->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                    <?php echo e($products->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function productTable() {
            return {
                confirmDelete(form) {
                    BizAlert.confirm(
                        'Delete Product?',
                        'This product will be archived. Historical sales data will remain intact.',
                        'Yes, Archive it'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Archiving...');
                            form.submit();
                        }
                    });
                },

                // The fully functional AJAX Toggle!
                async toggleStatus(productId, isActive) {
                    try {
                        const response = await fetch(`/admin/products/${productId}/toggle-status`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            const statusText = data.is_active ? 'Activated' : 'Deactivated';
                            BizAlert.toast(`Product has been ${statusText}`, 'success');
                        } else {
                            BizAlert.toast('Failed to update status', 'error');
                            // Revert the toggle visually if it failed on the server
                            event.target.checked = !isActive;
                        }
                    } catch (error) {
                        console.error(error);
                        BizAlert.toast('Network error. Try again.', 'error');
                        event.target.checked = !isActive;
                    }
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/products/index.blade.php ENDPATH**/ ?>