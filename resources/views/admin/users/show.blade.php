@extends('layouts.admin')

@section('title', 'Staff Details - Qlinkon BIZNESS')

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">LIST / USERS / DETAILS</h1>
@endsection

@section('content')
    <div class="pb-10 w-full">

        {{-- Header & Actions --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.users.index') }}" 
                   class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-500 hover:text-gray-800 hover:bg-gray-50 transition-colors shadow-sm shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Staff Member Details</h2>
                    <p class="text-sm text-gray-500 mt-1">Viewing profile and access level for <strong>{{ $user->name }}</strong>.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if (auth()->id() !== $user->id && ($user->roles->first()->slug ?? '') !== 'owner')
                    <a href="{{ route('admin.users.edit', $user->id) }}" 
                       class="px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-2">
                        <i data-lucide="pencil" class="w-4 h-4"></i> Edit Staff
                    </a>

                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove {{ addslashes($user->name) }}? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2.5 bg-red-50 text-red-600 border border-red-100 rounded-lg text-sm font-bold hover:bg-red-100 transition-colors shadow-sm flex items-center gap-2">
                            <i data-lucide="trash-2" class="w-4 h-4"></i> Remove
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column: Profile Card --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    {{-- Decorative Top Banner --}}
                    <div class="h-24 bg-gradient-to-r from-brand-500/10 to-brand-500/5 border-b border-brand-100/50"></div>
                    
                    <div class="px-6 pb-6 relative text-center">
                        {{-- Avatar --}}
                        <div class="w-20 h-20 rounded-full bg-brand-50 border-4 border-white text-brand-600 flex items-center justify-center text-2xl font-bold -mt-10 mx-auto shadow-sm ring-1 ring-gray-100">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        
                        {{-- Basic Info --}}
                        <div class="mt-4">
                            <h3 class="text-xl font-bold text-gray-800">{{ $user->name }}</h3>
                            <p class="text-[13px] text-gray-500 mt-0.5">{{ $user->email }}</p>
                        </div>

                        {{-- Badges --}}
                        <div class="flex items-center justify-center gap-2 mt-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-600 border border-purple-100">
                                {{ $user->roles->first()->name ?? 'No Role Assigned' }}
                            </span>
                            @if ($user->status === 'active')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-100">
                                    Inactive
                                </span>
                            @endif
                        </div>

                        {{-- System Details --}}
                        <div class="mt-6 pt-6 border-t border-gray-100 space-y-3">
                            <div class="flex items-center justify-between text-[13px]">
                                <span class="text-gray-500 font-medium">System ID</span>
                                <span class="font-bold text-gray-800">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[13px]">
                                <span class="text-gray-500 font-medium">Added On</span>
                                <span class="font-bold text-gray-800">{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[13px]">
                                <span class="text-gray-500 font-medium">Last Updated</span>
                                <span class="font-bold text-gray-800">{{ $user->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Stores & Permissions --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Store Access Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <i data-lucide="store" class="w-5 h-5 text-brand-500"></i> Store Access
                            </h3>
                            <p class="text-[13px] text-gray-500 mt-1">Locations this staff member is authorized to manage.</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 font-bold shrink-0">
                            {{ $user->stores->count() }}
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50/50 flex-1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                            @forelse($user->stores as $store)
                                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col hover:border-brand-300 transition-colors">
                                    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 mb-3 border border-gray-100">
                                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-gray-800 leading-tight">{{ $store->name }}</h4>
                                    
                                    {{-- If your store table has address/city, you could display it here --}}
                                    <p class="text-[11px] text-gray-400 mt-1 font-medium flex items-center gap-1">
                                        <i data-lucide="check-circle-2" class="w-3 h-3 text-green-500"></i> Authorized
                                    </p>
                                </div>
                            @empty
                                <div class="col-span-full py-12 flex flex-col items-center justify-center text-center bg-white border border-gray-200 border-dashed rounded-xl">
                                    <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                        <i data-lucide="store" class="w-6 h-6 text-gray-400"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-gray-800 mb-1">No Stores Assigned</h4>
                                    <p class="text-xs text-gray-500">This staff member cannot access any store data.</p>
                                    @if (auth()->id() !== $user->id && ($user->roles->first()->slug ?? '') !== 'owner')
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="mt-4 text-sm font-bold text-brand-500 hover:text-brand-700">Assign Stores &rarr;</a>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection