@extends('layouts.storefront')

@section('title', 'Digital Catalog and Online Appointment - Qlinkon Shop')
@push('styles')
<style>
    /* ── Hide ALL Google Translate UI chrome ── */
    .goog-te-banner-frame,
    .goog-te-balloon-frame,
    .goog-tooltip,
    .goog-tooltip-content,
    #goog-gt-tt,
    .goog-te-ftab-float,
    .goog-te-menu-value:hover,
    .goog-te-gadget-icon { display: none !important; }

    /* ── Prevent body shift from translate bar ── */
    body { top: 0 !important; position: static !important; }

    /* ── Hide the injected iframe bar ── */
    .skiptranslate { display: none !important; }

    /* ── Remove font changes Google Translate injects ── */
    font { background-color: transparent !important; }
</style>
@endpush
@section('content')
    @php
        // ── Prepare data for Alpine ──
        $isCatalog = $product->isCatalog();
        $images = $product->media->where('media_type', 'image')->values();
        $firstImg = $images->first()?->media_path;
        $youtubeVideos = $product->media->where('media_type', 'youtube')->values();
        $hasSku = $product->skus->isNotEmpty();
        $firstSku = $hasSku ? $product->skus->first() : null;
        $minPrice = $hasSku ? ($product->skus->min('price') ?? 0) : 0;
        $maxPrice = $hasSku ? ($product->skus->max('price') ?? 0) : 0;
        $inStock = $hasSku ? $product->skus->sum(fn($s) => $s->stocks->sum('qty')) > 0 : false;

        // Group SKU attributes for variant selector
        $attributes = collect();

        foreach ($product->skus as $sku) {
            foreach ($sku->skuValues as $sv) {
                
                // 1. Guard clause: Skip this loop if the related attributeValue is missing
                if (! $sv->attributeValue) {
                    continue; 
                }

                // 2. Use null-safe operator (?->) in case the parent attribute is missing
                $attrName = $sv->attribute?->name ?? 'Variant';

                if (! $attributes->has($attrName)) {
                    $attributes[$attrName] = collect();
                }

                // 3. We now know $sv->attributeValue is safe to access
                if (! $attributes[$attrName]->contains('id', $sv->attributeValue->id)) {
                    $attributes[$attrName]->push([
                        'id' => $sv->attributeValue->id,
                        'value' => $sv->attributeValue->value,
                    ]);
                }
            }
        }

        // Build a flat SKU list the frontend can filter against.
        // Each entry: { id, price, mrp, in_stock, values: { [attrName]: attrValueId } }
        // This lets the Alpine selector compute "which options are still valid?"
        // purely client-side by attribute-map equality — Shopify-style.
        $skuList = [];
        foreach ($product->skus as $sku) {
            $values = [];
            foreach ($sku->skuValues as $sv) {
                if (! $sv->attributeValue || ! $sv->attribute) {
                    continue;
                }
                $values[$sv->attribute->name] = $sv->attributeValue->id;
            }

            $skuList[] = [
                'id' => $sku->id,
                'price' => (float) $sku->price,
                'mrp' => $sku->mrp !== null ? (float) $sku->mrp : 0,
                'in_stock' => (bool) $sku->is_in_stock,
                'values' => $values,
            ];
        }

        // Pick the initial SKU — prefer first in-stock, fall back to first.
        $initialSku = collect($skuList)->firstWhere('in_stock', true)
            ?? ($skuList[0] ?? null);
    @endphp

    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-10" x-data="productPage()" x-init="init()">

        <nav
            class="flex text-[12px] sm:text-[13px] text-gray-500 font-medium mb-6 lg:mb-8 whitespace-nowrap overflow-x-auto no-scrollbar">

            @php $breadCat = $product->categories->first(); @endphp
            <a href="{{ url('/' . $company->slug) }}" class="hover:text-brand-500 transition-colors">Home</a>
            <span class="mx-2 sm:mx-3 text-gray-300">/</span>
            @if ($breadCat)
                <a href="{{ route('storefront.category', ['slug' => $company->slug, 'categorySlug' => $breadCat->slug]) }}"
                    class="hover:text-brand-500 transition-colors">
                    {{ $breadCat->name }}
                </a>
                <span class="mx-2 sm:mx-3 text-gray-300">/</span>
            @endif
            <span class="text-gray-900 font-bold truncate">{{ $product->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

            <div class="lg:col-span-6 xl:col-span-5 flex flex-col gap-4">
                <div class="w-full bg-[#f8f9fa] rounded-2xl overflow-hidden border border-gray-100 shadow-sm relative flex items-center justify-center"
                    style="min-height: 400px; aspect-ratio: 1/1;">
                    {{-- <button
                        class="absolute top-4 right-4 w-9 h-9 bg-white/80 backdrop-blur rounded-full flex items-center justify-center text-gray-600 hover:text-gray-900 transition-colors shadow-sm z-10">
                        <i data-lucide="zoom-in" class="w-4 h-4"></i>
                    </button> --}}
                    {{-- Product image — shown when no YouTube active ── --}}
                    <template x-if="!youtubeActive">
                        <img :src="activeImage || '{{ $product->primary_image_url }}'" alt="{{ $product->name }}"
                            class="w-full h-full object-cover mix-blend-multiply transition-all duration-300"
                            style="position: absolute; inset: 0; width: 100%; height: 100%;"
                            onerror="this.src='{{ asset('assets/images/no-product.webp') }}'">
                    </template>

                    {{-- YouTube embed — shown when video thumbnail clicked ── --}}
                    <template x-if="youtubeActive">
                        <div class="absolute inset-0 w-full h-full bg-black">
                            <iframe :src="'https://www.youtube.com/embed/' + youtubeActive + '?autoplay=1'"
                                class="w-full h-full" style="position: absolute; inset: 0; width: 100%; height: 100%;"
                                frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen>
                            </iframe>
                        </div>
                    </template>
                </div>

                <div class="flex gap-3 overflow-x-auto no-scrollbar pb-2 snap-x">

                    {{-- Product images ── --}}
                    @foreach ($images as $index => $media)
                        <button @click="activeImage = '{{ asset('storage/' . $media->media_path) }}'; youtubeActive = null"
                            class="w-[72px] h-[72px] sm:w-[84px] sm:h-[84px] shrink-0 rounded-xl overflow-hidden border-2 transition-all duration-200 snap-center bg-[#f8f9fa]"
                            :class="activeImage === '{{ asset('storage/' . $media->media_path) }}'
                                ?
                                'border-gray-900 scale-[0.98]' :
                                'border-transparent opacity-70 hover:opacity-100'">
                            <img src="{{ asset('storage/' . $media->media_path) }}"
                                class="w-full h-full object-cover mix-blend-multiply" loading="lazy">
                        </button>
                    @endforeach

                    {{-- YouTube videos ── --}}                    
                    @foreach ($youtubeVideos as $video)
                        @php
                            // Updated regex catches standard links, youtu.be, shorts, and embeds
                            preg_match('/(?:v=|youtu\.be\/|shorts\/|embed\/)([a-zA-Z0-9_-]{11})/', $video->media_path, $m);
                            $ytId = $m[1] ?? null;
                        @endphp
                        @if ($ytId)
                            <button @click="playYoutube('{{ $ytId }}')"
                                class="w-[72px] h-[72px] sm:w-[84px] sm:h-[84px] shrink-0 rounded-xl overflow-hidden border-2 transition-all duration-200 snap-center relative bg-black"
                                :class="youtubeActive === '{{ $ytId }}'
                                    ?
                                    'border-gray-900 scale-[0.98] opacity-100' :
                                    'border-transparent opacity-80 hover:opacity-100'">
                                <img src="https://img.youtube.com/vi/{{ $ytId }}/mqdefault.jpg"
                                    class="w-full h-full object-cover opacity-60">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div
                                        class="w-8 h-8 bg-white/90 backdrop-blur rounded-full flex items-center justify-center text-red-600 shadow-md">
                                        <i data-lucide="play" class="w-3.5 h-3.5 fill-current ml-0.5"></i>
                                    </div>
                                </div>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-6 xl:col-span-7 flex flex-col pt-2 lg:pl-4">

                <div class="flex items-center justify-between mb-4">
                    @if ($product->type === 'variable')
                        {{-- Reactive badge for variable products — reflects the currently selected variant's stock. --}}
                        <span x-show="currentInStock" x-cloak
                            class="bg-[#e6fcf5] text-[#108c2a] px-3 py-1.5 rounded-md text-[11px] font-black uppercase tracking-wider">
                            In Stock
                        </span>
                        <span x-show="!currentInStock" x-cloak
                            class="bg-red-50 text-red-600 px-3 py-1.5 rounded-md text-[11px] font-black uppercase tracking-wider">
                            Out of Stock
                        </span>
                    @elseif ($inStock)
                        <span
                            class="bg-[#e6fcf5] text-[#108c2a] px-3 py-1.5 rounded-md text-[11px] font-black uppercase tracking-wider">
                            In Stock
                        </span>
                    @else
                        <span
                            class="bg-red-50 text-red-600 px-3 py-1.5 rounded-md text-[11px] font-black uppercase tracking-wider">
                            Out of Stock
                        </span>
                    @endif
                    <button
                        class="w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors shadow-sm">
                        <i data-lucide="share-2" class="w-4 h-4"></i>
                    </button>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-[34px] leading-[1.2] font-bold text-[#1f2937] mb-5 tracking-tight">
                    {{ $product->name }}
                </h1>

                @if (! $isCatalog && get_setting('enable_product_pricing', 1))
                <div class="mb-8">
                    <div class="flex items-end gap-3 mb-1.5 flex-wrap">
                        <span class="text-3xl sm:text-[40px] leading-none font-bold text-[#3ba2e3]"
                            x-text="'₹' + parseFloat(currentPrice).toLocaleString('en-IN', {minimumFractionDigits: 2})">
                            ₹{{ number_format($minPrice, 2) }}
                        </span>
                        <template x-if="currentMrp > 0 && currentMrp > currentPrice">
                            <span class="text-lg sm:text-xl text-gray-400 font-medium line-through mb-1"
                                x-text="'₹' + parseFloat(currentMrp).toLocaleString('en-IN', {minimumFractionDigits: 2})">
                            </span>
                        </template>
                        <template x-if="currentMrp > 0 && currentMrp > currentPrice">
                            <span class="bg-[#e6fcf5] text-[#108c2a] px-2.5 py-1 rounded text-[12px] font-bold mb-1.5 ml-1"
                                x-text="Math.round(((currentMrp - currentPrice) / currentMrp) * 100) + '% OFF'">
                            </span>
                        </template>
                    </div>
                    <p class="text-[12px] text-gray-500 font-medium">
                        Inclusive of all taxes
                        @if ($product->saleUnit)
                            · Per {{ $product->saleUnit->name }}
                        @endif
                    </p>
                </div>
                @endif

                @if ($product->type === 'variable' && $attributes->isNotEmpty())
                    <div class="space-y-5 mb-8">
                        @foreach ($attributes as $attrName => $values)
                            <div>
                                <h3 class="text-[13px] font-bold text-gray-900 mb-3">
                                    {{ $attrName }}
                                    <span class="text-gray-400 font-medium ml-1"
                                        x-text="selectedAttrs['{{ $attrName }}'] ? ': ' + selectedAttrs['{{ $attrName }}'].value : ''"></span>
                                </h3>
                                <div class="flex flex-wrap gap-2.5">
                                    @foreach ($values as $val)
                                        <button
                                            type="button"
                                            data-attr="{{ $attrName }}"
                                            data-value-id="{{ $val['id'] }}"
                                            data-value-label="{{ $val['value'] }}"
                                            @click="selectAttr('{{ $attrName }}', {{ $val['id'] }}, '{{ addslashes($val['value']) }}')"
                                            :disabled="!isOptionValid('{{ $attrName }}', {{ $val['id'] }})"
                                            :aria-disabled="!isOptionValid('{{ $attrName }}', {{ $val['id'] }})"
                                            class="relative px-5 py-2.5 rounded-xl border-2 text-[13px] font-bold transition-all disabled:cursor-not-allowed"
                                            :class="{
                                                'border-gray-900 bg-gray-900 text-white': selectedAttrs['{{ $attrName }}']?.id === {{ $val['id'] }},
                                                'border-gray-200 bg-white text-gray-700 hover:border-gray-400': isOptionValid('{{ $attrName }}', {{ $val['id'] }}) && selectedAttrs['{{ $attrName }}']?.id !== {{ $val['id'] }} && !isOptionOutOfStock('{{ $attrName }}', {{ $val['id'] }}),
                                                'border-gray-200 bg-gray-50 text-gray-400 line-through': isOptionValid('{{ $attrName }}', {{ $val['id'] }}) && isOptionOutOfStock('{{ $attrName }}', {{ $val['id'] }}) && selectedAttrs['{{ $attrName }}']?.id !== {{ $val['id'] }},
                                                'border-gray-100 bg-gray-50 text-gray-300 line-through opacity-60': !isOptionValid('{{ $attrName }}', {{ $val['id'] }}),
                                            }">
                                            {{ $val['value'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Quantity selector ── --}}
                <div class="flex items-center gap-4 mb-6">
                    <span class="text-[13px] font-bold text-gray-900">Quantity</span>
                    <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                        <button @click="qty = Math.max(1, qty - 1)"
                            class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors">
                            <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>
                        <span class="w-12 text-center font-bold text-[15px]" x-text="qty"></span>
                        <button @click="qty++"
                            class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>
                    @if ($product->quantity_limitation)
                        <span class="text-[11px] text-gray-400">Max {{ $product->quantity_limitation }} per order</span>
                    @endif
                </div>              

                @if ($isCatalog)
                    {{-- ── Catalog: Send Inquiry button ── --}}
                    <div class="flex flex-col gap-3 mt-auto">
                        <button @click="showInquiry = !showInquiry"
                            class="w-full bg-teal-600 hover:bg-teal-700 text-white py-4 rounded-xl text-[15px] font-bold flex items-center justify-center gap-2.5 transition-all shadow-sm">
                            <i data-lucide="message-circle" class="w-5 h-5"></i> Send Inquiry
                        </button>
                    </div>

                    {{-- ── Inquiry Form ── --}}
                    <div x-show="showInquiry" x-cloak x-transition class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <form method="POST" action="{{ route('storefront.inquiry', ['slug' => $company->slug]) }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="product_name" value="{{ $product->name }}">
                            <div class="space-y-3">
                                <input type="text" name="customer_name" placeholder="Your Name *" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:border-teal-500 outline-none">
                                <input type="email" name="customer_email" placeholder="Email Address *" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:border-teal-500 outline-none">
                                <input type="tel" name="customer_phone" placeholder="Phone Number"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:border-teal-500 outline-none">
                                <textarea name="customer_notes" rows="3" placeholder="Your message or inquiry..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:border-teal-500 outline-none resize-none"></textarea>
                                <button type="submit"
                                    class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3 rounded-lg text-sm font-bold transition-colors">
                                    Submit Inquiry
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    {{-- ── Sellable: Cart buttons ── --}}
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-auto">
                        <button type="button" @click="addToCart()"
                            :disabled="!currentInStock"
                            class="flex-1 bg-white border-2 border-[#111827] text-[#111827] hover:bg-gray-50 py-4 rounded-xl text-[15px] font-bold flex items-center justify-center gap-2.5 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white">
                            <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                            <span x-text="currentInStock ? 'Add to Cart' : 'Out of Stock'"></span>
                        </button>
                        <button type="button" @click="buyNow()"
                            :disabled="!currentInStock"
                            class="flex-1 bg-[#111827] hover:bg-black text-white py-4 rounded-xl text-[15px] font-bold flex items-center justify-center gap-2.5 transition-all shadow-xl shadow-gray-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-[#111827]">
                            <i data-lucide="zap" class="w-5 h-5 fill-current"></i>
                            <span x-text="currentInStock ? 'Buy Now' : 'Unavailable'"></span>
                        </button>
                    </div>
                @endif

            </div>
        </div>

        <div class="mt-16 lg:mt-20 pt-10 border-t border-gray-200">
            <div class="max-w-4xl">

                {{-- Description ── --}}
                @if ($product->description)
                    <div class="mb-10">
                        <h2 class="text-[18px] sm:text-[22px] font-bold text-gray-900 mb-5 flex items-center gap-2.5">
                            <i data-lucide="align-left" class="w-5 h-5 text-[#3ba2e3]"></i>
                            Description
                        </h2>
                        <div class="prose prose-sm sm:prose-base text-gray-600 max-w-none leading-relaxed">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </div>
                @endif

                {{-- Product Guide / Plant Education sections ── --}}
                @if ($product->product_guide && count($product->product_guide))

                    @if(has_module('plant_education'))
                        @php
                            $plantCareMap = collect($product->product_guide)->whereIn('title', ['Sunlight', 'Watering'])->keyBy('title');
                            $extraGuides  = collect($product->product_guide)->whereNotIn('title', ['Sunlight', 'Watering'])->values();
                        @endphp

                        {{-- Plant Care Cards: simple open display, no accordion, no speak ── --}}
                        @if($plantCareMap->isNotEmpty())
                            <div class="mb-8">
                                <h2 class="text-[18px] sm:text-[22px] font-bold text-gray-900 mb-4 flex items-center gap-2.5">
                                    <i data-lucide="leaf" class="w-5 h-5 text-green-600"></i>
                                    Plant Education
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @if($plantCareMap->has('Sunlight') && ($plantCareMap->get('Sunlight')['description'] ?? ''))
                                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 sm:p-5">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-xl">☀️</span>
                                                <span class="text-[13px] font-bold text-amber-800">Sunlight</span>
                                            </div>
                                            <p class="text-sm text-gray-700 leading-relaxed">{{ $plantCareMap->get('Sunlight')['description'] }}</p>
                                        </div>
                                    @endif
                                    @if($plantCareMap->has('Watering') && ($plantCareMap->get('Watering')['description'] ?? ''))
                                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 sm:p-5">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-xl">💧</span>
                                                <span class="text-[13px] font-bold text-blue-800">Watering</span>
                                            </div>
                                            <p class="text-sm text-gray-700 leading-relaxed">{{ $plantCareMap->get('Watering')['description'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Additional guide sections in accordion ── --}}
                        @if($extraGuides->isNotEmpty())
                            <div class="mb-10">
                                <h2 class="text-[18px] sm:text-[22px] font-bold text-gray-900 mb-5 flex items-center gap-2.5">
                                    <i data-lucide="book-open" class="w-5 h-5 text-[#3ba2e3]"></i>
                                    Product Guide
                                </h2>
                                <div class="space-y-3 sm:space-y-4">
                                    @foreach ($extraGuides as $guide)
                                        <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm"
                                            x-data="{ open: false }">

                                            <div @click="open = !open"
                                                class="w-full flex items-center justify-between px-4 sm:px-5 py-3.5 sm:py-4 bg-gray-50/50 hover:bg-gray-50 transition-colors cursor-pointer select-none">

                                                <span class="text-[14px] sm:text-[15px] font-bold text-gray-800 pr-4"
                                                    data-guide-title="{{ $loop->index }}">{{ $guide['title'] ?? '' }}</span>

                                                <div class="flex items-center gap-3 sm:gap-4 flex-shrink-0">
                                                    <button type="button"
                                                        @click.stop="toggleSpeak({{ $loop->index }})"
                                                        class="flex items-center gap-1.5 rounded-full sm:rounded-lg px-2.5 py-2.5 sm:px-3 sm:py-1.5 border transition-all duration-200 shadow-sm"
                                                        :class="speakingKey === {{ $loop->index }}
                                                            ? 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100'
                                                            : 'bg-blue-50 text-blue-600 border-blue-200 hover:bg-blue-100'"
                                                        :title="speakingKey === {{ $loop->index }} ? 'Stop Reading' : 'Listen'">
                                                        <i data-lucide="volume-2" class="w-4 h-4"
                                                            x-show="speakingKey !== {{ $loop->index }}"></i>
                                                        <i data-lucide="square" class="w-4 h-4 fill-current"
                                                            x-show="speakingKey === {{ $loop->index }}" style="display: none;"></i>
                                                        <span class="hidden sm:inline text-[11px] font-black uppercase tracking-wider"
                                                              x-text="speakingKey === {{ $loop->index }} ? 'Stop' : 'Listen'"></span>
                                                    </button>
                                                    <div class="w-px h-6 bg-gray-200 hidden sm:block"></div>
                                                    <i data-lucide="chevron-down"
                                                        class="w-5 h-5 text-gray-400 transition-transform duration-300 flex-shrink-0"
                                                        :class="{ 'rotate-180': open }"></i>
                                                </div>
                                            </div>

                                            <div x-show="open" x-transition
                                                class="px-4 sm:px-5 py-4 text-[13.5px] sm:text-[14px] text-gray-600 leading-relaxed border-t border-gray-100 bg-white">
                                                {{ $guide['description'] ?? $guide['desc'] ?? '' }}
                                            </div>

                                            <div class="absolute opacity-0 pointer-events-none w-0 h-0 overflow-hidden" data-guide-index="{{ $loop->index }}">
                                                {{ $guide['description'] ?? $guide['desc'] ?? '' }}
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    @else

                        {{-- Standard accordion display for non-plant tenants ── --}}
                        <div class="mb-10">
                            <h2 class="text-[18px] sm:text-[22px] font-bold text-gray-900 mb-5 flex items-center gap-2.5">
                                <i data-lucide="book-open" class="w-5 h-5 text-[#3ba2e3]"></i>
                                Product Guide
                            </h2>
                            <div class="space-y-3 sm:space-y-4">
                                @foreach ($product->product_guide as $guide)
                                    <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm"
                                        x-data="{ open: false }">

                                        <div @click="open = !open"
                                            class="w-full flex items-center justify-between px-4 sm:px-5 py-3.5 sm:py-4 bg-gray-50/50 hover:bg-gray-50 transition-colors cursor-pointer select-none">

                                            <span class="text-[14px] sm:text-[15px] font-bold text-gray-800 pr-4"
                                                data-guide-title="{{ $loop->index }}">{{ $guide['title'] ?? '' }}</span>

                                            <div class="flex items-center gap-3 sm:gap-4 flex-shrink-0">
                                                <button type="button"
                                                    @click.stop="toggleSpeak({{ $loop->index }})"
                                                    class="flex items-center gap-1.5 rounded-full sm:rounded-lg px-2.5 py-2.5 sm:px-3 sm:py-1.5 border transition-all duration-200 shadow-sm"
                                                    :class="speakingKey === {{ $loop->index }}
                                                        ? 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100'
                                                        : 'bg-blue-50 text-blue-600 border-blue-200 hover:bg-blue-100'"
                                                    :title="speakingKey === {{ $loop->index }} ? 'Stop Reading' : 'Listen'">
                                                    <i data-lucide="volume-2" class="w-4 h-4"
                                                        x-show="speakingKey !== {{ $loop->index }}"></i>
                                                    <i data-lucide="square" class="w-4 h-4 fill-current"
                                                        x-show="speakingKey === {{ $loop->index }}" style="display: none;"></i>
                                                    <span class="hidden sm:inline text-[11px] font-black uppercase tracking-wider"
                                                          x-text="speakingKey === {{ $loop->index }} ? 'Stop' : 'Listen'"></span>
                                                </button>
                                                <div class="w-px h-6 bg-gray-200 hidden sm:block"></div>
                                                <i data-lucide="chevron-down"
                                                    class="w-5 h-5 text-gray-400 transition-transform duration-300 flex-shrink-0"
                                                    :class="{ 'rotate-180': open }"></i>
                                            </div>
                                        </div>

                                        <div x-show="open" x-transition
                                            class="px-4 sm:px-5 py-4 text-[13.5px] sm:text-[14px] text-gray-600 leading-relaxed border-t border-gray-100 bg-white">
                                            {{ $guide['description'] ?? $guide['desc'] ?? '' }}
                                        </div>

                                        <div class="absolute opacity-0 pointer-events-none w-0 h-0 overflow-hidden" data-guide-index="{{ $loop->index }}">
                                            {{ $guide['description'] ?? $guide['desc'] ?? '' }}
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        </div>

                    @endif

                @endif

                {{-- Related Products ── --}}
                @if ($related->isNotEmpty())
                    <div class="mt-10 pt-10 border-t border-gray-200">
                        <h2 class="text-[18px] sm:text-[22px] font-bold text-gray-900 mb-6 flex items-center gap-2.5">
                            <i data-lucide="sparkles" class="w-5 h-5 text-[#3ba2e3]"></i>
                            Related Products
                        </h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach ($related as $rel)
                                @php
                                    $relSku = $rel->skus->first();
                                    $relIsCatalog = $rel->product_type === 'catalog';
                                    $relShowPrice = ! $relIsCatalog && get_setting('enable_product_pricing', 1);
                                    $relPrice = $relSku?->price ?? 0;
                                @endphp
                                <a href="{{ route('storefront.product', ['slug' => $company->slug, 'productSlug' => $rel->slug]) }}"
                                    class="group block bg-white border border-gray-100 rounded-xl overflow-hidden hover:border-gray-200 hover:shadow-md transition-all">
                                    <div class="aspect-square bg-gray-50 overflow-hidden">
                                        <img src="{{ $rel->primary_image_url }}" alt="{{ $rel->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            loading="lazy"
                                            onerror="this.src='{{ asset('assets/images/no-product.webp') }}'">
                                    </div>
                                    <div class="p-3">
                                        <p
                                            class="text-[13px] font-semibold text-gray-800 line-clamp-2 group-hover:text-brand-600 transition-colors mb-1">
                                            {{ $rel->name }}
                                        </p>
                                        @if ($relShowPrice)
                                            <p class="text-[14px] font-bold text-gray-900">₹{{ number_format($relPrice, 2) }}</p>
                                        @elseif ($relIsCatalog)
                                            <p class="text-[12px] font-semibold text-brand-600">View Details</p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>

    {{-- ── Google Translate floating button ── --}}
    <div id="google_translate_element" style="display:none"></div>

    <div class="fixed bottom-24 right-6 z-50" x-data="{ open: false }">
       <button @click="open = !open"
            class="w-12 h-12 rounded-full bg-white border border-gray-200 shadow-lg flex items-center justify-center text-gray-600 hover:shadow-xl transition-all"
            title="Translate page">

            <img src="{{ asset('assets/icons/translate.svg') }}" 
                alt="Translate"
                >
        </button>

        {{-- Language picker ── --}}
        <div x-show="open" x-cloak @click.away="open = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute bottom-14 right-0 w-44 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-1.5">

            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-3 py-1.5">
                Select Language
            </p>

            @php
                $languages = [
                    'en'    => '🇬🇧 English',
                    'hi'    => '🇮🇳 Hindi',
                    'gu'    => '🇮🇳 Gujarati',
                    'mr'    => '🇮🇳 Marathi',
                    'ta'    => '🇮🇳 Tamil',
                    'te'    => '🇮🇳 Telugu',
                    'bn'    => '🇮🇳 Bengali',
                    'kn'    => '🇮🇳 Kannada',
                    'pa'    => '🇮🇳 Punjabi',
                    'ar'    => '🇸🇦 Arabic',
                    'zh-CN' => '🇨🇳 Chinese',
                ];
            @endphp

           @foreach($languages as $code => $label)
                <button
                    @click="translatePage('{{ $code }}'); open = false"
                    class="w-full text-left px-3 py-2 text-[13px] font-medium text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>
@endsection
@push('scripts')
    {{-- Google Translate ── --}}
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script>
        // ── Preload voices — must happen early, async on some browsers ──
        let availableVoices = [];

        function loadVoices() {
            availableVoices = window.speechSynthesis.getVoices();
            console.log('[Speak] Voices loaded:', availableVoices.length);
        }

        loadVoices();
        if (window.speechSynthesis.onvoiceschanged !== undefined) {
            window.speechSynthesis.onvoiceschanged = loadVoices;
        }
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                autoDisplay:  false,
            }, 'google_translate_element');

            // Remove the injected banner after translate loads
            const killBanner = setInterval(() => {
                const banner = document.querySelector('.goog-te-banner-frame');
                if (banner) {
                    banner.remove();
                    document.body.style.top = '0';
                    console.log('[Translate] Banner removed');
                }
                const skip = document.querySelector('.skiptranslate');
                if (skip) skip.style.display = 'none';
            }, 500);

            // Stop checking after 5 seconds
            setTimeout(() => clearInterval(killBanner), 5000);
        }

        function translatePage(langCode) {
            // Find the Google Translate select element and change it
            const select = document.querySelector('.goog-te-combo');
            if (select) {
                select.value = langCode;
                select.dispatchEvent(new Event('change'));
                console.log('[Translate] Language changed to:', langCode);
            } else {
                console.warn('[Translate] Google Translate not ready yet');
                // Retry once after short delay
                setTimeout(() => translatePage(langCode), 800);
            }
        }
    </script>
    <script>
        function productPage() {
            return {
                // ── Image state ──
                activeImage: '{{ $product->primary_image_url }}',
                youtubeActive: null,
                // ── Speak state ──
                speakingKey: null,

                // ── Inquiry state ──
                showInquiry: false,

                // ── Variant state ──
                // selectedAttrs[attrName] = { id: attrValueId, value: 'Red' }
                selectedAttrs: {},
                currentPrice: {{ $initialSku['price'] ?? $minPrice }},
                currentMrp: {{ $initialSku['mrp'] ?? ($firstSku?->mrp ?? 0) }},
                currentSkuId: {{ $initialSku['id'] ?? ($firstSku?->id ?? 'null') }},
                currentInStock: {{ isset($initialSku) ? ($initialSku['in_stock'] ? 'true' : 'false') : ($inStock ? 'true' : 'false') }},
                qty: 1,

                // ── Full SKU list from server (see @php block at top) ──
                // Shape: [{ id, price, mrp, in_stock, values: { attrName: attrValueId } }, ...]
                skus: @json($skuList),

                // Attribute display order — we always want the same order in the combinator
                // regardless of JS object key insertion quirks.
                attrOrder: @json($attributes->keys()->values()),

                init() {
                    // Seed selectedAttrs from the initial SKU (first in-stock, else first).
                    // Falls back to per-attribute first value if the product has no SKU
                    // linkage at all (legacy/edge data).
                    const initial = @json($initialSku);

                    if (initial && initial.values) {
                        for (const [attrName, attrValueId] of Object.entries(initial.values)) {
                            const display = this.lookupValueName(attrName, attrValueId);
                            this.selectedAttrs[attrName] = { id: attrValueId, value: display };
                        }
                    } else {
                        @foreach ($attributes as $attrName => $values)
                            @if ($values->isNotEmpty())
                                this.selectedAttrs['{{ $attrName }}'] = {
                                    id: {{ $values->first()['id'] }},
                                    value: '{{ addslashes($values->first()['value']) }}',
                                };
                            @endif
                        @endforeach
                    }

                    this.updateMatch();

                    console.log('[Product] Initialized', {
                        product: '{{ $product->slug }}',
                        type: '{{ $product->type }}',
                        skus: this.skus.length,
                        initial: this.currentSkuId,
                        inStock: this.currentInStock,
                    });
                },

                /**
                 * Resolve a human-readable value name for a given (attr, valueId) pair.
                 * Walks the SKU list because that's the source of truth we have on the client.
                 */
                lookupValueName(attrName, attrValueId) {
                    for (const sku of this.skus) {
                        if (sku.values[attrName] === attrValueId) {
                            // We don't ship the value labels in the JSON payload (keeps it slim);
                            // instead we read the label from the DOM button on first render.
                            const btn = document.querySelector(
                                `[data-attr="${attrName}"][data-value-id="${attrValueId}"]`
                            );
                            if (btn) return btn.dataset.valueLabel || btn.innerText.trim();
                        }
                    }
                    return '';
                },

                /**
                 * Handle a click on an attribute option.
                 * — Ignore clicks on options that aren't a valid combination with the current state.
                 * — After setting the clicked attr, auto-heal OTHER attrs if needed so the
                 *   final combination resolves to a real SKU (prefer in-stock).
                 */
                selectAttr(attrName, id, value) {
                    if (!this.isOptionValid(attrName, id)) {
                        console.warn('[Product] Ignored click on invalid option:', attrName, id);
                        return;
                    }

                    this.selectedAttrs[attrName] = { id, value };

                    // If the new combination doesn't fully match a SKU, fix up the other attrs.
                    if (!this.findMatchingSku(this.selectedAttrs)) {
                        this.healOtherAttrs(attrName);
                    }

                    this.updateMatch();
                    console.log('[Product] Attr selected:', attrName, value, '→ sku:', this.currentSkuId);
                },

                /**
                 * Recompute the matched SKU (price/mrp/id/stock) from selectedAttrs.
                 * If nothing matches (shouldn't happen after heal), fall back gracefully.
                 */
                updateMatch() {
                    const match = this.findMatchingSku(this.selectedAttrs);

                    if (match) {
                        this.currentPrice = match.price;
                        this.currentMrp = match.mrp;
                        this.currentSkuId = match.id;
                        this.currentInStock = !!match.in_stock;
                        return;
                    }

                    // No full match — keep previous price but mark out of stock to be safe.
                    // Disables cart actions so we never sell a non-existent variant.
                    console.warn('[Product] No SKU matched selectedAttrs — buttons disabled.', this.selectedAttrs);
                    this.currentSkuId = null;
                    this.currentInStock = false;
                },

                /**
                 * Find a SKU whose `values` map equals the given attribute selection.
                 * All selected attrs must match; unmatched attrs mean no full match.
                 */
                findMatchingSku(attrs) {
                    const names = this.attrOrder.length ? this.attrOrder : Object.keys(attrs);
                    const preferInStock = this.skus.filter(s => s.in_stock);
                    const pools = [preferInStock, this.skus]; // prefer in-stock, fall back to any

                    for (const pool of pools) {
                        for (const sku of pool) {
                            let ok = true;
                            for (const n of names) {
                                if (!(n in attrs)) { ok = false; break; }
                                if (sku.values[n] !== attrs[n].id) { ok = false; break; }
                            }
                            if (ok) return sku;
                        }
                    }
                    return null;
                },

                /**
                 * After the user picks (attrName → id), adjust OTHER attribute selections
                 * so the combination resolves to a real SKU. Prefers in-stock matches.
                 */
                healOtherAttrs(lockedAttr) {
                    // Candidate SKUs that satisfy the locked attribute.
                    const lockedId = this.selectedAttrs[lockedAttr]?.id;
                    const candidates = this.skus.filter(s => s.values[lockedAttr] === lockedId);
                    if (candidates.length === 0) return;

                    // Sort: in-stock first.
                    candidates.sort((a, b) => (b.in_stock === true) - (a.in_stock === true));

                    // Pick the candidate whose values differ LEAST from current selection.
                    const current = this.selectedAttrs;
                    let best = candidates[0];
                    let bestDiff = Infinity;
                    for (const sku of candidates) {
                        let diff = 0;
                        for (const n of this.attrOrder) {
                            if (n === lockedAttr) continue;
                            if (current[n] && sku.values[n] !== current[n].id) diff++;
                        }
                        if (diff < bestDiff) {
                            best = sku;
                            bestDiff = diff;
                            if (diff === 0) break;
                        }
                    }

                    for (const [n, vid] of Object.entries(best.values)) {
                        if (n === lockedAttr) continue;
                        const label = this.lookupValueName(n, vid);
                        this.selectedAttrs[n] = { id: vid, value: label };
                    }
                },

                /**
                 * Partial-match validity check.
                 *
                 * An option (attrName=valueId) is VALID if it appears in at least one SKU
                 * in the product's SKU list.  We intentionally do NOT filter by the other
                 * currently-selected attributes here because that causes "diagonal" combos
                 * (e.g. Small+Plastic / Large+Ceramic) to disable perfectly reachable
                 * options.  `healOtherAttrs()` already reconciles the other attrs after
                 * the user makes a selection, so the only thing we need to confirm at
                 * click-guard time is that the value actually exists somewhere.
                 *
                 * An option is DISABLED (returns false) only when it does not exist in
                 * any SKU at all — i.e. it was probably removed from the catalogue after
                 * the page was last rebuilt.
                 */
                isOptionValid(attrName, valueId) {
                    return this.skus.some(sku => sku.values[attrName] === valueId);
                },

                /**
                 * True when the option exists but every SKU that has it is out of stock.
                 * Used for the "line-through" / strikethrough visual — the button remains
                 * clickable (healOtherAttrs will still resolve to the best available match)
                 * but the user can see upfront that stock is limited.
                 *
                 * We also narrow by the OTHER currently-selected attrs so the OOS indicator
                 * reflects the actual combination the user is building towards, not the
                 * aggregate across all size/color combinations in the catalogue.
                 * If no narrowed match exists we fall back to the full-SKU aggregate.
                 */
                isOptionOutOfStock(attrName, valueId) {
                    // Narrow: SKUs with this value that also satisfy other selected attrs
                    const others = Object.entries(this.selectedAttrs).filter(([n]) => n !== attrName);
                    let pool = this.skus.filter(sku => {
                        if (sku.values[attrName] !== valueId) return false;
                        return others.length === 0
                            || others.some(([n, sel]) => sku.values[n] === sel.id);
                    });

                    // Fallback: no narrowed match → check across all SKUs with this value
                    if (!pool.length) {
                        pool = this.skus.filter(sku => sku.values[attrName] === valueId);
                    }

                    return pool.length > 0 && pool.every(sku => !sku.in_stock);
                },

                playYoutube(ytId) {
                    this.youtubeActive = ytId;
                    // Don't null activeImage — keep it for when video is closed
                    console.log('[Product] YouTube playing:', ytId);
                },

                addToCart() {
                    if (!this.currentSkuId) {
                        BizAlert?.toast('Please select a variant', 'error') || alert('Please select a variant');
                        return;
                    }
                    if (!this.currentInStock) {
                        BizAlert?.toast('This variant is out of stock', 'error') || alert('This variant is out of stock');
                        return;
                    }

                    // Build variant label from selected attrs
                    const variantLabel = Object.values(this.selectedAttrs)
                        .map(a => a.value).join(' / ');

                    window.addToCart(
                        {{ $product->id }},
                        this.currentSkuId,
                        '{{ addslashes($product->name) }}',
                        variantLabel,
                        this.currentPrice,
                        '{{ $firstImg ? asset('storage/' . $firstImg) : asset('assets/images/no-product.webp') }}',
                        this.qty,
                    );

                    console.log('[Product] Added to cart:', this.currentSkuId, 'qty:', this.qty);
                    // ── Toast notification ──
                    if (window.__alpineCart) {
                        window.__alpineCart.showToast('{{ addslashes($product->name) }}');
                    }
                },
                buyNow() {
                    if (!this.currentSkuId) {
                        alert('Please select a variant');
                        return;
                    }
                    if (!this.currentInStock) {
                        alert('This variant is out of stock');
                        return;
                    }

                    const variantLabel = Object.values(this.selectedAttrs)
                        .map(a => a.value).join(' / ');

                    // ── Clear cart and add only this item ──
                    window.clearCart();
                    window.addToCart(
                        {{ $product->id }},
                        this.currentSkuId,
                        '{{ addslashes($product->name) }}',
                        variantLabel,
                        this.currentPrice,
                        '{{ $firstImg ? asset('storage/' . $firstImg) : asset('assets/images/no-product.webp') }}',
                        1, // always 1 for buy now
                    );

                    // ── Open drawer directly on checkout view ──
                    if (window.__alpineCart) {
                        window.__alpineCart.cartView  = 'checkout';
                        window.__alpineCart.cartOpen  = true;
                        window.__alpineCart.syncFromStorage();
                    }

                    console.log('[Product] Buy Now:', this.currentSkuId);
                },

                toggleSpeak(index) {
                        // If already speaking this section — stop
                        if (this.speakingKey === index) {
                            window.speechSynthesis.cancel();
                            this.speakingKey = null;
                            return;
                        }

                        window.speechSynthesis.cancel();

                        // ── Read TRANSLATED text from DOM ──
                        // Google Translate already mutated these DOM nodes
                        const titleEl = document.querySelector(`[data-guide-title="${index}"]`);
                        const descEl  = document.querySelector(`[data-guide-index="${index}"]`);

                        const title = titleEl?.innerText?.trim() ?? '';
                        const desc  = descEl?.innerText?.trim() ?? '';
                        const text  = title + '. ' + desc;

                        if (!text.trim()) {
                            console.warn('[Speak] No text found for index:', index);
                            return;
                        }

                        const lang     = this.getSpeechLang();
                        const utterance = new SpeechSynthesisUtterance(text);
                        utterance.lang  = lang;
                        utterance.rate  = 0.92;
                        utterance.pitch = 1;

                        // ── Find matching voice (same as old PHP approach) ──
                        // Exact match first, then partial (e.g. 'gu' matches 'gu-IN')
                        const matchingVoice = availableVoices.find(v => v.lang === lang)
                            ?? availableVoices.find(v => v.lang.startsWith(lang.split('-')[0]));

                        if (!matchingVoice) {
                            // No voice installed for this language — warn and abort
                            // Same behavior as old PHP: don't speak gibberish
                            const langName = {
                                'hi-IN': 'Hindi', 'gu-IN': 'Gujarati', 'mr-IN': 'Marathi',
                                'ta-IN': 'Tamil', 'te-IN': 'Telugu', 'bn-IN': 'Bengali',
                                'kn-IN': 'Kannada', 'pa-IN': 'Punjabi',
                            }[lang] ?? lang;

                            this.speakingKey = null;
                            window.speechSynthesis.cancel();

                            alert(`Voice for ${langName} is not installed on this browser/device.\n\nOn Android Chrome: Settings → Accessibility → Text-to-Speech → Install "${langName}" voice.`);
                            console.warn('[Speak] No voice found for:', lang);
                            return;
                        }

                        utterance.voice = matchingVoice;
                        console.log('[Speak] Voice matched:', matchingVoice.name, '| lang:', lang);

                        // Set SYNCHRONOUSLY — Alpine re-renders immediately
                        this.speakingKey = index;

                        // ── Only clear speakingKey if THIS utterance is still the active one ──
                        // Prevents cancel() from triggering onend of OLD utterance
                        // which would overwrite the newly set speakingKey
                        utterance.onend = () => {
                            if (this.speakingKey === index) {
                                this.speakingKey = null;
                            }
                            console.log('[Speak] Finished index:', index, '| active was:', this.speakingKey);
                        };

                        utterance.onerror = (e) => {
                            if (e.error === 'interrupted') return;
                            if (this.speakingKey === index) {
                                this.speakingKey = null;
                            }
                            console.error('[Speak] Error:', e);
                        };

                        window.speechSynthesis.speak(utterance);
                        console.log('[Speak] Speaking:', text.slice(0, 60), '| voice:', matchingVoice.name);
                    },
                    getSpeechLang() {
                        // Read Google Translate cookie — format: /en/hi or /auto/gu
                        const cookie = document.cookie
                            .split('; ')
                            .find(row => row.startsWith('googtrans='));

                        if (!cookie) return 'en-IN'; // default

                        const langCode = cookie.split('=')[1]?.split('/')[2] ?? 'en';

                        // Map Google Translate codes → BCP-47 speech codes
                        const langMap = {
                            'en':    'en-IN',
                            'hi':    'hi-IN',
                            'gu':    'gu-IN',
                            'mr':    'mr-IN',
                            'ta':    'ta-IN',
                            'te':    'te-IN',
                            'bn':    'bn-IN',
                            'kn':    'kn-IN',
                            'pa':    'pa-IN',
                            'ar':    'ar-SA',
                            'zh-CN': 'zh-CN',
                            'fr':    'fr-FR',
                            'de':    'de-DE',
                            'es':    'es-ES',
                        };

                        const resolved = langMap[langCode] ?? 'en-IN';
                        console.log('[Speak] Language resolved:', langCode, '→', resolved);
                        return resolved;
                    },
            }
        }
    </script>
@endpush