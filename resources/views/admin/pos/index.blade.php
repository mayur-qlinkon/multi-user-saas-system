<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- 🌟 CRITICAL: Needed for the checkout and quick-client AJAX requests --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>POS Terminal - Qlinkon BIZNESS</title>
    
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . get_setting('favicon')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="{{ asset('assets/js/tailwind.min.js') }}"></script>

    {{-- 🌟 CRITICAL: Alpine.js for the posEngine --}}
        <script src="{{ asset('assets/js/lucide.min.js') }}"></script>
        <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
        <script defer src="{{ asset('assets/js/alpinejs.min.js') }}"></script>

    @php
        $primary = get_setting('primary_color', '#008a62');
        $hover = get_setting('primary_hover_color', '#007050');
        $stores = auth()->user()->stores ?? collect();
        $currentStoreId = session('store_id');
        $currentStore = $currentStoreId ? $stores->firstWhere('id', $currentStoreId) : $stores->first();
    @endphp
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif']
                    },
                    colors: {
                        brand: {
                            500: '{{ $primary }}', // Main Primary Color
                            600: '{{ $hover }}', // Hover State Color
                            700: '{{ $hover }}' // Deepest state
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* ═══════════════════════════════════════════════
           THEME VARIABLES
        ═══════════════════════════════════════════════ */
        :root {
            --brand-50: {{ $primary }}1A;
            --brand-100: {{ $primary }}33;
            --brand-600: {{ $hover }};
            --bg-page: #f4f6f9;
            --ease: cubic-bezier(0.4, 0, 0.2, 1);
        }

        [x-cloak] {
            display: none !important;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .product-card {
            box-shadow: 0 2px 10px -3px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body class="bg-white text-gray-800 font-sans h-[100dvh] flex overflow-hidden select-none">

    {{-- 🌟 MAIN ALPINE.JS WRAPPER: This wraps everything so the left and right panes can talk to each other --}}
    {{-- 🌟 Pass clients and payment methods into the engine --}}
    <div x-data="posEngine('{{ $companyState ?? '' }}', {{ $storeId ?? 0 }}, @js($clients ?? []), @js($paymentMethods ?? []))" @keydown.window="handleGlobalScan($event)"
        class="flex-1 flex h-full overflow-hidden w-full">

        {{-- ========================================== --}}
        {{-- LEFT PANE: PRODUCTS & SEARCH (~70%)        --}}
        {{-- ========================================== --}}
        <div class="flex-1 flex flex-col h-full overflow-hidden bg-white">

            {{-- 1. Search Header --}}
            <header class="h-[64px] border-b border-gray-100 flex items-center justify-between px-5 shrink-0">
                <div class="flex items-center gap-2 w-full lg:w-1/2">
                    <div class="relative w-full max-w-md">
                        <i data-lucide="search"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        {{-- 🌟 Alpine Binding: x-model and @input.debounce --}}
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="handleSearch()"
                            x-ref="searchInput" placeholder="Search your plants or scan barcode..."
                            class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all placeholder:text-gray-400">
                    </div>
                    @if(has_permission('pos.create_quick_product'))
                    <button @click="isProductModalOpen = true" title="Quick Add Product" style="margin-right: 10px;"
                        class="p-2 bg-brand-500 hover:bg-brand-600 text-white rounded flex items-center justify-center transition-colors shadow-sm">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                    </button>
                    @endif
                </div>

                <div class="flex items-center gap-2 lg:gap-4 shrink-0">
                    {{-- Loading Indicator --}}
                    <div x-show="isLoading" x-cloak class="flex items-center gap-2 text-brand-500 text-xs font-bold">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="hidden lg:inline">Loading...</span>
                    </div>
                    <div
                        class="flex items-center rounded border border-brand-700 overflow-hidden bg-brand-700 text-white shadow-sm">
                        {{-- 🌟 Forces focus back to the search bar for barcode scanning --}}
                        <button @click="openScanner()" title="Camera Scanner"
                            class="p-2 hover:bg-brand-600 transition-colors border-r border-brand-600">
                            <i data-lucide="scan-barcode" class="w-5 h-5"></i>
                        </button>
                        {{-- 🌟 Links back to the Invoices list to view POS history --}}
                        <a href="{{ route('admin.invoices.index') }}" title="POS History"
                            class="p-2 hover:bg-brand-600 transition-colors block">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </header>

            {{-- 2. Category Filter Bar --}}
            <div class="py-3 border-b border-gray-100 flex items-center px-4 gap-2.5 overflow-x-auto no-scrollbar shrink-0 scroll-smooth">
                <button @click="setCategory('')"
                    :class="activeCategory === '' ? 'bg-brand-500 text-white shadow-md border-brand-500' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border-gray-200'"
                    class="px-4 py-2 rounded-xl text-[13px] font-bold whitespace-nowrap transition-all border flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i> All
                </button>

                @foreach ($categories as $category)
                    <button @click="setCategory({{ $category->id }})"
                        :class="activeCategory === {{ $category->id }} ? 'bg-brand-500 text-white shadow-md border-brand-500' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border-gray-200'"
                        class="px-4 py-2 rounded-xl text-[13px] font-bold whitespace-nowrap transition-all border">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>

            {{-- 3. Product Grid & Infinite Scroll --}}
            <div class="flex-1 overflow-y-auto p-5 no-scrollbar relative">

                {{-- Empty State (No Products Found) --}}
                <div x-show="!isLoading && products.length === 0" x-cloak
                    class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
                    <i data-lucide="package-x" class="w-12 h-12 mb-3 opacity-20"></i>
                    <p class="font-medium">No products found</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4 pb-28 lg:pb-6 transition-all duration-300"
                    :class="(isLoading && page === 1) ? 'opacity-50 blur-[2px] pointer-events-none' : 'opacity-100 blur-0'">
                    {{-- 🌟 Sleek POS Card Template --}}
                    <template x-for="product in products" :key="product.product_sku_id">
                        <div @click="product.stock > 0 ? addToCart(product) : null"
                            class="bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col hover:border-brand-500 hover:shadow-md transition-all cursor-pointer group relative">

                            {{-- Image Section with Overlays --}}
                            <div class="aspect-[4/3] sm:aspect-[3/2] w-full bg-gray-50 relative overflow-hidden shrink-0">
                                <img :src="product.image_url || '/assets/images/placeholder.png'" :alt="product.product_name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                                {{-- Top Left Overlay: Price (Matches your reference image) --}}
                                <div class="absolute top-0 left-0 bg-[#5b61f4] text-white text-[12px] font-black px-2.5 py-1 rounded-br-xl z-10 shadow-sm"
                                     x-text="'₹' + parseFloat(product.display_price).toFixed(2)">
                                </div>

                                {{-- Top Right Overlay: Stock & Unit (Matches your reference image) --}}
                                <div class="absolute top-0 right-0 bg-[#0ea5e9] text-white text-[10px] font-bold px-2 py-1 rounded-bl-xl z-10 shadow-sm uppercase tracking-wide"
                                     x-text="product.stock + ' ' + (product.unit_name || '')">
                                </div>

                                {{-- Out of Stock Blocker (Faded overlay so you can't click it) --}}
                                <div x-show="product.stock <= 0" class="absolute inset-0 bg-white/70 backdrop-blur-[1px] flex items-center justify-center z-20">
                                    <span class="bg-red-500 text-white text-[10px] font-black px-2.5 py-1 rounded-lg uppercase tracking-widest shadow-sm">Out of Stock</span>
                                </div>
                            </div>

                            {{-- Info Section (Bottom) --}}
                            <div class="p-3 flex flex-col justify-center bg-white border-t border-gray-50 flex-1">
                                <h3 class="font-bold text-gray-800 text-[13px] leading-tight truncate mb-0.5 group-hover:text-brand-600 transition-colors" x-text="product.product_name"></h3>
                                <div class="flex items-center gap-1.5 opacity-70">
                                    <span class="text-[10px] text-gray-500 font-mono truncate uppercase tracking-widest" x-text="product.sku_code || product.barcode"></span>
                                    <template x-if="product.variant_name">
                                        <span class="text-gray-400 text-[10px] truncate">• <span class="font-bold" x-text="product.variant_name"></span></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- 🌟 The Infinite Scroll Trigger (Invisible element at the bottom) --}}
                <div x-intersect.margin.200px="loadMore()" class="h-10 w-full"></div>

            </div>
        </div>



        {{-- ========================================== --}}
        {{-- RIGHT PANE: CART & CHECKOUT (~30%)         --}}
        {{-- ========================================== --}}
        {{-- Mobile Overlay Backdrop --}}
        <div x-show="isMobileCartOpen" x-transition.opacity
            @click="isMobileCartOpen = false"
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 lg:hidden" x-cloak>
        </div>

<div :class="isMobileCartOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
            {{-- 🌟 Tuned widths: w-[340px] for iPad (lg), w-[400px] for PC (xl) --}}
            class="fixed inset-y-0 right-0 z-50 w-full sm:max-w-[400px] lg:relative lg:w-[340px] xl:w-[400px] bg-[#fdfdfd] border-l border-gray-100 flex flex-col h-full shrink-0 shadow-2xl lg:shadow-[-5px_0_15px_rgba(0,0,0,0.02)] transition-transform duration-300 ease-in-out">

            {{-- 1. Customer Selection --}}
            <div class="p-5 border-b border-gray-100 bg-white shrink-0">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center gap-2">
                        <button @click="isMobileCartOpen = false" class="lg:hidden p-1 bg-gray-100 text-gray-600 rounded-lg">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                        <h2 class="font-bold text-gray-800 text-[15px]">Customer</h2>
                    </div>
                    {{-- 🌟 Optional: Warehouse selector if you have multiple --}}
                    <select x-model="warehouse_id" @change="changeWarehouse()"
                        class="text-xs border-none bg-gray-50 rounded text-gray-500 font-bold outline-none cursor-pointer">
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

               <div class="flex items-center gap-2 mb-3">
                    {{-- Searchable Input --}}
                    <div class="relative flex-1" @click.away="isClientDropdownOpen = false">
                        <input type="text" x-model="clientSearchTerm" 
                            @focus="isClientDropdownOpen = true"
                            @input="isClientDropdownOpen = true; customer.id = ''"
                            placeholder="Walk-in Guest or Search..."
                            class="w-full pl-3 pr-8 py-2 bg-gray-50 border border-gray-100 rounded-lg text-xs font-bold text-gray-700 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition-all">
                        <i data-lucide="chevron-down" class="w-4 h-4 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>

                        {{-- Floating Dropdown List --}}
                        <ul x-show="isClientDropdownOpen" x-cloak x-transition
                            class="absolute z-[60] w-full bg-white border border-gray-200 rounded-lg shadow-2xl mt-1 max-h-60 overflow-y-auto top-full left-0 custom-scrollbar">
                            
                            {{-- Permanent Walk-in Guest Option --}}
                            <li @click="selectCustomer(null)"
                                class="px-4 py-3 hover:bg-brand-50 cursor-pointer border-b border-gray-100 transition-colors">
                                <div class="font-bold text-[13px] text-brand-600">Walk-in Guest</div>
                                <div class="text-[10px] text-gray-400 mt-0.5">Proceed without saving customer info</div>
                            </li>

                            <li x-show="filteredClientList.length === 0" class="px-4 py-4 text-xs text-gray-500 text-center font-medium">
                                No matching customers.
                            </li>

                            <template x-for="client in filteredClientList" :key="client.id">
                                <li @click="selectCustomer(client)"
                                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                    <div class="font-bold text-[13px] text-gray-800" x-text="client.name"></div>
                                    <div class="text-[11px] text-gray-500 mt-0.5 flex flex-wrap items-center gap-2">
                                        <span x-show="client.phone" x-text="'📞 ' + client.phone"></span>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- Quick Add Button --}}
                    @if(has_permission('pos.create_quick_client'))
                    <button type="button" @click="isClientModalOpen = true"
                        class="w-[36px] h-[36px] bg-brand-500 hover:bg-brand-600 text-white rounded-xl flex items-center justify-center shadow-md transition-all focus:ring-2 focus:ring-brand-500 focus:outline-none shrink-0">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                    </button>
                    @endif
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                    <span>Selected: <span class="font-bold text-brand-600"
                            x-text="customer.id ? customer.name : 'Guest'"></span></span>
                </div>
            </div>

            {{-- 2 & 3. SCROLLABLE BODY (Cart Items + Payment Math) --}}
            <div class="flex-1 overflow-y-auto bg-gray-50/30 custom-scrollbar pb-6">

                {{-- Cart Items Ledger --}}
                <div class="bg-white min-h-[150px]">
                    {{-- Empty State --}}
                    <div x-show="cart.length === 0" x-cloak class="flex flex-col items-center justify-center text-gray-400 p-8">
                        <i data-lucide="shopping-bag" class="w-12 h-12 mb-3 text-gray-300 stroke-1"></i>
                        <p class="font-medium text-sm text-gray-500">Order is empty</p>
                        <p class="text-xs mt-1">Scan or add items to get started.</p>
                    </div>

                    {{-- Filled State --}}
                    <ul x-show="cart.length > 0" x-cloak class="divide-y divide-gray-50 border-b border-gray-100">
                        <template x-for="(item, index) in cart" :key="item.product_sku_id">
                            <li class="p-4 hover:bg-gray-50/50 transition-colors group">
                                <div class="flex justify-between items-start mb-2 gap-3">
                                    {{-- Product Thumbnail --}}
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden shrink-0">
                                        <img :src="item.image_url || '/assets/images/placeholder.png'" class="w-full h-full object-cover">
                                    </div>
                                    
                                    <div class="flex-1 pr-2">
                                        <h3 class="text-[13px] font-bold text-gray-800 leading-tight" x-text="item.product_name"></h3>
                                        <div class="text-[10px] text-gray-400 font-mono mt-0.5" x-text="'₹' + item.unit_price + ' / ' + (item.unit_name || 'unit')"></div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-[14px] font-black text-gray-800" x-text="formatCurrency(item.unit_price * item.quantity)"></div>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center mt-3">
                                    {{-- Qty Controls (Using RAW SVGs so they never disappear) --}}
                                    <div class="flex items-center bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden h-9">
                                        <button @click="updateQty(index, -1)" class="w-10 h-full flex items-center justify-center text-gray-600 bg-gray-50 hover:bg-gray-100 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                                        </button>
                                        <input type="number" x-model.number="item.quantity" @input="calculateCart()" class="w-12 h-full border-x border-gray-200 text-center text-[13px] font-bold outline-none no-scrollbar bg-white">
                                        <button @click="updateQty(index, 1)" class="w-10 h-full flex items-center justify-center text-gray-600 bg-gray-50 hover:bg-gray-100 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                        </button>
                                    </div>

                                    {{-- Remove Button (Using RAW SVG) --}}
                                    <button @click="updateQty(index, -item.quantity)" class="w-9 h-9 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 rounded-lg transition-all active:scale-95">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </button>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- Detail Payment & Math --}}
                <div class="p-5 bg-[#fafafa] border-t border-gray-200">
                    <h2 class="mb-3 text-[14px] font-bold text-gray-800 uppercase tracking-widest">Payment Details</h2>

                    <div class="mb-4">
                        <x-payment-method-select name="payment_method_id" label="Payment Type" required="true" showIcons="true" class="!border-brand-500 focus:!ring-brand-500" x-model="payment.method_id" @change="handlePaymentChange($event)" data-payment-selector="true" />
                    </div>

                    <div class="space-y-3 text-[13px] font-medium text-gray-500">
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-2 shadow-sm">
                            <span class="font-bold text-gray-700">Received (₹)</span>
                            <input type="number" step="0.01" placeholder="0.00" x-model.number="payment.received" @input="calculatePayment()" :disabled="(payment.method_name || '').toLowerCase() !== 'cash'" class="w-24 bg-transparent text-right text-base font-black text-brand-600 focus:outline-none">
                        </div>

                        <div class="flex items-center justify-between px-1">
                            <span>Change Amount</span>
                            <span class="font-bold text-green-600" x-text="formatCurrency(payment.change)"></span>
                        </div>

                        <div class="flex items-center justify-between px-1">
                            <span>Due Amount</span>
                            <span class="font-bold text-red-500" x-text="formatCurrency(payment.due)"></span>
                        </div>

                        <hr class="my-3 border-gray-200">

                        <div class="flex items-center justify-between px-1">
                            <span>Sub total</span>
                            <span class="font-bold text-gray-800" x-text="formatCurrency(totals.subtotal)"></span>
                        </div>

                        @if(has_permission('pos.apply_discount'))
                        <div class="flex items-center justify-between">
                            <span class="px-1">Discount</span>
                            <div class="flex overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                                <select x-model="totals.discount_type" @change="calculateCart()" class="cursor-pointer border-r border-gray-200 bg-gray-50 px-2 py-1.5 text-xs font-bold focus:outline-none">
                                    <option value="fixed">₹</option>
                                    <option value="percent">%</option>
                                </select>
                                <input type="number" step="0.01" placeholder="0.00" x-model.number="totals.discount_value" @input="calculateCart()" class="w-16 px-2 py-1.5 text-right text-xs font-bold text-red-500 placeholder-gray-300 focus:outline-none">
                            </div>
                        </div>
                        @endif

                        <div class="flex items-center justify-between px-1">
                            <span>Tax Amount</span>
                            <span class="font-bold text-gray-800" x-text="formatCurrency(totals.tax)"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. STICKY CHECKOUT FOOTER --}}
            <div class="p-4 bg-white border-t border-gray-100 shrink-0 shadow-[0_-5px_15px_rgba(0,0,0,0.03)] z-20">
                <div class="flex items-end justify-between mb-3 px-1">
                    <div>
                        <span class="text-[14px] font-bold text-gray-800">Payable Amount</span>
                        <div class="text-[10px] text-gray-400 mt-0.5">Round off: <span x-text="totals.round_off"></span></div>
                    </div>
                    <span class="text-2xl font-black text-brand-600" x-text="formatCurrency(totals.payable)"></span>
                </div>

                @if(has_permission('pos.create_sale'))
                <button @click="placeOrder()" :disabled="cart.length === 0 || isProcessing"
                    :class="(cart.length === 0) ? 'bg-[#cbd5e1] cursor-not-allowed' : 'bg-brand-500 hover:bg-brand-600 shadow-lg hover:shadow-brand-500/30 active:scale-95'"
                    class="flex w-full items-center justify-center gap-2 rounded-xl py-3.5 text-sm font-bold tracking-wide text-white transition-all">
                    
                    <span x-show="!isProcessing">Place an Order</span>
                    <span x-show="isProcessing" class="flex items-center gap-2">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Processing...
                    </span>
                </button>
                @endif
            </div>
        </div>



        {{-- ======================================================= --}}
        {{-- 🟢 QUICK CLIENT MODAL COMPONENT                         --}}
        {{-- ======================================================= --}}
        <x-quick-client-modal :states="$states" />
        {{-- 🟢 CAMERA BARCODE SCANNER MODAL --}}
        <x-barcode-scanner-modal />
        {{-- 🌟 🟢 QUICK PRODUCT MODAL --}}
        <x-quick-product-modal :categories="$categories" :units="$units" />

        {{-- 🌟 🟢 RECEIPT PREVIEW MODAL --}}
        <div x-show="isReceiptModalOpen" style="display: none;"
            class="fixed inset-0 z-[120] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm px-4"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            <div
                class="bg-white w-full max-w-md rounded-2xl shadow-2xl flex flex-col overflow-hidden h-[85vh] border border-gray-100">

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-4 bg-white border-b border-gray-100 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-brand-50 flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-5 h-5 text-brand-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-[15px] leading-tight">Payment Successful</h3>
                            <p class="text-[11px] text-gray-500 font-medium">Bill generated successfully</p>
                        </div>
                    </div>
                    <button @click="closeReceiptModal()" title="Close"
                        class="text-gray-400 hover:text-red-500 transition-colors p-1.5 rounded-lg hover:bg-red-50">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Iframe Container (Simulates the POS Printer Machine) --}}
                <div class="flex-1 overflow-hidden flex flex-col bg-[#e5e7eb] relative items-center py-6 shadow-inner">

                    {{-- Realistic Printer Slot Visual --}}
                    <div
                        class="absolute top-0 left-1/2 -translate-x-1/2 w-[86mm] h-3 bg-gradient-to-b from-gray-800 to-gray-600 rounded-b-lg shadow-md z-10 border-b border-gray-900">
                    </div>

                    {{-- The actual receipt paper --}}
                    <div
                        class="w-[80mm] h-full bg-white shadow-[0_10px_25px_rgba(0,0,0,0.15)] relative flex flex-col overflow-hidden transition-all">
                        {{-- Jagged paper tear effect at the bottom (Optional but looks great) --}}
                        <div
                            class="absolute bottom-0 left-0 w-full h-2 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPgo8cG9seWdvbiBwb2ludHM9IjAsOCA0LDAgOCw4IiBmaWxsPSIjZTVlN2ViIi8+Cjwvc3ZnPg==')] z-10 rotate-180">
                        </div>

                        <iframe :src="currentReceiptUrl" id="receiptFrame"
                            class="w-full h-full bg-white no-scrollbar outline-none border-none pb-4"></iframe>
                    </div>
                </div>

                {{-- Footer Action Buttons --}}
                <div class="p-5 bg-white border-t border-gray-100 flex gap-3 shrink-0">
                    <button @click="closeReceiptModal()"
                        class="flex-1 py-2.5 text-sm font-bold text-gray-600 bg-gray-50 border border-gray-200 hover:bg-gray-100 hover:text-gray-900 rounded-xl transition-all focus:ring-2 focus:ring-gray-200 active:scale-95">
                        <i data-lucide="arrow-left" class="w-4 h-4 inline-block mr-1 mb-0.5"></i> New Order
                    </button>
                    <button @click="printReceipt()"
                        class="flex-1 py-2.5 text-[15px] font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-brand-500/30 transition-all focus:ring-2 focus:ring-brand-500 active:scale-95">
                        <i data-lucide="printer" class="w-5 h-5"></i> Print Bill
                    </button>
                </div>
            </div>
        </div>


        {{-- Floating Mobile Cart Button --}}
        <div class="lg:hidden fixed bottom-6 left-1/2 -translate-x-1/2 w-[92%] sm:w-[400px] z-[45]">
            <button @click="isMobileCartOpen = true" 
                class="w-full bg-brand-500 hover:bg-brand-600 text-white shadow-xl shadow-brand-500/30 rounded-2xl py-3.5 px-5 flex items-center justify-between transition-transform active:scale-95">
                
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        <span x-show="cart.length > 0" x-text="cart.length" 
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center">
                        </span>
                    </div>
                    <span class="font-bold text-sm">View Cart</span>
                </div>

                <div class="font-black text-lg" x-text="formatCurrency(totals.payable)"></div>
            </button>
        </div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/swal.js') }}"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            lucide.createIcons();
        });
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posEngine', (companyState, storeId, clientsList, paymentMethodsList) => ({
                clients: clientsList,
                paymentMethods: paymentMethodsList,

                // ─────────────────────────────────────────────────────────
                // 1. STATE MANAGEMENT
                // ─────────────────────────────────────────────────────────

                // Grid & Search State
                products: [],
                categories: [],
                activeCategory: '',
                searchQuery: '',
                // Searchable Dropdown State
                clientSearchTerm: '',
                isClientDropdownOpen: false,
                // 🌟 Filter the list based on what the user types
                get filteredClientList() {
                    if (this.clientSearchTerm.trim() === '') {
                        return this.clients;
                    }
                    const term = this.clientSearchTerm.toLowerCase();
                    return this.clients.filter(client => {
                        return client.name.toLowerCase().includes(term) || 
                               (client.phone && client.phone.includes(term));
                    });
                },
                // 🌟 Handle clicking an item in the dropdown
                selectCustomer(client) {
                    if (!client) {
                        // Walk-in Guest Selected
                        this.customer.id = '';
                        this.customer.name = 'Guest';
                        this.clientSearchTerm = ''; // Clear search box so placeholder shows
                    } else {
                        // Real Customer Selected
                        this.customer.id = client.id;
                        this.customer.name = client.name;
                        this.clientSearchTerm = client.name; // Put name in the search box
                    }
                    this.isClientDropdownOpen = false;
                },
                isLoading: false,

                // Infinite Scroll State
                page: 1,
                hasMorePages: true,

                // Scanner State
                scanBuffer: '',
                lastScanTime: 0,
                warehouse_id: "{{ $warehouses->where('store_id', session('store_id'))->where('is_default', 1)->first()?->id ?? $warehouses->where('store_id', session('store_id'))->first()?->id ?? '' }}",
                previous_warehouse_id: "{{ $warehouses->where('store_id', session('store_id'))->where('is_default', 1)->first()?->id ?? $warehouses->where('store_id', session('store_id'))->first()?->id ?? '' }}",

                // 🌟 CAMERA SCANNER STATE
                isScannerModalOpen: false,
                html5QrcodeScanner: null,

                // Cart & Customer State
                cart: JSON.parse(localStorage.getItem('pos_cart')) || [],
                customer: {
                    id: '',
                    name: 'Guest',
                    gstin: '',
                    state: companyState,
                    registration_type: 'unregistered'
                },

                // Checkout & Math State
                payment: {
                    method_id: paymentMethodsList.length > 0 ? paymentMethodsList[0].id : '',
                    method_name: paymentMethodsList.length > 0 ? paymentMethodsList[0].name : '',
                    received: '',
                    change: 0,
                    due: 0
                },
                totals: {
                    subtotal: 0,
                    discount_type: 'fixed',
                    discount_value: 0,
                    discount_amount: 0,
                    tax: 0,
                    round_off: '0.00',
                    payable: 0
                },
                isProcessing: false,
                isClientModalOpen: false,
                newClient: {
                    name: '',
                    phone: '',
                    city: '',
                    state_id: '',
                    registration_type: 'unregistered'
                },
                isProductModalOpen: false,
                isReceiptModalOpen: false,
                currentReceiptUrl: '',
                isMobileCartOpen: false,
                newProduct: {
                    name: '',
                    category_id: '',
                    unit_id: '',
                    price: '',
                    cost: '',
                    tax_percent: 0,
                    tax_type: 'exclusive',
                    sku: '',
                    barcode: '',
                    opening_stock: 0
                },

                // ─────────────────────────────────────────────────────────
                // CAMERA SCANNER LOGIC
                // ─────────────────────────────────────────────────────────
                openScanner() {
                    this.isScannerModalOpen = true;
                    setTimeout(() => {
                        if (!this.html5QrcodeScanner) {
                            this.html5QrcodeScanner = new Html5QrcodeScanner(
                                "camera-reader", {
                                    fps: 10,
                                    qrbox: {
                                        width: 250,
                                        height: 150
                                    }
                                },
                                false
                            );
                        }
                        this.html5QrcodeScanner.render(
                            (decodedText) => this.onCameraScanSuccess(decodedText),
                            (error) => {
                                /* Ignore standard scan frame errors */
                            }
                        );
                    }, 300);
                },

                closeScanner() {
                    this.isScannerModalOpen = false;
                    if (this.html5QrcodeScanner) {
                        this.html5QrcodeScanner.clear().catch(error => console.error(
                            "Failed to clear scanner", error));
                    }
                },

                onCameraScanSuccess(decodedText) {
                    // 1. Play beep (Optional)
                    let audio = new Audio('/assets/audio/beep.mp3');
                    audio.play().catch(e => {});

                    // 2. Close camera
                    this.closeScanner();

                    // 3. Process barcode
                    this.processBarcode(decodedText);
                },

                // ─────────────────────────────────────────────────────────
                // CUSTOMER & WAREHOUSE LOGIC
                // ─────────────────────────────────────────────────────────
                async saveQuickClient() {
                    if (!this.newClient.name || this.newClient.phone.length !== 10 || !this
                        .newClient.city || !this.newClient.state_id) {
                        BizAlert.toast('Please fill all required fields correctly', 'error');
                        return;
                    }

                    try {
                        let csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        let response = await fetch("/admin/clients", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfMeta.content
                            },
                            body: JSON.stringify(this.newClient)
                        });

                        let data = await response.json();

                        if (!response.ok) {
                            BizAlert.toast(data.message || Object.values(data.errors)[0][0],
                                'error');
                            return;
                        }

                        BizAlert.toast('Client added successfully!', 'success');
                        this.isClientModalOpen = false;

                        // Push to the array so the dropdown updates instantly
                        this.clients.push(data.client);
                        
                        // Auto-select the newly created client
                        this.selectCustomer(data.client);

                        this.newClient = {
                            name: '',
                            phone: '',
                            city: '',
                            state_id: '',
                            registration_type: 'unregistered'
                        };

                    } catch (error) {
                        console.error(error);
                        BizAlert.toast('Network error.', 'error');
                    }
                },



                // ─────────────────────────────────────────────────────────
                // QUICK ADD PRODUCT LOGIC
                // ─────────────────────────────────────────────────────────
                async saveQuickProduct() {
                    // Basic Validation
                    if (!this.newProduct.name || !this.newProduct.category_id || !this.newProduct
                        .unit_id || !this.newProduct.price || !this.newProduct.cost) {
                        BizAlert.toast('Please fill all required (*) fields.', 'error');
                        return;
                    }

                    BizAlert.loading('Saving product...');

                    try {
                        let csrfMeta = document.querySelector('meta[name="csrf-token"]');

                        // We MUST use FormData to handle the image file upload
                        let formData = new FormData();
                        formData.append('name', this.newProduct.name);
                        formData.append('category_id', this.newProduct.category_id);
                        formData.append('unit_id', this.newProduct.unit_id);
                        formData.append('price', this.newProduct.price);
                        formData.append('cost', this.newProduct.cost);
                        formData.append('tax_percent', this.newProduct.tax_percent);
                        formData.append('tax_type', this.newProduct.tax_type);
                        formData.append('sku', this.newProduct.sku);
                        formData.append('barcode', this.newProduct.barcode);
                        formData.append('opening_stock', this.newProduct.opening_stock);

                        // 🌟 Bind the opening stock directly to the active POS warehouse!
                        formData.append('warehouse_id', this.warehouse_id);

                        // Append Image if selected
                        let imageInput = this.$refs.productImageFile;
                        if (imageInput && imageInput.files.length > 0) {
                            formData.append('image', imageInput.files[0]);
                        }

                        let response = await fetch("/admin/pos/quick-product", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfMeta.content
                                // 🚨 DO NOT set 'Content-Type' here! Browser automatically sets it for FormData.
                            },
                            body: formData
                        });

                        let data = await response.json();

                        if (!response.ok) {
                            BizAlert.toast(data.message || Object.values(data.errors)[0][0],
                                'error');
                            return;
                        }

                        BizAlert.toast('Product Created & Added to Cart!', 'success');

                        // 1. Close Modal
                        this.isProductModalOpen = false;

                        // 2. Add directly to cart (the backend formats it perfectly for us)
                        this.addToCart(data.data);

                        // 3. Force the visual grid to refresh so it shows up there too
                        this.products = [];
                        this.page = 1;
                        this.fetchProducts(false);

                        // 4. Reset Form
                        this.newProduct = {
                            name: '',
                            category_id: '',
                            unit_id: '',
                            price: '',
                            cost: '',
                            tax_percent: 0,
                            tax_type: 'exclusive',
                            sku: '',
                            barcode: '',
                            opening_stock: 0
                        };
                        if (imageInput) imageInput.value = ''; // Clear file input

                    } catch (error) {
                        console.error(error);
                        BizAlert.toast('Network error while saving product.', 'error');
                    }
                },
                async changeWarehouse() {
                    if (this.cart.length > 0) {
                        let result = await Swal.fire({
                            title: 'Clear Cart?',
                            text: "Changing the warehouse will clear your current cart. Continue?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#008a62',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, change it!'
                        });

                        if (!result.isConfirmed) {
                            this.warehouse_id = this.previous_warehouse_id;
                            return;
                        }
                        this.cart = [];
                        this.calculateCart();
                    }

                    this.previous_warehouse_id = this.warehouse_id;
                    this.products = [];
                    this.page = 1;
                    this.hasMorePages = true;
                    this.fetchProducts(false);
                },

                // ─────────────────────────────────────────────────────────
                // 2. INITIALIZATION & DATA FETCHING
                // ─────────────────────────────────────────────────────────               
                init() {
                    this.fetchProducts();
                    if (this.paymentMethods && this.paymentMethods.length > 0) {
                        this.payment.method_id = this.paymentMethods[0].id;
                        // 🌟 Bulletproof fallback for the Javascript object too!
                        this.payment.method_name = this.paymentMethods[0].name || this.paymentMethods[0]
                            .title || this.paymentMethods[0].label || 'Cash';
                    }
                    this.calculateCart();
                },

                async fetchProducts(append = false) {
                    if (this.isLoading || (!this.hasMorePages && append)) return;

                    this.isLoading = true;
                    if (!append) {
                        this.page = 1;
                        // 🌟 DO NOT empty this.products here! Keep the old products on screen.
                    }

                    try {
                        const url = `/admin/api/products?page=${this.page}&search=${encodeURIComponent(this.searchQuery)}&category_id=${this.activeCategory}&warehouse_id=${this.warehouse_id}&per_page=15`;
                        const response = await fetch(url);
                        const res = await response.json();

                        if (res.status === 'success') {
                            if (append) {
                                this.products = [...this.products, ...res.data];
                            } else {
                                // 🌟 REPLACE the products array instantly once the new data arrives
                                this.products = res.data;
                            }
                            this.hasMorePages = this.page < res.meta.total_pages;
                        }
                    } catch (error) {
                        console.error("Failed to fetch products:", error);
                        BizAlert.toast('Failed to load products', 'error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                loadMore() {
                    if (this.hasMorePages && !this.isLoading) {
                        this.page++;
                        this.fetchProducts(true);
                    }
                },

                setCategory(categoryId) {
                    this.activeCategory = categoryId;
                    this.fetchProducts(false);
                },

                handleSearch() {
                    this.fetchProducts(false);
                },

                // ─────────────────────────────────────────────────────────
                // 3. THE LIGHTNING SCANNER (GLOBAL LISTENER)
                // ─────────────────────────────────────────────────────────
                handleGlobalScan(e) {
                    if (this.isProcessing) return;

                    const currentTime = new Date().getTime();

                    if (currentTime - this.lastScanTime > 50) {
                        this.scanBuffer = '';
                    }

                    if (e.key !== 'Enter' && e.key.length === 1) {
                        this.scanBuffer += e.key;
                    }

                    this.lastScanTime = currentTime;

                    if (e.key === 'Enter' && this.scanBuffer.length > 3) {
                        e.preventDefault();
                        this.processBarcode(this.scanBuffer);
                        this.scanBuffer = '';
                    }
                },

                async processBarcode(barcode) {
                    try {
                        const url =
                            `/admin/pos/scan?term=${encodeURIComponent(barcode)}&warehouse_id=${this.warehouse_id}`;
                        const response = await fetch(url);
                        const res = await response.json();

                        if (res.status === 'exact') {
                            this.addToCart(res.data);
                            BizAlert.toast(`Added ${res.data.product_name}`, 'success');
                        } else {
                            BizAlert.toast('Product not found or out of stock.', 'error');
                        }
                    } catch (error) {
                        console.error(error);
                    }
                },

                // ─────────────────────────────────────────────────────────
                // 4. CART MANAGEMENT & MATH
                // ─────────────────────────────────────────────────────────
                addToCart(product) {
                    let existingItem = this.cart.find(item => item.product_sku_id === product
                        .product_sku_id);

                    if (existingItem) {
                        existingItem.quantity++;
                    } else {
                        this.cart.unshift({
                            product_sku_id: product.product_sku_id || product.unique_id,
                            product_id: product.product_id || product.id,
                            product_name: product.name || product.product_name,
                            unit_price: parseFloat(product.unit_price || product.display_price || product.price || 0),
                            unit_id: product.unit_id,
                            unit_name: product.unit_name,
                            quantity: 1,
                            tax_percent: parseFloat(product.tax_percent || 0),
                            tax_type: product.tax_type || 'exclusive',
                            stock: product.stock || 999,
                            // 🌟 SAVE THE IMAGE URL FOR THE CART UI
                            image_url: product.image_url || '' 
                        });
                    }
                    this.calculateCart();
                },

                updateQty(index, change) {
                    let newQty = this.cart[index].quantity + change;
                    if (newQty <= 0) {
                        this.cart.splice(index, 1);
                    } else {
                        this.cart[index].quantity = newQty;
                    }
                    this.calculateCart();
                },

                calculateCart() {
                    let subAcc = 0;
                    let taxAcc = 0;

                    this.cart.forEach(item => {
                        let base = item.quantity * item.unit_price;
                        let tax = 0;
                        let taxable = base;

                        if (item.tax_type === 'inclusive') {
                            taxable = base / (1 + (item.tax_percent / 100));
                            tax = base - taxable;
                        } else {
                            tax = base * (item.tax_percent / 100);
                        }

                        subAcc += taxable;
                        taxAcc += tax;
                    });

                    let discountAmt = 0;
                    let discVal = parseFloat(this.totals.discount_value) || 0;

                    if (this.totals.discount_type === 'percent') {
                        discountAmt = subAcc * (discVal / 100);
                    } else {
                        discountAmt = discVal;
                    }

                    let afterDiscount = Math.max(0, subAcc - discountAmt);
                    let rawTotal = afterDiscount + taxAcc;

                    this.totals.subtotal = subAcc;
                    this.totals.discount_amount = discountAmt;
                    this.totals.tax = taxAcc;
                    this.totals.payable = Math.round(rawTotal);
                    this.totals.round_off = (this.totals.payable - rawTotal).toFixed(2);

                    this.calculatePayment();

                    localStorage.setItem('pos_cart', JSON.stringify(this.cart));
                },

                // ─────────────────────────────────────────────────────────
                // 5. CHECKOUT LOGIC
                // ─────────────────────────────────────────────────────────
                handlePaymentChange(e) {
                    let selectedOption = e.target.options[e.target.selectedIndex];
                    this.payment.method_name = selectedOption.text;
                    let slug = selectedOption.getAttribute('data-slug') || '';

                    // If they selected the empty "Unpaid" option
                    if (!this.payment.method_id) {
                        this.payment.received = 0;
                    }
                    // If digital payment, auto-fill full amount
                    else if (slug !== 'cash' && this.payment.method_name.toLowerCase() !== 'cash') {
                        this.payment.received = this.totals.payable;
                    }
                    // If cash, clear it so they type manually
                    else {
                        this.payment.received = '';
                    }

                    this.calculatePayment();
                },

                calculatePayment() {
                    let payable = parseFloat(this.totals.payable) || 0;

                    // Allow 0 received if no payment method is selected
                    if (!this.payment.method_id) {
                        this.payment.received = 0;
                    }

                    let received = parseFloat(this.payment.received) || 0;

                    if (payable === 0) {
                        this.payment.change = received;
                        this.payment.due = 0;
                        return;
                    }

                    let diff = received - payable;

                    if (diff >= 0) {
                        this.payment.change = diff;
                        this.payment.due = 0;
                    } else {
                        this.payment.change = 0;
                        this.payment.due = Math.abs(diff);
                    }
                },
                // ─────────────────────────────────────────────────────────
                // RECEIPT & PRINTING LOGIC
                // ─────────────────────────────────────────────────────────
                closeReceiptModal() {
                    this.isReceiptModalOpen = false;
                    this.currentReceiptUrl = '';

                    // NOW we clear the cart and prepare for the next customer
                    this.cart = [];
                    this.payment.received = '';
                    this.customer.id = ''; // Optional: Reset to Walk-in
                    this.customer.name = 'Guest';
                    this.clientSearchTerm = '';
                    this.calculateCart();
                },

                printReceipt() {
                    const frame = document.getElementById('receiptFrame');
                    if (frame) {
                        frame.contentWindow.focus();
                        frame.contentWindow.print();
                    }
                },

                async placeOrder() {
                    if (this.cart.length === 0) {
                        BizAlert.toast('Cart is empty', 'error');
                        return;
                    }

                    // 🌟 SMART UNPAID/CREDIT WARNING
                    if (this.payment.due > 0) {
                        let confirmUnpaid = await Swal.fire({
                            title: 'Generate Unpaid Bill?',
                            text: `There is a pending due of ₹${this.payment.due}. Generate as Unpaid/Credit?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#f59e0b', // Amber color for warning
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Yes, Generate Unpaid'
                        });

                        // If the cashier clicks cancel, stop the checkout
                        if (!confirmUnpaid.isConfirmed) {
                            return;
                        }
                    }

                    this.isProcessing = true;
                    BizAlert.loading('Processing Order...');

                    const payload = {
                        warehouse_id: this.warehouse_id,
                        customer_id: this.customer.id || null,
                        customer_name: this.customer.id ? null : this.customer.name,
                        payment_method_id: this.payment.method_id,
                        amount_received: this.payment.received,
                        items: this.cart,
                        // 🌟 WE MUST ACTUALLY SEND THE DISCOUNT TO THE SERVER
                        discount_type: this.totals.discount_type,
                        discount_value: this.totals.discount_value,
                        discount_amount: this.totals.discount_amount
                    };

                    try {
                        let response = await fetch('/admin/pos/store', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });

                        let data = await response.json();

                        if (!response.ok) throw new Error(data.message || 'Checkout Failed');

                        BizAlert.toast('Order Placed Successfully!', 'success');

                        this.currentReceiptUrl = `/admin/pos/receipt/${data.invoice_id}`;
                        this.isReceiptModalOpen = true;
                        this.isMobileCartOpen = false; // Close mobile drawer behind the receipt

                        this.cart = [];
                        this.payment.received = '';
                        this.calculateCart();

                    } catch (error) {
                        console.error(error);
                        BizAlert.toast(error.message, 'error');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                formatCurrency(val) {
                    return '₹' + parseFloat(val).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }));
        });
    </script>

</body>

</html>
