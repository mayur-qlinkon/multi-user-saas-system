@extends('layouts.platform')

@section('title', 'Manage Plans - Qlinkon Super Admin')
@section('header', 'Subscription Plans')

@section('styles')
    <style>
        [x-cloak] { display: none !important; }
        body.modal-open { overflow: hidden; }
        
        /* Custom scrollbar for multi-select */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .form-label { display: block; font-size: 12px; font-weight: 700; color: #4b5563; margin-bottom: 6px; }
        .form-input { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 12px; font-size: 13px; transition: all 0.2s; outline: none; }
        .form-input:focus { border-color: var(--brand-500); box-shadow: 0 0 0 3px rgba(0, 138, 98, 0.1); }
    </style>
@endsection

@section('content')
    <div class="pb-10" x-data="planManager(@js($plans), @js($modules))">

        {{-- ── HEADER ── --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Manage Plans</h1>
                <p class="text-sm text-gray-500 mt-1">Configure pricing, UI badges, and resource limits.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="openCreate()"
                    class="bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Create Plan
                </button>
            </div>
        </div>

        {{-- ── ALERTS ── --}}
        @if (session('success'))
            <div class="bg-green-50 text-green-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-green-100 mb-6 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 text-red-600 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix the following errors:</div>
                <ul class="list-disc list-inside pl-7 text-xs font-medium space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── PLANS GRID ── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse ($plans as $plan)
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden flex flex-col hover:shadow-md transition-shadow relative {{ $plan->is_recommended ? 'border-brand-500 ring-1 ring-brand-500' : 'border-gray-200' }}">

                    {{-- Badges --}}
                    <div class="absolute top-4 right-4 flex flex-col gap-2 items-end">
                        @if ($plan->is_active)
                            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Active</span>
                        @else
                            <span class="bg-gray-100 text-gray-500 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Inactive</span>
                        @endif
                        
                        @if($plan->is_recommended)
                            <span class="bg-brand-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-md uppercase flex items-center gap-1 shadow-sm">
                                <i data-lucide="star" class="w-3 h-3 fill-white"></i> Recommended
                            </span>
                        @endif
                    </div>

                    {{-- Plan Header --}}
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <p class="text-xs text-gray-400 font-bold mb-1 uppercase">Order: {{ $plan->sort_order }}</p>
                        <h3 class="text-xl font-black text-gray-800 mb-1">{{ $plan->name }}</h3>
                        @if($plan->description)
                            <p class="text-xs text-gray-500 mb-3">{{ $plan->description }}</p>
                        @endif
                        
                        <div class="flex items-baseline gap-1 mt-2">
                            <span class="text-3xl font-black text-gray-900">₹{{ number_format($plan->price, 0) }}</span>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">/{{ $plan->billing_cycle }}</span>
                        </div>
                        
                        @if($plan->trial_days > 0)
                            <p class="text-xs font-bold text-brand-600 mt-2 bg-brand-50 inline-block px-2 py-1 rounded">{{ $plan->trial_days }} Days Free Trial</p>
                        @endif
                    </div>

                    {{-- Plan Details --}}
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100 text-center">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Users</p>
                                <p class="text-base font-black text-gray-700">{{ $plan->user_limit }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100 text-center">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Stores</p>
                                <p class="text-base font-black text-gray-700">{{ $plan->store_limit }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100 text-center">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Products</p>
                                <p class="text-base font-black text-gray-700">{{ $plan->product_limit }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100 text-center">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Employees</p>
                                <p class="text-base font-black text-gray-700">{{ $plan->employee_limit }}</p>
                            </div>
                        </div>

                        <div class="mb-4 flex-1">
                            <p class="text-xs font-bold text-gray-800 mb-2 flex items-center gap-1.5">
                                <i data-lucide="boxes" class="w-4 h-4 text-brand-500"></i> {{ $plan->modules->count() }} Modules
                            </p>
                            <ul class="space-y-1.5">
                                @foreach($plan->modules->take(3) as $module)
                                    <li class="flex items-start gap-2 text-[13px] text-gray-600">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-green-500 shrink-0 mt-0.5"></i>
                                        <span class="font-medium truncate">{{ $module->name }}</span>
                                    </li>
                                @endforeach
                                @if($plan->modules->count() > 3)
                                    <li class="text-xs font-bold text-gray-400 pl-5">+ {{ $plan->modules->count() - 3 }} more...</li>
                                @endif
                            </ul>
                        </div>
                        
                        <div class="mt-auto pt-4 border-t border-gray-100">
                            <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Button Preview:</p>
                            <div class="w-full text-center py-2 bg-gray-100 text-gray-700 text-sm font-bold rounded-lg border border-gray-200">
                                {{ $plan->button_text ?: 'Get Started' }}
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="p-4 border-t border-gray-100 flex items-center justify-end gap-2 bg-white">
                        <button type="button" @click="openEdit({{ $plan->id }})"
                            class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                        </button>
                        <button type="button" @click="openDelete({{ $plan->id }}, '{{ addslashes($plan->name) }}')"
                            class="px-4 py-2 bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-white rounded-xl border border-gray-200 border-dashed">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="layers" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">No Plans Created</h3>
                    <p class="text-sm text-gray-500 max-w-sm">Create your first subscription plan to start onboarding companies.</p>
                    <button type="button" @click="openCreate()" class="mt-6 text-brand-600 font-bold text-sm hover:underline flex items-center gap-1">
                        Create Plan Now <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            @endforelse
        </div>

        {{-- ── FORM MODAL ── --}}
        <div x-cloak x-show="showFormModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeAll()" x-show="showFormModal" x-transition.opacity></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[95vh]"
                x-show="showFormModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">

                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-[16px] font-bold text-gray-800 tracking-tight" x-text="isEditing ? 'Edit Plan' : 'Create New Plan'"></h3>
                    </div>
                    <button type="button" @click="closeAll()" class="text-gray-400 hover:text-red-500 transition-colors p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="isEditing ? `/platform/plans/${form.id}` : '{{ route('platform.plans.store') }}'" method="POST" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <template x-if="isEditing"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-8">
                        
                        {{-- Section: Basic & Pricing --}}
                        <div>
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">1. Identity & Pricing</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="form-label">Plan Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" x-model="form.name" required placeholder="e.g. Premium Plan" class="form-input">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="form-label">Short Description</label>
                                    <input type="text" name="description" x-model="form.description" placeholder="e.g. Best for growing businesses" class="form-input">
                                </div>
                                
                                <div>
                                    <label class="form-label">Price (₹) <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" name="price" x-model="form.price" required placeholder="0.00" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Billing Cycle <span class="text-red-500">*</span></label>
                                    <select name="billing_cycle" x-model="form.billing_cycle" required class="form-input bg-white">
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
                                        <option value="lifetime">Lifetime (One-time)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Free Trial Days</label>
                                    <input type="number" name="trial_days" x-model="form.trial_days" min="0" placeholder="0 for no trial" class="form-input">
                                </div>
                            </div>
                        </div>

                        {{-- Section: UI Configuration --}}
                        <div>
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">2. Frontend UI Customization</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Button Text</label>
                                    <input type="text" name="button_text" x-model="form.button_text" placeholder="e.g. Get Started, Start Trial" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Button Link (Optional URL)</label>
                                    <input type="text" name="button_link" x-model="form.button_link" placeholder="Leave empty for default checkout" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" x-model="form.sort_order" placeholder="0" class="form-input">
                                    <p class="text-[10px] text-gray-400 mt-1">Lower numbers appear first.</p>
                                </div>
                                
                                <div class="flex flex-col gap-3 justify-center sm:pl-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_recommended" value="1" x-model="form.is_recommended" class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500">
                                        <span class="text-sm font-bold text-gray-700">Highlight as "Recommended"</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500">
                                        <span class="text-sm font-bold text-gray-700">Plan is Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Limits & Modules --}}
                        <div>
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">3. Limits & Features</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
                                <div>
                                    <label class="form-label">User Limit <span class="text-red-500">*</span></label>
                                    <input type="number" name="user_limit" x-model="form.user_limit" required min="1" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Store Limit <span class="text-red-500">*</span></label>
                                    <input type="number" name="store_limit" x-model="form.store_limit" required min="1" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Product Limit <span class="text-red-500">*</span></label>
                                    <input type="number" name="product_limit" x-model="form.product_limit" required min="1" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Employee Limit <span class="text-red-500">*</span></label>
                                    <input type="number" name="employee_limit" x-model="form.employee_limit" required min="1" class="form-input">
                                </div>
                            </div>

                            <div class="flex items-center justify-between mb-2">
                                <label class="form-label mb-0">Assigned Modules</label>
                                <button type="button" @click="toggleAllModules()" x-show="availableModules.length > 0"
                                    class="text-[10px] font-bold px-2 py-1 rounded transition-colors border"
                                    :class="areAllSelected() ? 'bg-gray-100 text-gray-600 border-gray-200' : 'bg-brand-50 text-brand-600 border-brand-100 hover:bg-brand-100'">
                                    <span x-text="areAllSelected() ? 'Deselect All' : 'Select All'"></span>
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                <template x-for="module in availableModules" :key="module.id">
                                    <label class="flex items-center gap-3 p-2.5 border rounded-lg cursor-pointer transition-colors hover:bg-gray-50"
                                        :class="form.modules.includes(module.id) ? 'border-brand-500 bg-brand-50/20' : 'border-gray-200 bg-white'">
                                        <input type="checkbox" name="modules[]" :value="module.id" x-model="form.modules"
                                            class="w-4 h-4 text-brand-600 bg-gray-100 border-gray-300 rounded focus:ring-brand-500 cursor-pointer">
                                        <span class="text-xs font-bold text-gray-800 truncate" x-text="module.name"></span>
                                    </label>
                                </template>
                            </div>
                            <template x-if="availableModules.length === 0">
                                <p class="text-sm text-gray-500 italic p-4 bg-gray-50 rounded-lg border border-dashed border-gray-200 text-center">No active modules found.</p>
                            </template>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50 shrink-0">
                        <button type="button" @click="closeAll()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-bold hover:bg-brand-700 transition-colors shadow-sm" x-text="isEditing ? 'Update Plan' : 'Save Plan'"></button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── DELETE MODAL ── --}}
        <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeAll()" x-show="showDeleteModal" x-transition.opacity></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden text-center"
                x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="p-6 pt-8">
                    <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-triangle" class="w-8 h-8 text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Delete Plan?</h3>
                    <p class="text-sm text-gray-500">Are you sure you want to delete the <strong class="text-gray-800" x-text="deleteForm.name"></strong> plan? Existing subscriptions will remain active due to soft deletes.</p>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-center gap-3 bg-gray-50">
                    <button type="button" @click="closeAll()" class="px-6 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                    <form :action="`/platform/plans/${deleteForm.id}`" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition-colors shadow-sm">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        function planManager(allPlans, availableModules) {
            return {
                plans: allPlans,
                availableModules: availableModules,
                showFormModal: false,
                showDeleteModal: false,
                isEditing: false,

                form: {
                    id: '', name: '', description: '', price: '', billing_cycle: 'monthly', trial_days: 0,
                    user_limit: 1, store_limit: 1, product_limit: 50, employee_limit: 50, is_recommended: false, is_active: true,
                    button_text: 'Get Started', button_link: '', sort_order: 0, modules: []
                },

                deleteForm: { id: '', name: '' },

                openCreate() {
                    document.body.classList.add('modal-open');
                    this.isEditing = false;
                    this.form = {
                        id: '', name: '', description: '', price: '', billing_cycle: 'monthly', trial_days: 0,
                        user_limit: 1, store_limit: 1, product_limit: 50, employee_limit: 50, is_recommended: false, is_active: true,
                        button_text: 'Get Started', button_link: '', sort_order: 0, modules: []
                    };
                    this.showFormModal = true;
                },

                openEdit(id) {
                    let plan = this.plans.find(p => p.id === id);
                    if (!plan) return;

                    document.body.classList.add('modal-open');
                    this.isEditing = true;

                    this.form = {
                        id: plan.id,
                        name: plan.name,
                        description: plan.description || '',
                        price: plan.price,
                        billing_cycle: plan.billing_cycle || 'monthly',
                        trial_days: plan.trial_days || 0,
                        user_limit: plan.user_limit,
                        store_limit: plan.store_limit,
                        product_limit: plan.product_limit,
                        employee_limit: plan.employee_limit,
                        is_recommended: !!plan.is_recommended,
                        is_active: !!plan.is_active,
                        button_text: plan.button_text || 'Get Started',
                        button_link: plan.button_link || '',
                        sort_order: plan.sort_order || 0,
                        modules: plan.modules ? plan.modules.map(m => m.id) : []
                    };
                    this.showFormModal = true;
                },

                openDelete(id, name) {
                    document.body.classList.add('modal-open');
                    this.deleteForm = { id, name };
                    this.showDeleteModal = true;
                },

                closeAll() {
                    document.body.classList.remove('modal-open');
                    this.showFormModal = false;
                    this.showDeleteModal = false;
                },

                areAllSelected() {
                    if (this.availableModules.length === 0) return false;
                    return this.form.modules.length === this.availableModules.length;
                },

                toggleAllModules() {
                    if (this.areAllSelected()) {
                        this.form.modules = []; 
                    } else {
                        this.form.modules = this.availableModules.map(m => m.id); 
                    }
                }
            }
        }
    </script>
@endsection