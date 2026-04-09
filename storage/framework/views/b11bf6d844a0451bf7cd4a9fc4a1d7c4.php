

<?php $__env->startSection('title', 'Create Role - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        // 1. Process Permissions into a Grid Matrix safely
        $matrix = [];
        $jsMatrix = [];

        foreach ($permissions as $module => $perms) {
            // Defensively match keywords in either name or slug
            $view = $perms->first(fn($p) => preg_match('/view|read|index|show/i', $p->slug . $p->name));
            $create = $perms->first(fn($p) => preg_match('/create|add|store/i', $p->slug . $p->name));
            $update = $perms->first(fn($p) => preg_match('/update|edit/i', $p->slug . $p->name));
            $delete = $perms->first(fn($p) => preg_match('/delete|destroy|remove/i', $p->slug . $p->name));

            // Catch anything else (print, export, convert)
            $standardIds = array_filter([$view?->id, $create?->id, $update?->id, $delete?->id]);
            $others = $perms->filter(fn($p) => !in_array($p->id, $standardIds));

            $allIds = $perms->pluck('id')->toArray();
            $key = Str::slug($module);

            $matrix[$module] = [
                'key' => $key,
                'view' => $view,
                'create' => $create,
                'update' => $update,
                'delete' => $delete,
                'others' => $others,
                'all_ids' => $allIds,
            ];

            // Map for Alpine.js logic
            $jsMatrix[$key] = [
                'view_id' => $view?->id,
                'all_ids' => $allIds,
            ];
        }
    ?>

    <div class="pb-10" x-data="roleMatrixForm(<?php echo \Illuminate\Support\Js::from(old('permissions', []))->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($jsMatrix)->toHtml() ?>)">

        
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#212538] tracking-tight">Create New Role</h1>
                <p class="text-[13px] text-gray-500 font-medium mt-1">Define exact access permissions for a new user group.
                </p>
            </div>
            <a href="<?php echo e(route('admin.roles.index')); ?>"
                class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm">
                Cancel
            </a>
        </div>

        <?php if($errors->any()): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200">
                <div class="font-bold mb-2">Please fix the following errors:</div>
                <ul class="list-disc list-inside text-sm">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('admin.roles.store')); ?>" method="POST" @submit="BizAlert.loading('Creating Role...')">
            <?php echo csrf_field(); ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 flex flex-col">

                
                <div class="p-6 border-b border-gray-200">
                    <div class="max-w-md">
                        <label class="block text-[12px] font-bold text-gray-700 uppercase tracking-wider mb-2">Role Name
                            <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                            placeholder="e.g. Sales Manager"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-800 font-medium focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all">
                    </div>
                </div>

                
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <label class="flex items-center gap-3 cursor-pointer w-max">
                        <span class="text-[12px] font-bold text-gray-700 uppercase tracking-wider">Permissions <span
                                class="text-red-500">*</span></span>
                        <div class="flex items-center gap-2 ml-4">
                            <input type="checkbox" @change="toggleAll()" :checked="isAllSelected"
                                class="w-4 h-4 rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] transition-colors cursor-pointer">
                            <span class="text-[13px] font-medium text-gray-700">All Permissions</span>
                        </div>
                    </label>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-white border-b border-gray-200">
                            <tr class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-4 min-w-[200px]">MODULE / PERMISSIONS</th>
                                <th class="px-6 py-4 text-center w-[120px]">SELECT ALL</th>
                                <th class="px-6 py-4 text-center w-[100px]">VIEW</th>
                                <th class="px-6 py-4 text-center w-[100px]">CREATE</th>
                                <th class="px-6 py-4 text-center w-[100px]">UPDATE</th>
                                <th class="px-6 py-4 text-center w-[100px]">DELETE</th>
                                <th class="px-6 py-4 text-left min-w-[150px]">OTHER</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php $__currentLoopData = $matrix; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">

                                    
                                    <td class="px-6 py-4 text-[13px] font-medium text-gray-700 capitalize">
                                        <?php echo e(str_replace('_', ' ', $module ?: 'General')); ?>

                                    </td>

                                    
                                    <td class="px-6 py-4 text-center bg-gray-50/30">
                                        <?php if(count($data['all_ids']) > 0): ?>
                                            <input type="checkbox" @change="toggleRow('<?php echo e($data['key']); ?>')"
                                                :checked="isRowSelected('<?php echo e($data['key']); ?>')"
                                                class="w-4 h-4 rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] transition-colors cursor-pointer">
                                        <?php endif; ?>
                                    </td>

                                    
                                    <td class="px-6 py-4 text-center">
                                        <?php if($data['view']): ?>
                                            <input type="checkbox" name="permissions[]" value="<?php echo e($data['view']->id); ?>"
                                                x-model.number="selected"
                                                @change="checkDependencies('<?php echo e($data['key']); ?>', 'view', <?php echo e($data['view']->id); ?>)"
                                                class="w-4 h-4 rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] transition-colors cursor-pointer">
                                        <?php endif; ?>
                                    </td>

                                    
                                    <td class="px-6 py-4 text-center">
                                        <?php if($data['create']): ?>
                                            <input type="checkbox" name="permissions[]" value="<?php echo e($data['create']->id); ?>"
                                                x-model.number="selected"
                                                @change="checkDependencies('<?php echo e($data['key']); ?>', 'action', <?php echo e($data['create']->id); ?>)"
                                                class="w-4 h-4 rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] transition-colors cursor-pointer">
                                        <?php endif; ?>
                                    </td>

                                    
                                    <td class="px-6 py-4 text-center">
                                        <?php if($data['update']): ?>
                                            <input type="checkbox" name="permissions[]" value="<?php echo e($data['update']->id); ?>"
                                                x-model.number="selected"
                                                @change="checkDependencies('<?php echo e($data['key']); ?>', 'action', <?php echo e($data['update']->id); ?>)"
                                                class="w-4 h-4 rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] transition-colors cursor-pointer">
                                        <?php endif; ?>
                                    </td>

                                    
                                    <td class="px-6 py-4 text-center">
                                        <?php if($data['delete']): ?>
                                            <input type="checkbox" name="permissions[]" value="<?php echo e($data['delete']->id); ?>"
                                                x-model.number="selected"
                                                @change="checkDependencies('<?php echo e($data['key']); ?>', 'action', <?php echo e($data['delete']->id); ?>)"
                                                class="w-4 h-4 rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] transition-colors cursor-pointer">
                                        <?php endif; ?>
                                    </td>

                                    
                                    <td class="px-6 py-4 text-left">
                                        <?php if($data['others']->count() > 0): ?>
                                            <div class="flex flex-wrap gap-3">
                                                <?php $__currentLoopData = $data['others']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $other): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                                        <input type="checkbox" name="permissions[]"
                                                            value="<?php echo e($other->id); ?>" x-model.number="selected"
                                                            @change="checkDependencies('<?php echo e($data['key']); ?>', 'action', <?php echo e($other->id); ?>)"
                                                            class="w-4 h-4 rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] transition-colors cursor-pointer">
                                                        <span
                                                            class="text-[11px] font-medium text-gray-600 uppercase tracking-wider"><?php echo e(explode('.', $other->slug)[1] ?? $other->name); ?></span>
                                                    </label>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="bg-white border border-gray-200 p-5 rounded-lg flex justify-end gap-4 shadow-sm">
                <button type="submit"
                    class="bg-gray-800 text-white px-8 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-900 shadow-md transition-all active:scale-95 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Role
                </button>
            </div>

        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function roleMatrixForm(oldSelected = [], matrixData = {}) {
            return {
                selected: oldSelected.map(Number),
                matrix: matrixData,

                // Check if every single ID in the matrix is currently in the selected array
                get isAllSelected() {
                    let allIds = Object.values(this.matrix).flatMap(m => m.all_ids);
                    return allIds.length > 0 && allIds.every(id => this.selected.includes(id));
                },

                // Select or Deselect everything
                toggleAll() {
                    if (this.isAllSelected) {
                        this.selected = [];
                    } else {
                        this.selected = Object.values(this.matrix).flatMap(m => m.all_ids);
                    }
                },

                // Check if a specific row is fully selected
                isRowSelected(modKey) {
                    let ids = this.matrix[modKey].all_ids;
                    return ids.length > 0 && ids.every(id => this.selected.includes(id));
                },

                // Select or Deselect an entire row
                toggleRow(modKey) {
                    let ids = this.matrix[modKey].all_ids;
                    if (this.isRowSelected(modKey)) {
                        this.selected = this.selected.filter(id => !ids.includes(id));
                    } else {
                        ids.forEach(id => {
                            if (!this.selected.includes(id)) this.selected.push(id);
                        });
                    }
                },

                // 🌟 SMART DEPENDENCIES: Handles the "View" logic
                checkDependencies(modKey, type, id) {
                    this.$nextTick(() => {
                        let isChecked = this.selected.includes(id);
                        let viewId = this.matrix[modKey].view_id;

                        // RULE 1: If user checks ANY action (create/update/delete/print), force-check 'View'
                        if (isChecked && type === 'action' && viewId) {
                            if (!this.selected.includes(viewId)) {
                                this.selected.push(viewId);
                            }
                        }

                        // RULE 2: If user unchecks 'View', force-uncheck ALL actions in that row
                        if (!isChecked && type === 'view') {
                            let allModIds = this.matrix[modKey].all_ids;
                            this.selected = this.selected.filter(s => !allModIds.includes(s));
                        }
                    });
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/roles/create.blade.php ENDPATH**/ ?>