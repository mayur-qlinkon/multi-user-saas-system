

<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('content'); ?>

    <h2 class="text-xl font-semibold mb-6 text-gray-700 text-center">
        Login to your account
    </h2>

    <form method="POST" action="<?php echo e(route('login.store')); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>

        
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Email
            </label>

            <input type="email" name="email" value="<?php echo e(old('email')); ?>" required
                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500">

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
            <label class="block text-sm font-medium text-gray-600 mb-1">
                Password
            </label>

            <input type="password" name="password" required
                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500">

            <?php $__errorArgs = ['password'];
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

        
        <div class="flex items-center justify-between text-sm">

            <label class="flex items-center space-x-2">
                <input type="checkbox" name="remember">
                <span>Remember me</span>
            </label>

            <a href="#" class="text-blue-600 hover:underline">
                Forgot password?
            </a>

        </div>

        
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
            Login
        </button>

    </form>

    <div class="mt-6 text-center text-sm text-gray-600">
        Don't have an account?
        <a href="<?php echo e(route('register')); ?>" class="text-blue-600 hover:underline">
            Register
        </a>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/auth/login.blade.php ENDPATH**/ ?>