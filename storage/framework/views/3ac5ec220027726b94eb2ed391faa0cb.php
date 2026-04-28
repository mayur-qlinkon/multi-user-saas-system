<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo e(get_system_setting('app_name', 'Qlinkon')); ?> — Smart ERP for Indian SMEs">
    <title><?php echo e(get_system_setting('app_name', 'Qlinkon')); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if(get_system_setting('app_favicon')): ?>
        <link rel="icon" href="<?php echo e(asset('storage/' . get_system_setting('app_favicon'))); ?>">
    <?php endif; ?>
    <script src="<?php echo e(asset('assets/js/tailwind.min.js')); ?>"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#f0fdfa', 100: '#ccfbf1', 500: '#0f766e', 600: '#115e59', 700: '#134e4a' }
                    }
                }
            }
        }
    </script>
    <script defer src="<?php echo e(asset('assets/js/alpinejs.min.js')); ?>"></script>
    <style>
        body { font-family: Poppins, sans-serif; }
        [x-cloak] { display: none !important; }
        .hero-gradient {
            background: linear-gradient(135deg, #0f766e 0%, #134e4a 60%, #0f172a 100%);
        }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased">

    <?php
        $actionUrl = route('login');

        if (auth()->check()) {
            $user = auth()->user();

            // Grabs the slug from the user's associated company, or falls back to 'store'
            $companySlug = $user->company->slug ?? request()->route('slug') ?? 'store';

            if ($user->hasRole('customer')) {
                $actionUrl = route('storefront.portal.dashboard', ['slug' => $companySlug]);
            } elseif ($user->hasRole('owner')) {
                $actionUrl = route('admin.dashboard');
            } elseif ($user->hasRole('super_admin')) {
                $actionUrl = route('platform.dashboard');
            } elseif ($user->hasRole('employee')) {
                $actionUrl = route('admin.hrm.employee.dashboard');
            } else {
                // Fallback for Super Admin or other roles
                $actionUrl = route('admin.dashboard');
            }
        }
    ?>


    
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <?php if(get_system_setting('app_logo')): ?>
                    <img src="<?php echo e(asset('storage/' . get_system_setting('app_logo'))); ?>"
                        alt="<?php echo e(get_system_setting('app_name', 'Qlinkon')); ?>"
                        class="h-8 w-auto object-contain">
                <?php else: ?>
                    <div class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center font-bold text-sm">
                        <?php echo e(strtoupper(substr(get_system_setting('app_name', 'Q'), 0, 1))); ?>

                    </div>
                    <span class="font-bold text-gray-800 text-lg"><?php echo e(get_system_setting('app_name', 'Qlinkon')); ?></span>
                <?php endif; ?>
            </div>
            <a href="<?php echo e($actionUrl); ?>"
                class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
                <?php echo e(auth()->check() ? 'Dashboard' : 'Login'); ?>

            </a>
        </div>
    </nav>

    
    <section class="hero-gradient min-h-screen flex items-center pt-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-24 lg:py-32 text-center">
            <span class="inline-block text-xs font-bold tracking-widest text-brand-100 uppercase mb-4 bg-white/10 px-4 py-1.5 rounded-full">
                Smart ERP for Indian SMEs
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
                Run Your Business<br>
                <span class="text-brand-100">Smarter, Faster.</span>
            </h1>
            <p class="text-base sm:text-lg text-teal-100/80 max-w-2xl mx-auto mb-10 leading-relaxed">
                <?php echo e(get_system_setting('app_name', 'Qlinkon')); ?> provides a complete ERP solution — invoicing,
                inventory, POS, attendance &amp; more. Built for Indian small businesses, it offers
                powerful multi‑store handling so you can manage all your outlets in one software.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#contact"
                    class="w-full sm:w-auto bg-white text-brand-700 hover:bg-brand-50 font-bold px-8 py-3.5 rounded-xl text-sm transition-colors shadow-md">
                    Get in Touch
                </a>
                <a href="<?php echo e($actionUrl); ?>"
                    class="w-full sm:w-auto border border-white/30 text-white hover:bg-white/10 font-semibold px-8 py-3.5 rounded-xl text-sm transition-colors">
                    <?php echo e(auth()->check() ? 'Go to Dashboard →' : 'Login to Dashboard →'); ?>

                </a>
            </div>
        </div>
    </section>

    
    <section class="py-20 bg-gray-50 border-y border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <h2 class="text-2xl sm:text-3xl font-bold text-center text-gray-800 mb-12">
                Everything your business needs
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <?php $__currentLoopData = [
                    ['icon' => '🧾', 'label' => 'GST Invoicing'],
                    ['icon' => '📦', 'label' => 'Inventory'],
                    ['icon' => '🖥️', 'label' => 'POS System'],
                    ['icon' => '⏱️', 'label' => 'Attendance'],
                    ['icon' => '📊', 'label' => 'Reports'],
                    ['icon' => '👥', 'label' => 'Multi-Users'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white rounded-2xl p-5 text-center shadow-sm border border-gray-100">
                        <span class="text-3xl block mb-2"><?php echo e($f['icon']); ?></span>
                        <p class="text-xs font-semibold text-gray-700"><?php echo e($f['label']); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>

    
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-3">Get in Touch</h2>
                <p class="text-gray-500 text-sm">Have questions? Drop us a message and our team will get back to you.</p>
            </div>

            <?php if(session('success')): ?>
                <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                    <span class="text-xl">✅</span>
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('landing.inquire')); ?>"
                class="bg-gray-50 rounded-2xl border border-gray-200 p-8 space-y-5">
                <?php echo csrf_field(); ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            Your Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="<?php echo e(old('name')); ?>"
                            placeholder="Rajesh Kumar"
                            class="w-full border <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php else: ?> border-gray-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none bg-white">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Phone</label>
                        <input type="text" name="phone" value="<?php echo e(old('phone')); ?>"
                            placeholder="+91 9876543210"
                            class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>"
                        placeholder="rajesh@mybusiness.com"
                        class="w-full border <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php else: ?> border-gray-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none bg-white">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        Message <span class="text-red-500">*</span>
                    </label>
                    <textarea name="message" rows="4"
                        placeholder="Tell us about your business and what you need..."
                        class="w-full border <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php else: ?> border-gray-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none bg-white resize-none"><?php echo e(old('message')); ?></textarea>
                    <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button type="submit"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl text-sm transition-colors shadow-sm">
                    Send Message
                </button>
            </form>
        </div>
    </section>

    
    <footer class="bg-gray-900 text-gray-400 py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm">
            <p class="font-semibold text-white">
                <?php echo e(get_system_setting('app_name', 'Qlinkon')); ?>

            </p>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs">
                <?php if(get_system_setting('support_email')): ?>
                    <a href="mailto:<?php echo e(get_system_setting('support_email')); ?>" class="hover:text-white transition-colors">
                        <?php echo e(get_system_setting('support_email')); ?>

                    </a>
                <?php endif; ?>
                <?php if(get_system_setting('support_phone')): ?>                    
                     <a href="tel:<?php echo e(get_system_setting('support_phone')); ?>" class="hover:text-white transition-colors">
                        <?php echo e(get_system_setting('support_phone')); ?>

                    </a>
                <?php endif; ?>
                
                <a href="<?php echo e($actionUrl); ?>" class="hover:text-white transition-colors">
                    <?php echo e(auth()->check() ? 'Dashboard' : 'Admin Login'); ?>

                </a>
            </div>
            <p class="text-xs">&copy; <?php echo e(date('Y')); ?> <?php echo e(get_system_setting('app_name', 'Qlinkon')); ?>. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
<?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\yesteday\resources\views/landing.blade.php ENDPATH**/ ?>