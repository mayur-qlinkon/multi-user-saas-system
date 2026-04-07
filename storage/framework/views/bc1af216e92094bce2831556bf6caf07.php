

<?php $__env->startSection('title', 'Staff Management - Qlinkon BIZNESS'); ?>

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
<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">LIST / USERS</h1>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="pb-10" x-data="userManager(<?php echo \Illuminate\Support\Js::from($users)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($stores)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($roles)->toHtml() ?>)">

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                
                <p class="text-sm text-gray-500 mt-1">Manage your cashiers, managers, and their store assignments.</p>
            </div>
            <div class="flex items-center gap-2">
                <?php if(check_plan_limit('users')): ?>
                    <button type="button" @click="openCreate()"
                        class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4"></i> Add Staff Member
                    </button>
                <?php else: ?>
                    <div class="flex items-center gap-3">

                        <span
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-red-50 text-red-600 text-xs font-bold border border-red-100">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            User Limit Reached
                        </span>

                        <button type="button"
                            class="bg-gray-100 text-gray-400 px-5 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 cursor-not-allowed"
                            title="You have reached your staff limit. Upgrade your plan to add more users.">

                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            Add Staff Member

                        </button>

                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div
                class="bg-green-50 text-green-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-green-100 mb-6 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div
                class="bg-red-50 text-red-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6 flex items-center gap-2">
                <i data-lucide="alert-octagon" class="w-5 h-5"></i> <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div
                class="bg-[#fee2e2] text-[#ef4444] px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix
                    the following errors:</div>
                <ul class="list-disc list-inside pl-7 text-xs font-medium space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

       
        <?php if(is_owner()): ?>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
                <form action="<?php echo e(route('admin.users.index')); ?>" method="GET" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto items-center">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider shrink-0">
                        Filter:
                    </label>
                    <select name="store_id" class="w-full sm:w-64 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-brand-500 outline-none bg-white shadow-sm" onchange="this.form.submit()">
                        <option value="">All Company Staff</option>
                        <?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($store->id); ?>" <?php if(request('store_id') == $store->id): echo 'selected'; endif; ?>>
                                <?php echo e($store->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    
                    <?php if(request()->has('store_id') && request('store_id') != ''): ?>
                        <a href="<?php echo e(route('admin.users.index')); ?>" class="text-xs font-bold text-red-500 hover:text-red-700 transition-colors">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Staff
                                Member</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Assigned
                                Role</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">
                                Store</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Status
                            </th>
                            <th
                                class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider text-right">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-full bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center text-sm font-bold shrink-0">
                                            <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800"><?php echo e($user->name); ?></div>
                                            <div class="text-[12px] text-gray-400 mt-0.5"><?php echo e($user->email); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-600 border border-purple-100">
                                        <?php echo e($user->roles->first()->name ?? 'No Role'); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php $__empty_2 = true; $__currentLoopData = $user->stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                            <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-600 border border-gray-200 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">
                                                <i data-lucide="store" class="w-3 h-3 text-brand-500"></i> <?php echo e($store->name); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                            <span class="text-gray-400 text-xs italic">No Store Assigned</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($user->status === 'active'): ?>
                                        <span
                                            class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Active</span>
                                    <?php else: ?>
                                        <span
                                            class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <?php if(auth()->id() !== $user->id && ($user->roles->first()->slug ?? '') !== 'owner'): ?>
                                            <button type="button" @click="openEdit(<?php echo e($user->id); ?>)"
                                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 flex items-center justify-center transition-colors"
                                                title="Edit Staff">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>
                                        <?php endif; ?>

                                        
                                        <?php if(auth()->id() !== $user->id && ($user->roles->first()->slug ?? '') !== 'owner'): ?>
                                            <button type="button"
                                                @click="openDelete(<?php echo e($user->id); ?>, '<?php echo e(addslashes($user->name)); ?>')"
                                                class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors"
                                                title="Remove Staff">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="users" class="w-12 h-12 mb-3 text-gray-300"></i>
                                        <h3 class="text-lg font-bold text-gray-800 mb-1">No Staff Members Found</h3>
                                        <p class="text-sm font-medium">Add your first cashier or manager to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div x-cloak x-show="showFormModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeAll()" x-show="showFormModal"
                x-transition.opacity></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]"
                x-show="showFormModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">

                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50 shrink-0">
                    <h3 class="text-[16px] font-bold text-gray-800 tracking-tight"
                        x-text="isEditing ? 'Edit Staff Member' : 'Add New Staff Member'"></h3>
                    <button type="button" @click="closeAll()" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="isEditing ? `/admin/users/${form.id}` : '<?php echo e(route('admin.users.store')); ?>'" method="POST"
                    class="flex flex-col flex-1 overflow-hidden">
                    <?php echo csrf_field(); ?>
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="p-6 overflow-y-auto space-y-5">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="sm:col-span-2">
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Full Name <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="form.name" required
                                    placeholder="e.g. Jane Doe"
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Email Address (Login ID)
                                    <span class="text-red-500">*</span></label>
                                <input type="email" name="email" x-model="form.email" required
                                    placeholder="jane@example.com"
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Password <span
                                        x-show="!isEditing" class="text-red-500">*</span></label>
                                <input type="password" name="password" x-model="form.password" :required="!isEditing"
                                    placeholder="••••••••"
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                                <p x-show="isEditing" class="text-[10px] text-gray-400 mt-1">Leave blank to keep current
                                    password.</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">

                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Assign to Store <span
                                        class="text-red-500">*</span></label>
                                <select name="store_id" x-model="form.store_id" required
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all bg-white">
                                    <option value="">-- Select Store --</option>
                                    <template x-for="store in stores" :key="store.id">
                                        <option :value="store.id" x-text="store.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Assign Role <span
                                        class="text-red-500">*</span></label>
                                <select name="role_id" x-model="form.role_id" :disabled="form.is_owner" required
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all bg-white disabled:bg-gray-100 disabled:cursor-not-allowed">
                                    <option value="">-- Select Role --</option>
                                    <template x-for="role in roles" :key="role.id">
                                        <option :value="role.id" x-text="role.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Account Status <span
                                        class="text-red-500">*</span></label>
                                <select name="status" x-model="form.status" required
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all bg-white">
                                    <option value="active">Active (Can Login)</option>
                                    <option value="inactive">Inactive (Suspended)</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50 shrink-0">
                        <button type="button" @click="closeAll()"
                            class="px-5 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-md text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-brand-500 text-white rounded-md text-sm font-bold hover:bg-brand-600 transition-colors shadow-sm"
                            x-text="isEditing ? 'Update Staff Member' : 'Save Staff Member'"></button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeAll()" x-show="showDeleteModal"
                x-transition.opacity></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden text-center"
                x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="p-6 pt-8">
                    <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-triangle" class="w-8 h-8 text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Remove Staff Member?</h3>
                    <p class="text-sm text-gray-500">Are you sure you want to delete <strong class="text-gray-800"
                            x-text="deleteForm.name"></strong>'s access? This action cannot be undone.</p>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-center gap-3 bg-gray-50/50">
                    <button type="button" @click="closeAll()"
                        class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-md text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                    <form :action="`/admin/users/${deleteForm.id}`" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit"
                            class="px-6 py-2.5 bg-red-500 text-white rounded-md text-sm font-bold hover:bg-red-600 transition-colors shadow-sm">Yes,
                            Remove</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function userManager(allUsers, availableStores, availableRoles) {
            return {
                users: allUsers,
                stores: availableStores,
                roles: availableRoles,

                showFormModal: false,
                showDeleteModal: false,
                isEditing: false,

                form: {
                    id: '',
                    name: '',
                    email: '',
                    password: '',
                    store_id: '',
                    role_id: '',
                    status: 'active'
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
                        email: '',
                        password: '',
                        store_id: '',
                        role_id: '',
                        status: 'active',
                        is_owner: false
                    };
                    this.showFormModal = true;
                },

                openEdit(id) {
                    let user = this.users.find(u => u.id === id);
                    if (!user) return;

                    document.body.classList.add('modal-open');
                    this.isEditing = true;

                    // Extract the first store and role ID from the pivot relationships
                    let userStoreId = user.stores && user.stores.length > 0 ? user.stores[0].id : '';
                    let userRole = user.roles && user.roles.length > 0 ? user.roles[0] : null;
                    let userRoleId = userRole ? userRole.id : '';
                    let isOwner = userRole && userRole.slug === 'owner';

                    this.form = {
                        id: user.id,
                        name: user.name,
                        email: user.email,
                        password: '',
                        store_id: userStoreId,
                        role_id: userRoleId,
                        status: user.status || 'active',
                        is_owner: isOwner
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/users.blade.php ENDPATH**/ ?>