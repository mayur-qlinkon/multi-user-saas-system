@extends('layouts.storefront')

@section('title', $company->name . ' — Choose Your Branch')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">

    <div class="text-center mb-10">
        @if(get_setting('icon'))
            <img src="{{ asset('storage/'.get_setting('icon')) }}" class="h-14 mx-auto mb-4 object-contain">
        @endif
        <h1 class="text-2xl font-black text-gray-900">{{ $company->name }}</h1>
        <p class="text-gray-500 mt-2">Choose your nearest branch to continue shopping</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($activeStores as $store)
        <a href="{{ route('store.index', ['slug' => $company->slug, 'store_slug' => $store->slug]) }}"
           class="group block bg-white border border-gray-200 rounded-2xl p-5 hover:border-brand-400 hover:shadow-md transition-all">

            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                    @if($store->logo)
                        <img src="{{ asset('storage/'.$store->logo) }}" class="w-full h-full object-cover">
                    @else
                        <i data-lucide="store" class="w-6 h-6 text-gray-400"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 group-hover:text-brand-600 transition-colors">{{ $store->name }}</h3>
                    @if($store->city)
                        <p class="text-sm text-gray-500 mt-0.5">{{ $store->city }}</p>
                    @endif
                    @if($store->business_hours)
                        <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3"></i> {{ $store->business_hours }}
                        </p>
                    @endif
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-gray-300 group-hover:text-brand-500 shrink-0 mt-1 transition-colors"></i>
            </div>
        </a>
        @endforeach
    </div>

</div>
@endsection