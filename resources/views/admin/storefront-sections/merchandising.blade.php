@extends('layouts.admin')

@section('title', 'Merchandising — Product & Category Manager')

@section('header-title')
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Merchandising</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">Control what products appear in each category</p>
    </div>
@endsection

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* ════════════════════════════════════════
                       LAYOUT — Fixed two-panel, full height
                    ════════════════════════════════════════ */
        .merch-wrap {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            height: auto;
        }
        
        @media (min-width: 768px) {
            .merch-wrap {
                grid-template-columns: 280px 1fr;
                height: calc(100vh - 140px);
                min-height: 500px;
            }
        }

        /* ── Panels ── */
        .panel {
            background: #fff;
            border: 1.5px solid #f1f5f9;
            border-radius: 18px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 400px;
            max-height: 550px;
        }
        
        @media (min-width: 768px) {
            .panel {
                min-height: auto;
                max-height: none;
            }
        }

        .panel-header {
            padding: 16px 18px;
            border-bottom: 1.5px solid #f3f4f6;
            background: #fafafa;
            flex-shrink: 0;
        }

        .panel-body {
            flex: 1;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .panel-body::-webkit-scrollbar {
            width: 3px;
        }

        .panel-body::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 4px;
        }

        /* ════════════════════════════════════════
                       LEFT PANEL — Category List
                    ════════════════════════════════════════ */
        .cat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            cursor: pointer;
            border-left: 3px solid transparent;
            transition: background 140ms ease, border-color 140ms ease;
            text-decoration: none;
        }

        .cat-item:hover {
            background: #f0fdf4;
            border-left-color: var(--brand-600);
        }

        .cat-item.active {
            background: color-mix(in srgb, var(--brand-600) 8%, white);
            border-left-color: var(--brand-600);
        }

        .cat-item.active .cat-name {
            color: var(--brand-600);
            font-weight: 700;
        }

        .cat-thumb {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            object-fit: cover;
            background: #f1f5f9;
            flex-shrink: 0;
        }

        .cat-thumb-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cbd5e1;
            flex-shrink: 0;
        }

        .cat-name {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cat-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            background: #f3f4f6;
            color: #6b7280;
            flex-shrink: 0;
        }

        .cat-item.active .cat-badge {
            background: color-mix(in srgb, var(--brand-600) 15%, white);
            color: var(--brand-600);
        }

        /* ════════════════════════════════════════
                       RIGHT PANEL — Product List
                    ════════════════════════════════════════ */
        .product-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 18px;
            border-bottom: 1px solid #f8fafc;
            transition: background 120ms ease;
            position: relative;
        }

        .product-row:hover {
            background: #fafafa;
        }

        .product-row.is-dragging {
            background: color-mix(in srgb, var(--brand-600) 5%, white);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            z-index: 10;
        }

        .sortable-ghost-row {
            opacity: 0.3;
            background: color-mix(in srgb, var(--brand-600) 8%, white) !important;
        }

        /* Drag handle */
        .drag-handle {
            color: #d1d5db;
            cursor: grab;
            flex-shrink: 0;
            padding: 4px;
            border-radius: 6px;
            transition: color 120ms ease;
        }

        .drag-handle:hover {
            color: var(--brand-600);
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        /* Product thumbnail */
        .prod-thumb {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            object-fit: cover;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            flex-shrink: 0;
        }

        /* Product info */
        .prod-name {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
        }

        .prod-meta {
            font-size: 11px;
            color: #9ca3af;
            font-weight: 500;
            margin-top: 1px;
        }

        /* Action buttons */
        .row-action {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: background 120ms ease, color 120ms ease, transform 80ms ease;
            flex-shrink: 0;
        }

        .row-action:active {
            transform: scale(0.88);
        }

        .btn-star {
            background: #fefce8;
            color: #d97706;
        }

        .btn-star:hover {
            background: #fef3c7;
        }

        .btn-star.featured {
            background: #fef3c7;
            color: #b45309;
        }

        .btn-eye {
            background: #f0fdf4;
            color: #22c55e;
        }

        .btn-eye:hover {
            background: #dcfce7;
        }

        .btn-eye.hidden-in-cat {
            background: #f3f4f6;
            color: #d1d5db;
        }

        .btn-remove {
            background: #fff1f2;
            color: #f43f5e;
        }

        .btn-remove:hover {
            background: #ffe4e6;
            color: #e11d48;
        }

        /* Badges on product row */
        .featured-badge {
            font-size: 9px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 4px;
            background: #fef3c7;
            color: #b45309;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .hidden-badge {
            font-size: 9px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 4px;
            background: #f3f4f6;
            color: #9ca3af;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .no-storefront-badge {
            font-size: 9px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 4px;
            background: #fef2f2;
            color: #f87171;
        }

        /* ════════════════════════════════════════
                       SEARCH DROPDOWN
                    ════════════════════════════════════════ */
        .search-wrap {
            position: relative;
        }

        .search-input {
            width: 100%;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 9px 14px 9px 36px;
            font-size: 13px;
            outline: none;
            font-family: inherit;
            transition: border-color 150ms ease;
            background: #fff;
        }

        .search-input:focus {
            border-color: var(--brand-600);
        }

        .search-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }

        .search-results {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            z-index: 50;
            max-height: 320px;
            overflow-y: auto;
        }

        .search-result-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f8fafc;
            transition: background 120ms ease;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background: #f0fdf4;
        }

        .search-result-item.adding {
            opacity: 0.6;
            pointer-events: none;
        }

        /* ════════════════════════════════════════
                       EMPTY + LOADING STATES
                    ════════════════════════════════════════ */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 200px;
            color: #9ca3af;
            text-align: center;
            padding: 32px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            width: 24px;
            height: 24px;
            border: 2px solid #e5e7eb;
            border-top-color: var(--brand-600);
            border-radius: 50%;
            animation: spin 600ms linear infinite;
        }

        /* ════════════════════════════════════════
                       SEARCH IN LEFT PANEL
                    ════════════════════════════════════════ */
        .cat-search {
            width: 100%;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            padding: 7px 10px 7px 32px;
            font-size: 12px;
            outline: none;
            font-family: inherit;
            background: #fff;
            transition: border-color 150ms ease;
        }

        .cat-search:focus {
            border-color: var(--brand-600);
        }

        /* ════════════════════════════════════════
                       SORT ORDER CHIP
                    ════════════════════════════════════════ */
        .order-chip {
            font-size: 10px;
            font-weight: 800;
            color: #9ca3af;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 6px;
            padding: 2px 6px;
            min-width: 24px;
            text-align: center;
            flex-shrink: 0;
        }
    </style>
