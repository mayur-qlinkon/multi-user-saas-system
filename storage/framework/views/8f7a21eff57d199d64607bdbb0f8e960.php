<?php $__env->startSection('title', 'Products Management - Qlinkon BIZNESS'); ?>
<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Products</h1>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="space-y-6 pb-10" x-data="productTable()">

        
        <?php if(session('success')): ?>
            <div class="bg-green-50 text-green-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-green-100 mb-6 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> <?php echo e(session('success')); ?>

            </div>
              <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('success')); ?>", 'success'));
            </script>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="bg-red-50 text-red-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6 flex items-center gap-2">
                <i data-lucide="alert-octagon" class="w-5 h-5"></i> <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="bg-[#fee2e2] text-[#ef4444] px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix the following errors:</div>
                <ul class="list-disc list-inside pl-7 text-xs font-medium space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="flex justify-end mb-4">
            <form action="<?php echo e(route('admin.products.index')); ?>" method="GET"
                class="flex flex-col sm:flex-row flex-wrap w-full md:w-auto gap-3 items-stretch sm:items-center">

                
                <div class="flex w-full sm:w-auto sm:flex-1 lg:flex-none lg:w-[320px]">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                        placeholder="Search Product Name or SKU..."
                        class="min-w-0 w-full flex-1 border border-gray-200 rounded-l-lg px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none placeholder:text-gray-400 bg-white">
                    <button type="submit"
                        class="shrink-0 px-4 sm:px-5 py-2.5 text-sm font-semibold text-white bg-[#108c2a] hover:bg-[#0e7a24] rounded-r-lg border border-l-0 border-[#108c2a] transition-colors">
                        Search
                    </button>
                </div>

                
                <div class="relative w-full sm:w-auto sm:flex-1 lg:flex-none lg:w-[220px]">
                    <select name="category_id" onchange="this.form.submit()"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 pr-10 text-sm text-gray-600 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none appearance-none cursor-pointer bg-white shadow-sm">
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
                    class="shrink-0 w-full sm:w-auto bg-white hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-lg text-sm font-medium flex items-center justify-center transition-colors shadow-sm">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">

            <div class="px-4 sm:px-6 py-4 flex flex-col md:flex-row md:justify-between md:items-center border-b border-gray-100 gap-4 bg-white">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest">
                    Product Catalog <span class="text-gray-400 font-medium text-sm ml-1">(<?php echo e($products->total()); ?>

                        items)</span>
                </h2>

                <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2 w-full md:w-auto md:justify-end">
                    <button
                        x-show="selected.length > 0"
                        x-transition.opacity
                        x-cloak
                        @click="confirmBulkDelete()"
                        class="bg-[#ef4444] hover:bg-red-600 text-white px-4 py-2.5 rounded-lg text-sm font-bold flex items-center justify-center gap-1.5 transition-colors shadow-sm whitespace-nowrap w-full md:w-auto">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Bulk Delete (<span x-text="selected.length"></span>)
                    </button>

                    <?php if(check_plan_limit('products')): ?>
                        <?php if(has_permission('products.create')): ?>
                        <a href="<?php echo e(route('admin.products.create')); ?>"
                            class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2.5 rounded-lg text-sm font-bold flex items-center justify-center gap-1.5 transition-colors shadow-sm whitespace-nowrap w-full md:w-auto">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add Product
                        </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-red-50 text-red-600 text-xs font-bold border border-red-100">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            Product Limit Reached
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="hidden md:block overflow-x-auto min-h-[400px]">
                <table class="w-full text-left text-sm whitespace-nowrap min-w-[1000px]">
                    <thead
                        class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-[#f8fafc]">
                        <tr
                            class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-[#f8fafc]">
                            <th class="px-6 py-4 w-10">
                                <input type="checkbox"
                                    @change="selected = $event.target.checked ? [<?php echo e($products->pluck('id')->join(',')); ?>] : []"
                                    :checked="selected.length > 0 && selected.length === <?php echo e($products->count()); ?>"
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
                                        x-model.number="selected"
                                        value="<?php echo e($product->id); ?>"
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
                                    $hasSku = $product->skus->isNotEmpty();
                                    $minPrice = $hasSku ? ($product->skus->min('price') ?? 0) : 0;
                                    $maxPrice = $hasSku ? ($product->skus->max('price') ?? 0) : 0;
                                    $totalVariants = $product->skus->count();
                                    $totalStock = $hasSku ? $product->skus->sum('total_stock') : 0;
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
                                        
                                        <?php if(has_permission('products.view')): ?>
                                        <a href="<?php echo e(route('admin.products.show', $product->id)); ?>"
                                            class="hover:text-indigo-500 transition-colors" title="View">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <?php endif; ?>

                                        <?php if(has_permission('products.update')): ?>
                                        <a href="<?php echo e(route('admin.products.edit', $product->id)); ?>"
                                            class="hover:text-blue-500 transition-colors" title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <?php endif; ?>

                                        <?php if(has_permission('products.duplicate')): ?>
                                        <form action="<?php echo e(route('admin.products.duplicate', $product->id)); ?>"
                                                method="POST" class="inline-block m-0 p-0">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="hover:text-amber-500 transition-colors" title="Duplicate">
                                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                                </button>
                                        </form>
                                        <?php endif; ?>

                                        <?php if(has_permission('products.delete')): ?>
                                        <form action="<?php echo e(route('admin.products.destroy', $product->id)); ?>" method="POST"
                                            @submit.prevent="confirmDelete($event.target)" class="inline-block m-0 p-0">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="hover:text-red-500 transition-colors"
                                                title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?> 
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

                
                <div class="md:hidden divide-y divide-gray-50 border-t border-gray-50">
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $hasSku = $product->skus->isNotEmpty();
                            $minPrice = $hasSku ? ($product->skus->min('price') ?? 0) : 0;
                            $maxPrice = $hasSku ? ($product->skus->max('price') ?? 0) : 0;
                            $totalVariants = $product->skus->count();
                            $totalStock = $hasSku ? $product->skus->sum('total_stock') : 0;
                        ?>
                        <div class="p-4 hover:bg-gray-50/50 transition-colors flex flex-col gap-3">
                            
                            
                            <div class="flex items-start gap-3">
                                <div class="pt-1">
                                    <input type="checkbox" x-model.number="selected" value="<?php echo e($product->id); ?>"
                                        class="rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] w-4 h-4 cursor-pointer">
                                </div>
                                <div class="w-12 h-12 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center shrink-0">
                                    <img src="<?php echo e($product->primary_image_url); ?>" alt="Img" class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-[14px] text-gray-900 leading-tight truncate"><?php echo e($product->name); ?></p>
                                    <p class="text-[11px] text-gray-500 mt-0.5 font-medium">
                                        <?php echo e($product->category->name ?? 'Uncategorized'); ?> • <?php echo e($product->productUnit->short_name ?? 'Pc'); ?>

                                    </p>
                                </div>
                            </div>

                            
                            <div class="flex items-center justify-between bg-gray-50/80 px-3 py-2.5 rounded-lg border border-gray-100">
                                <div>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Price</p>
                                    <p class="text-[12px] font-bold text-gray-800">
                                        <?php if($minPrice == $maxPrice): ?>
                                            ₹<?php echo e(number_format($minPrice, 2)); ?>

                                        <?php else: ?>
                                            ₹<?php echo e(number_format($minPrice, 2)); ?> - ₹<?php echo e(number_format($maxPrice, 2)); ?>

                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Type</p>
                                    <p class="text-[11px] font-bold text-gray-600"><?php echo e($product->type === 'variable' ? $totalVariants . ' Variants' : 'Single'); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Stock</p>
                                    <p class="text-[12px] font-black <?php echo e($totalStock > 0 ? 'text-[#108c2a]' : 'text-red-500'); ?>">
                                        <?php echo e($totalStock); ?>

                                    </p>
                                </div>
                            </div>

                            
                            <div class="flex items-center justify-between pt-2 border-t border-gray-50 mt-1">
                                <span class="text-[10px] text-gray-400 font-medium">Added: <?php echo e($product->created_at->format('d M, y')); ?></span>
                                <div class="flex items-center justify-end gap-1.5">
                                    <?php if(has_permission('products.view')): ?>
                                        <a href="<?php echo e(route('admin.products.show', $product->id)); ?>" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-indigo-600 flex items-center justify-center transition-colors" title="View">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if(has_permission('products.update')): ?>
                                        <a href="<?php echo e(route('admin.products.edit', $product->id)); ?>" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors" title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if(has_permission('products.duplicate')): ?>
                                        <form action="<?php echo e(route('admin.products.duplicate', $product->id)); ?>" method="POST" class="inline-block m-0 p-0">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition-colors" title="Duplicate">
                                                <i data-lucide="copy" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if(has_permission('products.delete')): ?>
                                        <form action="<?php echo e(route('admin.products.destroy', $product->id)); ?>" method="POST" @submit.prevent="confirmDelete($event.target)" class="inline-block m-0 p-0">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition-colors" title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?> 
                                </div>
                            </div>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-8 text-center text-gray-400 bg-white">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="package-open" class="w-12 h-12 mb-3 opacity-20"></i>
                                <p class="font-medium text-gray-500 text-sm">No products found in your inventory.</p>
                                <a href="<?php echo e(route('admin.products.create')); ?>" class="text-[#108c2a] font-bold mt-2 text-sm hover:underline">Add your first product</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if(method_exists($products, 'links') && $products->hasPages()): ?>
                <div class="px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4">
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
                selected: [], // Array to hold selected product IDs

                // New Bulk Delete Function
                confirmBulkDelete() {
                    BizAlert.confirm(
                        'Delete Selected Products?',
                        `You are about to archive ${this.selected.length} products. This action cannot be undone.`,
                        'Yes, Archive Them'
                    ).then(async (result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Archiving...');
                            
                            try {
                                const response = await fetch('<?php echo e(route("admin.products.bulk-delete")); ?>', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ ids: this.selected })
                                });

                                const data = await response.json();

                                if (data.success) {
                                    // Let the session flash message handle the success alert on reload
                                    window.location.reload();
                                } else {
                                    BizAlert.toast(data.message || 'Failed to delete products', 'error');
                                }
                            } catch (error) {
                                console.error(error);
                                BizAlert.toast('Network error. Try again.', 'error');
                            }
                        }
                    });
                },

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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas\resources\views/admin/products/index.blade.php ENDPATH**/ ?>