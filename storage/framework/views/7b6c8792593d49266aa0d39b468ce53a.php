

<?php $__env->startSection('title', 'My Stores - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">List / Stores</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6 pb-10" x-data="storeCrud()">

        <?php if(session('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('success')); ?>", 'success'));
            </script>
        <?php endif; ?>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>                
                <p class="text-sm text-gray-500 font-medium">Manage your different business branches and outlets.</p>
            </div>

            <?php if(check_plan_limit('stores')): ?>
                <button @click="openCreateModal()"
                    class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-all shadow-md active:scale-95 whitespace-nowrap">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Add New Branch
                </button>
            <?php else: ?>
                <div class="flex items-center gap-3">

                    <span
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-red-50 text-red-600 text-xs font-bold border border-red-100">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                        Branch Limit Reached
                    </span>

                    <button type="button"
                        class="bg-gray-100 text-gray-400 px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 cursor-not-allowed shadow-sm whitespace-nowrap"
                        title="You have reached your branch limit. Upgrade your plan to add more branches.">

                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        Add New Branch

                    </button>

                </div>
            <?php endif; ?>
        </div>
        

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php $__empty_1 = true; $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-lg transition-all duration-300 group relative">

                    <div class="absolute top-4 right-4">
                        <?php if($store->is_active): ?>
                            <span
                                class="bg-[#dcfce7] text-[#16a34a] px-2.5 py-1 rounded-lg font-bold text-[10px] uppercase tracking-wider">Active</span>
                        <?php else: ?>
                            <span
                                class="bg-gray-100 text-gray-400 px-2.5 py-1 rounded-lg font-bold text-[10px] uppercase tracking-wider">Inactive</span>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center gap-4 mb-6">
                        <div
                            class="w-16 h-16 rounded-2xl border border-gray-100 overflow-hidden bg-gray-50 flex-shrink-0 shadow-sm">
                            <img src="<?php echo e($store->logo_url); ?>" alt="<?php echo e($store->name); ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold text-[#212538] truncate"><?php echo e($store->name); ?></h3>
                            <div class="flex items-center gap-1.5 text-gray-400 text-xs font-medium">
                                <i data-lucide="map-pin" class="w-3 h-3"></i>
                                <span class="truncate"><?php echo e($store->city ?? 'Location N/A'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 mb-6 flex-1">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400 font-medium">GSTIN</span>
                            <span class="text-gray-700 font-mono font-bold"><?php echo e($store->gst_number ?? 'Not Set'); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400 font-medium">Currency</span>
                            <span class="text-gray-700 font-bold"><?php echo e($store->currency); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400 font-medium">Contact</span>
                            <span class="text-gray-700 font-bold"><?php echo e($store->phone ?? 'N/A'); ?></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-4 border-t border-gray-50">
                        <button
                            @click="openEditModal(<?php echo e($store->toJson()); ?>, '<?php echo e($store->logo_url); ?>', '<?php echo e($store->signature_url); ?>')"
                            class="flex-1 bg-gray-50 hover:bg-brand-50 text-gray-600 hover:text-brand-600 py-2.5 rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2">
                            <i data-lucide="settings-2" class="w-4 h-4"></i> Configure
                        </button>

                        <form action="<?php echo e(route('admin.stores.destroy', $store->id)); ?>" method="POST"
                            @submit.prevent="confirmDelete($event.target)">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit"
                                class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full py-20 text-center bg-white rounded-2xl border border-dashed border-gray-200">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="store" class="w-10 h-10 text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">No Stores Found</h3>
                    <p class="text-gray-500 text-sm max-w-xs mx-auto mt-1">Start by creating your first business branch to
                        manage inventory and sales.</p>
                </div>
            <?php endif; ?>
        </div>


        
        <div x-show="isModalOpen" style="display: none;"
            class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/70 backdrop-blur-sm transition-opacity"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="relative w-full max-w-5xl p-4 my-8" @click.away="closeModal()">
                <div
                    class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[90vh]">

                    
                    <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-white sticky top-0 z-20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                                <i data-lucide="store" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-[#212538]"
                                    x-text="modalMode === 'create' ? 'Setup New Branch' : 'Edit Branch Configuration'"></h3>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">Define your store identity, billing
                                    details, and location.</p>
                            </div>
                        </div>
                        <button @click="closeModal()" type="button"
                            class="text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl p-2 transition-colors">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    
                    <form :action="formAction" method="POST" enctype="multipart/form-data"
                        class="flex flex-col flex-1 overflow-hidden" @submit="BizAlert.loading('Processing...')">
                        <?php echo csrf_field(); ?>
                        <template x-if="modalMode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="overflow-y-auto flex-1 custom-scrollbar bg-gray-50/30">

                            
                            <div class="p-6 border-b border-gray-100 bg-white">
                                <div class="flex flex-col md:flex-row gap-8 items-start">
                                    
                                    <div class="flex flex-col items-center shrink-0">
                                        <div class="relative group">
                                            <div
                                                class="w-32 h-32 rounded-2xl border-2 border-dashed border-gray-300 overflow-hidden bg-gray-50 flex items-center justify-center group-hover:border-brand-400 transition-colors">
                                                <template x-if="logoPreview">
                                                    <img :src="logoPreview" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!logoPreview">
                                                    <div class="text-center">
                                                        <i data-lucide="image"
                                                            class="w-8 h-8 text-gray-300 mx-auto mb-1"></i>
                                                        <span class="text-[10px] font-bold text-gray-400 uppercase">Store
                                                            Logo</span>
                                                    </div>
                                                </template>
                                            </div>
                                            <label
                                                class="absolute -bottom-3 -right-3 bg-brand-600 text-white w-10 h-10 rounded-xl flex items-center justify-center cursor-pointer shadow-lg hover:bg-brand-700 transition-transform active:scale-95 border-2 border-white">
                                                <i data-lucide="camera" class="w-5 h-5"></i>
                                                <input type="file" name="logo_file" class="hidden"
                                                    @change="previewLogo($event)" accept="image/*">
                                            </label>
                                        </div>
                                    </div>

                                    
                                    <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div class="md:col-span-2">
                                            <label
                                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Store
                                                / Branch Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" x-model="formData.name" required
                                                placeholder="e.g. Ahmedabad Main Branch"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 font-bold focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all shadow-sm">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Official
                                                Email</label>
                                            <input type="email" name="email" x-model="formData.email"
                                                placeholder="store@company.com"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all shadow-sm">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Contact
                                                Phone</label>
                                            <input type="text" name="phone" x-model="formData.phone" maxlength="10"
                                                inputmode="numeric" placeholder="99251XXXXX"
                                                @input="formData.phone = $event.target.value.replace(/[^0-9]/g,'').slice(0,10)"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all shadow-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">

                                
                                <div class="space-y-5">
                                    <h4
                                        class="flex items-center gap-2 text-sm font-bold text-gray-800 border-b border-gray-200 pb-2">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-brand-500"></i> Location Details
                                    </h4>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">City</label>
                                            <input type="text" name="city" x-model="formData.city"
                                                placeholder="e.g. Ahmedabad"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all shadow-sm">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Zip/PIN</label>
                                            <input type="text" name="zip_code" x-model="formData.zip_code"
                                                placeholder="e.g. 380001" maxlength="6"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all shadow-sm">
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">State
                                            <span class="text-red-500">*</span></label>
                                        <select name="state_id" x-model="formData.state_id" required
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all appearance-none bg-white shadow-sm">
                                            <option value="">-- Select State --</option>
                                            <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($state->id); ?>"><?php echo e($state->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Full
                                            Street Address</label>
                                        <textarea name="address" x-model="formData.address" rows="3"
                                            placeholder="Shop number, building, street, landmark..."
                                            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all resize-none shadow-sm"></textarea>
                                    </div>
                                </div>

                                
                                <div class="space-y-5">
                                    <h4
                                        class="flex items-center gap-2 text-sm font-bold text-gray-800 border-b border-gray-200 pb-2">
                                        <i data-lucide="indian-rupee" class="w-4 h-4 text-brand-500"></i> Billing &
                                        Configuration
                                    </h4>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">GSTIN</label>
                                            <input type="text" name="gst_number" x-model="formData.gst_number"
                                                placeholder="24AAAAA0000A1Z5"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono uppercase text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all shadow-sm">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Currency</label>
                                            <select name="currency" x-model="formData.currency"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all appearance-none bg-white shadow-sm">
                                                <option value="INR">₹ INR (Rupee)</option>
                                                <option value="USD">$ USD (Dollar)</option>
                                            </select>
                                        </div>
                                    </div>

                                    
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                                            Store UPI ID <span
                                                class="bg-brand-100 text-brand-600 px-1.5 py-0.5 rounded text-[8px]">QR
                                                CODE</span>
                                        </label>
                                        <input type="text" name="upi_id" x-model="formData.upi_id"
                                            placeholder="e.g. storename@okhdfcbank"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all shadow-sm">
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Inv
                                                Prefix</label>
                                            <input type="text" name="invoice_prefix" x-model="formData.invoice_prefix"
                                                placeholder="INV-"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono uppercase text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all shadow-sm">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Pur
                                                Prefix</label>
                                            <input type="text" name="purchase_prefix"
                                                x-model="formData.purchase_prefix" placeholder="PUR-"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono uppercase text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all shadow-sm">
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Authorized
                                            Signature (For PDFs)</label>
                                        <div
                                            class="flex items-center gap-4 bg-white p-2 border border-gray-200 rounded-xl shadow-sm">
                                            <div
                                                class="w-24 h-12 rounded-lg border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                                                <template x-if="signaturePreview">
                                                    <img :src="signaturePreview"
                                                        class="max-w-full max-h-full object-contain">
                                                </template>
                                                <template x-if="!signaturePreview">
                                                    <i data-lucide="pen-tool" class="w-4 h-4 text-gray-300"></i>
                                                </template>
                                            </div>
                                            <label
                                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-xs font-bold cursor-pointer transition-colors w-full text-center">
                                                Upload Stamp/Sign
                                                <input type="file" name="signature_file" class="hidden"
                                                    @change="previewSignature($event)" accept="image/*">
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        
                        <div
                            class="p-5 border-t border-gray-200 bg-white flex items-center justify-between sticky bottom-0 z-20">
                            
                            <label class="relative inline-flex items-center cursor-pointer group">
                                <input type="checkbox" name="is_active" value="1" x-model="formData.is_active"
                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500 group-hover:shadow-sm">
                                </div>
                                <span class="ms-3 text-sm font-bold text-gray-700">Store is Active</span>
                            </label>

                            <div class="flex items-center gap-3">
                                <button type="button" @click="closeModal()"
                                    class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-100 hover:text-gray-900 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="bg-brand-500 hover:bg-brand-600 text-white px-8 py-2.5 rounded-xl text-sm font-bold transition-all active:scale-95 flex items-center gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                                    <span x-text="modalMode === 'create' ? 'Onboard Store' : 'Save Changes'"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function storeCrud() {
            return {
                isModalOpen: false,
                modalMode: 'create',
                formAction: '<?php echo e(route('admin.stores.store')); ?>',
                logoPreview: null,
                formData: {
                    name: '',
                    gst_number: '',
                    currency: 'INR',
                    email: '',
                    phone: '',
                    upi_id: '',
                    city: '',
                    state_id: '',
                    zip_code: '',
                    invoice_prefix: '',
                    purchase_prefix: '',
                    address: '',
                    is_active: true
                },
                signaturePreview: null,

                previewLogo(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.logoPreview = URL.createObjectURL(file);
                    }
                },

                openCreateModal() {
                    this.modalMode = 'create';
                    this.formAction = '<?php echo e(route('admin.stores.store')); ?>';
                    this.formData = {
                        name: '',
                        gst_number: '',
                        currency: 'INR',
                        email: '',
                        phone: '',
                        upi_id: '',
                        city: '',
                        state_id: '',
                        zip_code: '',
                        invoice_prefix: '',
                        purchase_prefix: '',
                        address: '',
                        is_active: true
                    };
                    this.logoPreview = null;
                    this.signaturePreview = null;
                    this.isModalOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                openEditModal(store, logoUrl, signatureUrl) {
                    this.modalMode = 'edit';
                    this.formAction = `/admin/stores/${store.id}`;
                    this.formData = {
                        name: store.name,
                        gst_number: store.gst_number || '',
                        currency: store.currency || 'INR',
                        phone: store.phone || '',
                        upi_id: store.upi_id || '',
                        email: store.email || '',
                        city: store.city || '',
                        state_id: store.state_id || '', // 🌟 Map new field
                        zip_code: store.zip_code || '', // 🌟 Map new field
                        invoice_prefix: store.invoice_prefix || '', // 🌟 Map new field
                        purchase_prefix: store.purchase_prefix || '', // 🌟 Map new field
                        address: store.address || '',
                        is_active: store.is_active
                    };
                    this.logoPreview = logoUrl;
                    this.signaturePreview = signatureUrl; // Set signature preview
                    this.isModalOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                // Add this new helper right below previewLogo()
                previewSignature(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.signaturePreview = URL.createObjectURL(file);
                    }
                },

                closeModal() {
                    this.isModalOpen = false;
                    document.body.style.overflow = 'auto';
                },

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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/stores.blade.php ENDPATH**/ ?>