@endpush

@section('content')
    <div class="pb-4" x-data="merchandising()">

        {{-- ── Page Header ── --}}
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Merchandising</h1>
                <p class="text-sm text-gray-400 font-medium mt-0.5">
                    Assign products to categories · Set display order · Pin featured items
                </p>
            </div>
            <div
                class="flex items-center gap-2 text-xs text-gray-400 font-medium bg-white border border-gray-100 rounded-xl px-4 py-2.5">
                <i data-lucide="info" class="w-3.5 h-3.5"></i>
                Changes save automatically
            </div>
        </div>

        {{-- ── Two Panel Layout ── --}}
        <div class="merch-wrap">

            {{-- ════════════════════════════
             LEFT PANEL — Categories
        ════════════════════════════ --}}
            <div class="panel">
                <div class="panel-header">
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">
                        Categories <span class="text-gray-300">({{ $categories->count() }})</span>
                    </p>
                    {{-- Category search ── --}}
                    <div class="relative">
                        <i data-lucide="search"
                            class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        <input type="text" x-model="catSearch" placeholder="Search categories..." class="cat-search">
                    </div>
                </div>

                <div class="panel-body">
                    @forelse($categories as $cat)
                        <button type="button"
                            class="cat-item w-full text-left {{ $selectedCategory?->id === $cat->id ? 'active' : '' }}"
                            x-show="!catSearch || '{{ strtolower($cat->name) }}'.includes(catSearch.toLowerCase())"
                            @click="selectCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                            :class="{ 'active': activeCategoryId === {{ $cat->id }} }"
                            id="cat-item-{{ $cat->id }}">

                            {{-- Category image ── --}}
                            @if ($cat->image)
                                <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}" class="cat-thumb"
                                    onerror="this.style.display='none'">
                            @else
                                {{-- <div class="cat-thumb-placeholder">
                                    <i data-lucide="folder" class="w-4 h-4"></i>
                                </div> --}}
                            @endif

                            <span class="cat-name">{{ $cat->name }}</span>
                            <span class="cat-badge">{{ $cat->total_products ?? 0 }}</span>
                        </button>
                    @empty
                        <div class="empty-state">
                            <i data-lucide="folder-x" class="w-10 h-10 mb-2 text-gray-200"></i>
                            <p class="text-sm font-semibold">No categories found</p>
                            <a href="{{ route('admin.categories.index') }}"
                                class="mt-2 text-xs text-brand-600 font-bold hover:underline">
                                Create categories →
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ════════════════════════════
             RIGHT PANEL — Products
        ════════════════════════════ --}}
            <div class="panel">

                {{-- Right panel header ── --}}
                <div class="panel-header">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm font-bold text-gray-800" x-text="activeCategoryName || 'Select a category'">
                            </p>
                            <p class="text-[11px] text-gray-400 font-medium mt-0.5" x-show="activeCategoryId"
                                x-text="`${productCount} product${productCount !== 1 ? 's' : ''} assigned`">
                            </p>
                        </div>
                        {{-- Reorder tip ── --}}
                        <div x-show="activeCategoryId && productCount > 1" x-cloak
                            class="flex items-center gap-1.5 text-[11px] text-gray-400 font-medium bg-gray-50 px-3 py-1.5 rounded-lg">
                            <i data-lucide="grip-vertical" class="w-3.5 h-3.5"></i>
                            Drag to reorder
                        </div>
                    </div>

                    {{-- Product search / add ── --}}
                    <div class="search-wrap" x-show="activeCategoryId" x-cloak>
                        <i data-lucide="search" class="w-4 h-4 search-icon"></i>
                        <input type="text" x-model="productSearch" @input.debounce.350ms="searchProducts()"
                            @focus="showResults = productSearch.length > 0 || searchResults.length > 0"
                            @click.away="showResults = false" placeholder="Search to add products..." class="search-input">

                        {{-- Search results dropdown ── --}}
                        <div class="search-results" x-show="showResults && searchResults.length > 0" x-cloak>
                            <template x-for="prod in searchResults" :key="prod.id">
                                <div class="search-result-item" :class="{ 'adding': addingIds.includes(prod.id) }"
                                    @click="addProduct(prod)">
                                    <img :src="prod.image_url" class="w-8 h-8 rounded-lg object-cover flex-shrink-0"
                                        onerror="this.src='{{ asset('assets/images/no-product.png') }}'">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate" x-text="prod.name"></p>
                                        <p class="text-[10px] text-gray-400"
                                            x-text="prod.hsn_code ? 'HSN: ' + prod.hsn_code : ''"></p>
                                    </div>
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        <template x-if="!prod.in_storefront">
                                            <span class="no-storefront-badge">Not in storefront</span>
                                        </template>
                                        <template x-if="addingIds.includes(prod.id)">
                                            <div class="spinner" style="width:16px;height:16px;"></div>
                                        </template>
                                        <template x-if="!addingIds.includes(prod.id)">
                                            <i data-lucide="plus-circle" class="w-4 h-4 text-brand-600"></i>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- No results state ── --}}
                        <div class="search-results"
                            x-show="showResults && searchResults.length === 0 && productSearch.length > 1 && !isSearching"
                            x-cloak>
                            <div class="p-4 text-center text-sm text-gray-400">
                                <i data-lucide="search-x" class="w-5 h-5 mx-auto mb-1 text-gray-300"></i>
                                No unassigned products found for "<span x-text="productSearch"></span>"
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Product list body ── --}}
                <div class="panel-body" id="product-list-body">

                    {{-- Initial state — no category selected ── --}}
                    <template x-if="!activeCategoryId">
                        <div class="empty-state">
                            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                                <i data-lucide="arrow-left" class="w-7 h-7 text-gray-300"></i>
                            </div>
                            <p class="text-sm font-bold text-gray-500">Select a category</p>
                            <p class="text-xs text-gray-400 mt-1">Click any category on the left to manage its products</p>
                        </div>
                    </template>

                    {{-- Loading state ── --}}
                    <template x-if="activeCategoryId && isLoading">
                        <div class="empty-state">
                            <div class="spinner mb-3"></div>
                            <p class="text-sm text-gray-400 font-medium">Loading products...</p>
                        </div>
                    </template>

                    {{-- Product rows ── --}}
                    <template x-if="activeCategoryId && !isLoading">
                        <div>
                            <template x-if="products.length === 0">
                                <div class="empty-state">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                                        <i data-lucide="package-plus" class="w-6 h-6 text-gray-300"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500">No products yet</p>
                                    <p class="text-xs text-gray-400 mt-1">Search above to add products to this category</p>
                                </div>
                            </template>

                            <div id="sortable-product-list">
                                <template x-for="(prod, index) in products" :key="prod.product_id ?? prod.product?.id">
                                    <div class="product-row" :data-product-id="prod.product_id ?? prod.product?.id"
                                        :class="{ 'opacity-60': removingIds.includes(prod.product_id ?? prod.product?.id) }">

                                        {{-- Drag handle ── --}}
                                        <div class="drag-handle sortable-handle">
                                            <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                        </div>

                                        {{-- Sort order chip ── --}}
                                        <span class="order-chip" x-text="index + 1"></span>

                                        {{-- Product image ── --}}
                                        <img :src="prod.product?.primary_image_url ?? prod.image_url ??
                                            '{{ asset('assets/images/no-product.png') }}'"
                                            class="prod-thumb"
                                            onerror="this.src='{{ asset('assets/images/no-product.png') }}'">

                                        {{-- Product info ── --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="prod-name"
                                                x-text="prod.product?.name ?? prod.name ?? 'Unknown Product'"></p>
                                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                                <template x-if="prod.is_featured">
                                                    <span class="featured-badge">⭐ Featured</span>
                                                </template>
                                                <template x-if="!prod.is_active">
                                                    <span class="hidden-badge">Hidden</span>
                                                </template>
                                                <template x-if="!(prod.product?.show_in_storefront ?? true)">
                                                    <span class="no-storefront-badge">Not in storefront</span>
                                                </template>
                                                <span class="text-[10px] text-gray-400" x-show="prod.product?.hsn_code"
                                                    x-text="'HSN: ' + (prod.product?.hsn_code ?? '')">
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Action buttons ── --}}
                                        <div class="flex items-center gap-1.5 flex-shrink-0">

                                            {{-- Featured toggle ── --}}
                                            <button type="button" class="row-action btn-star"
                                                :class="{ 'featured': prod.is_featured }"
                                                :title="prod.is_featured ? 'Unfeature' : 'Mark as Featured'"
                                                @click="toggleFeatured(prod)">
                                                <i data-lucide="star" class="w-3.5 h-3.5"
                                                    :class="prod.is_featured ? 'fill-current' : ''"></i>
                                            </button>

                                            {{-- Active toggle ── --}}
                                            <button type="button" class="row-action btn-eye"
                                                :class="{ 'hidden-in-cat': !prod.is_active }"
                                                :title="prod.is_active ? 'Hide from this category' : 'Show in this category'"
                                                @click="toggleActive(prod)">
                                                <i :data-lucide="prod.is_active ? 'eye' : 'eye-off'"
                                                    class="w-3.5 h-3.5"></i>
                                            </button>

                                            {{-- Remove ── --}}
                                            <button type="button" class="row-action btn-remove"
                                                title="Remove from category" @click="removeProduct(prod)">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>

                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                </div>
            </div>

        </div>

    </div>
