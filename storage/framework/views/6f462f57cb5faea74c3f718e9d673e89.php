

<?php $__env->startSection('title', 'Payment Methods'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">List / Payment Methods</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] { display: none !important; }

        /* ── Required Custom Styles ── */
        .sortable-ghost {
            opacity: 0.35;
            background: #f0fdf4 !important; /* Tailwind green-50 equivalent */
        }

        .drag-handle {
            cursor: grab;
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }

        .drag-disabled {
            cursor: not-allowed;
            opacity: 0.3;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div x-data="paymentMethodApp()" x-init="boot()" class="pb-12">


        
        <div class="pm-card">

            
            <div class="bg-white p-5 border-b border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between rounded-t-xl">
                <span class="text-[16px] font-bold text-gray-900">All Methods</span>
                
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <input type="text" x-model="search" placeholder="Search methods…"
                            class="block w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors bg-gray-50 placeholder-gray-400">
                    </div>

                    
                    <?php if(has_permission('payment_methods.create')): ?>
                        <button @click="openModal()"
                            class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 transition-all shadow-md active:scale-95 whitespace-nowrap">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add Method
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table id="sortable-table" class="w-full text-left border-collapse min-w-[720px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center w-14">Move</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Method Name</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">Gateway</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center">Type</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-center">Status</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider text-right pr-6">Actions</th>
                        </tr>
                    </thead>

                    
                   <template x-for="row in filteredMethods" :key="row.id">
                        <tbody class="sortable-row" :data-id="row.id">
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                
                                <td class="px-5 py-4 text-center">
                                    <span :class="search === '' ? 'drag-handle text-gray-400 hover:text-brand-500' : 'drag-disabled text-gray-300'"
                                        :title="search === '' ? 'Drag to reorder' : 'Clear search to reorder'">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.8" stroke="currentColor" class="w-[18px] h-[18px] inline-block">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5M3.75 15h16.5" />
                                        </svg>
                                    </span>
                                </td>

                                
                                <td class="px-5 py-4">
                                    <div class="font-bold text-gray-900 text-sm" x-text="row.label"></div>
                                    <div class="text-[11px] text-gray-500 mt-0.5 font-medium flex items-center gap-1">
                                        <span class="bg-gray-100 rounded px-1.5 py-0.5 font-mono" x-text="row.slug"></span>
                                    </div>
                                </td>

                                
                                <td class="px-5 py-4">
                                    <template x-if="row.gateway">
                                        <span class="bg-blue-50 text-blue-700 border border-blue-200 rounded-md px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase inline-block" x-text="row.gateway.toUpperCase()"></span>
                                    </template>
                                    <template x-if="!row.gateway">
                                        <span class="bg-gray-50 text-gray-400 border border-gray-200 rounded-md px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase inline-block">N/A</span>
                                    </template>
                                </td>

                                
                                <td class="px-5 py-4 text-center">
                                    <template x-if="row.is_online">
                                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-md px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 shadow-[0_0_0_2px_rgba(59,130,246,0.3)]"></span> Online
                                        </span>
                                    </template>
                                    <template x-if="!row.is_online">
                                        <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-md px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Offline
                                        </span>
                                    </template>
                                </td>

                                
                                <td class="px-5 py-4 text-center">
                                    <template x-if="row.is_active">
                                        <span class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-600 border border-brand-200 rounded-md px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-brand-500 shadow-[0_0_0_2px_rgba(16,185,129,0.25)]"></span> Active
                                        </span>
                                    </template>
                                    <template x-if="!row.is_active">
                                        <span class="inline-flex items-center gap-1.5 bg-gray-50 text-gray-500 border border-gray-200 rounded-md px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                                        </span>
                                    </template>
                                </td>

                                
                                <td class="px-5 py-4 text-right pr-6">
                                    <div class="inline-flex items-center gap-2">
                                        <?php if(has_permission('payment_methods.update')): ?>
                                            <button @click="openModal(row)" title="Edit"
                                                class="w-8 h-8 rounded-lg border border-gray-200 bg-white text-brand-500 hover:bg-brand-500 hover:text-white hover:border-brand-500 flex items-center justify-center transition-colors">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if(has_permission('payment_methods.delete')): ?>
                                            <button @click="deleteMethod(row.id)" title="Delete"
                                                class="w-8 h-8 rounded-lg border border-gray-200 bg-white text-red-500 hover:bg-red-500 hover:text-white hover:border-red-500 flex items-center justify-center transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </template>

                                       
                    <tbody x-show="filteredMethods.length === 0">
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-16 h-16 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center mb-2">
                                        <i data-lucide="credit-card" class="w-8 h-8 text-brand-500"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-[15px] font-bold text-gray-900" x-text="search ? 'No results found' : 'No payment methods yet'"></h3>
                                        <p class="text-sm text-gray-500 mt-1 max-w-xs mx-auto" x-text="search ? 'Try a different search term.' : 'Click Add Method to create your first payment gateway.'"></p>
                                    </div>
                                    <button x-show="!search" @click="openModal()" class="mt-2 bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 transition-all shadow-md active:scale-95">
                                        <i data-lucide="plus" class="w-4 h-4"></i> Add First Method
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div x-cloak x-show="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm" @click.self="closeModal()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto flex flex-col" @click.stop
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                
                <div class="sticky top-0 z-10 bg-white border-b border-gray-100 px-6 py-5 flex items-center justify-between rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center">
                            <i data-lucide="credit-card" class="w-5 h-5 text-brand-500"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900" x-text="isEdit ? 'Edit Payment Method' : 'Add Payment Method'"></h3>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="isEdit ? 'Update gateway details and settings' : 'Configure a new payment gateway'"></p>
                        </div>
                    </div>
                    <button @click="closeModal()" type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:text-red-500 hover:bg-red-50 hover:border-red-200 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Display Label <span class="text-red-500">*</span></label>
                            <input type="text" x-model="form.label" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                                placeholder="e.g. Credit / Debit Card" required @keydown.enter.prevent="saveMethod()">
                        </div>

                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">URL Slug <span class="text-gray-400 font-medium normal-case">(optional)</span></label>
                            <input type="text" x-model="form.slug" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                                placeholder="Auto-generated">
                        </div>

                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Gateway <span class="text-gray-400 font-medium normal-case">(optional)</span></label>
                            <input type="text" x-model="form.gateway" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                                placeholder="e.g. razorpay">
                        </div>
                    </div>

                    <hr class="my-5 border-gray-100">

                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        
                        <label class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-100 rounded-xl cursor-pointer hover:border-brand-300 hover:bg-brand-50/50 transition-colors">
                            <div class="relative">
                                <input type="checkbox" x-model="form.is_online" class="sr-only">
                                <div class="block w-10 h-6 bg-gray-200 rounded-full transition-colors" :class="form.is_online ? 'bg-brand-500' : ''"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform" :class="form.is_online ? 'translate-x-4' : ''"></div>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-800">Online Gateway</div>
                                <div class="text-[11px] text-gray-500">Processes via internet</div>
                            </div>
                        </label>

                        
                        <label class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-100 rounded-xl cursor-pointer hover:border-brand-300 hover:bg-brand-50/50 transition-colors">
                            <div class="relative">
                                <input type="checkbox" x-model="form.is_active" class="sr-only">
                                <div class="block w-10 h-6 bg-gray-200 rounded-full transition-colors" :class="form.is_active ? 'bg-brand-500' : ''"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform" :class="form.is_active ? 'translate-x-4' : ''"></div>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-800">Active Status</div>
                                <div class="text-[11px] text-gray-500">Visible to customers</div>
                            </div>
                        </label>
                    </div>
                </div>

               
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 rounded-b-2xl">
                    <button type="button" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold text-sm rounded-xl hover:bg-gray-100 transition-colors" @click="closeModal()" :disabled="isSaving">
                        Cancel
                    </button>
                    <button type="button" class="px-6 py-2.5 bg-brand-500 text-white font-bold text-sm rounded-xl hover:bg-brand-600 transition-all shadow-md active:scale-95 flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed" @click="saveMethod()" :disabled="isSaving">
                        <i data-lucide="loader-2" x-show="isSaving" class="w-4 h-4 animate-spin" style="display: none;"></i>
                        <span x-text="isSaving ? 'Saving…' : (isEdit ? 'Update Method' : 'Save Method')"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('assets/js/sortable.min.js')); ?>"></script>
    <script>
        function paymentMethodApp() {
            return {
                methods: <?php echo json_encode($paymentMethods ?? [], 15, 512) ?>,
                search: '',
                showModal: false,
                isEdit: false,
                isSaving: false,

                form: {
                    id: null,
                    label: '',
                    slug: '',
                    gateway: '',
                    is_online: false,
                    is_active: true
                },

                /* ── Computed filtered + sorted list ── */
                get filteredMethods() {
                    const sorted = this.methods.slice().sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
                    if (!this.search.trim()) return sorted;
                    const q = this.search.toLowerCase();
                    return sorted.filter(m =>
                        (m.label && m.label.toLowerCase().includes(q)) ||
                        (m.slug && m.slug.toLowerCase().includes(q)) ||
                        (m.gateway && m.gateway.toLowerCase().includes(q))
                    );
                },

                /* ── Init ── */
                boot() {
                    this.$nextTick(() => this.initSortable());
                },

                /* ── Drag-and-drop ── */
                initSortable() {
                    const table = document.getElementById('sortable-table');
                    if (!table) return;
                    const _this = this;

                    new Sortable(table, {
                        draggable: 'tbody.sortable-row',
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'sortable-ghost',

                        /* Prevent drag while searching */
                        onMove() {
                            return _this.search === '';
                        },

                        onEnd: async function(evt) {
                            if (evt.oldIndex === evt.newIndex) return;

                            /* Re-sync Alpine array to match new DOM order */
                            const rows = [...table.querySelectorAll('tbody.sortable-row[data-id]')];
                            const idOrder = rows.map(r => parseInt(r.dataset.id));

                            const reordered = idOrder
                                .map(id => _this.methods.find(m => m.id === id))
                                .filter(Boolean);

                            /* Fill in any items not currently rendered (e.g. if filtered) */
                            const renderedIds = new Set(idOrder);
                            _this.methods.filter(m => !renderedIds.has(m.id)).forEach(m => reordered.push(m));

                            /* Update sort_order values */
                            const payload = [];
                            reordered.forEach((m, i) => {
                                m.sort_order = i + 1;
                                payload.push({
                                    id: m.id,
                                    sort_order: m.sort_order
                                });
                            });
                            _this.methods = reordered;

                            /* Persist to backend */
                            try {
                                const res = await fetch("<?php echo e(route('admin.payment_methods.reorder')); ?>", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                                    },
                                    body: JSON.stringify({
                                        order: payload
                                    })
                                });
                                const result = await res.json();
                                result.success ?
                                    BizAlert.toast('Sort order saved!', 'success') :
                                    BizAlert.toast('Failed to save order.', 'error');
                            } catch (err) {
                                console.error('Reorder error:', err);
                                BizAlert.toast('Network error saving order.', 'error');
                            }
                        }
                    });
                },

                /* ── Modal ── */
                openModal(row = null) {
                    this.isEdit = !!row;
                    this.form = row ? {
                        ...row
                    } : {
                        id: null,
                        label: '',
                        slug: '',
                        gateway: '',
                        is_online: false,
                        is_active: true
                    };
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    setTimeout(() => {
                        this.isSaving = false;
                    }, 300);
                },

                /* ── Save (create / update) ── */
                async saveMethod() {
                    if (!this.form.label.trim()) {
                        BizAlert.toast('Display label is required.', 'error');
                        return;
                    }
                    this.isSaving = true;
                    const method = this.isEdit ? 'PUT' : 'POST';
                    const url = this.isEdit ?
                        `/admin/payment-methods/${this.form.id}` :
                        `/admin/payment-methods`;

                    try {
                        const res = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                            },
                            body: JSON.stringify(this.form)
                        });
                        const result = await res.json();

                        if (res.ok && result.success) {
                            if (this.isEdit) {
                                const idx = this.methods.findIndex(m => m.id === this.form.id);
                                if (idx !== -1) {
                                    result.data.sort_order = this.methods[idx].sort_order;
                                    this.methods.splice(idx, 1, result.data);
                                }
                            } else {
                                result.data.sort_order = this.methods.length ?
                                    Math.max(...this.methods.map(m => m.sort_order ?? 0)) + 1 :
                                    1;
                                this.methods.push(result.data);
                            }
                            this.$nextTick(() => {
                                if (typeof lucide !== 'undefined') {
                                    lucide.createIcons();
                                }
                            });
                            BizAlert.toast(result.message, 'success');
                            this.closeModal();
                        } else {
                            BizAlert.toast(result.message || 'Validation failed.', 'error');
                        }
                    } catch (err) {
                        console.error('Save error:', err);
                        BizAlert.toast('Network error occurred.', 'error');
                    } finally {
                        this.isSaving = false;
                    }
                },

                /* ── Delete ── */
                async deleteMethod(id) {
                    const confirm = await BizAlert.confirm(
                        'Delete Payment Method?',
                        'This action cannot be undone.',
                        'Yes, Delete It',
                        'warning'
                    );
                    if (!confirm.isConfirmed) return;

                    try {
                        const res = await fetch(`/admin/payment-methods/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                            }
                        });
                        const result = await res.json();

                        if (res.ok && result.success) {
                            this.methods = this.methods.filter(m => m.id !== id);
                            BizAlert.toast(result.message, 'success');
                        } else {
                            BizAlert.toast(result.message || 'Failed to delete.', 'error');
                        }
                    } catch (err) {
                        BizAlert.toast('Network error occurred.', 'error');
                    }
                }
            };
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/payment_methods.blade.php ENDPATH**/ ?>