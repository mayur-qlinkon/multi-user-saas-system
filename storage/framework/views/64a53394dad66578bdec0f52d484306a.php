

<?php $__env->startSection('title', 'Manage Plans - Qlinkon Super Admin'); ?>
<?php $__env->startSection('header', 'Subscription Plans'); ?>

<?php $__env->startSection('styles'); ?>
    <style>
        [x-cloak] { display: none !important; }
        body.modal-open { overflow: hidden; }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="pb-10" x-data="planIndexManager()">

        
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Manage Plans</h1>
                <p class="text-sm text-gray-500 mt-1">Configure pricing, UI badges, and resource limits.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('platform.plans.create')); ?>"
                    class="bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Create Plan
                </a>
            </div>
        </div>

        
        <?php if(session('success')): ?>
            <div class="bg-green-50 text-green-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-green-100 mb-6 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php $__empty_1 = true; $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow relative">

                    
                    <div class="absolute top-4 right-4 flex flex-col gap-2 items-end">
                        <?php if($plan->is_active): ?>
                            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Active</span>
                        <?php else: ?>
                            <span class="bg-gray-100 text-gray-500 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Inactive</span>
                        <?php endif; ?>
                    </div>

                    
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">                        
                        <h3 class="text-xl font-black text-gray-800 mb-1"><?php echo e($plan->name); ?></h3>
                        <?php if($plan->description): ?>
                            <p class="text-xs text-gray-500 mb-3"><?php echo e($plan->description); ?></p>
                        <?php endif; ?>
                        
                        <div class="flex items-baseline gap-1 mt-2">
                            <span class="text-3xl font-black text-gray-900">₹<?php echo e(number_format($plan->price, 0)); ?></span>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">/<?php echo e($plan->billing_cycle); ?></span>
                        </div>
                        
                        <?php if($plan->trial_days > 0): ?>
                            <p class="text-xs font-bold text-brand-600 mt-2 bg-brand-50 inline-block px-2 py-1 rounded"><?php echo e($plan->trial_days); ?> Days Free Trial</p>
                        <?php endif; ?>
                    </div>

                    
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100 text-center">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Users</p>
                                <p class="text-base font-black text-gray-700"><?php echo e($plan->user_limit); ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100 text-center">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Stores</p>
                                <p class="text-base font-black text-gray-700"><?php echo e($plan->store_limit); ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100 text-center">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Products</p>
                                <p class="text-base font-black text-gray-700"><?php echo e($plan->product_limit); ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100 text-center">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Employees</p>
                                <p class="text-base font-black text-gray-700"><?php echo e($plan->employee_limit); ?></p>
                            </div>
                            <div class="bg-brand-50 col-span-2 rounded-lg p-2.5 border border-brand-100 text-center">
                                <p class="text-[10px] text-brand-600 font-bold uppercase tracking-wider mb-0.5">Daily OCR Scans</p>
                                <p class="text-base font-black text-brand-800"><?php echo e($plan->ocr_scan_limit); ?></p>
                            </div>
                        </div>

                        <div class="mb-4 flex-1">
                            <p class="text-xs font-bold text-gray-800 mb-2 flex items-center gap-1.5">
                                <i data-lucide="boxes" class="w-4 h-4 text-brand-500"></i> <?php echo e($plan->modules->count()); ?> Modules
                            </p>
                            <ul class="space-y-1.5">
                                <?php $__currentLoopData = $plan->modules->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-start gap-2 text-[13px] text-gray-600">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-green-500 shrink-0 mt-0.5"></i>
                                        <span class="font-medium truncate"><?php echo e($module->name); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php if($plan->modules->count() > 3): ?>
                                    <li class="text-xs font-bold text-gray-400 pl-5">+ <?php echo e($plan->modules->count() - 3); ?> more...</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    
                    <div class="p-4 border-t border-gray-100 flex items-center justify-end gap-2 bg-white">
                        <a href="<?php echo e(route('platform.plans.edit', $plan->id)); ?>"
                            class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                        </a>
                        <button type="button" @click="openDelete(<?php echo e($plan->id); ?>, '<?php echo e(addslashes($plan->name)); ?>')"
                            class="px-4 py-2 bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-white rounded-xl border border-gray-200 border-dashed">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="layers" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">No Plans Created</h3>
                    <p class="text-sm text-gray-500 max-w-sm">Create your first subscription plan to start onboarding companies.</p>
                    <a href="<?php echo e(route('platform.plans.create')); ?>" class="mt-6 text-brand-600 font-bold text-sm hover:underline flex items-center gap-1">
                        Create Plan Now <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        
        <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()" x-show="showDeleteModal" x-transition.opacity></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden text-center"
                x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="p-6 pt-8">
                    <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-triangle" class="w-8 h-8 text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Delete Plan?</h3>
                    <p class="text-sm text-gray-500">Are you sure you want to delete the <strong class="text-gray-800" x-text="deleteForm.name"></strong> plan? Existing subscriptions will remain active due to soft deletes.</p>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-center gap-3 bg-gray-50">
                    <button type="button" @click="closeModal()" class="px-6 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                    <form :action="`/platform/plans/${deleteForm.id}`" method="POST">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition-colors shadow-sm">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        function planIndexManager() {
            return {
                showDeleteModal: false,
                deleteForm: { id: '', name: '' },

                openDelete(id, name) {
                    document.body.classList.add('modal-open');
                    this.deleteForm = { id, name };
                    this.showDeleteModal = true;
                },

                closeModal() {
                    document.body.classList.remove('modal-open');
                    this.showDeleteModal = false;
                }
            }
        }
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/platform/plans/index.blade.php ENDPATH**/ ?>