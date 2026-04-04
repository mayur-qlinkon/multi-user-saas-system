

<?php $__env->startSection('title', 'Warehouses - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-400 uppercase tracking-widest">Inventory / Warehouses</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6 pb-10" x-data="warehouseCrud()">

        <?php if(session('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('success')); ?>", 'success'));
            </script>
        <?php endif; ?>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>                
                <p class="text-sm text-gray-500 font-medium">Manage stock storage locations across your different branches.
                </p>
            </div>

            <button @click="openCreateModal()"
                class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-all shadow-md active:scale-95 whitespace-nowrap">
                <i data-lucide="plus-circle" class="w-5 h-5"></i> Add Warehouse
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">

            <div
                class="px-6 py-4 flex flex-col sm:flex-row justify-between items-center border-b border-gray-100 gap-4 bg-white">
                <h2 class="text-[1.15rem] font-bold text-[#212538] tracking-tight">All Storage Locations</h2>

                <div class="w-full sm:w-64 relative">
                    <input type="text" x-model="search" placeholder="Search name or city..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all placeholder:text-gray-400">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-[#f8fafc]">
                        <tr>
                            <th class="px-6 py-4">WAREHOUSE / STORE</th>
                            <th class="px-6 py-4">CONTACT PERSON</th>
                            <th class="px-6 py-4">LOCATION</th>
                            <th class="px-6 py-4 text-center">STATUS</th>
                            <th class="px-6 py-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 transition-colors"
                                x-show="matchesSearch('<?php echo e(strtolower($warehouse->name)); ?>', '<?php echo e(strtolower($warehouse->city)); ?>')">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-[#475569] text-[13.5px]"><?php echo e($warehouse->name); ?></span>
                                        <span
                                            class="text-[10px] bg-brand-50 text-brand-600 px-1.5 py-0.5 rounded font-black uppercase mt-1 w-fit">
                                            Store: <?php echo e($warehouse->store->name ?? 'N/A'); ?>

                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-gray-700 text-[13px]"><?php echo e($warehouse->contact_person ?? 'Not Set'); ?></span>
                                        <span
                                            class="text-[12px] text-gray-400 font-medium"><?php echo e($warehouse->phone ?? '-'); ?></span>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 text-gray-500 font-medium italic">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-gray-300"></i>
                                        <?php echo e($warehouse->city ?? 'N/A'); ?>

                                        <?php echo e($warehouse->state_id ? ', ' . $warehouse->state->name : ''); ?>

                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <?php if($warehouse->is_default): ?>
                                            <span
                                                class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded-md font-black text-[9px] uppercase tracking-tighter border border-blue-100">Primary
                                                Hub</span>
                                        <?php endif; ?>

                                        <?php if($warehouse->is_active): ?>
                                            <span
                                                class="bg-[#dcfce7] text-[#16a34a] px-2.5 py-0.5 rounded-md font-bold text-[10px] uppercase">Active</span>
                                        <?php else: ?>
                                            <span
                                                class="bg-gray-100 text-gray-400 px-2.5 py-0.5 rounded-md font-bold text-[10px] uppercase">Disabled</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditModal(<?php echo e($warehouse->toJson()); ?>)"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-brand-100 text-brand-600 hover:bg-brand-50 transition-colors"
                                            title="Edit">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <form action="<?php echo e(route('admin.warehouses.destroy', $warehouse->id)); ?>"
                                            method="POST" @submit.prevent="confirmDelete($event.target)"
                                            class="inline-block">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-100 text-red-500 hover:bg-red-50 transition-colors"
                                                title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="box" class="w-10 h-10 mb-2 opacity-20"></i>
                                        <p class="font-medium">No warehouses found for your stores.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="h-12 border-t border-gray-100 bg-white w-full"></div>
        </div>

        <div x-show="isModalOpen" x-cloak style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/60 backdrop-blur-sm transition-opacity"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="relative w-full max-w-3xl p-4" @click.away="closeModal()">
                <div
                    class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col h-full max-h-[85vh] md:max-h-[90vh]">

                    <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-white sticky top-0 z-10">
                        <div>
                            <h3 class="text-xl font-bold text-[#212538]"
                                x-text="modalMode === 'create' ? 'Create New Warehouse' : 'Edit Warehouse Details'"></h3>
                            <p class="text-xs text-gray-400 font-medium mt-0.5">Specify storage capacity and contact info
                                for this location.</p>
                        </div>
                        <button @click="closeModal()" type="button"
                            class="text-gray-400 hover:bg-gray-100 rounded-full p-2 transition-colors">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <form :action="formAction" method="POST" class="flex flex-col flex-1 overflow-hidden"
                        @submit="BizAlert.loading('Saving...')">
                        <?php echo csrf_field(); ?>
                        <template x-if="modalMode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="p-6 overflow-y-auto flex-1 custom-scrollbar space-y-5">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Warehouse
                                        Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" x-model="formData.name" required
                                        placeholder="e.g. Main Hub Godown"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                                </div>

                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Linked
                                        Store <span class="text-red-500">*</span></label>
                                    <select name="store_id" x-model="formData.store_id" required
                                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none appearance-none cursor-pointer">
                                        <option value="">Select a Branch</option>
                                        <?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($store->id); ?>"><?php echo e($store->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Contact
                                        Person</label>
                                    <input type="text" name="contact_person" x-model="formData.contact_person"
                                        placeholder="Manager Name"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                        Phone Number
                                    </label>

                                    <input type="tel" name="phone" x-model="formData.phone"
                                        placeholder="0000000000" maxlength="10" minlength="10" pattern="[0-9]{10}"
                                        inputmode="numeric"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email
                                        (Optional)</label>
                                    <input type="email" name="email" x-model="formData.email"
                                        placeholder="warehouse@example.com"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                                </div>
                            </div>

                            <div class="bg-gray-50/50 rounded-2xl p-5 border border-gray-100 space-y-4">
                                <h4
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2">
                                    Location Details</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-1">
                                        <label
                                            class="block text-[11px] font-bold text-gray-400 uppercase mb-1">City</label>
                                        <input type="text" name="city" x-model="formData.city"
                                            placeholder="Ahmedabad"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-brand-500/20">
                                    </div>
                                    <div class="md:col-span-1">
                                        <label
                                            class="block text-[11px] font-bold text-gray-400 uppercase mb-1">State</label>
                                        <select name="state_id" x-model="formData.state_id"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-brand-500/20 bg-white">
                                            <option value="">Select State</option>
                                            <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($state->id); ?>"><?php echo e($state->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="md:col-span-1">
                                        <label
                                            class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Pincode</label>
                                        <input 
                                            type="text" 
                                            name="zip_code" 
                                            x-model="formData.zip_code"
                                            placeholder="380001"
                                            maxlength="6"
                                            pattern="[0-9]{6}"
                                            inputmode="numeric"
                                            @input="formData.zip_code = formData.zip_code.replace(/\D/g, '').slice(0,6)"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-brand-500/20">                                          
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Full
                                        Address</label>
                                    <textarea name="address" x-model="formData.address" rows="2" placeholder="Exact storage site location..."
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm text-gray-700 outline-none focus:ring-2 focus:ring-brand-500/20 resize-none"></textarea>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-6 pt-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_default" value="1"
                                        x-model="formData.is_default" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                    </div>
                                    <span class="ms-3 text-sm font-bold text-gray-600">Set as Primary Warehouse</span>
                                </label>

                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="formData.is_active"
                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600">
                                    </div>
                                    <span class="ms-3 text-sm font-bold text-gray-600">Active</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="p-6 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3 sticky bottom-0 z-10">
                            <button type="button" @click="closeModal()"
                                class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-100 transition-colors">Cancel</button>
                            <button type="submit"
                                class="bg-brand-500 hover:bg-brand-600 text-white px-10 py-2.5 rounded-xl text-sm font-bold transition-all active:scale-95">
                                <span x-text="modalMode === 'create' ? 'Create Warehouse' : 'Save Changes'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function warehouseCrud() {
            return {
                search: '',
                isModalOpen: false,
                modalMode: 'create',
                formAction: '<?php echo e(route('admin.warehouses.store')); ?>',
                formData: {
                    store_id: '',
                    name: '',
                    contact_person: '',
                    phone: '',
                    email: '',
                    city: '',
                    state: '',
                    zip_code: '',
                    address: '',
                    is_active: true,
                    is_default: false
                },

                matchesSearch(name, city) {
                    if (this.search === '') return true;
                    const query = this.search.toLowerCase();
                    return name.includes(query) || city.includes(query);
                },

                openCreateModal() {
                    this.modalMode = 'create';
                    this.formAction = '<?php echo e(route('admin.warehouses.store')); ?>';
                    this.formData = {
                        store_id: '',
                        name: '',
                        contact_person: '',
                        phone: '',
                        email: '',
                        city: '',
                        state_id: '',
                        zip_code: '',
                        address: '',
                        is_active: true,
                        is_default: false
                    };
                    this.isModalOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                openEditModal(w) {
                    this.modalMode = 'edit';
                    this.formAction = `/admin/warehouses/${w.id}`;
                    this.formData = {
                        store_id: w.store_id,
                        name: w.name,
                        contact_person: w.contact_person || '',
                        phone: w.phone || '',
                        email: w.email || '',
                        city: w.city || '',
                        state_id: w.state_id || '',
                        zip_code: w.zip_code || '',
                        address: w.address || '',
                        is_active: w.is_active,
                        is_default: w.is_default
                    };
                    this.isModalOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                closeModal() {
                    this.isModalOpen = false;
                    document.body.style.overflow = 'auto';
                },

                confirmDelete(form) {
                    BizAlert.confirm(
                        'Delete Warehouse?',
                        'This location will be permanently removed. Ensure all stock has been transferred first!',
                        'Yes, Delete'
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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/warehouses.blade.php ENDPATH**/ ?>