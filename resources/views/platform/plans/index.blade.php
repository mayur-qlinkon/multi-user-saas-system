@extends('layouts.platform')

@section('title', 'Manage Plans - Qlinkon Super Admin')
@section('header', 'Subscription Plans')

@section('styles')
    <style>
        [x-cloak] { display: none !important; }
        body.modal-open { overflow: hidden; }
    </style>
@endsection

@section('content')
    <div class="pb-10" x-data="planIndexManager()">

        {{-- ── HEADER ── --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Manage Plans</h1>
                <p class="text-sm text-gray-500 mt-1">Configure pricing, UI badges, and resource limits.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('platform.plans.create') }}"
                    class="bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Create Plan
                </a>
            </div>
        </div>

        {{-- ── ALERTS ── --}}
        @if (session('success'))
            <div class="bg-green-50 text-green-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-green-100 mb-6 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> {{ session('success') }}
            </div>
        @endif

        {{-- ── PLANS GRID ── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse ($plans as $plan)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow relative">

                    {{-- Badges --}}
                    <div class="absolute top-4 right-4 flex flex-col gap-2 items-end">
                        @if ($plan->is_active)
                            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Active</span>
                        @else
                            <span class="bg-gray-100 text-gray-500 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Inactive</span>
                        @endif
                    </div>

                    {{-- Plan Header --}}
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">                        
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
                            <div class="bg-brand-50 col-span-2 rounded-lg p-2.5 border border-brand-100 text-center">
                                <p class="text-[10px] text-brand-600 font-bold uppercase tracking-wider mb-0.5">Daily OCR Scans</p>
                                <p class="text-base font-black text-brand-800">{{ $plan->ocr_scan_limit }}</p>
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
                    </div>

                    {{-- Actions --}}
                    <div class="p-4 border-t border-gray-100 flex items-center justify-end gap-2 bg-white">
                        <a href="{{ route('platform.plans.edit', $plan->id) }}"
                            class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                        </a>
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
                    <a href="{{ route('platform.plans.create') }}" class="mt-6 text-brand-600 font-bold text-sm hover:underline flex items-center gap-1">
                        Create Plan Now <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            @endforelse
        </div>

        {{-- ── DELETE MODAL ── --}}
        <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()" x-show="showDeleteModal" x-transition.opacity></div>
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
                    <button type="button" @click="closeModal()" class="px-6 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
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
        function planIndexManager() {
            return {
                showDeleteModal: false,
                deleteForm: { id: '', name: '' },

                openDelete(id, name) {
                    document.body.classList.add('modal-open');
                    this.deleteForm = { id, name };
                    this.showDeleteModal = true;
                },

                closeModal() {
                    document.body.classList.remove('modal-open');
                    this.showDeleteModal = false;
                }
            }
        }
    </script>
@endsection