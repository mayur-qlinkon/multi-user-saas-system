<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', config('app.name')); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if(get_system_setting('app_favicon')): ?>
        <link rel="icon" href="<?php echo e(asset('storage/'.get_system_setting('app_favicon'))); ?>">
    <?php endif; ?>
    <script src="<?php echo e(asset('assets/js/tailwind.min.js')); ?>"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#f0fdfa',
                            100: '#ccfbf1',
                            400: '#2dd4bf',
                            500: '#0f766e',
                            600: '#115e59',
                            700: '#134e4a',
                            800: '#0d3b37',
                            900: '#082b28',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="<?php echo e(asset('assets/js/alpinejs.min.js')); ?>"></script>
    <style>
        body { font-family: Poppins, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-800 antialiased">

<div class="min-h-screen flex">

    
    <div class="hidden lg:flex w-[420px] xl:w-[480px] shrink-0 flex-col bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 relative overflow-hidden">

        
        <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-white/[0.03] pointer-events-none"></div>
        <div class="absolute -bottom-16 -right-16 w-64 h-64 rounded-full bg-white/[0.04] pointer-events-none"></div>
        <div class="absolute top-1/2 -right-8 w-40 h-40 rounded-full bg-brand-600/30 pointer-events-none"></div>

        
        <div class="relative z-10 flex flex-col flex-1 p-10 xl:p-12">

            
            <div class="shrink-0">
                <?php if(get_system_setting('app_logo')): ?>
                    <img src="<?php echo e(asset('storage/'.get_system_setting('app_logo'))); ?>"
                        alt="<?php echo e(get_system_setting('app_name', config('app.name'))); ?>"
                        class="h-9 w-auto object-contain brightness-0 invert opacity-90">
                <?php else: ?>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center">
                            <span class="font-bold text-white text-sm">
                                <?php echo e(strtoupper(substr(get_system_setting('app_name', config('app.name')), 0, 1))); ?>

                            </span>
                        </div>
                        <span class="font-bold text-white text-lg tracking-tight">
                            <?php echo e(get_system_setting('app_name', config('app.name'))); ?>

                        </span>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="flex-1 flex flex-col justify-center py-10">
                <?php if (! empty(trim($__env->yieldContent('panel')))): ?>
                    <?php echo $__env->yieldContent('panel'); ?>
                <?php else: ?>
                    
                    <h1 class="text-3xl xl:text-4xl font-800 text-white leading-snug tracking-tight mb-4">
                        Smart ERP for<br>Indian SMEs.
                    </h1>
                    <p class="text-brand-100/70 text-base font-300 leading-relaxed mb-8">
                        One platform for invoicing, inventory, POS, attendance, and more.
                    </p>
                    <div class="space-y-3">
                        <?php $__currentLoopData = ['GST-ready invoicing & POS', 'Multi-store inventory control', 'Attendance & HR management']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full bg-brand-500/30 border border-brand-400/30 flex items-center justify-center shrink-0">
                                    <svg class="w-2.5 h-2.5 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="text-sm text-brand-100/60 font-400"><?php echo e($feat); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>

            
            <p class="text-xs text-brand-100/30 font-300">
                &copy; <?php echo e(date('Y')); ?> <?php echo e(get_system_setting('app_name', config('app.name'))); ?>

            </p>
        </div>
    </div>

    
    <div class="flex-1 flex flex-col items-center justify-center p-6 sm:p-10 bg-white">

        
        <div class="lg:hidden mb-8 flex items-center gap-2.5">
            <?php if(get_system_setting('app_logo')): ?>
                <img src="<?php echo e(asset('storage/'.get_system_setting('app_logo'))); ?>"
                    alt="<?php echo e(get_system_setting('app_name', config('app.name'))); ?>"
                    class="h-8 w-auto object-contain">
            <?php else: ?>
                <div class="w-8 h-8 rounded-lg bg-brand-700 flex items-center justify-center">
                    <span class="font-bold text-white text-sm">
                        <?php echo e(strtoupper(substr(get_system_setting('app_name', config('app.name')), 0, 1))); ?>

                    </span>
                </div>
                <span class="font-bold text-gray-800 text-base">
                    <?php echo e(get_system_setting('app_name', config('app.name'))); ?>

                </span>
            <?php endif; ?>
        </div>

        <div class="w-full max-w-[400px]">

            
            <?php if (! empty(trim($__env->yieldContent('heading')))): ?>
                <div class="mb-7">
                    <h2 class="text-2xl font-700 text-gray-900 tracking-tight"><?php echo $__env->yieldContent('heading'); ?></h2>
                    <?php if (! empty(trim($__env->yieldContent('subheading')))): ?>
                        <p class="text-sm text-gray-500 mt-1.5 leading-relaxed"><?php echo $__env->yieldContent('subheading'); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            
            <?php if(session('status')): ?>
                <div class="mb-5 flex items-start gap-3 bg-brand-50 border border-brand-200 text-brand-800 text-sm px-4 py-3 rounded-xl">
                    <svg class="w-4 h-4 shrink-0 mt-0.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?>
            <?php if(session('success')): ?>
                <div class="mb-5 flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                    <svg class="w-4 h-4 shrink-0 mt-0.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
                    <svg class="w-4 h-4 shrink-0 mt-0.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            
            <?php echo $__env->yieldContent('content'); ?>

        </div>
    </div>

</div>

<?php echo $__env->yieldContent('scripts'); ?>

</body>
</html>
<?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/layouts/auth.blade.php ENDPATH**/ ?>