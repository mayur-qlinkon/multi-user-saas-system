<?php $__env->startSection('badge', 'Access Denied'); ?>
<?php $__env->startSection('code', '403'); ?>
<?php $__env->startSection('title', 'You don\'t have permission'); ?>
<?php $__env->startSection('description', "You don't have the required permissions to view this page. If you believe this is a mistake, contact your administrator."); ?>

<?php $__env->startSection('actions'); ?>
    <a href="<?php echo e(url('/')); ?>" class="btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Go Home
    </a>
    <a href="javascript:history.back()" class="btn-ghost">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Go Back
    </a>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\yesteday\resources\views/errors/403.blade.php ENDPATH**/ ?>