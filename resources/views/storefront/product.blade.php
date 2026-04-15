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
                $attrName = $sv->attribute->name ?? 'Variant';
                if (!$attributes->has($attrName)) {
                    $attributes[$attrName] = collect();
                }
                if (!$attributes[$attrName]->contains('id', $sv->attributeValue->id)) {
                    $attributes[$attrName]->push([
                        'id' => $sv->attributeValue->id,
                        'value' => $sv->attributeValue->value,
                    ]);
                }
            }
        }

        // Build SKU map for JS price switching
        // skuMap[attrValueId_attrValueId] = { price, cost, sku_id, in_stock }
        $skuMap = [];
        foreach ($product->skus as $sku) {
            $key = $sku->skuValues->pluck('attribute_value_id')->sort()->implode('_');
            $skuMap[$key ?: $sku->id] = [
                'id' => $sku->id,
                'price' => $sku->price,
                'mrp' => $sku->mrp,
                'in_stock' => $sku->stocks->sum('qty') > 0,
            ];
        }
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
                        <img :src="activeImage || '{{ asset('assets/images/no-product.png') }}'" alt="{{ $product->name }}"
                            class="w-full h-full object-cover mix-blend-multiply transition-all duration-300"
                            style="position: absolute; inset: 0; width: 100%; height: 100%;"
                            onerror="this.src='{{ asset('assets/images/no-product.png') }}'">
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
                    @if ($inStock)
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
                                            @click="selectAttr('{{ $attrName }}', {{ $val['id'] }}, '{{ addslashes($val['value']) }}')"
                                            class="px-5 py-2.5 rounded-xl border-2 text-[13px] font-bold transition-all"
                                            :class="selectedAttrs['{{ $attrName }}']?.id === {{ $val['id'] }} ?
                                                'border-gray-900 bg-gray-900 text-white' :
                                                'border-gray-200 bg-white text-gray-700 hover:border-gray-400'">
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
                        <button @click="addToCart()"
                            class="flex-1 bg-white border-2 border-[#111827] text-[#111827] hover:bg-gray-50 py-4 rounded-xl text-[15px] font-bold flex items-center justify-center gap-2.5 transition-all shadow-sm">
                            <i data-lucide="shopping-cart" class="w-5 h-5"></i> Add to Cart
                        </button>
                        <button @click="buyNow()"
                            class="flex-1 bg-[#111827] hover:bg-black text-white py-4 rounded-xl text-[15px] font-bold flex items-center justify-center gap-2.5 transition-all shadow-xl shadow-gray-300">
                            <i data-lucide="zap" class="w-5 h-5 fill-current"></i> Buy Now
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
                                            onerror="this.src='{{ asset('assets/images/no-product.png') }}'">
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
                activeImage: '{{ $firstImg ? asset('storage/' . $firstImg) : asset('assets/images/no-product.png') }}',
                youtubeActive: null,
                // ── Speak state ──
                speakingKey: null,

                // ── Inquiry state ──
                showInquiry: false,

                // ── SKU state ──
                selectedAttrs: {},
                currentPrice: {{ $minPrice }},
                currentMrp: {{ $firstSku?->mrp ?? 0 }},
                currentSkuId: {{ $firstSku?->id ?? 'null' }},
                qty: 1,

                // ── SKU map from PHP ──
                skuMap: @json($skuMap),

                init() {
                    // Pre-select first value per attribute group
                    @foreach ($attributes as $attrName => $values)
                        @if ($values->isNotEmpty())
                            this.selectAttr(
                                '{{ $attrName }}',
                                {{ $values->first()['id'] }},
                                '{{ addslashes($values->first()['value']) }}'
                            );
                        @endif
                    @endforeach

                    console.log('[Product] Initialized', {
                        product: '{{ $product->slug }}',
                        skus: {{ $product->skus->count() }},
                        type: '{{ $product->type }}',
                        skuMap: this.skuMap,
                    });
                },

                selectAttr(attrName, id, value) {
                    this.selectedAttrs[attrName] = {
                        id,
                        value
                    };
                    this.updatePrice();
                    console.log('[Product] Attr selected:', attrName, value);
                },

                updatePrice() {
                    // Build lookup key from selected attribute value IDs sorted
                    const ids = Object.values(this.selectedAttrs)
                        .map(a => a.id)
                        .sort((a, b) => a - b)
                        .join('_');

                    const sku = this.skuMap[ids];
                    if (sku) {
                        this.currentPrice = sku.price;
                        this.currentMrp = sku.mrp;
                        this.currentSkuId = sku.id;
                        console.log('[Product] SKU matched:', ids, sku);
                    }
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

                    // Build variant label from selected attrs
                    const variantLabel = Object.values(this.selectedAttrs)
                        .map(a => a.value).join(' / ');

                    window.addToCart(
                        {{ $product->id }},
                        this.currentSkuId,
                        '{{ addslashes($product->name) }}',
                        variantLabel,
                        this.currentPrice,
                        '{{ $firstImg ? asset('storage/' . $firstImg) : asset('assets/images/no-product.png') }}',
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
                        '{{ $firstImg ? asset('storage/' . $firstImg) : asset('assets/images/no-product.png') }}',
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