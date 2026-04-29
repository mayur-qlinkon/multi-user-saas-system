<?php $__env->startSection('title', 'Attributes - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-400 uppercase tracking-widest">Attributes</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6 pb-10" x-data="attributeManager()">

        <?php if(session('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("<?php echo e(session('success')); ?>", 'success'));
            </script>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div
                class="bg-[#fee2e2] text-[#ef4444] px-4 py-3 rounded-xl text-sm font-bold shadow-sm flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                Please check the form. Validations failed.
            </div>
        <?php endif; ?>

        <div>
            
            <p class="text-sm text-gray-500 font-medium">Define and manage options for your variable products.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <div class="lg:col-span-4 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-[#212538]">Definitions</h2>
                    <?php if(has_permission('attributes.create')): ?>
                    <button @click="openAttrModal('create')"
                        class="bg-[#212538] hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition-all shadow-sm">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add New
                    </button>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col gap-3">
                    <?php $__empty_1 = true; $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isActive = $activeAttribute && $activeAttribute->id == $attr->id;
                            // Type styling mapping
                            $typeStyles = [
                                'text' => 'bg-blue-50 text-blue-600',
                                'color' => 'bg-orange-50 text-orange-600',
                                'button' => 'bg-purple-50 text-purple-600',
                            ];
                        ?>

                        <div
                            class="relative group flex items-center justify-between p-4 rounded-xl border transition-all duration-200 <?php echo e($isActive ? 'border-[#f97316] bg-[#fff7ed] shadow-sm' : 'border-gray-200 bg-white hover:border-orange-200 hover:shadow-sm'); ?>">

                            <a href="<?php echo e(route('admin.attributes.index', ['active_id' => $attr->id])); ?>"
                                class="absolute inset-0 z-0 rounded-xl"></a>

                            <div class="relative z-10 flex flex-col gap-1.5 pointer-events-none">
                                <span class="font-bold text-gray-800 text-[15px]"><?php echo e($attr->name); ?></span>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="<?php echo e($typeStyles[$attr->type] ?? 'bg-gray-100 text-gray-600'); ?> text-[10px] px-2 py-0.5 rounded font-black uppercase tracking-wider">
                                        <?php echo e($attr->type); ?>

                                    </span>
                                    <span class="text-xs text-gray-400 font-medium"><?php echo e($attr->values_count); ?> values</span>
                                </div>
                            </div>

                            <div
                                class="relative z-10 flex items-center gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                <?php if(has_permission('attributes.update')): ?>
                                <button @click="openAttrModal('edit', <?php echo e($attr->toJson()); ?>)"
                                    class="w-8 h-8 flex items-center justify-center rounded bg-white border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-200 transition-colors shadow-sm"
                                    title="Edit">
                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                </button>
                                <?php endif; ?>

                                <?php if(has_permission('attributes.delete')): ?>
                                <form action="<?php echo e(route('admin.attributes.destroy', $attr->id)); ?>" method="POST"
                                    @submit.prevent="confirmDelete($event.target, 'Delete Attribute?', 'This will remove the attribute and ALL its configured values.')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="w-8 h-8 flex items-center justify-center rounded bg-white border border-gray-200 text-gray-500 hover:text-red-600 hover:border-red-200 transition-colors shadow-sm"
                                        title="Delete">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="bg-white border-2 border-dashed border-gray-200 rounded-xl p-8 text-center">
                            <i data-lucide="layers" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                            <p class="text-sm text-gray-500 font-medium">No attributes defined yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-8 flex flex-col">
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col min-h-[400px]">

                    <?php if($activeAttribute): ?>
                        <div
                            class="px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-100 bg-gray-50/50 gap-4">
                            <div>
                                <h2 class="text-[1.15rem] font-bold text-[#212538]"><?php echo e($activeAttribute->name); ?> Options
                                </h2>
                                <p class="text-xs text-gray-400 font-medium mt-0.5">Manage values for the
                                    "<?php echo e($activeAttribute->name); ?>" attribute</p>
                            </div>
                            <?php if(has_permission('attributes.create')): ?>
                            <button @click="openValueModal('create')"
                                class="bg-[#f97316] hover:bg-[#ea580c] text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center justify-center gap-2 transition-colors shadow-sm w-full sm:w-auto">
                                <i data-lucide="plus" class="w-4 h-4"></i> Add Value
                            </button>
                            <?php endif; ?>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                                <?php $__empty_1 = true; $__currentLoopData = $activeAttribute->values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div
                                        class="group flex items-center justify-between p-4 rounded-xl border border-gray-200 bg-white hover:border-gray-300 hover:shadow-sm transition-all">

                                        <div class="flex items-center gap-3">
                                            <?php if($activeAttribute->type === 'color'): ?>
                                                <div class="w-8 h-8 rounded-full shadow-inner border border-gray-200 flex-shrink-0"
                                                    style="background-color: <?php echo e($val->color_code ?? '#fff'); ?>"></div>
                                            <?php else: ?>
                                                <div
                                                    class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100 flex-shrink-0">
                                                    <i data-lucide="hash" class="w-4 h-4"></i>
                                                </div>
                                            <?php endif; ?>

                                            <div class="flex flex-col min-w-0">
                                                <span
                                                    class="text-sm font-bold text-gray-800 truncate"><?php echo e($val->value); ?></span>
                                                <?php if($activeAttribute->type === 'color' && $val->color_code): ?>
                                                    <span
                                                        class="text-[10px] text-gray-400 font-mono tracking-widest"><?php echo e($val->color_code); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-center gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                            <?php if(has_permission('attributes.update')): ?>
                                            <button @click="openValueModal('edit', <?php echo e($val->toJson()); ?>)"
                                                class="text-gray-400 hover:text-blue-600 p-1.5 transition-colors"
                                                title="Edit">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </button>
                                            <?php endif; ?>

                                            <?php if(has_permission('attributes.delete')): ?>
                                            <form action="<?php echo e(route('admin.attribute-values.destroy', $val->id)); ?>"
                                                method="POST"
                                                @submit.prevent="confirmDelete($event.target, 'Remove Value?', 'This will delete this option.')">
                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="text-gray-400 hover:text-red-600 p-1.5 transition-colors"
                                                    title="Delete">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="col-span-full py-16 text-center">
                                        <p class="text-gray-400 font-medium">No options available yet. Click "Add Value" to
                                            define them.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex-1 flex flex-col items-center justify-center p-10 text-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="mouse-pointer-click" class="w-8 h-8 text-gray-300"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-700">Select an Attribute</h3>
                            <p class="text-sm text-gray-400 mt-1 max-w-sm">Choose an attribute from the left sidebar to
                                manage its values and options.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div x-show="isAttrModalOpen" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity"
            x-transition.opacity>

            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
                @click.away="closeModals()">
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-[#212538]"
                        x-text="attrMode === 'create' ? 'Add Definition' : 'Edit Definition'"></h3>
                    <button @click="closeModals()" type="button"
                        class="text-gray-400 hover:bg-gray-100 rounded-full p-2 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="attrFormAction" method="POST" class="p-5 space-y-4"
                    @submit="BizAlert.loading('Saving...')">
                    <?php echo csrf_field(); ?>
                    <template x-if="attrMode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5">Attribute Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="attrData.name" required
                            placeholder="e.g. Size, Color, Capacity"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#f97316]/20 focus:border-[#f97316]">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5">Display Type <span
                                class="text-red-500">*</span></label>
                        <select name="type" x-model="attrData.type"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#f97316]/20 focus:border-[#f97316] appearance-none bg-white">
                            <option value="text">Text (Dropdown / Label)</option>
                            <option value="color">Color Swatch</option>
                            <option value="button">Button (Pill)</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full mt-2 bg-[#212538] hover:bg-gray-800 text-white py-3 rounded-xl text-sm font-bold shadow-md transition-all">
                        <span x-text="attrMode === 'create' ? 'Save Attribute' : 'Update Attribute'"></span>
                    </button>
                </form>
            </div>
        </div>

        <?php if($activeAttribute): ?>
            <div x-show="isValueModalOpen" style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity"
                x-transition.opacity>

                <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
                    @click.away="closeModals()">
                    <div class="flex items-center justify-between p-5 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#212538]"
                            x-text="valueMode === 'create' ? 'Add <?php echo e($activeAttribute->name); ?> Option' : 'Edit Option'">
                        </h3>
                        <button @click="closeModals()" type="button"
                            class="text-gray-400 hover:bg-gray-100 rounded-full p-2 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="valueFormAction" method="POST" class="p-5 space-y-4"
                        @submit="BizAlert.loading('Saving...')">
                        <?php echo csrf_field(); ?>
                        <template x-if="valueMode === 'edit'"><input type="hidden" name="_method"
                                value="PUT"></template>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5">Value Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="value" x-model="valueData.value" required
                                placeholder="e.g. Small, Red, Plastic"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#f97316]/20 focus:border-[#f97316]">
                        </div>

                        <?php if($activeAttribute->type === 'color'): ?>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5">Hex Color
                                    Code</label>
                                <div
                                    class="flex items-center gap-3 border border-gray-200 rounded-xl p-2 focus-within:ring-2 focus-within:ring-[#f97316]/20 focus-within:border-[#f97316]">
                                    <input type="color" name="color_code" x-model="valueData.color_code"
                                        class="w-8 h-8 rounded cursor-pointer border-0 p-0 bg-transparent">
                                    <input type="text" x-model="valueData.color_code"
                                        class="flex-1 text-sm outline-none bg-transparent font-mono text-gray-600"
                                        placeholder="#000000">
                                </div>
                            </div>
                        <?php endif; ?>

                        <button type="submit"
                            class="w-full mt-2 bg-[#f97316] hover:bg-[#ea580c] text-white py-3 rounded-xl text-sm font-bold shadow-md transition-all">
                            <span x-text="valueMode === 'create' ? 'Add Option' : 'Update Option'"></span>
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function attributeManager() {
            return {
                search: '',

                // Attribute Modal
                isAttrModalOpen: false,
                attrMode: 'create',
                attrFormAction: '',
                attrData: {
                    name: '',
                    type: 'text'
                },

                // Value Modal
                isValueModalOpen: false,
                valueMode: 'create',
                valueFormAction: '',
                valueData: {
                    value: '',
                    color_code: '#ffffff'
                },

                matchesSearch(name) {
                    if (this.search === '') return true;
                    return name.includes(this.search.toLowerCase());
                },

                openAttrModal(mode, attr = null) {
                    this.attrMode = mode;
                    if (mode === 'create') {
                        this.attrFormAction = '<?php echo e(route('admin.attributes.store')); ?>';
                        this.attrData = {
                            name: '',
                            type: 'text'
                        };
                    } else {
                        this.attrFormAction = `/admin/attributes/${attr.id}`;
                        this.attrData = {
                            name: attr.name,
                            type: attr.type
                        };
                    }
                    this.isAttrModalOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                openValueModal(mode, val = null) {
                    this.valueMode = mode;
                    if (mode === 'create') {
                        // We use the ID of the currently selected attribute from Laravel
                        this.valueFormAction =
                            '<?php echo e($activeAttribute ? route('admin.attribute-values.store', $activeAttribute->id) : '#'); ?>';
                        this.valueData = {
                            value: '',
                            color_code: '#ffffff'
                        };
                    } else {
                        this.valueFormAction = `/admin/attribute-values/${val.id}`;
                        this.valueData = {
                            value: val.value,
                            color_code: val.color_code || '#ffffff'
                        };
                    }
                    this.isValueModalOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                closeModals() {
                    this.isAttrModalOpen = false;
                    this.isValueModalOpen = false;
                    document.body.style.overflow = 'auto';
                },

                confirmDelete(form, title, text) {
                    BizAlert.confirm(title, text, 'Yes, Delete', 'warning')
                        .then((result) => {
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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/products/attributes.blade.php ENDPATH**/ ?>