<?php $__env->startSection('title', 'Add Warehouse - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-xs sm:text-sm font-bold text-gray-400 uppercase tracking-widest">Inventory / Add Warehouse</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="w-full" x-data="warehouseForm()">

        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <p class="text-sm text-gray-500 font-medium">Create a new storage location for your inventory.</p>
            </div>
            <a href="<?php echo e(route('admin.warehouses.index')); ?>"
                class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm shrink-0">
                Back
            </a>
        </div>

        
        <?php if($errors->any()): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl shadow-sm text-sm">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i> Please fix the following errors:
                </div>
                <ul class="list-disc list-inside ml-6 space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        
        <form action="<?php echo e(route('admin.warehouses.store')); ?>" method="POST" class="space-y-6" @submit="isSubmitting = true">
            <?php echo csrf_field(); ?>

            
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">1. Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Warehouse Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="<?php echo e(old('name')); ?>" required placeholder="e.g. Main Godown"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Linked Store <span class="text-red-500">*</span></label>
                        <select name="store_id" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none bg-white">
                            <option value="">Select a Store</option>
                            <?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($store->id); ?>" <?php echo e(old('store_id') == $store->id ? 'selected' : ''); ?>>
                                    <?php echo e($store->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>

            
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">2. Contact Details <span class="text-gray-400 font-normal text-xs">(Optional)</span></h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Manager / Contact Person</label>
                        <input type="text" name="contact_person" value="<?php echo e(old('contact_person')); ?>" placeholder="John Doe"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Phone Number  <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" x-model="phone" maxlength="10" placeholder="0000000000" inputmode="numeric"
                            @input="phone = phone.replace(/\D/g, '').slice(0,10)" required
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Email Address</label>
                        <input type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="warehouse@example.com"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all">
                    </div>
                </div>
            </div>

            
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">3. Location Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">City</label>
                        <input type="text" name="city" value="<?php echo e(old('city')); ?>" placeholder="e.g. Ahmedabad"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">State</label>
                        <select name="state_id"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none bg-white">
                            <option value="">Select State</option>
                            <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($state->id); ?>" <?php echo e(old('state_id') == $state->id ? 'selected' : ''); ?>>
                                    <?php echo e($state->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Pincode / Zip</label>
                        <input type="text" name="zip_code" x-model="zip" maxlength="6" placeholder="380001" inputmode="numeric"
                            @input="zip = zip.replace(/\D/g, '').slice(0,6)"
                            class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-[13px] font-bold text-gray-700 mb-1.5">Full Address</label>
                    <textarea name="address" rows="2" placeholder="Exact storage site location..."
                        class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all resize-y"><?php echo e(old('address')); ?></textarea>
                </div>
            </div>

           
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">4. Settings</h2>
                <div class="flex flex-col sm:flex-row gap-8">
                    
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" class="sr-only peer" <?php echo e(old('is_default') ? 'checked' : ''); ?>>
                        
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <div class="ms-3">
                            <span class="block text-[13px] font-bold text-gray-800">Primary Hub</span>
                            <span class="block text-xs text-gray-400 font-normal">Auto-route new items here</span>
                        </div>
                    </label>

                    <label class="inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                        
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#108c2a]"></div>
                        <div class="ms-3">
                            <span class="block text-[13px] font-bold text-gray-800">Active Status</span>
                            <span class="block text-xs text-gray-400 font-normal">Visible in dropdowns</span>
                        </div>
                    </label>

                </div>
            </div>

            
            <div class="flex justify-end pt-2">
                <button type="submit" :disabled="isSubmitting"
                    class="bg-[#108c2a] hover:bg-[#0c6b1f] text-white px-8 py-3 rounded-xl text-sm font-bold shadow-md flex items-center justify-center gap-2 transition-all disabled:opacity-70">
                    <i data-lucide="save" class="w-4 h-4" x-show="!isSubmitting"></i>
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isSubmitting" x-cloak></i>
                    <span x-text="isSubmitting ? 'Saving...' : 'Save Warehouse'"></span>
                </button>
            </div>

        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function warehouseForm() {
            return {
                isSubmitting: false,
                phone: '<?php echo e(old('phone')); ?>',
                zip: '<?php echo e(old('zip_code')); ?>',
            }
        }
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/warehouses/create.blade.php ENDPATH**/ ?>