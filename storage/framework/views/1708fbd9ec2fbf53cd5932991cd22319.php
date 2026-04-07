

<?php $__env->startSection('title', 'Units Management - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Units / Products</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6 pb-10" x-data="unitCrud()">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">            

            <?php if(session('success')): ?>
                <div
                    class="bg-[#dcfce7] text-[#16a34a] px-4 py-2 rounded-lg text-sm font-bold shadow-sm flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div
                    class="bg-[#fee2e2] text-[#ef4444] px-4 py-2 rounded-lg text-sm font-bold shadow-sm flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i> Check form for errors.
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">

            <div
                class="px-6 py-4 flex flex-col sm:flex-row justify-between items-center border-b border-gray-100 gap-4 bg-white">
                <h2 class="text-[1.15rem] font-bold text-[#212538] tracking-tight">All Units</h2>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="w-full sm:w-64 relative">
                        <input type="text" x-model="search" placeholder="Search units..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all placeholder:text-gray-400">
                    </div>

                    <button @click="openCreateModal()"
                        class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-1.5 transition-colors shadow-sm whitespace-nowrap">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-[#f8fafc]">
                        <tr>
                            <th class="px-6 py-4 w-1/4">ID</th>
                            <th class="px-6 py-4 w-1/4">UNIT NAME</th>
                            <th class="px-6 py-4 w-1/4">SHORT NAME</th>
                            <th class="px-6 py-4 w-1/4 text-center">STATUS</th>
                            <th class="px-6 py-4 w-1/4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 transition-colors"
                                x-show="matchesSearch('<?php echo e(strtolower($unit->name)); ?>', '<?php echo e(strtolower($unit->short_name)); ?>')">

                                <td class="px-6 py-4 text-gray-500 font-medium">
                                    #<?php echo e($unit->id); ?>

                                </td>

                                <td class="px-6 py-4">
                                    <span class="font-bold text-[#475569] text-[13.5px]"><?php echo e($unit->name); ?></span>
                                </td>

                                <td class="px-6 py-4">
                                    <?php if($unit->short_name): ?>
                                        <span
                                            class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-mono font-bold"><?php echo e($unit->short_name); ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php if($unit->is_active): ?>
                                        <span
                                            class="bg-[#dcfce7] text-[#16a34a] px-3 py-1 rounded-md font-bold text-[11px] uppercase tracking-wider">Active</span>
                                    <?php else: ?>
                                        <span
                                            class="bg-gray-200 text-gray-500 px-3 py-1 rounded-md font-bold text-[11px] uppercase tracking-wider">Inactive</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2.5">
                                        <button @click="openEditModal(<?php echo e($unit->toJson()); ?>)"
                                            class="w-[32px] h-[32px] flex items-center justify-center rounded border border-[#108c2a] text-[#108c2a] hover:bg-green-50 transition-colors"
                                            title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </button>

                                        <form action="<?php echo e(route('admin.units.destroy', $unit->id)); ?>" method="POST"
                                            @submit.prevent="deleteUnit($event.target)" class="inline-block">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                class="w-[32px] h-[32px] flex items-center justify-center rounded border border-red-400 text-red-500 hover:bg-red-50 transition-colors"
                                                title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 font-medium">
                                    No units found. Click "Add" to create one.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="h-12 border-t border-gray-100 bg-white w-full"></div>
        </div>

        <div x-show="isModalOpen" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/50 backdrop-blur-sm transition-opacity"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="relative w-full max-w-md p-4" @click.away="closeModal()">

                <div class="relative bg-white rounded-xl shadow-2xl border border-gray-100"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <div class="flex items-center justify-between p-5 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#212538]"
                            x-text="modalMode === 'create' ? 'Add New Unit' : 'Edit Unit'"></h3>
                        <button @click="closeModal()" type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="formAction" method="POST" class="p-5">
                        <?php echo csrf_field(); ?>
                        <template x-if="modalMode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                    Unit Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" x-model="formData.name" required
                                    placeholder="e.g. Kilogram"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all placeholder:text-gray-400">
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-red-500 text-xs font-medium mt-1"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                    Short Name
                                </label>
                                <input type="text" name="short_name" x-model="formData.short_name"
                                    placeholder="e.g. kg"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all placeholder:text-gray-400">
                                <?php $__errorArgs = ['short_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-red-500 text-xs font-medium mt-1"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="flex items-center pt-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="formData.is_active"
                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#108c2a]">
                                    </div>
                                    <span class="ms-3 text-sm font-bold text-gray-600">Active</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full text-white bg-[#108c2a] hover:bg-[#0c6b1f] focus:ring-4 focus:outline-none focus:ring-green-300 font-bold rounded-lg text-sm px-5 py-3 text-center transition-colors shadow-sm">
                            <span x-text="modalMode === 'create' ? 'Save Unit' : 'Update Unit'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function unitCrud() {
            return {
                search: '',
                isModalOpen: false,
                modalMode: 'create',
                formAction: '<?php echo e(route('admin.units.store')); ?>',

                formData: {
                    id: null,
                    name: '',
                    short_name: '',
                    is_active: true
                },

                matchesSearch(name, shortName) {
                    if (this.search === '') return true;
                    const query = this.search.toLowerCase();
                    return name.includes(query) || shortName.includes(query);
                },

                openCreateModal() {
                    this.modalMode = 'create';
                    this.formAction = '<?php echo e(route('admin.units.store')); ?>';
                    this.formData = {
                        id: null,
                        name: '',
                        short_name: '',
                        is_active: true
                    };
                    this.isModalOpen = true;
                },

                openEditModal(unit) {
                    this.modalMode = 'edit';
                    this.formAction = `/admin/units/${unit.id}`;
                    this.formData = {
                        id: unit.id,
                        name: unit.name,
                        short_name: unit.short_name || '',
                        is_active: unit.is_active === 1 || unit.is_active === true
                    };
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                },

                // NEW: SweetAlert2 Delete Handler
                deleteUnit(form) {
                    BizAlert.confirm(
                        'Delete Unit?',
                        'Are you sure you want to permanently delete this unit?',
                        'Yes, delete it!'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Deleting...');
                            form.submit();
                        }
                    });
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/units.blade.php ENDPATH**/ ?>