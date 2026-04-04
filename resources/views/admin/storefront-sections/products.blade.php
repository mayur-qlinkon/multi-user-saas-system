@extends('layouts.admin')

@section('title', 'Manage Products — ' . $section->title)

@section('header-title')
    <div>
        <h1 class="text-[17px] font-bold text-gray-800 leading-none">Manage Products</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $section->title }}</p>
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .product-row {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; background: #fff;
        border: 1.5px solid #f1f5f9; border-radius: 12px;
        transition: border-color 140ms ease, box-shadow 140ms ease;
    }
    .product-row:hover { border-color: #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .product-row.dragging { opacity: 0.5; border-color: var(--brand-600); }

    .drag-handle {
        cursor: grab; color: #d1d5db;
        padding: 2px 4px; border-radius: 4px;
        transition: color 140ms ease;
        flex-shrink: 0;
    }
    .drag-handle:hover { color: #9ca3af; }
    .drag-handle:active { cursor: grabbing; }

    .search-result-row {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 12px; border-radius: 10px;
        cursor: pointer; transition: background 120ms ease;
    }
    .search-result-row:hover { background: #f0f7f4; }

    .empty-state {
        display: flex; flex-direction: column; align-items: center;
        justify-content: center; padding: 40px 20px; text-align: center;
        border: 1.5px dashed #e2e8f0; border-radius: 14px;
        color: #9ca3af;
    }

    .panel {
        background: #fff; border: 1.5px solid #f1f5f9;
        border-radius: 16px; padding: 20px;
    }
    .panel-title {
        font-size: 11px; font-weight: 800; color: #374151;
        text-transform: uppercase; letter-spacing: 0.08em;
        margin-bottom: 14px; display: flex;
        align-items: center; gap: 6px;
    }
    .panel-title i { color: var(--brand-600); }

    .search-input {
        width: 100%; border: 1.5px solid #e5e7eb;
        border-radius: 10px; padding: 9px 12px 9px 36px;
        font-size: 13px; outline: none;
        transition: border-color 150ms ease;
    }
    .search-input:focus { border-color: var(--brand-600); }
</style>
@endpush

@section('content')
<div class="pb-10" x-data="sectionProducts()" x-init="init()">

    {{-- ── Breadcrumb ── --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-400 font-medium">
            <a href="{{ route('admin.storefront-sections.index') }}" class="hover:text-brand-600 transition-colors">
                Storefront Sections
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('admin.storefront-sections.edit', $section->id) }}"
                class="hover:text-brand-600 transition-colors truncate max-w-[160px]">
                {{ $section->title }}
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-gray-700 font-semibold">Manage Products</span>
        </div>
        <a href="{{ route('admin.storefront-sections.edit', $section->id) }}"
            class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-gray-800 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Section
        </a>
    </div>

    {{-- ── Stats bar ── --}}
    <div class="flex items-center gap-3 mb-5 text-sm text-gray-500 font-medium">
        <span class="flex items-center gap-1.5">
            <i data-lucide="list-checks" class="w-4 h-4 text-brand-600"></i>
            <span x-text="assignedProducts.length + ' products in section'"></span>
        </span>
        <span class="text-gray-300">·</span>
        <span>Max {{ $section->products_limit }} shown on storefront</span>
        <template x-if="assignedProducts.length > {{ $section->products_limit }}">
            <span class="text-amber-600 font-semibold flex items-center gap-1">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                Only first {{ $section->products_limit }} will display
            </span>
        </template>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- ════════ LEFT — Assigned Products ════════ --}}
        <div class="panel">
            <p class="panel-title">
                <i data-lucide="list-checks" class="w-4 h-4"></i>
                Products in Section
                <span class="text-gray-400 font-normal normal-case ml-1">(drag to reorder)</span>
            </p>

            {{-- Loading ── --}}
            <template x-if="loadingAssigned">
                <div class="flex items-center justify-center py-10 text-gray-400 text-sm gap-2">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Loading...
                </div>
            </template>

            {{-- Empty ── --}}
            <template x-if="!loadingAssigned && assignedProducts.length === 0">
                <div class="empty-state">
                    <i data-lucide="package-x" class="w-10 h-10 mb-3 text-gray-200"></i>
                    <p class="font-semibold text-gray-400 mb-1">No products yet</p>
                    <p class="text-xs text-gray-400">Search and add products from the right panel</p>
                </div>
            </template>

            {{-- Product list ── --}}
            <template x-if="!loadingAssigned && assignedProducts.length > 0">
                <div id="assigned-list" class="space-y-2">
                    <template x-for="(product, index) in assignedProducts" :key="product.product_id">
                        <div class="product-row"
                            :data-product-id="product.product_id"
                            :class="{ 'opacity-40': removingId === product.product_id }">

                            {{-- Drag handle ── --}}
                            <div class="drag-handle">
                                <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                            </div>

                            {{-- Position number ── --}}
                            <span class="text-[11px] font-black text-gray-300 w-5 text-center flex-shrink-0"
                                x-text="index + 1"></span>

                            {{-- Image ── --}}
                            <img :src="product.image"
                                class="w-12 h-12 rounded-lg object-cover flex-shrink-0 bg-gray-100 border border-gray-100"
                                onerror="this.src='{{ asset('assets/images/no-product.png') }}'">

                            {{-- Info ── --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-semibold text-gray-800 truncate" x-text="product.name"></p>
                                <p class="text-[11px] text-gray-400 font-medium mt-0.5">
                                    ₹<span x-text="parseFloat(product.price).toFixed(2)"></span>
                                    <template x-if="product.sku">
                                        <span class="ml-1 opacity-60" x-text="'· ' + product.sku"></span>
                                    </template>
                                </p>
                            </div>

                            {{-- Remove ── --}}
                            <button @click="removeProduct(product.product_id)"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors flex-shrink-0">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Save order button ── --}}
            <template x-if="orderChanged">
                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-3">
                    <button @click="saveOrder()" :disabled="savingOrder"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white transition-colors"
                        style="background: var(--brand-600);"
                        :class="savingOrder ? 'opacity-60 cursor-not-allowed' : 'hover:opacity-90'">
                        <i data-lucide="loader-2" x-show="savingOrder" class="w-3.5 h-3.5 animate-spin"></i>
                        <i data-lucide="save" x-show="!savingOrder" class="w-3.5 h-3.5"></i>
                        <span x-text="savingOrder ? 'Saving...' : 'Save Order'"></span>
                    </button>
                    <button @click="loadAssigned()" class="text-sm font-semibold text-gray-400 hover:text-gray-600 transition-colors">
                        Cancel
                    </button>
                </div>
            </template>
        </div>

        {{-- ════════ RIGHT — Search & Add ════════ --}}
        <div class="panel">
            <p class="panel-title">
                <i data-lucide="search" class="w-4 h-4"></i>
                Search & Add Products
            </p>

            {{-- Search input ── --}}
            <div class="relative mb-4">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none"></i>
                <input type="text"
                    x-model="searchQuery"
                    @input.debounce.350ms="searchProducts()"
                    placeholder="Search by name or SKU..."
                    class="search-input">
                <template x-if="searchQuery.length > 0">
                    <button @click="searchQuery = ''; searchResults = []"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </template>
            </div>

            {{-- Search results ── --}}
            <div class="space-y-1 max-h-[420px] overflow-y-auto no-scrollbar">

                {{-- Searching ── --}}
                <template x-if="searching">
                    <div class="flex items-center justify-center py-6 text-gray-400 text-sm gap-2">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Searching...
                    </div>
                </template>

                {{-- Results ── --}}
                <template x-if="!searching && searchResults.length > 0">
                    <div>
                        <template x-for="product in searchResults" :key="product.product_id">
                            <div class="search-result-row" @click="addProduct(product)">
                                <img :src="product.image"
                                    class="w-10 h-10 rounded-lg object-cover flex-shrink-0 bg-gray-100 border border-gray-100"
                                    onerror="this.src='{{ asset('assets/images/no-product.png') }}'">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] font-semibold text-gray-800 truncate" x-text="product.name"></p>
                                    <p class="text-[11px] text-gray-400">
                                        ₹<span x-text="parseFloat(product.price).toFixed(2)"></span>
                                    </p>
                                </div>
                                <button class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold transition-colors"
                                    style="background: var(--brand-600); color: white;"
                                    :class="addingId === product.product_id ? 'opacity-60' : 'hover:opacity-90'">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- No results ── --}}
                <template x-if="!searching && searchQuery.length > 0 && searchResults.length === 0">
                    <div class="text-center py-8 text-gray-400 text-sm">
                        <i data-lucide="search-x" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                        <p class="font-semibold">No products found</p>
                        <p class="text-xs mt-1">Try a different search term</p>
                    </div>
                </template>

                {{-- Default prompt ── --}}
                <template x-if="!searching && searchQuery.length === 0">
                    <div class="text-center py-10 text-gray-400 text-sm">
                        <i data-lucide="search" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                        <p>Type to search products</p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
