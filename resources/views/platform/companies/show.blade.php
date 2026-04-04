@extends('layouts.app')

@section('title', $company->name)
@section('header', $company->name)

@section('content')
<div class="w-full">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('platform.companies.index') }}" class="hover:text-brand-600 font-medium">Companies</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        <span class="text-gray-800 font-semibold">{{ $company->name }}</span>
    </div>

    @if (session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm font-medium px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Hero row --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6 flex flex-col sm:flex-row sm:items-center gap-5">
        <div class="w-16 h-16 rounded-2xl bg-brand-500/10 text-brand-600 font-black text-2xl flex items-center justify-center shrink-0">
            {{ strtoupper(substr($company->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <h2 class="text-xl font-bold text-gray-900">{{ $company->name }}</h2>
                @if ($company->is_active)
                    <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-[11px] font-bold px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 bg-red-50 text-red-600 text-[11px] font-bold px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Inactive
                    </span>
                @endif
            </div>
            <code class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-lg">{{ $company->slug }}</code>
        </div>
        <div class="flex gap-2 shrink-0">
            <a href="{{ route('platform.companies.edit', $company) }}"
                class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:border-brand-400 text-gray-700 hover:text-brand-600 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </a>
            <form method="POST" action="{{ route('platform.companies.destroy', $company) }}"
                onsubmit="return confirm('Permanently terminate {{ addslashes($company->name) }}? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:border-red-400 text-gray-700 hover:text-red-600 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline stroke-linecap="round" stroke-linejoin="round" points="3 6 5 6 21 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                    Terminate
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Stat cards --}}
        @php
            $stats = [
                ['label' => 'Total Users',  'value' => $company->users->count(),  'icon' => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M23 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75'],
                ['label' => 'Stores',       'value' => $company->stores->count(), 'icon' => 'M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'],
                ['label' => 'Plan',         'value' => $company->subscription?->plan?->name ?? 'No Plan', 'icon' => 'M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-brand-500/10 text-brand-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-gray-800">{{ $stat['value'] }}</p>
                    <p class="text-xs text-gray-500 font-medium">{{ $stat['label'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Company info --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                <h3 class="font-bold text-gray-700 text-sm">Company Info</h3>
            </div>
            <dl class="divide-y divide-gray-50">
                @foreach ([
                    ['Email',      $company->email],
                    ['Phone',      $company->phone      ?? '—'],
                    ['City',       $company->city       ?? '—'],
                    ['State',      $company->state?->name ?? '—'],
                    ['GST',        $company->gst_number ?? '—'],
                    ['Created',    $company->created_at->format('d M Y')],
                ] as [$label, $value])
                    <div class="flex items-center justify-between px-5 py-3">
                        <dt class="text-xs font-semibold text-gray-500">{{ $label }}</dt>
                        <dd class="text-sm font-medium text-gray-800 text-right">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Owner info --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-orange-50/50">
                <h3 class="font-bold text-gray-700 text-sm">Primary Owner</h3>
            </div>
            @if ($owner)
                <div class="p-5 flex items-center gap-4 border-b border-gray-50">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 font-bold text-sm flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($owner->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $owner->name }}</p>
                        <p class="text-xs text-gray-500">{{ $owner->email }}</p>
                    </div>
                    <span class="ml-auto text-[11px] font-bold px-2.5 py-1 rounded-full {{ $owner->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ ucfirst($owner->status) }}
                    </span>
                </div>
                <dl class="divide-y divide-gray-50">
                    @foreach ([
                        ['Phone',   $owner->phone      ?? '—'],
                        ['Joined',  $owner->created_at->format('d M Y')],
                    ] as [$label, $value])
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-xs font-semibold text-gray-500">{{ $label }}</dt>
                            <dd class="text-sm font-medium text-gray-800">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <div class="px-5 py-10 text-center text-sm text-gray-400">No owner found for this company.</div>
            @endif
        </div>

        {{-- Subscription --}}
        @if ($company->subscription)
            @php $sub = $company->subscription; @endphp
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden md:col-span-2">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="font-bold text-gray-700 text-sm">Subscription</h3>
                </div>
                <dl class="grid grid-cols-2 md:grid-cols-4 divide-x divide-y divide-gray-50">
                    @foreach ([
                        ['Plan',    $sub->plan?->name ?? '—'],
                        ['Status',  $sub->is_active ? 'Active' : 'Expired'],
                        ['Starts',  $sub->starts_at  ? \Carbon\Carbon::parse($sub->starts_at)->format('d M Y')  : '—'],
                        ['Expires', $sub->expires_at  ? \Carbon\Carbon::parse($sub->expires_at)->format('d M Y') : 'Lifetime'],
                    ] as [$label, $value])
                        <div class="px-5 py-4">
                            <dt class="text-xs font-semibold text-gray-500 mb-1">{{ $label }}</dt>
                            <dd class="text-sm font-bold text-gray-800">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif

        {{-- Stores list --}}
        @if ($company->stores->isNotEmpty())
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden md:col-span-2">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="font-bold text-gray-700 text-sm">Stores ({{ $company->stores->count() }})</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ($company->stores as $store)
                        <div class="flex items-center justify-between px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-600 text-xs font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($store->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">{{ $store->name }}</p>
                                    <code class="text-xs text-gray-400">{{ $store->slug }}</code>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $store->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $store->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