@endsection

@push('scripts')    
    <script src="{{ asset('assets/js/sortable.min.js') }}"></script>
    <script>
        function merchandising() {
            return {
                // ── State ──
                activeCategoryId: {{ $selectedCategory?->id ?? 'null' }},
                activeCategoryName: '{{ addslashes($selectedCategory?->name ?? '') }}',
                products: @json($assignedProducts ?? []),
                productCount: {{ $assignedProducts?->count() ?? 0 }},
                isLoading: false,
                catSearch: '',

                // Search state
                productSearch: '',
                searchResults: [],
                showResults: false,
                isSearching: false,

                // Optimistic UI tracking
                addingIds: [],
                removingIds: [],

                // Sortable instance
                sortableInstance: null,

                // ════════════════════════════════════════
                //  INIT
                // ════════════════════════════════════════
                init() {
                    console.log('[Merchandising] Initialized', {
                        category: this.activeCategoryId,
                        products: this.products.length,
                    });

                    if (this.activeCategoryId) {
                        this.$nextTick(() => {
                            this.$nextTick(() => {
                                this.initSortable();
                                window.initIcons && window.initIcons(
                                    document.getElementById('sortable-product-list')
                                );
                            });
                        });
                    }

                    // Close search on escape
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') this.showResults = false;
                    });
                },

                // ════════════════════════════════════════
                //  SELECT CATEGORY
                // ════════════════════════════════════════
                async selectCategory(id, name) {
                    if (this.activeCategoryId === id) return;

                    this.activeCategoryId = id;
                    this.activeCategoryName = name;
                    this.isLoading = true;
                    this.products = [];
                    this.productSearch = '';
                    this.searchResults = [];
                    this.showResults = false;

                    // Update URL without full reload
                    const url = new URL(window.location.href);
                    url.searchParams.set('category', id);
                    history.pushState({}, '', url.toString());

                    console.log('[Merchandising] Loading category:', id, name);

                    try {
                        const res = await fetch(`/admin/merchandising/${id}/products`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });
                        const data = await res.json();

                        if (data.success) {
                            this.products = data.products;
                            this.productCount = data.count;
                            console.log('[Merchandising] Loaded', data.count, 'products');

                            // Double $nextTick — first tick: Alpine renders x-if template
                            // Second tick: DOM is fully painted, icons + sortable can initialize
                            this.$nextTick(() => {
                                this.$nextTick(() => {
                                    this.initSortable();
                                    window.initIcons && window.initIcons(
                                        document.getElementById('sortable-product-list')
                                    );
                                });
                            });
                        } else {
                            BizAlert.toast('Failed to load category products.', 'error');
                        }

                    } catch (err) {
                        console.error('[Merchandising] Load failed:', err);
                        BizAlert.toast('Network error loading category.', 'error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                // ════════════════════════════════════════
                //  SEARCH PRODUCTS TO ADD
                // ════════════════════════════════════════
                async searchProducts() {
                    const q = this.productSearch.trim();
                    if (q.length < 1) {
                        this.searchResults = [];
                        this.showResults = false;
                        return;
                    }

                    this.isSearching = true;
                    this.showResults = true;

                    try {
                        const res = await fetch(
                            `/admin/merchandising/${this.activeCategoryId}/search?q=${encodeURIComponent(q)}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                            });
                        const data = await res.json();

                        if (data.success) {
                            this.searchResults = data.products;
                            console.log('[Merchandising] Search results:', data.count, 'for query:', q);
                        }
                    } catch (err) {
                        console.error('[Merchandising] Search error:', err);
                    } finally {
                        this.isSearching = false;
                    }
                },

                // ════════════════════════════════════════
                //  ADD PRODUCT
                // ════════════════════════════════════════
                async addProduct(prod) {
                    if (this.addingIds.includes(prod.id)) return;
                    this.addingIds.push(prod.id);

                    console.log('[Merchandising] Adding product:', prod.id, prod.name);

                    try {
                        const res = await fetch(`/admin/merchandising/${this.activeCategoryId}/add`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                product_id: prod.id
                            }),
                        });
                        const data = await res.json();

                        if (data.success) {
                            // Add to product list
                            this.products.push({
                                product_id: data.product.id,
                                is_active: data.product.is_active,
                                is_featured: data.product.is_featured,
                                sort_order: data.product.sort_order,
                                product: {
                                    id: data.product.id,
                                    name: data.product.name,
                                    primary_image_url: data.product.image_url,
                                    show_in_storefront: data.product.in_storefront,
                                },
                            });

                            this.productCount++;

                            // Remove from search results
                            this.searchResults = this.searchResults.filter(p => p.id !== prod.id);
                            if (this.searchResults.length === 0) {
                                this.showResults = false;
                                this.productSearch = '';
                            }

                            // Update category badge count
                            this.updateCategoryBadge(this.activeCategoryId, this.productCount);

                            BizAlert.toast(data.message, 'success');
                            this.$nextTick(() => {
                                this.initSortable();
                                window.initIcons && window.initIcons(document.getElementById(
                                    'sortable-product-list'));
                            });

                            console.log('[Merchandising] Product added:', data.product.id);
                        } else {
                            BizAlert.toast(data.message || 'Failed to add product.', 'error');
                        }

                    } catch (err) {
                        console.error('[Merchandising] Add product error:', err);
                        BizAlert.toast('Network error. Please try again.', 'error');
                    } finally {
                        this.addingIds = this.addingIds.filter(id => id !== prod.id);
                    }
                },

                // ════════════════════════════════════════
                //  REMOVE PRODUCT
                // ════════════════════════════════════════
                async removeProduct(prod) {
                    const productId = prod.product_id ?? prod.product?.id;
                    const name = prod.product?.name ?? 'this product';

                    const result = await BizAlert.confirm(
                        `Remove "${name}"?`,
                        'It will be removed from this category only. The product still exists.',
                        'Yes, Remove'
                    );
                    if (!result.isConfirmed) return;

                    this.removingIds.push(productId);
                    console.log('[Merchandising] Removing product:', productId);

                    try {
                        const res = await fetch(`/admin/merchandising/${this.activeCategoryId}/products/${productId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });
                        const data = await res.json();

                        if (data.success) {
                            this.products = this.products.filter(p => (p.product_id ?? p.product?.id) !== productId);
                            this.productCount = Math.max(0, this.productCount - 1);
                            this.updateCategoryBadge(this.activeCategoryId, this.productCount);
                            BizAlert.toast('Product removed.', 'success');
                            console.log('[Merchandising] Product removed:', productId);
                        } else {
                            BizAlert.toast(data.message || 'Remove failed.', 'error');
                        }

                    } catch (err) {
                        console.error('[Merchandising] Remove error:', err);
                        BizAlert.toast('Network error.', 'error');
                    } finally {
                        this.removingIds = this.removingIds.filter(id => id !== productId);
                    }
                },

                // ════════════════════════════════════════
                //  TOGGLE FEATURED
                // ════════════════════════════════════════
                async toggleFeatured(prod) {
                    const productId = prod.product_id ?? prod.product?.id;
                    console.log('[Merchandising] Toggling featured:', productId, '→', !prod.is_featured);

                    // Optimistic update
                    prod.is_featured = !prod.is_featured;

                    try {
                        const res = await fetch(
                            `/admin/merchandising/${this.activeCategoryId}/products/${productId}/toggle-featured`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                            });
                        const data = await res.json();

                        if (data.success) {
                            prod.is_featured = data.is_featured;
                            BizAlert.toast(data.message, 'success');
                            this.$nextTick(() => {
                                window.initIcons && window.initIcons(document.getElementById(
                                    'sortable-product-list'));
                            });
                        } else {
                            prod.is_featured = !prod.is_featured; // revert
                            BizAlert.toast(data.message || 'Failed.', 'error');
                        }

                    } catch (err) {
                        prod.is_featured = !prod.is_featured; // revert on error
                        console.error('[Merchandising] Toggle featured error:', err);
                        BizAlert.toast('Network error.', 'error');
                    }
                },

                // ════════════════════════════════════════
                //  TOGGLE ACTIVE (per-category visibility)
                // ════════════════════════════════════════
                async toggleActive(prod) {
                    const productId = prod.product_id ?? prod.product?.id;
                    console.log('[Merchandising] Toggling active:', productId, '→', !prod.is_active);

                    // Optimistic update
                    prod.is_active = !prod.is_active;

                    try {
                        const res = await fetch(
                            `/admin/merchandising/${this.activeCategoryId}/products/${productId}/toggle-active`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                            });
                        const data = await res.json();

                        if (data.success) {
                            prod.is_active = data.is_active;
                            BizAlert.toast(data.message, 'success');
                            this.$nextTick(() => {
                                window.initIcons && window.initIcons(document.getElementById(
                                    'sortable-product-list'));
                            });
                        } else {
                            prod.is_active = !prod.is_active; // revert
                            BizAlert.toast(data.message || 'Failed.', 'error');
                        }

                    } catch (err) {
                        prod.is_active = !prod.is_active; // revert
                        console.error('[Merchandising] Toggle active error:', err);
                        BizAlert.toast('Network error.', 'error');
                    }
                },

                // ════════════════════════════════════════
                //  REORDER — via SortableJS
                // ════════════════════════════════════════
                initSortable() {
                    const el = document.getElementById('sortable-product-list');
                    if (!el) return;

                    // Destroy previous instance
                    if (this.sortableInstance) {
                        this.sortableInstance.destroy();
                        this.sortableInstance = null;
                    }

                    this.sortableInstance = Sortable.create(el, {
                        animation: 200,
                        handle: '.sortable-handle',
                        ghostClass: 'sortable-ghost-row',
                        dragClass: 'is-dragging',

                        onEnd: async (evt) => {
                            // Reorder local array to match DOM
                            const moved = this.products.splice(evt.oldIndex, 1)[0];
                            this.products.splice(evt.newIndex, 0, moved);

                            // Update order chips
                            this.products.forEach((p, i) => p._index = i + 1);

                            const productIds = this.products.map(p => p.product_id ?? p.product?.id);
                            console.log('[Merchandising] New order:', productIds);

                            try {
                                const res = await fetch(
                                    `/admin/merchandising/${this.activeCategoryId}/reorder`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector(
                                                'meta[name="csrf-token"]').content,
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            product_ids: productIds
                                        }),
                                    });
                                const data = await res.json();

                                if (data.success) {
                                    BizAlert.toast('Order saved!', 'success');
                                } else {
                                    BizAlert.toast('Failed to save order.', 'error');
                                }
                            } catch (err) {
                                console.error('[Merchandising] Reorder error:', err);
                                BizAlert.toast('Network error saving order.', 'error');
                            }
                        },
                    });

                    console.log('[Merchandising] Sortable initialized on', this.products.length, 'products');
                },

                // ════════════════════════════════════════
                //  HELPERS
                // ════════════════════════════════════════
                updateCategoryBadge(categoryId, count) {
                    const item = document.getElementById(`cat-item-${categoryId}`);
                    const badge = item?.querySelector('.cat-badge');
                    if (badge) badge.textContent = count;
                },
            }
        }
    </script>
@endpush
