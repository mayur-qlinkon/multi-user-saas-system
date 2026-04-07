

<?php $__env->startSection('title', $query ? "Search: {$query} — {$company->name}" : "Search — {$company->name}"); ?>

<?php $__env->startSection('content'); ?>

<div class="flex-1 max-w-[1400px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col lg:flex-row gap-8 lg:gap-10">

    
    <aside class="hidden lg:block w-[220px] shrink-0">
        <div class="sticky top-[100px] bg-white border border-gray-200 p-4 rounded-2xl shadow-sm">
            <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4">Categories</h3>
            <nav class="grid gap-2">
                <a href="<?php echo e(route('storefront.index', $company->slug)); ?>"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-[13px] font-semibold transition-colors text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                    All Categories
                </a>
                <?php $__currentLoopData = $navCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('storefront.category', ['slug' => $company->slug, 'categorySlug' => $cat->slug])); ?>"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-[13px] transition-colors text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                        <?php echo e($cat->name); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>
        </div>
    </aside>

    
    <div class="flex-1 min-w-0 pb-12">

        
        <div class="mb-6" x-data="searchDropdown()">
            <form @submit.prevent="goToSearch()" class="relative">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none z-10"></i>
                <input type="text"
                    x-model="query"
                    @input.debounce.300ms="suggest()"
                    @keydown.enter="goToSearch()"
                    @click.away="open = false"
                    placeholder="Search for products..."
                    class="w-full bg-white border border-gray-200 rounded-full py-3 pl-11 pr-12 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:border-transparent transition-shadow shadow-sm"
                    style="--tw-ring-color: var(--brand-600)">
                <button type="submit"
                    class="absolute right-3 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-full text-xs font-bold text-white transition-colors"
                    style="background: var(--brand-600);">
                    Search
                </button>

                
                <div x-show="open" x-cloak
                    class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50">
                    <template x-if="!loading && results.length > 0">
                        <div>
                            <template x-for="product in results" :key="product.slug">
                                <a :href="'/' + companySlug + '/product/' + product.slug"
                                    class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors">
                                    <img :src="product.image"
                                        class="w-9 h-9 rounded-lg object-cover flex-shrink-0 bg-gray-100"
                                        onerror="this.src='<?php echo e(asset('assets/images/no-product.png')); ?>'">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13px] font-semibold text-gray-800 truncate" x-text="product.name"></p>
                                        <p class="text-[12px] text-gray-400">₹<span x-text="parseFloat(product.price).toFixed(2)"></span></p>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </form>
        </div>

        
        <?php if($query): ?>
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h1 class="text-lg font-bold text-gray-800">
                        <?php if($total > 0): ?>
                            <?php echo e($total); ?> result<?php echo e($total === 1 ? '' : 's'); ?> for
                            <span style="color: var(--brand-600)">"<?php echo e($query); ?>"</span>
                        <?php else: ?>
                            No results for <span class="text-gray-500">"<?php echo e($query); ?>"</span>
                        <?php endif; ?>
                    </h1>
                    <?php if($total > 0): ?>
                        <p class="text-sm text-gray-400 mt-0.5">Showing <?php echo e($products->firstItem()); ?>–<?php echo e($products->lastItem()); ?> of <?php echo e($total); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if(!$query): ?>
            <div class="flex flex-col items-center justify-center py-24 text-center text-gray-400">
                <i data-lucide="search" class="w-14 h-14 mb-4 opacity-20"></i>
                <p class="text-lg font-semibold text-gray-500 mb-1">Search our catalogue</p>
                <p class="text-sm">Type a product name above to get started</p>
            </div>

        
        <?php elseif($total === 0): ?>
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                    <i data-lucide="search-x" class="w-8 h-8 text-gray-300"></i>
                </div>
                <h2 class="text-base font-bold text-gray-700 mb-1">Nothing found for "<?php echo e($query); ?>"</h2>
                <p class="text-sm text-gray-400 mb-6">Try a different word or browse our categories</p>
                <a href="<?php echo e(route('storefront.index', $company->slug)); ?>"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white"
                    style="background: var(--brand-600);">
                    <i data-lucide="home" class="w-4 h-4"></i>
                    Back to Home
                </a>
            </div>

        
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5 mb-8">
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $firstSku  = $product->skus->first();
                        $price     = $firstSku?->price ?? 0;
                        $cost      = $firstSku?->cost ?? 0;
                        $isNew     = $product->created_at->diffInDays(now()) <= 14;
                        $discount  = $cost > 0 && $price < $cost ? round((($cost - $price) / $cost) * 100) : 0;
                    ?>

                    <a href="<?php echo e(route('storefront.product', ['slug' => $company->slug, 'productSlug' => $product->slug])); ?>"
                        class="group block bg-white rounded-xl border border-gray-100 hover:border-gray-200 hover:shadow-md transition-all overflow-hidden">

                        <div class="bg-[#f8f9fa] aspect-square overflow-hidden relative">
                            <img src="<?php echo e($product->primary_image_url); ?>"
                                alt="<?php echo e($product->name); ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                                onerror="this.src='<?php echo e(asset('assets/images/no-product.png')); ?>'">

                            <?php if($discount > 0): ?>
                                <span class="absolute top-2 left-2 text-[9px] font-black px-2 py-0.5 rounded bg-red-100 text-red-600 uppercase tracking-wide">
                                    <?php echo e($discount); ?>% off
                                </span>
                            <?php elseif($isNew): ?>
                                <span class="absolute top-2 left-2 text-[9px] font-black px-2 py-0.5 rounded bg-green-100 text-green-700 uppercase tracking-wide">
                                    New
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="p-3">
                            <h3 class="text-[13px] sm:text-[14px] font-semibold text-gray-800 leading-[1.3] line-clamp-2 mb-2 group-hover:text-brand-600 transition-colors">
                                <?php echo e($product->name); ?>

                            </h3>
                            <div class="flex items-baseline gap-1.5 flex-wrap">
                                <span class="font-bold text-[15px] text-gray-900">
                                    ₹<?php echo e(number_format($price, 2)); ?>

                                </span>
                                <?php if($discount > 0): ?>
                                    <span class="text-[11px] text-gray-400 line-through">₹<?php echo e(number_format($cost, 2)); ?></span>
                                    <span class="text-[11px] font-bold text-green-600"><?php echo e($discount); ?>% off</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <?php if($products->hasPages()): ?>
                <div class="flex items-center justify-center gap-2 flex-wrap">

                    
                    <?php if($products->onFirstPage()): ?>
                        <span class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-300 cursor-not-allowed">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo e($products->previousPageUrl()); ?>"
                            class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-colors">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>

                    
                    <?php $__currentLoopData = $products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $products->currentPage()): ?>
                            <span class="w-9 h-9 flex items-center justify-center rounded-xl text-sm font-bold text-white"
                                style="background: var(--brand-600);">
                                <?php echo e($page); ?>

                            </span>
                        <?php else: ?>
                            <a href="<?php echo e($url); ?>"
                                class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-colors">
                                <?php echo e($page); ?>

                            </a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <?php if($products->hasMorePages()): ?>
                        <a href="<?php echo e($products->nextPageUrl()); ?>"
                            class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-colors">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    <?php else: ?>
                        <span class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-300 cursor-not-allowed">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.storefront', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/storefront/search.blade.php ENDPATH**/ ?>