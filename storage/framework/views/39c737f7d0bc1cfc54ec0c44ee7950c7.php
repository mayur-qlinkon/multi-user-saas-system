
<?php $__env->startSection('title', 'Create Account - ' . ($company->name ?? 'Store')); ?>

<?php $__env->startSection('content'); ?>
<div class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50/50">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Create Account</h2>
            <p class="text-sm text-gray-500 mt-2">Join <span class="font-bold text-gray-800"><?php echo e($company->name); ?></span> for faster checkout</p>
        </div>

        <form action="<?php echo e(route('storefront.register.submit', ['slug' => $company->slug])); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>

            <div>
                <label for="name" class="block text-[12px] font-bold text-gray-700 uppercase tracking-wide mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="name" id="name" value="<?php echo e(old('name')); ?>" required autofocus
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all outline-none <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                </div>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-[11px] font-semibold text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="email" class="block text-[12px] font-bold text-gray-700 uppercase tracking-wide mb-1.5">Email Address <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>" required
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
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-[11px] font-semibold text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="phone" class="block text-[12px] font-bold text-gray-700 uppercase tracking-wide mb-1.5">Phone Number</label>
                <div class="relative">
                    <i data-lucide="phone" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="tel" name="phone" id="phone" value="<?php echo e(old('phone')); ?>"
                        pattern="[0-9]{10}"
                        maxlength="10"
                        minlength="10"
                        inputmode="numeric"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all outline-none <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                </div>
                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-[11px] font-semibold text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-1">
                <div>
                    <label for="password" class="block text-[12px] font-bold text-gray-700 uppercase tracking-wide mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all outline-none <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-[12px] font-bold text-gray-700 uppercase tracking-wide mb-1.5">Confirm <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all outline-none">
                </div>
            </div>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-[11px] font-semibold text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <div class="pt-4">
                <button type="submit" class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white transition-all hover:shadow-lg hover:-translate-y-0.5" style="background: var(--brand-600);">
                    Create Account
                </button>
            </div>
        </form>

        <p class="mt-8 text-center text-sm text-gray-600">
            Already have an account? 
            <a href="<?php echo e(route('storefront.login', ['slug' => $company->slug])); ?>" class="font-bold text-brand-600 hover:text-brand-700 transition-colors">Log in</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.storefront', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\yesteday\resources\views/storefront/auth/register.blade.php ENDPATH**/ ?>