<?php $__env->startSection('title', 'Add New Branch - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-xs sm:text-sm font-bold text-gray-400 uppercase tracking-widest">Stores / Add Branch</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="w-full mx-auto space-y-4 sm:space-y-6 pb-10" x-data="storeCreateForm()">

        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <p class="text-xs sm:text-sm text-gray-500 font-medium">Register a new physical or virtual branch for your business.</p>
            </div>
            <a href="<?php echo e(route('admin.stores.index')); ?>"
                class="w-full sm:w-auto bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold transition-colors shadow-sm shrink-0 text-center">
                Back
            </a>
        </div>

        
        <?php if($errors->any()): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 sm:px-5 py-4 rounded-xl shadow-sm text-sm">
                <div class="font-bold flex items-center gap-2 mb-2">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i> Please fix the following errors:
                </div>
                <ul class="list-disc list-inside ml-2 sm:ml-6 space-y-1 text-xs sm:text-sm">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        
        <form action="<?php echo e(route('admin.stores.store')); ?>" method="POST" enctype="multipart/form-data" class="space-y-5 sm:space-y-6" @submit="isSubmitting = true">
            <?php echo csrf_field(); ?>

            
            
            
            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-sm sm:text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <i data-lucide="store" class="w-4 h-4 text-brand-500"></i> 1. Branch Identity
                </h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="lg:col-span-1 space-y-5">
                        
                        <div>
                            <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Branch Logo (Optional)</label>
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl border-2 border-dashed border-gray-300 overflow-hidden bg-gray-50 flex items-center justify-center shrink-0">
                                    <template x-if="logoPreview">
                                        <img :src="logoPreview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!logoPreview">
                                        <i data-lucide="image" class="w-6 h-6 sm:w-8 sm:h-8 text-gray-300"></i>
                                    </template>
                                </div>
                                <label class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 px-3 py-2 rounded-lg text-xs font-bold cursor-pointer transition-colors shadow-sm">
                                    Browse Image
                                    <input type="file" name="logo" class="hidden" @change="previewImage($event, 'logoPreview')" accept="image/*">
                                </label>
                            </div>
                        </div>

                        
                        <div>
                            <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Authorized Signature (For PDFs)</label>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div class="w-32 h-12 rounded-lg border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                                    <template x-if="signaturePreview">
                                        <img :src="signaturePreview" class="w-full h-full object-contain">
                                    </template>
                                    <template x-if="!signaturePreview">
                                        <i data-lucide="pen-tool" class="w-4 h-4 text-gray-300"></i>
                                    </template>
                                </div>
                                <label class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 px-3 py-2 rounded-lg text-xs font-bold cursor-pointer transition-colors shadow-sm">
                                    Upload Sign
                                    <input type="file" name="signature" class="hidden" @change="previewImage($event, 'signaturePreview')" accept="image/*">
                                </label>
                            </div>
                        </div>
                    </div>

                    
                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Branch Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="<?php echo e(old('name')); ?>" required placeholder="e.g. Downtown Hub"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Official Email</label>
                            <input type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="branch@company.com"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Contact Phone</label>
                            <input type="tel" name="phone" x-model="phone" maxlength="10" placeholder="0000000000" inputmode="numeric"
                                @input="phone = phone.replace(/\D/g, '').slice(0,10)"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                        </div>
                    </div>
                </div>
            </div>

            
            
            
            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-sm sm:text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-brand-500"></i> 2. Location Details
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5 mb-4 sm:mb-5">
                    <div>
                        <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">City</label>
                        <input type="text" name="city" value="<?php echo e(old('city')); ?>" placeholder="e.g. Ahmedabad"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">State</label>
                        <select name="state_id"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none bg-white shadow-sm">
                            <option value="">Select State</option>
                            <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($state->id); ?>" <?php echo e(old('state_id') == $state->id ? 'selected' : ''); ?>><?php echo e($state->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Pincode / Zip</label>
                        <input type="text" name="zip_code" x-model="zip" maxlength="6" placeholder="380001" inputmode="numeric"
                            @input="zip = zip.replace(/\D/g, '').slice(0,6)"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Full Street Address</label>
                    <textarea name="address" rows="2" placeholder="Shop number, building, street, landmark..."
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all resize-y shadow-sm"><?php echo e(old('address')); ?></textarea>
                </div>
            </div>

            
            
            
            
            <?php if($isMultiStore): ?>
                <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100 space-y-8">
                    
                    
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <i data-lucide="receipt" class="w-4 h-4 text-brand-500"></i> 3. Billing & Compliance <span class="text-gray-400 font-normal text-xs">(Overrides Global Settings)</span>
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">GSTIN</label>
                                <input type="text" name="gst_number" value="<?php echo e(old('gst_number')); ?>" placeholder="24AAAAA0000A1Z5" maxlength="15"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm uppercase font-mono focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Store UPI ID</label>
                                <input type="text" name="upi_id" value="<?php echo e(old('upi_id')); ?>" placeholder="store@bank"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Currency</label>
                                <select name="currency" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-bold text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none bg-white shadow-sm">
                                    <option value="INR" <?php echo e(old('currency') == 'INR' ? 'selected' : ''); ?>>₹ INR (Rupee)</option>
                                    <option value="USD" <?php echo e(old('currency') == 'USD' ? 'selected' : ''); ?>>$ USD (Dollar)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Invoice Prefix</label>
                                <input type="text" name="invoice_prefix" value="<?php echo e(old('invoice_prefix')); ?>" placeholder="e.g. BOM-INV-"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm uppercase font-mono focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Quotation Prefix</label>
                                <input type="text" name="quotation_prefix" value="<?php echo e(old('quotation_prefix')); ?>" placeholder="e.g. BOM-QTN-"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm uppercase font-mono focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                            </div>
                            <div class="hidden lg:block"></div> 
                        </div>
                    </div>

                    
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <i data-lucide="building-2" class="w-4 h-4 text-brand-500"></i> 4. Bank Account Details
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                            <div class="md:col-span-2 lg:col-span-1">
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Bank Name</label>
                                <input type="text" name="bank_name" value="<?php echo e(old('bank_name')); ?>" placeholder="e.g. HDFC Bank"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Account Name</label>
                                <input type="text" name="account_name" value="<?php echo e(old('account_name')); ?>" placeholder="e.g. Acme Corp Ltd"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Account Number</label>
                                <input type="text" name="account_number" value="<?php echo e(old('account_number')); ?>" placeholder="e.g. 502000123456"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">IFSC Code</label>
                                <input type="text" name="ifsc_code" value="<?php echo e(old('ifsc_code')); ?>" placeholder="e.g. HDFC0001234"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm uppercase font-mono focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Branch Name</label>
                                <input type="text" name="branch_name" value="<?php echo e(old('branch_name')); ?>" placeholder="e.g. CG Road Branch"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                            </div>
                        </div>
                    </div>

                    
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <i data-lucide="file-text" class="w-4 h-4 text-brand-500"></i> 5. Invoice Content
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Invoice Footer Note</label>
                                <textarea name="invoice_footer_note" rows="2" placeholder="e.g. Thank you for your business!"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all resize-y shadow-sm"><?php echo e(old('invoice_footer_note')); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Terms & Conditions</label>
                                <textarea name="invoice_terms" rows="3" placeholder="1. Goods once sold will not be taken back..."
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all resize-y shadow-sm"><?php echo e(old('invoice_terms')); ?></textarea>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endif; ?>

            
            
            
            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 sticky bottom-0 z-20">
                
                
                <label class="inline-flex items-center cursor-pointer shrink-0">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500"></div>
                    <div class="ms-3">
                        <span class="block text-[13px] font-bold text-gray-800">Active Branch</span>
                        <span class="block text-[11px] text-gray-400 font-normal">Visible for billing & inventory</span>
                    </div>
                </label>

                
                <button type="submit" :disabled="isSubmitting"
                    class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-md shadow-brand-500/20 flex items-center justify-center gap-2 transition-all disabled:opacity-70 active:scale-95 shrink-0">
                    <i data-lucide="save" class="w-4 h-4" x-show="!isSubmitting"></i>
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isSubmitting" x-cloak></i>
                    <span x-text="isSubmitting ? 'Registering...' : 'Register Branch'"></span>
                </button>

            </div>

        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function storeCreateForm() {
            return {
                isSubmitting: false,
                phone: '<?php echo e(old('phone')); ?>',
                zip: '<?php echo e(old('zip_code')); ?>',
                logoPreview: null,
                signaturePreview: null,

                previewImage(event, target) {
                    const file = event.target.files[0];
                    if (file) {
                        this[target] = URL.createObjectURL(file);
                    }
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/stores/create.blade.php ENDPATH**/ ?>