function sectionProducts() {
    return {
        sectionId:        {{ $section->id }},
        assignedProducts: [],
        searchResults:    [],
        searchQuery:      '',
        loadingAssigned:  false,
        searching:        false,
        orderChanged:     false,
        savingOrder:      false,
        removingId:       null,
        addingId:         null,
        sortable:         null,

        init() {
            this.loadAssigned();

            // Re-run Lucide whenever search results or assigned products change
            this.$watch('searchResults', () => {
                this.$nextTick(() => lucide.createIcons());
            });
            this.$watch('assignedProducts', () => {
                this.$nextTick(() => lucide.createIcons());
            });

            console.log('[SectionProducts] Init section #{{ $section->id }}');
        },

        // ── Load assigned products ──
        async loadAssigned() {
            this.loadingAssigned = true;
            this.orderChanged    = false;

            try {
                const res  = await fetch('{{ route('admin.storefront-sections.products.load', $section->id) }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    this.assignedProducts = data.products;
                    this.$nextTick(() => {
                        this.initSortable();
                        lucide.createIcons();
                    });
                }
            } catch (e) {
                console.error('[SectionProducts] Load error:', e);
            } finally {
                this.loadingAssigned = false;
            }
        },

        // ── Init SortableJS ──
        initSortable() {
            const el = document.getElementById('assigned-list');
            if (!el) return;

            if (this.sortable) this.sortable.destroy();

            this.sortable = Sortable.create(el, {
                animation:  150,
                handle:     '.drag-handle',
                ghostClass: 'dragging',
                onEnd: () => {
                    // Sync Alpine array with DOM order after drag
                    const newOrder = [...el.querySelectorAll('[data-product-id]')]
                        .map(el => parseInt(el.dataset.productId));

                    this.assignedProducts = newOrder.map(id =>
                        this.assignedProducts.find(p => p.product_id === id)
                    ).filter(Boolean);

                    this.orderChanged = true;
                    console.log('[SectionProducts] Order changed:', newOrder);
                }
            });
        },

        // ── Save order ──
        async saveOrder() {
            this.savingOrder = true;
            const productIds = this.assignedProducts.map(p => p.product_id);

            try {
                const res  = await fetch('{{ route('admin.storefront-sections.products.reorder', $section->id) }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ product_ids: productIds }),
                });
                const data = await res.json();

                if (data.success) {
                    this.orderChanged = false;
                    BizAlert.toast('Order saved!', 'success');
                }
            } catch (e) {
                console.error('[SectionProducts] Save order error:', e);
                BizAlert.toast('Failed to save order', 'error');
            } finally {
                this.savingOrder = false;
            }
        },

        // ── Search products ──
        async searchProducts() {
            if (this.searchQuery.length === 0) {
                this.searchResults = [];
                return;
            }

            this.searching = true;
            try {
                const url  = '{{ route('admin.storefront-sections.products.search', $section->id) }}?q=' + encodeURIComponent(this.searchQuery);
                const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                if (data.success) this.searchResults = data.products;
            } catch (e) {
                console.error('[SectionProducts] Search error:', e);
            } finally {
                this.searching = false;
            }
        },

        // ── Add product ──
        async addProduct(product) {
            if (this.addingId === product.product_id) return;
            this.addingId = product.product_id;

            try {
                const res  = await fetch('{{ route('admin.storefront-sections.products.add', $section->id) }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ product_id: product.product_id }),
                });
                const data = await res.json();

                if (data.success) {
                    // Add to assigned list
                    this.assignedProducts.push({
                        product_id: data.product_id,
                        name:       data.name,
                        image:      data.image,
                        price:      data.price,
                    });
                    // Remove from search results
                    this.searchResults = this.searchResults.filter(
                        p => p.product_id !== product.product_id
                    );
                    BizAlert.toast(data.message, 'success');
                    this.$nextTick(() => {
                        this.initSortable();
                        lucide.createIcons();
                    });
                }
            } catch (e) {
                console.error('[SectionProducts] Add error:', e);
                BizAlert.toast('Failed to add product', 'error');
            } finally {
                this.addingId = null;
            }
        },

        // ── Remove product ──
        async removeProduct(productId) {
            this.removingId = productId;

            try {
                const res  = await fetch(
                    `{{ url('admin/storefront-sections/' . $section->id . '/products') }}/${productId}`,
                    {
                        method:  'DELETE',
                        headers: {
                            'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }
                );
                const data = await res.json();

                if (data.success) {
                    this.assignedProducts = this.assignedProducts.filter(
                        p => p.product_id !== productId
                    );
                    // Re-add to search results if query matches
                    BizAlert.toast('Product removed.', 'success');
                    if (this.searchQuery.length > 0) this.searchProducts();
                }
            } catch (e) {
                console.error('[SectionProducts] Remove error:', e);
                BizAlert.toast('Failed to remove product', 'error');
            } finally {
                this.removingId = null;
            }
        },
    }
}
</script>
@endpush