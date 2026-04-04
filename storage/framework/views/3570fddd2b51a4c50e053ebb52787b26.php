

<?php $__env->startSection('title', 'My Addresses'); ?>

<?php $__env->startSection('content'); ?>
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">

        
        <div class="bg-slate-50/80 px-6 py-5 border-b border-slate-100">
            <h2 class="font-bold text-slate-900 text-lg tracking-tight">Delivery Details</h2>
            <p class="text-sm text-slate-500 mt-1">Update your default shipping address for faster checkout.</p>
        </div>

        
        <div class="p-6">
            
            <form id="address-form" class="space-y-6">
                
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-bold text-slate-700 mb-2 text-[12px] uppercase tracking-wide">
                            Full Name <span class="text-red-500 ml-0.5">*</span>
                        </label>
                        <div class="relative">
                            <i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            <input type="text" name="name" id="name"
                                value="<?php echo e(old('name', $client?->name ?? $user->name)); ?>"
                                class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all bg-slate-50 focus:bg-white">
                        </div>
                        <span class="text-[11px] font-semibold text-red-500 error-msg mt-1 block" id="error-name"></span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-2 text-[12px] uppercase tracking-wide">
                            Phone Number <span class="text-red-500 ml-0.5">*</span>
                        </label>
                        <div class="relative">
                            <i data-lucide="phone" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            <input type="tel" name="phone" id="phone"
                                value="<?php echo e(old('phone', $client?->phone ?? $user->phone)); ?>"
                                class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all bg-slate-50 focus:bg-white">
                        </div>
                        <span class="text-[11px] font-semibold text-red-500 error-msg mt-1 block" id="error-phone"></span>
                    </div>
                </div>

                
                <div>
                    <label class="block font-bold text-slate-700 mb-2 text-[12px] uppercase tracking-wide">
                        Complete Address <span class="text-red-500 ml-0.5">*</span>
                    </label>
                    <textarea name="address" id="address" rows="3"
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all bg-slate-50 focus:bg-white resize-none"
                        placeholder="House/flat no, street, area, landmark"><?php echo e(old('address', $client?->address)); ?></textarea>
                    <span class="text-[11px] font-semibold text-red-500 error-msg mt-1 block" id="error-address"></span>
                </div>

                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-bold text-slate-700 mb-2 text-[12px] uppercase tracking-wide">
                            City <span class="text-red-500 ml-0.5">*</span>
                        </label>
                        <input type="text" name="city" id="city" value="<?php echo e(old('city', $client?->city)); ?>"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all bg-slate-50 focus:bg-white">
                        <span class="text-[11px] font-semibold text-red-500 error-msg mt-1 block" id="error-city"></span>
                    </div>
                    
                    <div>
                        <label class="block font-bold text-slate-700 mb-2 text-[12px] uppercase tracking-wide">
                            PIN Code / Zip <span class="text-red-500 ml-0.5">*</span>
                        </label>
                        <input type="text" name="zip_code" id="zip_code"
                            value="<?php echo e(old('zip_code', $client?->zip_code)); ?>" inputmode="numeric" pattern="[0-9]*" maxlength="10"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all bg-slate-50 focus:bg-white" />
                        <span class="text-[11px] font-semibold text-red-500 error-msg mt-1 block" id="error-zip_code"></span>
                    </div>
                </div>

                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-bold text-slate-700 mb-2 text-[12px] uppercase tracking-wide">
                            State / Province <span class="text-red-500 ml-0.5">*</span>
                        </label>
                        <div class="relative">
                            <select name="state_id" id="state_id"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all appearance-none bg-slate-50 focus:bg-white text-slate-900">
                                <option value="" disabled <?php echo e(!$client?->state_id ? 'selected' : ''); ?>>Select State</option>
                                <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($state->id); ?>" <?php echo e($client?->state_id == $state->id ? 'selected' : ''); ?>>
                                        <?php echo e($state->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                        </div>
                        <span class="text-[11px] font-semibold text-red-500 error-msg mt-1 block" id="error-state_id"></span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-2 text-[12px] uppercase tracking-wide">
                            Country <span class="text-red-500 ml-0.5">*</span>
                        </label>
                        <div class="relative">
                            <select name="country" id="country"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all appearance-none bg-slate-50 focus:bg-white">
                                <option value="India" <?php echo e(($client?->country ?? 'India') == 'India' ? 'selected' : ''); ?>>India</option>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                        </div>
                        <span class="text-[11px] font-semibold text-red-500 error-msg mt-1 block" id="error-country"></span>
                    </div>
                </div>

                
                <div class="flex gap-4 pt-6 mt-6 border-t border-slate-100">
                    <button type="button" id="save-btn" 
    class="px-6 py-2.5 bg-primary hover:bg-primaryDark text-white rounded-xl text-sm font-bold shadow-md shadow-primary/20 transition-all flex items-center gap-2 hover:-translate-y-0.5">
                        <span>Save Address</span>
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin hidden" id="btn-loader"></i>
                    </button>
                    <button type="reset" class="px-6 py-2.5 border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        Discard
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btn = document.getElementById('save-btn');

                if (btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();

                        const form = document.getElementById('address-form');
                        const loader = document.getElementById('btn-loader');
                        const formData = new FormData(form);

                        // Clear errors
                        document.querySelectorAll('.error-msg').forEach(el => el.textContent = '');

                        // UI Loading
                        btn.disabled = true;
                        btn.classList.add('opacity-75');
                        loader.classList.remove('hidden');

                        // Dynamic URL fetching the tenant slug
                        const url = "<?php echo e(route('storefront.portal.addresses.store', ['slug' => request()->route('slug')])); ?>";

                        fetch(url, {
                            method: "POST",
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        })
                        .then(async response => {
                            const data = await response.json();
                            if (!response.ok) return Promise.reject({ status: response.status, data: data });
                            return data;
                        })
                        .then(data => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500,
                                customClass: { popup: 'rounded-2xl' }
                            });
                        })
                        .catch(error => {
                            if (error.status === 422) {
                                const errors = error.data.errors;
                                for (const [key, value] of Object.entries(errors)) {
                                    const errorSpan = document.getElementById('error-' + key);
                                    if (errorSpan) errorSpan.textContent = value[0];
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.data?.message || 'Something went wrong.',
                                    confirmButtonColor: 'var(--color-primary)',
                                    customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl' }
                                });
                            }
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.classList.remove('opacity-75');
                            loader.classList.add('hidden');
                        });
                    });
                }
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.customer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/customer/addresses.blade.php ENDPATH**/ ?>