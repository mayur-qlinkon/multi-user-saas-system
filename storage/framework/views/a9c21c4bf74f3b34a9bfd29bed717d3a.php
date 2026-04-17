

<?php $__env->startSection('title', 'My Stores - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-xs sm:text-sm font-bold text-gray-400 uppercase tracking-widest">List / Stores</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-4 sm:space-y-6 pb-10" x-data="storeIndex()">

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

        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="w-full sm:w-auto">                
                <p class="text-xs sm:text-sm text-gray-500 font-medium">Manage your different business branches and outlets.</p>
            </div>

            <div class="w-full sm:w-auto">
                <?php if($canAddMore): ?>
                    <?php if(has_permission('stores.create')): ?>
                        <a href="<?php echo e(route('admin.stores.create')); ?>"
                            class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 transition-all shadow-md active:scale-95 whitespace-nowrap">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                            Add New Branch
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full">
                        <span class="inline-flex items-center justify-center gap-1 px-3 py-2 sm:py-1.5 rounded-lg sm:rounded-full bg-red-50 text-red-600 text-xs font-bold border border-red-100 whitespace-nowrap">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            Branch Limit Reached
                        </span>

                        <button type="button"
                            class="w-full sm:w-auto bg-gray-100 text-gray-400 px-5 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 cursor-not-allowed shadow-sm whitespace-nowrap"
                            title="You have reached your branch limit. Upgrade your plan to add more branches.">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                            Add New Branch
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
            <?php $__empty_1 = true; $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 flex flex-col hover:shadow-lg transition-all duration-300 group relative">

                    
                    <div class="absolute top-4 right-4">
                        <?php if($store->is_active): ?>
                            <span class="bg-[#dcfce7] text-[#16a34a] px-2.5 py-1 rounded-lg font-bold text-[9px] sm:text-[10px] uppercase tracking-wider">Active</span>
                        <?php else: ?>
                            <span class="bg-gray-100 text-gray-400 px-2.5 py-1 rounded-lg font-bold text-[9px] sm:text-[10px] uppercase tracking-wider">Inactive</span>
                        <?php endif; ?>
                    </div>

                    
                    <div class="flex items-center gap-3 sm:gap-4 mb-5 sm:mb-6 pr-14">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl border border-gray-100 overflow-hidden bg-gray-50 flex-shrink-0 shadow-sm">
                            <img src="<?php echo e($store->logo_url); ?>" alt="<?php echo e($store->name); ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-[#212538] truncate" title="<?php echo e($store->name); ?>"><?php echo e($store->name); ?></h3>
                            <div class="flex items-center gap-1.5 text-gray-400 text-[11px] sm:text-xs font-medium mt-0.5">
                                <i data-lucide="map-pin" class="w-3 h-3 shrink-0"></i>
                                <span class="truncate"><?php echo e($store->city ?? 'Location N/A'); ?></span>
                            </div>
                        </div>
                    </div>

                    
                    <div class="space-y-2.5 sm:space-y-3 mb-5 sm:mb-6 flex-1">
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-gray-400 font-medium">GSTIN</span>
                            <span class="text-gray-700 font-mono font-bold"><?php echo e($store->gst_number ?? 'Not Set'); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-gray-400 font-medium">Currency</span>
                            <span class="text-gray-700 font-bold"><?php echo e($store->currency); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-gray-400 font-medium">Contact</span>
                            <span class="text-gray-700 font-bold"><?php echo e($store->phone ?? 'N/A'); ?></span>
                        </div>
                    </div>

                    
                    <div class="flex items-center gap-2 pt-4 border-t border-gray-50">
                        
                        <?php if(has_permission('stores.view')): ?>
                            <a href="<?php echo e(route('admin.stores.show', $store->id)); ?>"
                                class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-600 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center justify-center gap-1.5 sm:gap-2">
                                <i data-lucide="eye" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i> <span class="hidden xs:inline">View</span>
                            </a>
                        <?php endif; ?>

                        
                        <?php if(has_permission('stores.update')): ?>
                            <a href="<?php echo e(route('admin.stores.edit', $store->id)); ?>"
                                class="flex-1 bg-gray-50 hover:bg-brand-50 text-gray-600 hover:text-brand-600 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center justify-center gap-1.5 sm:gap-2">
                                <i data-lucide="settings-2" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i> <span class="hidden xs:inline">Edit</span>
                            </a>
                        <?php endif; ?>

                        
                        <?php if(has_permission('stores.delete')): ?>
                            <form action="<?php echo e(route('admin.stores.destroy', $store->id)); ?>" method="POST"
                                @submit.prevent="confirmDelete($event.target)" class="shrink-0">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" title="Archive Store"
                                    class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full py-16 sm:py-20 text-center bg-white rounded-2xl border border-dashed border-gray-200">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="store" class="w-8 h-8 sm:w-10 sm:h-10 text-gray-300"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-800">No Stores Found</h3>
                    <p class="text-gray-500 text-xs sm:text-sm max-w-xs mx-auto mt-1">Start by creating your first business branch to manage inventory and sales.</p>
                </div>
            <?php endif; ?>
        </div>

        
        <?php if($stores->hasPages()): ?>
            <div class="mt-6">
                <?php echo e($stores->links()); ?>

            </div>
        <?php endif; ?>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function storeIndex() {
            return {
                confirmDelete(form) {
                    BizAlert.confirm(
                        'Archive Store?',
                        'Deactivating this store will hide it from the active outlets, but historical data will be saved.',
                        'Yes, Archive'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Processing...');
                            form.submit();
                        }
                    });
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/stores/index.blade.php ENDPATH**/ ?>