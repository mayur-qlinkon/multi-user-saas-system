

<?php $__env->startSection('title', 'Create Plan - Qlinkon'); ?>
<?php $__env->startSection('header', 'Create New Plan'); ?>

<?php $__env->startSection('styles'); ?>
    <style>
        .form-label { display: block; font-size: 12px; font-weight: 700; color: #4b5563; margin-bottom: 6px; }
        .form-input { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 13px; transition: all 0.2s; outline: none; }
        .form-input:focus { border-color: var(--brand-500); box-shadow: 0 0 0 3px rgba(0, 138, 98, 0.1); }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="w-full pb-10" x-data="planFormManager(<?php echo \Illuminate\Support\Js::from($modules->pluck('id'))->toHtml() ?>)">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="<?php echo e(route('platform.plans.index')); ?>" class="text-sm font-bold text-gray-500 hover:text-gray-800 flex items-center gap-1 mb-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Plans
                </a>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Create Subscription Plan</h1>
            </div>
        </div>

        <?php if($errors->any()): ?>
            <div class="bg-red-50 text-red-600 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix the following errors:</div>
                <ul class="list-disc list-inside pl-7 text-xs font-medium space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('platform.plans.store')); ?>" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <?php echo csrf_field(); ?>

            <div class="p-8 space-y-8">
                
                
                <div>
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">1. Identity & Pricing</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="form-label">Plan Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="<?php echo e(old('name')); ?>" required placeholder="e.g. Premium Plan" class="form-input">
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Short Description</label>
                            <input type="text" name="description" value="<?php echo e(old('description')); ?>" placeholder="e.g. Best for growing businesses" class="form-input">
                        </div>
                        
                        <div>
                            <label class="form-label">Price (₹) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="price" value="<?php echo e(old('price', 0)); ?>" required class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Billing Cycle <span class="text-red-500">*</span></label>
                            <select name="billing_cycle" required class="form-input bg-white">
                                <option value="monthly" <?php echo e(old('billing_cycle') == 'monthly' ? 'selected' : ''); ?>>Monthly</option>
                                <option value="yearly" <?php echo e(old('billing_cycle') == 'yearly' ? 'selected' : ''); ?>>Yearly</option>
                                <option value="lifetime" <?php echo e(old('billing_cycle') == 'lifetime' ? 'selected' : ''); ?>>Lifetime (One-time)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Free Trial Days</label>
                            <input type="number" name="trial_days" value="<?php echo e(old('trial_days', 0)); ?>" min="0" class="form-input">
                        </div>
                        <div class="mt-2 md:mt-8">
                            <label class="flex items-center gap-2 cursor-pointer w-max">
                                <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', true) ? 'checked' : ''); ?> class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500">
                                <span class="text-sm font-bold text-gray-700">Plan is Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                
                <div>
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">2. Resource Limits</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        <div>
                            <label class="form-label">User Limit <span class="text-red-500">*</span></label>
                            <input type="number" name="user_limit" value="<?php echo e(old('user_limit', 1)); ?>" required min="1" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Store Limit <span class="text-red-500">*</span></label>
                            <input type="number" name="store_limit" value="<?php echo e(old('store_limit', 1)); ?>" required min="1" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Product Limit <span class="text-red-500">*</span></label>
                            <input type="number" name="product_limit" value="<?php echo e(old('product_limit', 50)); ?>" required min="1" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Employee Limit <span class="text-red-500">*</span></label>
                            <input type="number" name="employee_limit" value="<?php echo e(old('employee_limit', 50)); ?>" required min="1" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Daily OCR Scans <span class="text-red-500">*</span></label>
                            <input type="number" name="ocr_scan_limit" value="<?php echo e(old('ocr_scan_limit', 50)); ?>" required min="0" class="form-input">
                        </div>
                    </div>
                </div>

                
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-4">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">3. Assigned Modules</h4>
                        <button type="button" @click="toggleAllModules()" x-show="availableModules.length > 0"
                            class="text-[10px] font-bold px-2 py-1 rounded transition-colors border"
                            :class="areAllSelected() ? 'bg-gray-100 text-gray-600 border-gray-200' : 'bg-brand-50 text-brand-600 border-brand-100 hover:bg-brand-100'">
                            <span x-text="areAllSelected() ? 'Deselect All' : 'Select All'"></span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-colors hover:bg-gray-50"
                                :class="selectedModules.includes(<?php echo e($module->id); ?>) ? 'border-brand-500 bg-brand-50/20' : 'border-gray-200 bg-white'">
                                <input type="checkbox" name="modules[]" value="<?php echo e($module->id); ?>" x-model="selectedModules"
                                    class="w-4 h-4 text-brand-600 bg-gray-100 border-gray-300 rounded focus:ring-brand-500 cursor-pointer">
                                <span class="text-sm font-bold text-gray-800 truncate"><?php echo e($module->name); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($modules->isEmpty()): ?>
                            <p class="text-sm text-gray-500 col-span-full">No active modules found in the system.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="px-8 py-5 border-t border-gray-100 flex justify-end gap-3 bg-gray-50">
                <a href="<?php echo e(route('platform.plans.index')); ?>" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</a>
                <button type="submit" class="px-8 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-bold hover:bg-brand-700 transition-colors shadow-sm">Create Plan</button>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        function planFormManager(allModuleIds) {
            return {
                availableModules: allModuleIds,
                selectedModules: <?php echo json_encode(old('modules', []), 512) ?>,
                
                areAllSelected() {
                    return this.availableModules.length > 0 && this.selectedModules.length === this.availableModules.length;
                },

                toggleAllModules() {
                    if (this.areAllSelected()) {
                        this.selectedModules = []; 
                    } else {
                        this.selectedModules = [...this.availableModules]; 
                    }
                }
            }
        }
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/platform/plans/create.blade.php ENDPATH**/ ?>