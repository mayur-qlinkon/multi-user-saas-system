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

<div class="min-h-screen flex flex-col items-center justify-center p-6 sm:p-10 bg-gray-50">

    
    <div class="mb-8">
        
        <img src="<?php echo e(asset('assets/images/logo.png')); ?>" alt="Qlinkon" class="h-10 w-auto object-contain">
    </div>

    
    <div class="w-full max-w-[420px] bg-white p-8 sm:p-10 rounded-2xl shadow-sm border border-gray-100">

        
        <?php if (! empty(trim($__env->yieldContent('heading')))): ?>
            <div class="mb-7 text-center">
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

<?php echo $__env->yieldContent('scripts'); ?>

</body>
</html>
<?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/layouts/auth.blade.php ENDPATH**/ ?>