

<?php $__env->startSection('title', 'Manage Modules - Qlinkon Super Admin'); ?>
<?php $__env->startSection('header', 'System Modules'); ?>

<?php $__env->startSection('styles'); ?>
    <style>
        [x-cloak] {
            display: none !important;
        }

        body.modal-open {
            overflow: hidden;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="pb-10" x-data="moduleManager(<?php echo \Illuminate\Support\Js::from($modules)->toHtml() ?>)">

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">System Modules</h1>
                <p class="text-sm text-gray-500 mt-1">Define the feature blocks you can attach to subscription plans.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="openCreate()"
                    class="bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Module
                </button>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div
                class="bg-green-50 text-green-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-green-100 mb-6 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="bg-red-50 text-red-600 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix
                    the following errors:</div>
                <ul class="list-disc list-inside pl-7 text-xs font-medium space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col hover:shadow-md transition-shadow relative group">

                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                            <i data-lucide="box" class="w-5 h-5"></i>
                        </div>
                        <?php if($module->is_active): ?>
                            <span
                                class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Active</span>
                        <?php else: ?>
                            <span
                                class="bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Inactive</span>
                        <?php endif; ?>
                    </div>

                    <h3 class="text-[15px] font-bold text-gray-800 mb-1"><?php echo e($module->name); ?></h3>
                    <p class="text-xs font-mono text-gray-400 mb-6 bg-gray-50 px-2 py-1 rounded inline-block self-start">
                        <?php echo e($module->slug); ?></p>

                    <div
                        class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button type="button" @click="openEdit(<?php echo e($module->id); ?>)"
                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Module">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                        <button type="button" @click="openDelete(<?php echo e($module->id); ?>, '<?php echo e(addslashes($module->name)); ?>')"
                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Delete Module">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div
                    class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-white rounded-xl border border-gray-200 border-dashed">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="boxes" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">No Modules Found</h3>
                    <p class="text-sm text-gray-500 max-w-sm">Add modules like "POS", "Inventory", or "Accounting" to attach
                        to your plans.</p>
                </div>
            <?php endif; ?>
        </div>

        <div x-cloak x-show="showFormModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeAll()" x-show="showFormModal"
                x-transition.opacity></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col"
                x-show="showFormModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">

                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center">
                            <i data-lucide="box" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-[16px] font-bold text-gray-800 tracking-tight"
                            x-text="isEditing ? 'Edit Module' : 'Add New Module'"></h3>
                    </div>
                    <button type="button" @click="closeAll()"
                        class="text-gray-400 hover:text-red-500 transition-colors p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="isEditing ? `/platform/modules/${form.id}` : '<?php echo e(route('platform.modules.store')); ?>'"
                    method="POST">
                    <?php echo csrf_field(); ?>
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="p-6 space-y-5">

                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Module Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="form.name" required
                                placeholder="e.g. Advanced Analytics"
                                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active"
                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600">
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-gray-700">Module is Active</span>
                            </label>
                            <p class="text-[11px] text-gray-500 mt-1 pl-14">Inactive modules cannot be selected in plans.
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50">
                        <button type="button" @click="closeAll()"
                            class="px-5 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-6 py-2 bg-brand-600 text-white rounded-lg text-sm font-bold hover:bg-brand-700 transition-colors shadow-sm"
                            x-text="isEditing ? 'Update Module' : 'Save Module'"></button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeAll()" x-show="showDeleteModal"
                x-transition.opacity></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden text-center"
                x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="p-6 pt-8">
                    <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-triangle" class="w-8 h-8 text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Delete Module?</h3>
                    <p class="text-sm text-gray-500">Are you sure you want to delete <strong class="text-gray-800"
                            x-text="deleteForm.name"></strong>? It will be removed from all attached plans.</p>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-center gap-3 bg-gray-50">
                    <button type="button" @click="closeAll()"
                        class="px-6 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                    <form :action="`/platform/modules/${deleteForm.id}`" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit"
                            class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition-colors shadow-sm">Yes,
                            Delete</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        function moduleManager(allModules) {
            return {
                modules: allModules,
                showFormModal: false,
                showDeleteModal: false,
                isEditing: false,

                form: {
                    id: '',
                    name: '',
                    is_active: true
                },

                deleteForm: {
                    id: '',
                    name: ''
                },

                openCreate() {
                    document.body.classList.add('modal-open');
                    this.isEditing = false;
                    this.form = {
                        id: '',
                        name: '',
                        is_active: true
                    };
                    this.showFormModal = true;
                },

                openEdit(id) {
                    let module = this.modules.find(m => m.id === id);
                    if (!module) return;

                    document.body.classList.add('modal-open');
                    this.isEditing = true;

                    this.form = {
                        id: module.id,
                        name: module.name,
                        is_active: module.is_active == 1 || module.is_active == true
                    };

                    this.showFormModal = true;
                },

                openDelete(id, name) {
                    document.body.classList.add('modal-open');
                    this.deleteForm = {
                        id,
                        name
                    };
                    this.showDeleteModal = true;
                },

                closeAll() {
                    document.body.classList.remove('modal-open');
                    this.showFormModal = false;
                    this.showDeleteModal = false;
                }
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/platform/modules.blade.php ENDPATH**/ ?>