

<?php $__env->startSection('title', 'Suppliers Management - Qlinkon BIZNESS'); ?>
<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">List / Suppliers</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] {
            display: none !important;
        }

        body.modal-open {
            overflow: hidden;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="pb-10" x-data="supplierCrud(<?php echo \Illuminate\Support\Js::from($suppliers)->toHtml() ?>)">

        <?php if(session('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('success')); ?>", 'success'));
            </script>
        <?php endif; ?>
        

        <div class="bg-white rounded-t-xl shadow-sm border border-gray-100 p-4 border-b-0 mb-0">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                
                
                <div class="w-full md:w-auto flex-1 max-w-xl">
                    <div class="relative w-full">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[#108c2a]"></i>
                        
                        <input type="text" x-model="search" placeholder="Search Name, Phone, or City..."
                            class="w-full border border-gray-200 rounded-lg pl-10 pr-10 py-2.5 text-sm text-gray-700 focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all placeholder-gray-400">

                        <button type="button" x-show="search.length > 0" @click="search = ''" x-cloak
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 p-1.5 rounded-md transition-colors flex items-center justify-center"
                            title="Clear Filters">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-start md:justify-end">

                    <?php if(has_permission('suppliers.export')): ?>
                        <button type="button" @click="exportCSV()"
                            class="bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 px-3 md:px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#108c2a]"></i> CSV
                        </button>                                        
                        <button type="button" @click="exportPDF()"
                            class="bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 px-3 md:px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                            <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i> PDF
                        </button>
                    <?php endif; ?>

                    <?php if(has_permission('suppliers.create')): ?>
                        <button type="button" @click="openCreateModal()"
                            class="bg-[#108c2a] hover:bg-green-700 text-white px-4 md:px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add Supplier
                        </button>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        
        <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-[#f8fafc]">
                        <tr>
                            <th class="px-6 py-4">SUPPLIER DETAILS</th>
                            <th class="px-6 py-4">CONTACT</th>
                            <th class="px-6 py-4">CITY</th>
                            <th class="px-6 py-4 text-center">STATUS</th>
                            <th class="px-6 py-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 transition-colors group"
                                x-show="matchesSearch('<?php echo e(strtolower($supplier->name)); ?>', '<?php echo e(strtolower($supplier->phone)); ?>', '<?php echo e(strtolower($supplier->city)); ?>')">

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-full bg-green-50 border border-green-100 text-[#108c2a] flex items-center justify-center text-sm font-bold shrink-0">
                                            <?php echo e(strtoupper(substr($supplier->name, 0, 1))); ?>

                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="font-bold text-[#475569] text-[13.5px]"><?php echo e($supplier->name); ?></span>
                                            <span
                                                class="text-[11px] text-gray-400 truncate max-w-[200px]"><?php echo e($supplier->email ?? 'No email added'); ?></span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-[#475569]">
                                    <div class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400"></i>
                                        <?php echo e($supplier->phone ?? 'N/A'); ?>

                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <?php if($supplier->city): ?>
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                            <?php echo e($supplier->city); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic font-medium">-</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php if($supplier->is_active): ?>
                                        <span
                                            class="bg-[#dcfce7] text-[#16a34a] px-3 py-1 rounded-md font-bold text-[10px] uppercase tracking-wider">Active</span>
                                    <?php else: ?>
                                        <span
                                            class="bg-gray-200 text-gray-500 px-3 py-1 rounded-md font-bold text-[10px] uppercase tracking-wider">Inactive</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center justify-end gap-2 transition-opacity">
                                        <?php if(has_permission('suppliers.update')): ?>
                                            <button @click="openEditModal(<?php echo e($supplier->toJson()); ?>)"
                                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 flex items-center justify-center transition-colors"
                                                title="Edit">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>
                                        <?php endif; ?>

                                        
                                        <?php if(has_permission('suppliers.delete')): ?>
                                            <form action="<?php echo e(route('admin.suppliers.destroy', $supplier->id)); ?>" method="POST"
                                                @submit.prevent="confirmDelete($event.target)" class="inline-block">
                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors"
                                                    title="Delete">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400 font-medium">
                                    <div class="flex flex-col items-center justify-center">
                                        <i data-lucide="users" class="w-12 h-12 mb-3 text-gray-300"></i>
                                        <p class="text-sm font-medium">No suppliers found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="h-4 bg-white w-full"></div>
        </div>

        
        <div x-show="isModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/60 backdrop-blur-sm transition-opacity p-4 md:p-6"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="relative w-full max-w-4xl" @click.away="closeModal()">
                <div
                    class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col h-full max-h-[90vh]">

                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-100 bg-white sticky top-0 z-20 shrink-0">
                        <div>
                            <h3 class="text-xl font-bold text-[#212538]"
                                x-text="modalMode === 'create' ? 'Onboard New Supplier' : 'Update Supplier Details'"></h3>
                            <p class="text-xs text-gray-400 font-medium mt-1">Maintain accurate vendor records for
                                inventory and billing.</p>
                        </div>
                        <button @click="closeModal()" type="button"
                            class="text-gray-400 hover:bg-gray-100 hover:text-gray-700 rounded-full p-2 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="formAction" method="POST" class="flex flex-col flex-1 overflow-hidden"
                        @submit="BizAlert.loading('Saving...')">
                        <?php echo csrf_field(); ?>
                        <template x-if="modalMode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="p-6 overflow-y-auto flex-1 space-y-8 custom-scrollbar">

                            <div>
                                <h4 class="text-xs font-bold text-gray-800 mb-4 uppercase tracking-widest border-b pb-2">1.
                                    Basic Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="md:col-span-2">
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Company
                                            / Vendor Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="name" x-model="formData.name" required
                                            placeholder="e.g. Acme Corp"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none transition-all">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Phone
                                            Number</label>
                                        <input type="tel" name="phone" x-model="formData.phone"
                                            placeholder="+91 00000 00000" maxlength="10" minlength="10"
                                            pattern="[0-9]{10}" inputmode="numeric"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none transition-all">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email
                                            Address</label>
                                        <input type="email" name="email" x-model="formData.email"
                                            placeholder="vendor@email.com"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none transition-all">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4
                                    class="text-xs font-bold text-[#108c2a] mb-4 uppercase tracking-widest border-b border-[#108c2a]/20 pb-2">
                                    2. Compliance & Location</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Registration
                                            Type <span class="text-red-500">*</span></label>
                                        <select name="registration_type" x-model="formData.registration_type"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none bg-white">
                                            <option value="regular">Regular</option>
                                            <option value="composition">Composition</option>
                                            <option value="unregistered">Unregistered</option>
                                            <option value="sez">SEZ</option>
                                            <option value="overseas">Overseas</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">GSTIN</label>
                                        <input type="text" name="gstin" x-model="formData.gstin"
                                            placeholder="15 Digit GSTIN" maxlength="15"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm uppercase focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">PAN
                                            Number</label>
                                        <input type="text" name="pan" x-model="formData.pan"
                                            placeholder="10 Digit PAN" maxlength="10"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm uppercase focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">City</label>
                                        <input type="text" name="city" x-model="formData.city"
                                            placeholder="e.g. Mumbai"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">State
                                            <span class="text-red-500">*</span></label>
                                        <select name="state_id" x-model="formData.state_id" required
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none bg-white">
                                            <option value="">Select State</option>
                                            <?php $__currentLoopData = $states ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($state->id); ?>"><?php echo e($state->name); ?>

                                                    (<?php echo e($state->code); ?>)
                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">PIN
                                            Code</label>
                                        <input type="text" name="pincode" x-model="formData.pincode"
                                            placeholder="e.g. 400001"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Physical
                                        Address</label>
                                    <textarea name="address" x-model="formData.address" rows="2" placeholder="Full office or warehouse address..."
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none transition-all resize-none"></textarea>
                                </div>
                            </div>

                            <div>
                                <h4
                                    class="text-xs font-bold text-orange-600 mb-4 uppercase tracking-widest border-b border-orange-100 pb-2">
                                    3. Banking & Credit Setup</h4>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-5 bg-orange-50/40 p-5 rounded-xl border border-orange-100">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Opening
                                            Bal. (₹)</label>
                                        <input type="number" step="0.01" name="opening_balance"
                                            x-model="formData.opening_balance"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none bg-white">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Balance
                                            Type</label>
                                        <select name="balance_type" x-model="formData.balance_type"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none bg-white">
                                            <option value="payable">Payable (We owe)</option>
                                            <option value="advance">Advance (They owe)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Credit
                                            Days</label>
                                        <input type="number" name="credit_days" x-model="formData.credit_days"
                                            placeholder="e.g. 30"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none bg-white">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Credit
                                            Limit (₹)</label>
                                        <input type="number" step="0.01" name="credit_limit"
                                            x-model="formData.credit_limit" placeholder="0 = No Limit"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none bg-white">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                                    <div class="md:col-span-2">
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Bank
                                            Name</label>
                                        <input type="text" name="bank_name" x-model="formData.bank_name"
                                            placeholder="e.g. HDFC Bank"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Account
                                            Number</label>
                                        <input type="text" name="account_number" x-model="formData.account_number"
                                            placeholder="Account Number"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">IFSC
                                            Code</label>
                                        <input type="text" name="ifsc_code" x-model="formData.ifsc_code"
                                            placeholder="e.g. HDFC0001234"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm uppercase focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label
                                            class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Branch
                                            Name</label>
                                        <input type="text" name="branch" x-model="formData.branch"
                                            placeholder="e.g. Navrangpura"
                                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-xs font-bold text-gray-800 mb-4 uppercase tracking-widest border-b pb-2">4.
                                    Additional Notes</h4>
                                <textarea x-model="formData.notes" rows="2" name="notes"
                                    placeholder="Any internal notes or terms regarding this supplier..."
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none transition-all resize-none mb-4"></textarea>

                                <label
                                    class="relative inline-flex items-center cursor-pointer bg-gray-50 p-3 rounded-xl border border-gray-100 pr-5 w-fit">
                                    <input type="checkbox" name="is_active" value="1" x-model="formData.is_active"
                                        class="sr-only peer">
                                    <div
                                        class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#108c2a]">
                                    </div>
                                    <span class="ms-3 text-sm font-bold text-gray-700">Active Supplier Account</span>
                                </label>
                            </div>

                        </div>

                        <div
                            class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 sticky bottom-0 z-20 shrink-0">
                            <button type="button" @click="closeModal()"
                                class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-bold hover:bg-white hover:shadow-sm transition-all">Cancel</button>
                            <button type="submit"
                                class="bg-brand-500 hover:bg-brand-600 text-white px-8 py-2.5 rounded-xl text-sm font-bold shadow-md transition-all active:scale-95 flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span x-text="modalMode === 'create' ? 'Save Supplier' : 'Update Details'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

    <script>
        function supplierCrud(allSuppliersData) {
            return {
                allSuppliers: allSuppliersData,
                search: '',
                isModalOpen: false,
                modalMode: 'create',
                formAction: '<?php echo e(route('admin.suppliers.store')); ?>',

                formData: {
                    name: '',
                    email: '',
                    phone: '',
                    address: '',
                    city: '',
                    pincode: '',
                    state_id: '',
                    gstin: '',
                    pan: '',
                    registration_type: 'regular',
                    bank_name: '',
                    account_number: '',
                    ifsc_code: '',
                    branch: '',
                    opening_balance: 0,
                    balance_type: 'payable',
                    credit_days: 0,
                    credit_limit: 0,
                    is_active: true,
                    notes: '',
                },

                matchesSearch(name, phone, city) {
                    if (this.search === '') return true;
                    const query = this.search.toLowerCase();
                    return name.includes(query) || phone.includes(query) || city.includes(query);
                },

                openCreateModal() {
                    document.body.classList.add('modal-open');
                    this.modalMode = 'create';
                    this.formAction = '<?php echo e(route('admin.suppliers.store')); ?>';
                    this.formData = {
                        name: '',
                        email: '',
                        phone: '',
                        address: '',
                        city: '',
                        pincode: '',
                        state_id: '',
                        gstin: '',
                        pan: '',
                        registration_type: 'regular',
                        bank_name: '',
                        account_number: '',
                        ifsc_code: '',
                        branch: '',
                        opening_balance: 0,
                        balance_type: 'payable',
                        credit_days: 0,
                        credit_limit: 0,
                        is_active: true,
                        notes: '',
                    };
                    this.isModalOpen = true;
                },

                openEditModal(sup) {
                    document.body.classList.add('modal-open');
                    this.modalMode = 'edit';
                    this.formAction = `/admin/suppliers/${sup.id}`;
                    this.formData = {
                        name: sup.name,
                        email: sup.email || '',
                        phone: sup.phone || '',
                        address: sup.address || '',
                        city: sup.city || '',
                        pincode: sup.pincode || '',
                        state_id: sup.state_id || '',
                        gstin: sup.gstin || '',
                        pan: sup.pan || '',
                        registration_type: sup.registration_type || 'regular',
                        bank_name: sup.bank_name || '',
                        account_number: sup.account_number || '',
                        ifsc_code: sup.ifsc_code || '',
                        branch: sup.branch || '',
                        opening_balance: sup.opening_balance || 0,
                        balance_type: sup.balance_type || 'payable',
                        credit_days: sup.credit_days || 0,
                        credit_limit: sup.credit_limit || 0,
                        is_active: sup.is_active,
                        notes: sup.notes || '',
                    };
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                    document.body.classList.remove('modal-open');
                },

                confirmDelete(form) {
                    BizAlert.confirm('Delete Supplier?',
                            'Old transaction records will be preserved, but this supplier will be hidden.', 'Yes, Delete')
                        .then((result) => {
                            if (result.isConfirmed) {
                                BizAlert.loading('Processing...');
                                form.submit();
                            }
                        });
                },

                // --- EXPORT LOGIC ---
                exportCSV() {
                    const headers = ["Name", "Email", "Phone", "GSTIN", "PAN", "City", "Pincode", "Address", "Bank Name",
                        "Acc No.", "IFSC", "Current Balance"
                    ];
                    const rows = this.allSuppliers.map(sup => [
                        `"${sup.name || ''}"`,
                        `"${sup.email || ''}"`,
                        `"${sup.phone || ''}"`,
                        `"${sup.gstin || ''}"`,
                        `"${sup.pan || ''}"`,
                        `"${sup.city || ''}"`,
                        `"${sup.pincode || ''}"`,
                        `"${sup.address || ''}"`,
                        `"${sup.bank_name || ''}"`,
                        `"${sup.account_number || ''}"`,
                        `"${sup.ifsc_code || ''}"`,
                        `"${sup.current_balance || 0}"`
                    ]);

                    let csvContent = headers.join(",") + "\n" + rows.map(e => e.join(",")).join("\n");
                    const blob = new Blob([csvContent], {
                        type: 'text/csv;charset=utf-8;'
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.setAttribute("download", "Suppliers_Full_Report.csv");
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    BizAlert.toast('Supplier CSV Exported!', 'success');
                },

                exportPDF() {
                    const {
                        jsPDF
                    } = window.jspdf;
                    const doc = new jsPDF('l', 'mm', 'a4'); // Landscape for better fit

                    doc.setFontSize(16);
                    doc.setTextColor(16, 140, 42); // Brand Green
                    doc.text("Qlinkon BIZNESS - Suppliers Directory", 14, 15);

                    const head = [
                        ["Supplier Name", "Phone", "Email", "GSTIN", "City"]
                    ];
                    const body = this.allSuppliers.map(sup => [
                        sup.name || '-',
                        sup.phone || '-',
                        sup.email || '-',
                        sup.gstin || '-',
                        sup.city || '-'
                    ]);

                    doc.autoTable({
                        head: head,
                        body: body,
                        startY: 22,
                        theme: 'striped',
                        headStyles: {
                            fillColor: [16, 140, 42],
                            fontStyle: 'bold'
                        },
                        styles: {
                            fontSize: 10,
                            cellPadding: 4
                        },
                        columnStyles: {
                            0: {
                                cellWidth: 60
                            }, // Name
                            1: {
                                cellWidth: 40
                            }, // Phone
                            3: {
                                cellWidth: 50
                            } // GSTIN
                        }
                    });

                    doc.save("Suppliers_Directory.pdf");
                    BizAlert.toast('PDF Directory Exported!', 'success');
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/suppliers.blade.php ENDPATH**/ ?>