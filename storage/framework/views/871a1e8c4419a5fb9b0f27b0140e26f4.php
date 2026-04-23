<?php $__env->startSection('title', 'Staff Management - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">USERS</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="pb-10">

        
        <div class="mb-6 flex flex-col sm:flex-row flex-wrap sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest">User Management</h3>
                <p class="text-sm text-gray-500 mt-1">Manage your cashiers, managers, and their store assignments.</p>
            </div>
            <div class="flex items-center gap-2">
                <?php if(check_plan_limit('users')): ?>
                    <a href="<?php echo e(route('admin.users.create')); ?>"
                        class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4"></i> Add Staff Member
                    </a>
                <?php else: ?>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-red-50 text-red-600 text-xs font-bold border border-red-100">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            User Limit Reached
                        </span>
                        <?php if(has_permission('users.create')): ?>
                        <button type="button"
                            class="bg-gray-100 text-gray-400 px-5 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 cursor-not-allowed"
                            title="You have reached your staff limit. Upgrade your plan to add more users.">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            Add Staff Member
                        </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if(session('success')): ?>
            <div class="bg-green-50 text-green-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-green-100 mb-6 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="bg-red-50 text-red-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6 flex items-center gap-2">
                <i data-lucide="alert-octagon" class="w-5 h-5"></i> <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="bg-[#fee2e2] text-[#ef4444] px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix the following errors:</div>
                <ul class="list-disc list-inside pl-7 text-xs font-medium space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
            <form action="<?php echo e(route('admin.users.index')); ?>" method="GET" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto items-center">
                <?php if(is_owner()): ?>
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
                <?php endif; ?>

                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        <input type="text" id="searchInput" placeholder="Search name or email..." 
                            class="w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-brand-500 outline-none bg-white shadow-sm transition-all">
                    </div>
            </div>
            

        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Staff Member</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Assigned Role</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Assigned Stores</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Status</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTbody" class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center text-sm font-bold shrink-0">
                                            <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800"><?php echo e($user->name); ?></div>
                                            <div class="text-[12px] text-gray-400 mt-0.5"><?php echo e($user->email); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-600 border border-purple-100">
                                        <?php echo e($user->roles->first()->name ?? 'No Role'); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <?php
                                        // Count total stores in the system vs stores assigned to this user
                                        $totalSystemStores = $stores->count();
                                        $userStoreCount = $user->stores->count();
                                    ?>

                                    
                                    <?php if($userStoreCount === 0): ?>
                                        <span class="text-gray-400 text-[11px] italic font-medium">No Access</span>

                                    
                                    <?php elseif($userStoreCount === $totalSystemStores && $totalSystemStores > 0): ?>
                                        <span class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-700 border border-brand-200 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest shadow-sm">
                                            <i data-lucide="globe" class="w-3.5 h-3.5"></i> All Stores Access
                                        </span>

                                    
                                    <?php else: ?>
                                        <div class="flex flex-wrap gap-1.5 max-w-[220px]">
                                            
                                            <?php $__currentLoopData = $user->stores->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-600 border border-gray-200 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                                    <i data-lucide="store" class="w-3 h-3 text-brand-500"></i> <?php echo e($store->name); ?>

                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            
                                            <?php if($userStoreCount > 2): ?>
                                                <?php
                                                    // Create a comma-separated list of the hidden stores
                                                    $hiddenStores = $user->stores->skip(2)->pluck('name')->implode(', ');
                                                ?>
                                                <span title="Also assigned to: <?php echo e($hiddenStores); ?>" 
                                                    class="truncate cursor-help inline-flex items-center text-[10px] font-black text-brand-600 bg-brand-50 px-1.5 py-0.5 rounded border border-brand-100 hover:bg-brand-100 hover:border-brand-300 transition-colors">
                                                    +<?php echo e($userStoreCount - 2); ?> More
                                                </span>
                                        <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <?php if($user->status === 'active'): ?>
                                        <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Active</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 transition-opacity transition-opacity">
                                        
                                        
                                        <?php if(has_permission('users.view')): ?>
                                            <a href="<?php echo e(route('admin.users.show', $user->id)); ?>" 
                                            class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-200 hover:text-gray-800 flex items-center justify-center transition-colors"
                                            title="View Staff Details">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if(auth()->id() !== $user->id && ($user->roles->first()->slug ?? '') !== 'owner'): ?>
                                            
                                            <?php if(has_permission('users.update')): ?>
                                                <a href="<?php echo e(route('admin.users.edit', $user->id)); ?>" 
                                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 flex items-center justify-center transition-colors"
                                                title="Edit Staff">
                                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                                </a>
                                            <?php endif; ?>

                                            
                                            <?php if(has_permission('users.delete')): ?>
                                                <form action="<?php echo e(route('admin.users.destroy', $user->id)); ?>" method="POST" 
                                                    class="inline-block delete-staff-form" 
                                                    data-name="<?php echo e(addslashes($user->name)); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit"
                                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors"
                                                        title="Remove Staff">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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
            
            
            <?php if($users->hasPages()): ?>
                <div class="p-4 border-t border-gray-100">
                    <?php echo e($users->appends(request()->query())->links()); ?>

                </div>
            <?php endif; ?>
        </div>

    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const tbody = document.getElementById('usersTbody');
        let debounceTimer;

        if (searchInput && tbody) {
            searchInput.addEventListener('input', function (e) {
                clearTimeout(debounceTimer);
                
                // Wait 300ms after the user stops typing to fire the request
                debounceTimer = setTimeout(() => {
                    const query = e.target.value;
                    const url = new URL(window.location.href);
                    url.searchParams.set('search', query);

                    // Fetch the updated page
                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Parse the returned HTML
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Extract the new tbody and inject it
                        const newTbody = doc.getElementById('usersTbody');
                        if (newTbody) {
                            tbody.innerHTML = newTbody.innerHTML;
                            
                            // Re-initialize Lucide icons for the new rows
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        }
                    })
                    .catch(error => console.error('Search failed:', error));
                }, 300);
            });
        }      
    
    
    
        // SweetAlert Delete Confirmation (Using Event Delegation for AJAX compatibility)
        document.addEventListener('submit', function(e) {
            // Check if the submitted form has our specific class
            if (e.target && e.target.classList.contains('delete-staff-form')) {
                e.preventDefault(); // Stop immediate submission
                
                const form = e.target;
                const staffName = form.getAttribute('data-name');

                Swal.fire({
                    title: 'Remove Staff Member?',
                    html: `Are you sure you want to remove <b>${staffName}</b>?<br>This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444', // Tailwind red-500
                    cancelButtonColor: '#6b7280',  // Tailwind gray-500
                    confirmButtonText: 'Yes, remove them!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true // Puts primary action on the right
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state while processing
                        Swal.fire({
                            title: 'Removing...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });
                        form.submit(); // Submit the form
                    }
                });
            }
        });

    });
</script>
    
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/users/index.blade.php ENDPATH**/ ?>