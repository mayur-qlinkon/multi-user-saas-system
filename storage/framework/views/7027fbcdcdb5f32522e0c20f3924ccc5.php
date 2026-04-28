 
<?php $__env->startSection('title', 'Login - ' . ($company->name ?? 'Store')); ?>

<?php $__env->startSection('content'); ?>
<div class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50/50">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Welcome Back</h2>
            <p class="text-sm text-gray-500 mt-2">Log in to manage your orders at <span class="font-bold text-gray-800"><?php echo e($company->name); ?></span></p>
        </div>

        
        <?php if(session('error')): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 shrink-0 mt-0.5"></i>
                <p class="text-sm font-medium text-red-800"><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('storefront.login.submit', ['slug' => $company->slug])); ?>" method="POST" class="space-y-5">
            <?php echo csrf_field(); ?>

            <div>
                <label for="email" class="block text-[12px] font-bold text-gray-700 uppercase tracking-wide mb-1.5">Email Address</label>
                <div class="relative">
                    <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>" required autofocus
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all outline-none <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                </div>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-[11px] font-semibold text-red-500 mt-1.5"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-[12px] font-bold text-gray-700 uppercase tracking-wide">Password</label>
                    <a href="<?php echo e(route('password.request')); ?>" class="text-[12px] font-bold text-brand-600 hover:text-brand-700">Forgot Password?</a>
                </div>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="password" name="password" id="password" required
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all outline-none">
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500 cursor-pointer">
                <label for="remember" class="ml-2 block text-sm text-gray-600 cursor-pointer">Remember me</label>
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white transition-all hover:shadow-lg hover:-translate-y-0.5" style="background: var(--brand-600);">
                Log In
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-gray-600">
            Don't have an account? 
            <a href="<?php echo e(route('storefront.register', ['slug' => $company->slug])); ?>" class="font-bold text-brand-600 hover:text-brand-700 transition-colors">Create one</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.storefront', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\yesteday\resources\views/storefront/auth/login.blade.php ENDPATH**/ ?>