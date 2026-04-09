@extends('layouts.admin')

@section('title', 'Add Staff Member - Qlinkon BIZNESS')

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">CREATE / USER</h1>
@endsection

@section('content')
    <div class="pb-10 w-full">

        {{-- Header & Back Button --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.users.index') }}" 
                   class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-500 hover:text-gray-800 hover:bg-gray-50 transition-colors shadow-sm">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Add New Staff Member</h3>
                    <p class="text-sm text-gray-500 mt-1">Create a new account and assign store access.</p>
                </div>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="bg-[#fee2e2] text-[#ef4444] px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix the following errors:</div>
                <ul class="list-disc list-inside pl-7 text-xs font-medium space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Form Container --}}
        <form action="{{ route('admin.users.store') }}" method="POST" class="w-full bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            @csrf

            {{-- 1. Account Details Section --}}
            <div class="p-6 md:p-8 border-b border-gray-100">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="user" class="w-5 h-5 text-brand-500"></i> Account Details
                    </h3>
                    <p class="text-[13px] text-gray-500 mt-1">Basic login information and role assignment.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    
                    {{-- Full Name --}}
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Jane Doe"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="jane@example.com"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all shadow-sm">
                    </div>

                    {{-- Role Selection --}}
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-2">Assign Role <span class="text-red-500">*</span></label>
                        <select name="role_id" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all bg-white shadow-sm appearance-none">
                            <option value="">-- Select a Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Selection --}}
                    <div>
                        <label class="block text-[13px] font-bold text-gray-700 mb-2">Account Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all bg-white shadow-sm appearance-none">
                            <option value="active" @selected(old('status', 'active') == 'active')>Active (Can Login)</option>
                            <option value="inactive" @selected(old('status') == 'inactive')>Inactive (Suspended)</option>
                        </select>
                    </div>

                </div>
            </div>

            {{-- 2. Store Assignment Section --}}
            <div class="p-6 md:p-8 bg-gray-50/30">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="store" class="w-5 h-5 text-brand-500"></i> Assign Stores
                    </h3>
                    <p class="text-[13px] text-gray-500 mt-1">Select one or multiple stores this staff member can access.</p>
                </div>

                {{-- Custom Multi-Select Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @forelse($stores as $store)
                        <label class="block relative cursor-pointer group">
                            {{-- Hidden Checkbox + Peer logic --}}
                            <input type="checkbox" name="store_ids[]" value="{{ $store->id }}" class="peer sr-only" 
                                @checked(is_array(old('store_ids')) && in_array($store->id, old('store_ids')))>
                            
                            {{-- Visual Card --}}
                            <div class="p-4 border-2 border-gray-200 bg-white rounded-xl transition-all duration-200 ease-in-out hover:border-brand-300 peer-checked:border-brand-500 peer-checked:bg-brand-50 shadow-sm flex flex-col h-full">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 peer-checked:bg-brand-100 peer-checked:text-brand-600 transition-colors">
                                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                                    </div>
                                    
                                    {{-- Custom Checkmark Box --}}
                                    <div class="w-5 h-5 rounded border-2 border-gray-300 flex items-center justify-center peer-checked:bg-brand-500 peer-checked:border-brand-500 transition-colors">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100"></i>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-gray-800 block mt-auto leading-tight">{{ $store->name }}</span>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-full py-8 text-center bg-white border border-gray-200 border-dashed rounded-xl">
                            <i data-lucide="store" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                            <p class="text-sm font-bold text-gray-500">No stores available to assign.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Form Footer / Actions --}}
            <div class="px-6 py-5 bg-gray-50 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" 
                   class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors text-center shadow-sm">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-brand-500 text-white rounded-lg text-sm font-bold hover:bg-brand-600 transition-colors shadow-sm flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Staff Member
                </button>
            </div>

        </form>
    </div>
@endsection