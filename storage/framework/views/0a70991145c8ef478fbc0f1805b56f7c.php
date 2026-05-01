

<?php $__env->startSection('title', 'Access Control & Roles - Qlinkon BIZNESS'); ?>
<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Roles</h1>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="space-y-6 pb-10" x-data="roleIndex()">

        <?php if(session('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('success')); ?>", 'success'));
            </script>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('error')); ?>", 'error'));
            </script>
        <?php endif; ?>

       
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
            
            
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                </div>
                <input type="text" x-model="search" placeholder="Search roles..."
                    class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-[#108c2a] sm:text-sm transition-colors">
            </div>

            
            <div class="ml-auto flex shrink-0 w-full sm:w-auto">
                <a href="<?php echo e(route('admin.roles.create')); ?>"
                    class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2 whitespace-nowrap">
                    <i data-lucide="shield-plus" class="w-4 h-4"></i> New Role
                </a>
            </div>
            
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col hover:shadow-md transition-shadow"
                    x-show="matchesSearch('<?php echo e(strtolower($role->name)); ?>')">

                    <div class="flex flex-col mb-6 flex-1">
                        <h3 class="text-xl font-bold text-[#108c2a]"><?php echo e($role->name); ?></h3>
                        <span class="inline-block bg-gray-100 text-gray-500 text-[10px] font-mono px-2 py-0.5 rounded mt-1.5 uppercase w-max border border-gray-200">
                            <?php echo e($role->slug); ?>

                        </span>
                    </div>

                    <div class="flex justify-end gap-2.5 mt-auto">
                        <a href="<?php echo e(route('admin.roles.edit', $role->id)); ?>"
                            class="bg-[#eff6ff] hover:bg-blue-100 text-[#2563eb] px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-1.5 transition-colors flex-1 justify-center">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i> Manage
                        </a>

                        <form action="<?php echo e(route('admin.roles.destroy', $role->id)); ?>" method="POST"
                            @submit.prevent="confirmDelete($event.target)">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit"
                                class="bg-[#fef2f2] hover:bg-red-100 text-[#ef4444] px-4 py-2 rounded-lg text-sm font-bold transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full py-20 text-center bg-white rounded-xl border border-dashed border-gray-200">
                    <p class="text-gray-400 font-medium">No roles created yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function roleIndex() {
            return {
                search: '',
                matchesSearch(name) {
                    return this.search === '' || name.includes(this.search.toLowerCase());
                },
                confirmDelete(form) {
                    BizAlert.confirm('Delete Role?',
                            'This will permanently remove access for all users assigned to this role.', 'Yes, Delete')
                        .then((result) => {
                            if (result.isConfirmed) {
                                BizAlert.loading('Removing Role...');
                                form.submit();
                            }
                        });
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/roles/index.blade.php ENDPATH**/ ?>