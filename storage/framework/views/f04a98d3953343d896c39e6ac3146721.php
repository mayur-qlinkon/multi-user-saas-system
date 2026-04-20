

<?php $__env->startSection('title', 'Home - ' . $company->name); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <style>
        /* 🚫 Force hide scrollbar for a clean mobile look */
        .hide-scrollbar::-webkit-scrollbar {
            display: none !important;
        }
        .hide-scrollbar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        /* Swiper Customization specific to this page */
        .hero-swiper .swiper-button-next,
        .hero-swiper .swiper-button-prev {
            background-color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: #6b7280;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        .hero-swiper .swiper-button-next:after,
        .hero-swiper .swiper-button-prev:after {
            font-size: 16px;
            font-weight: bold;
        }

        .hero-swiper .swiper-button-next {
            right: 20px;
        }

        .hero-swiper .swiper-button-prev {
            left: 20px;
        }

        .hero-swiper .swiper-pagination-bullet {
            background-color: #d1d5db;
            opacity: 1;
            width: 18px;
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .hero-swiper .swiper-pagination-bullet-active {
            background-color: #4b5563;
            width: 24px;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    
    
    <div class="lg:hidden bg-white border-b border-gray-100/50 sticky top-[72px] z-30">
        
        <div class="flex overflow-x-auto py-4 hide-scrollbar gap-2.5 snap-x px-4 scroll-pl-4 after:shrink-0 after:w-4">
            
            
            <a href="<?php echo e(route('storefront.index', $company->slug)); ?>"
                class="snap-start flex-shrink-0 whitespace-nowrap px-5 py-2 rounded-xl text-[13px] font-bold transition-all border-2
                <?php echo e(!request()->route('categorySlug') 
                    ? 'bg-slate-900 border-slate-900 text-white shadow-md' 
                    : 'bg-white border-gray-100 text-gray-500 hover:border-gray-200'); ?>">
                All
            </a>

            
            <?php $__currentLoopData = $navCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php 
                    $isActive = request()->route('categorySlug') === $cat->slug; 
                ?>
                <a href="<?php echo e(route('storefront.category', ['slug' => $company->slug, 'categorySlug' => $cat->slug])); ?>"
                    class="snap-start flex-shrink-0 whitespace-nowrap px-5 py-2 rounded-xl text-[13px] font-bold transition-all border-2
                    <?php echo e($isActive 
                        ? 'bg-slate-900 border-slate-900 text-white shadow-sm' 
                        : 'bg-white border-gray-100 text-gray-600 hover:border-gray-200'); ?>">
                    <?php echo e($cat->name); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="flex-1 max-w-[1400px] w-full mx-auto px-4 sm:px-6 lg:px-8 pt-0 pb-6 lg:py-10 flex flex-col lg:flex-row gap-8 lg:gap-10">

        <aside class="hidden lg:block w-[220px] shrink-0">
            <div class="sticky top-[100px] bg-white border border-gray-200 p-4 rounded-2xl shadow-sm">
                <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4">Categories</h3>
                <nav class="grid gap-2">
                    
                    <a href="<?php echo e(route('storefront.index', $company->slug)); ?>"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-[13px] font-semibold transition-colors <?php echo e(!request()->route('categorySlug') ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-brand-100'); ?>">
                        All Categories
                    </a>

                    
                    <?php $__currentLoopData = $navCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php 
                            $isActive = request()->route('categorySlug') === $cat->slug; 
                        ?>
                        <a href="<?php echo e(route('storefront.category', ['slug' => $company->slug, 'categorySlug' => $cat->slug])); ?>"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-[13px] transition-colors <?php echo e($isActive ? 'bg-gray-100 text-gray-900 border-gray-200' : 'border-transparent text-gray-700 hover:bg-gray-50'); ?>">
                            <?php echo e($cat->name); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </nav>
            </div>
        </aside>

        <div class="flex-1 min-w-0 pb-12">

            <div class="swiper hero-swiper rounded-2xl overflow-hidden mb-6 shadow-sm relative group">
                <div class="swiper-wrapper">
                    <?php $__empty_1 = true; $__currentLoopData = $heroBanners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="swiper-slide relative bg-gray-100 aspect-[21/9] md:aspect-[3/1] w-full overflow-hidden">
                            
                            
                            <?php if($banner->link): ?>
                                <a href="<?php echo e($banner->link); ?>" <?php echo e($banner->target === '_blank' ? 'target=_blank rel=noopener' : ''); ?> class="absolute inset-0 z-10"></a>
                            <?php endif; ?>

                            <img src="<?php echo e(asset('storage/' . $banner->image)); ?>"
                                alt="<?php echo e($banner->alt_text ?? 'Banner'); ?>"
                                class="absolute inset-0 w-full h-full object-cover" 
                                loading="eager"
                                onerror="this.src='<?php echo e(asset('assets/images/placeholder.png')); ?>'">
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="swiper-slide bg-gray-100 aspect-[21/9] md:aspect-[3/1] flex items-center justify-center">
                            <p class="text-gray-400 font-medium text-sm">No banners available</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="swiper-button-next opacity-0 group-hover:opacity-100 transition-opacity hidden md:flex"></div>
                <div class="swiper-button-prev opacity-0 group-hover:opacity-100 transition-opacity hidden md:flex"></div>
                <div class="swiper-pagination !-bottom-8"></div>
            </div>        

            
            <?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $sectionProducts = $section->getRelation('resolved_products');
                    $cols = $section->columns ?? 4;
                ?>

                <section class="mb-10"
                    x-data
                    x-intersect.once="
                        fetch('<?php echo e(route('storefront.analytics.section.view', ['slug' => $company->slug])); ?>', {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content 
                            },
                            body: JSON.stringify({ section_id: <?php echo e($section->id); ?> })
                        })
                    "
                >

                    
                    <?php if($section->show_section_title): ?>
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-gray-800 tracking-tight">
                                    <?php echo e($section->title); ?>

                                </h2>
                                <?php if($section->subtitle): ?>
                                    <p class="text-sm text-gray-400 font-medium mt-0.5"><?php echo e($section->subtitle); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if($section->show_view_all && $section->resolved_view_all_url): ?>
                                <a href="<?php echo e($section->resolved_view_all_url); ?>"
                                    class="text-[13px] font-bold flex items-center gap-1 transition-colors hover:opacity-80"
                                    style="color: var(--brand-600);">
                                    View All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    
                    <?php if($section->type === 'custom_html' && $section->custom_html): ?>
                        <div class="w-full prose prose-sm sm:prose-base max-w-none">
                            <?php echo $section->custom_html; ?>

                        </div>
                        
                        
                    <?php elseif($section->type === 'banner' && $sectionProducts->isNotEmpty()): ?>
                        <?php
                            $bannerCols = $sectionProducts->count() > 1 ? min($cols, $sectionProducts->count()) : 1;
                            $useGrid = $sectionProducts->count() > 1;
                            $bannerHeight = $useGrid ? '160px' : '220px';
                        ?>

                        <div class="<?php echo e($useGrid ? 'grid gap-3' : 'space-y-3'); ?>"
                            <?php if($useGrid): ?> style="grid-template-columns: repeat(<?php echo e($bannerCols); ?>, 1fr)" <?php endif; ?>>

                            <?php $__currentLoopData = $sectionProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $bannerLink = $banner->link ?? null; ?>

                                <div class="relative rounded-2xl overflow-hidden shadow-sm group"
                                    style="height: <?php echo e($bannerHeight); ?>; background: #f3f4f6;">

                                    
                                    <?php if($bannerLink && !$banner->button_text): ?>
                                        <a href="<?php echo e($bannerLink); ?>"
                                            <?php echo e($banner->target === '_blank' ? 'target=_blank rel=noopener' : ''); ?>

                                            class="absolute inset-0 z-10">
                                        </a>
                                    <?php endif; ?>

                                    
                                    <img src="<?php echo e(asset('storage/' . $banner->image)); ?>"
                                        alt="<?php echo e($banner->alt_text ?? ($banner->title ?? '')); ?>"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        loading="lazy" onerror="this.parentElement.style.display='none'">

                                    
                                    <?php if($banner->title || $banner->subtitle || $banner->button_text): ?>
                                        <div class="absolute inset-0 flex items-center px-6 z-5"
                                            style="background: linear-gradient(to right, rgba(0,0,0,0.42), transparent 65%)">
                                            <div class="text-white max-w-sm">
                                                <?php if($banner->title): ?>
                                                    <h3
                                                        class="text-base sm:text-xl font-bold drop-shadow-md leading-tight mb-1">
                                                        <?php echo e($banner->title); ?>

                                                    </h3>
                                                <?php endif; ?>
                                                <?php if($banner->subtitle): ?>
                                                    <p class="text-xs sm:text-sm opacity-90 mb-2"><?php echo e($banner->subtitle); ?>

                                                    </p>
                                                <?php endif; ?>
                                                <?php if($banner->button_text && $bannerLink): ?>
                                                    <a href="<?php echo e($bannerLink); ?>"
                                                        <?php echo e($banner->target === '_blank' ? 'target=_blank rel=noopener' : ''); ?>

                                                        class="inline-flex items-center gap-1.5 bg-white text-gray-900 px-3 py-1.5 rounded-full text-xs font-bold shadow-md hover:-translate-y-0.5 transition-all relative z-20">
                                                        <?php echo e($banner->button_text); ?>

                                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        
                    <?php elseif($sectionProducts->isNotEmpty()): ?>
                        <div
                            class="grid grid-cols-2 md:grid-cols-3 <?php echo e($cols >= 4 ? 'lg:grid-cols-4' : 'lg:grid-cols-' . $cols); ?> gap-4 sm:gap-5">
                            <?php $__currentLoopData = $sectionProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $firstSku = $product->skus->first();
                                    $isCatalog = $product->product_type === 'catalog';
                                    $showPrice = ! $isCatalog && get_setting('enable_product_pricing', 1);
                                    $price = $firstSku?->price ?? 0; // Our Selling Price
                                    $mrp = $firstSku?->mrp ?? 0;     // Maximum Retail Price

                                    $isFeatured = $product->categoryPivots->first()?->is_featured ?? false;
                                    $isNew = $product->created_at->diffInDays(now()) <= 14;

                                    // Calculate discount based on MRP vs Selling Price
                                    $discount = $showPrice && $mrp > 0 && $price < $mrp
                                        ? round((($mrp - $price) / $mrp) * 100)
                                        : 0;
                                ?>

                                <a href="<?php echo e(route('storefront.product', [
                                    'slug' => $company->slug,
                                    'productSlug' => $product->slug,
                                    ])); ?>"
                                    @click="fetch('<?php echo e(route('storefront.analytics.section.click', ['slug' => $company->slug, 'id'=>$section->id])); ?>', {
                                            method: 'POST',
                                            keepalive: true,
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'Accept': 'application/json'
                                            }
                                        })"
                                    class="group block cursor-pointer bg-white rounded-xl border border-gray-100 hover:border-gray-200 hover:shadow-md transition-all overflow-hidden">

                                    
                                    <div class="bg-[#f8f9fa] aspect-square overflow-hidden relative">
                                        <img src="<?php echo e($product->primary_image_url); ?>" alt="<?php echo e($product->name); ?>"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            loading="lazy"
                                            onerror="this.src='<?php echo e(asset('assets/images/no-product.png')); ?>'">

                                        
                                        <?php if($isFeatured): ?>
                                            <span
                                                class="absolute top-2 left-2 text-[9px] font-black px-2 py-0.5 rounded bg-yellow-100 text-yellow-700 uppercase tracking-wide">⭐
                                                Featured</span>
                                        <?php elseif($discount > 0): ?>
                                            <span
                                                class="absolute top-2 left-2 text-[9px] font-black px-2 py-0.5 rounded bg-red-100 text-red-600 uppercase tracking-wide"><?php echo e($discount); ?>%
                                                off</span>
                                        <?php elseif($isNew): ?>
                                            <span
                                                class="absolute top-2 left-2 text-[9px] font-black px-2 py-0.5 rounded bg-green-100 text-green-700 uppercase tracking-wide">New</span>
                                        <?php endif; ?>
                                    </div>

                                    
                                    <div class="p-3">
                                        <h3
                                            class="text-[13px] sm:text-[14px] font-semibold text-gray-800 leading-[1.3] line-clamp-2 mb-2 group-hover:text-brand-600 transition-colors">
                                            <?php echo e($product->name); ?>

                                        </h3>
                                        <?php if($showPrice): ?>
                                            <div class="flex items-baseline gap-1.5 flex-wrap">
                                                <span class="font-bold text-[15px] text-gray-900">
                                                    ₹<?php echo e(number_format($price, 2)); ?>

                                                </span>
                                                <?php if($discount > 0): ?>
                                                    <span class="text-[11px] text-gray-400 line-through">₹<?php echo e(number_format($mrp, 2)); ?></span>
                                                    <span class="text-[11px] font-bold text-green-600"><?php echo e($discount); ?>% off</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif($isCatalog): ?>
                                            <span class="text-[12px] font-semibold text-brand-600">View Details</span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        
                    <?php else: ?>
                        <div
                            class="flex items-center justify-center py-10 border border-dashed border-gray-200 rounded-xl text-gray-400 text-sm font-medium gap-2 bg-gray-50">
                            <i data-lucide="package-x" class="w-4 h-4"></i>
                            No products in this section yet
                        </div>
                    <?php endif; ?>

                </section>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                
                <div class="py-16 text-center text-gray-400">
                    <i data-lucide="layout-dashboard" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
                    <p class="font-medium">Storefront sections not configured yet.</p>
                    <p class="text-sm mt-1">Go to Admin → Storefront Sections to set up your homepage.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Swiper Banner
            var swiper = new Swiper(".hero-swiper", {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.storefront', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/storefront/index.blade.php ENDPATH**/ ?>