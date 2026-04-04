<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="company-slug" content="{{ request()->route('slug') ?? '' }}">

    @php
        try {
            $user = Auth::user();
            $primary = get_setting('primary_color') ?: '#008a62';
            $hover = get_setting('primary_hover_color') ?: '#007050';
            
            // 🛡️ Fallback safe company object
            $company = $company ?? new \App\Models\Company(['name' => 'StoreFront', 'slug' => request()->route('slug') ?? 'store']);
            $companySlug = $company->slug;

            // 🌟 Auto-Fill Logic
            $autoFill = [
                'name'    => '',
                'phone'   => '',
                'email'   => '',
                'address' => '',
                'notes'   => ''
            ];

            if ($user) {
                $client = \App\Models\Client::where('company_id', $company->id ?? null)
                                            ->where('user_id', $user->id)
                                            ->first();
                                            
                $autoFill = [
                    'name'    => $client?->name ?? $user->name ?? '',
                    'phone'   => $client?->phone ?? $user->phone ?? '',
                    'email'   => $user->email ?? '',
                    'address' => $client?->address ?? '',
                    'notes'   => ''
                ];
                
                if ($client?->city || $client?->zip_code) {
                    $append = implode(', ', array_filter([$client?->city, $client?->zip_code]));
                    if ($append) {
                         $autoFill['address'] .= ($autoFill['address'] ? ', ' : '') . $append;
                    }
                }
            }
        } catch (\Throwable $e) {
            // 🛡️ Fail-safe logging prevents 500 errors
            \Illuminate\Support\Facades\Log::error('Storefront Layout Error: ' . $e->getMessage());
            
            // Emergency defaults
            $primary = '#008a62';
            $hover = '#007050';
            $companySlug = request()->route('slug') ?? 'store';
            $autoFill = ['name' => '', 'phone' => '', 'email' => '', 'address' => '', 'notes' => ''];
        }
    @endphp

    <title>@yield('title', get_setting('seo_title', 'Qlinkon Shop'))</title>
    @yield('meta')

    @if (get_setting('favicon'))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . get_setting('favicon')) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- @vite(['resources/css/storefront.css', 'resources/js/storefront.js']) --}}
    
        <script src="{{ asset('assets/js/tailwind.min.js') }}"></script>    
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            brand: {
                                50: 'var(--color-brand-50)',
                                100: 'var(--color-brand-100)',
                                500: 'var(--brand-500)',
                                600: 'var(--brand-600)',
                                700: 'var(--brand-700)',
                            }
                        }
                    }
                }
            }
        </script>
        <script src="{{ asset('assets/js/lucide.min.js') }}"></script>
        <script defer src="{{ asset('assets/js/sweetalert2.js') }}"></script>
        <script defer src="{{ asset('assets/js/alpinejs.min.js') }}"></script>        

    <style>   
        :root {
            --brand-500: {{ $primary }};
            --brand-600: {{ $hover }};
            --font-sans: "Poppins", sans-serif;
            --color-brand-50: color-mix(in srgb, var(--brand-500) 10%, white);
            --color-brand-100: color-mix(in srgb, var(--brand-500) 20%, white);
            --color-brand-500: var(--brand-500);
            --color-brand-600: var(--brand-600);
            --color-brand-700: var(--brand-700);
        }

        body {
            background-color: #ffffff;
            color: #1f2937;
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

        .co-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 5px;
        }

        .co-input {
            width: 100%;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 9px 13px;
            font-size: 13px;
            color: #1f2937;
            outline: none;
            font-family: inherit;
            background: #fff;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .co-input:focus {
            border-color: var(--brand-600);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-600) 10%, transparent);
        }

        .co-input.error {
            border-color: #f43f5e;
        }
    </style>

    <script defer src="{{ asset('assets/js/alpinejs.min.js') }}"></script>
    @stack('styles')
</head>

<body class="antialiased font-sans flex flex-col min-h-screen" x-data="{
    cartOpen: false,
    qrScannerOpen: false,
    cartView: 'cart',
    cartItems: [],
    cartCount: 0,
    cartSubtotal: '0.00',
    cartTotal: '0.00',

    // 🌟 Inject the safe Laravel variable directly into Alpine
    form: @js($autoFill),
    formErrors: {},
    isSubmitting: false,
    orderResult: {},

    mobileMenuOpen: false,

    initCart() {
        window.__alpineCart = this;
        this.syncFromStorage();

        // Re-run Lucide whenever cartView changes — covers all x-if template swaps
        this.$watch('cartView', () => {
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
                console.log('[Lucide] Icons re-initialized for view:', this.cartView);
            });
        });

        // Re-run on cartOpen too — covers the shopping-bag icon in header
        this.$watch('cartOpen', (val) => {
            if (val) {
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            }
        });

        console.log('[Cart] Alpine ready');
    },

    syncFromStorage() {
        this.cartItems = window.getCart ? window.getCart() : [];
        const totals = window.getCartTotals ? window.getCartTotals() : { count: 0, subtotal: '0.00', total: '0.00' };
        this.cartCount = totals.count;
        this.cartSubtotal = totals.subtotal;
        this.cartTotal = totals.total;
    },

    removeItem(skuId) {
        window.removeFromCart(skuId);
        this.syncFromStorage();
    },
    changeQty(skuId, qty) {
        window.updateCartQty(skuId, qty);
        this.syncFromStorage();
    },

    openCart() {
        this.cartView = 'cart';
        this.cartOpen = true;
    },

    goToCheckout() {
        this.formErrors = {};
        this.cartView = 'checkout';
        this.$nextTick(() => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    },

    validateForm() {
        const e = {};
        if (!this.form.name.trim()) e.name = 'Name is required';
        if (!this.form.phone.trim()) e.phone = 'Phone number is required';
        if (this.form.phone.trim() && !/^[6-9]\d{9}$/.test(this.form.phone.trim()))
            e.phone = 'Enter a valid 10-digit mobile number';
        if (!this.form.address.trim()) e.address = 'Delivery address is required';
        this.formErrors = e;
        return Object.keys(e).length === 0;
    },

    async submitOrder() {
        if (!this.validateForm()) return;
        this.isSubmitting = true;
        this.formErrors = {};

        const result = await window.placeOrder(this.form);
        this.isSubmitting = false;

        if (result.success) {
            this.orderResult = result;
            this.cartView = 'success';
            this.syncFromStorage();
            // Re-init icons after Alpine renders the success view
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        } else {
            this.formErrors.server = result.message;
        }
    },

    continueShopping() {
        this.cartOpen = false;
        this.cartView = 'cart';
        this.orderResult = {};
        this.form = { name: '', phone: '', email: '', address: '', city: '', state: '', pincode: '', notes: '' };
    },
    showToast(productName) {
    // Simple toast — no library needed
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] flex items-center gap-2.5 bg-gray-900 text-white text-sm font-semibold px-4 py-3 rounded-2xl shadow-xl transition-all duration-300 opacity-0 translate-y-2';
        toast.innerHTML = `
            <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 text-green-400 flex-shrink-0' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2.5'>
                <path stroke-linecap='round' stroke-linejoin='round' d='M5 13l4 4L19 7'/>
            </svg>
            <span>${productName} added to cart</span>
        `;
        document.body.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(-50%) translateY(0)';
            });
        });

        // Animate out and remove
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(8px)';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    },
}" x-init="initCart()">

    {{-- ════════ HEADER ════════ --}}
    <header class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-sm">
        <div
            class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 h-[72px] flex items-center justify-between gap-4 sm:gap-8">

           {{-- Logo directly on the left --}}
            <a href="/{{ $companySlug }}" class="flex items-center gap-2.5 shrink-0">
                @if (get_setting('icon'))
                    <img src="{{ asset('storage/' . get_setting('icon')) }}" alt="Store Logo" class="h-10 object-contain">
                @else
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded flex items-center justify-center text-white shadow-sm"
                        style="background: var(--brand-600);">
                        <i data-lucide="store" class="w-5 h-5 fill-current"></i>
                    </div>
                    <span class="font-bold text-[18px] sm:text-[22px] text-gray-800 tracking-tight">
                        {{ $company->name ?? 'StoreFront' }}
                    </span>
                @endif
            </a>

            <div class="flex-1 max-w-3xl hidden md:block relative" x-data="searchDropdown()">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none z-10"></i>
                <input type="text"
                    x-model="query"
                    @input.debounce.300ms="suggest()"
                    @keydown.enter="goToSearch()"
                    @focus="open = results.length > 0"
                    @click.away="open = false"
                    placeholder="Search for products..."
                    class="w-full bg-[#f3f4f6] rounded-full py-2.5 pl-11 pr-10 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-shadow">

                {{-- Clear button ── --}}
                <button x-show="query.length > 0" @click="query = ''; results = []; open = false"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>

                {{-- Dropdown ── --}}
                <div x-show="open" x-cloak
                    class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50">

                    {{-- Loading ── --}}
                    <template x-if="loading">
                        <div class="flex items-center gap-2 px-4 py-3 text-sm text-gray-400">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                            Searching...
                        </div>
                    </template>

                    {{-- Results ── --}}
                    <template x-if="!loading && results.length > 0">
                        <div>
                            <div class="px-4 py-2 border-b border-gray-50">
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider"
                                    x-text="results.length + ' results for &quot;' + query + '&quot;'"></p>
                            </div>
                            <template x-for="product in results" :key="product.slug">
                                <a :href="'/' + companySlug + '/product/' + product.slug"
                                    class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors">
                                    <img :src="product.image"
                                        class="w-10 h-10 rounded-lg object-cover flex-shrink-0 bg-gray-100 border border-gray-100"
                                        onerror="this.src='{{ asset('assets/images/no-product.png') }}'">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13px] font-semibold text-gray-800 truncate" x-text="product.name"></p>
                                        <p class="text-[12px] text-gray-400 font-medium">
                                            ₹<span x-text="parseFloat(product.price).toFixed(2)"></span>
                                        </p>
                                    </div>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-gray-300 flex-shrink-0"></i>
                                </a>
                            </template>

                            {{-- View all results ── --}}
                            <div class="px-4 py-2.5 border-t border-gray-50">
                                <button @click="goToSearch()"
                                    class="w-full text-center text-[12px] font-bold py-1.5 rounded-lg transition-colors"
                                    style="color: var(--brand-600);">
                                    View all results for "<span x-text="query"></span>"
                                    <i data-lucide="arrow-right" class="w-3 h-3 inline ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- No results ── --}}
                    <template x-if="!loading && results.length === 0 && query.length >= 2">
                        <div class="px-4 py-4 text-center text-sm text-gray-400">
                            <i data-lucide="search-x" class="w-6 h-6 mx-auto mb-1.5 opacity-40"></i>
                            <p class="font-semibold">No products found for "<span x-text="query"></span>"</p>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center gap-4 shrink-0 text-gray-600">
                {{-- 1. QR Scanner Button --}}
                <button @click="qrScannerOpen = true; startScanner();" class="hover:text-brand-600 transition-colors">
                    <i data-lucide="qr-code" class="w-[22px] h-[22px]"></i>
                </button>
                <a href="
                @auth
                    @if($user->hasRole('customer'))
                        {{ route('storefront.portal.dashboard', ['slug' => $companySlug]) }}
                    @elseif($user->hasRole('owner'))
                        {{ route('admin.dashboard') }}
                    @elseif($user->hasRole('employee'))
                        {{ route('admin.hrm.employee.dashboard') }}
                    @else
                        {{ route('admin.dashboard') }}
                    @endif
                @else
                    {{ route('storefront.login',['slug' => $companySlug]) }}
                @endauth    
                " class="hover:text-gray-900 transition-colors sm:block">
                    <i data-lucide="user" class="w-[22px] h-[22px]"></i>
                </a>
                <button @click="openCart()" class="hover:text-gray-900 transition-colors relative">
                    <i data-lucide="shopping-bag" class="w-[22px] h-[22px]"></i>
                    <span
                        class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full"
                        x-show="cartCount > 0" x-text="cartCount > 9 ? '9+' : cartCount"></span>
                </button>
            </div>
        </div>
    </header>

    {{-- Mobile search ── --}}
    <div class="md:hidden px-4 py-3 bg-white border-b border-gray-100 shadow-sm z-30 relative"
            x-data="searchDropdown()">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none"></i>
                <input type="text"
                    x-model="query"
                    @input.debounce.300ms="suggest()"
                    @keydown.enter="goToSearch()"
                    @click.away="open = false"
                    placeholder="Search for products..."
                    class="w-full bg-[#f3f4f6] rounded-full py-2 pl-10 pr-4 text-sm focus:outline-none">
            </div>

            {{-- Mobile dropdown ── --}}
            <div x-show="open" x-cloak
                class="absolute left-4 right-4 top-full mt-1 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50">
                <template x-if="!loading && results.length > 0">
                    <div>
                        <template x-for="product in results" :key="product.slug">
                            <a :href="'/' + companySlug + '/product/' + product.slug"
                                class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors">
                                <img :src="product.image"
                                    class="w-9 h-9 rounded-lg object-cover flex-shrink-0 bg-gray-100"
                                    onerror="this.src='{{ asset('assets/images/no-product.png') }}'">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] font-semibold text-gray-800 truncate" x-text="product.name"></p>
                                    <p class="text-[12px] text-gray-400">₹<span x-text="parseFloat(product.price).toFixed(2)"></span></p>
                                </div>
                            </a>
                        </template>
                        <div class="px-4 py-2 border-t border-gray-50">
                            <button @click="goToSearch()" class="w-full text-center text-[12px] font-bold py-1"
                                style="color: var(--brand-600);">
                                View all results →
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    {{-- ════════════════════════════════════════
         CART DRAWER
    ════════════════════════════════════════ --}}
    <div x-cloak x-show="cartOpen" class="relative z-50" role="dialog" aria-modal="true">

        {{-- Backdrop ── --}}
        <div x-show="cartOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500/50 transition-opacity" @click="cartOpen = false">
        </div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="cartOpen" x-transition:enter="transform transition ease-in-out duration-300"
                        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-300"
                        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                        class="pointer-events-auto w-screen max-w-[420px]">

                        <div class="flex h-full flex-col bg-white shadow-xl">

                            {{-- ── Drawer header ── --}}
                            <div
                                class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
                                <template x-if="cartView === 'cart'">
                                    <h2 class="flex items-center text-base font-bold text-gray-900">
                                        <i data-lucide="shopping-bag" class="w-5 h-5 mr-2 text-gray-400"></i>
                                        My Cart
                                        <span class="text-gray-400 font-medium text-sm ml-1.5"
                                            x-text="'(' + cartCount + ')'"></span>
                                    </h2>
                                </template>
                                <template x-if="cartView === 'checkout'">
                                    <div class="flex items-center gap-2">
                                        <button @click="cartView = 'cart'"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-500 transition-colors">
                                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                                        </button>
                                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                            <i data-lucide="clipboard-list" class="w-4 h-4 text-gray-400"></i>
                                            Checkout
                                        </h2>
                                    </div>
                                </template>
                                <template x-if="cartView === 'success'">
                                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                        <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                                        Order Placed!
                                    </h2>
                                </template>
                                <button @click="cartOpen = false"
                                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                            {{-- ════════ VIEW: CART ════════ --}}
                            <template x-if="cartView === 'cart'">
                                <div class="flex flex-col flex-1 min-h-0">
                                    <div class="flex-1 overflow-y-auto no-scrollbar px-4 py-4 sm:px-5">

                                        <template x-if="cartItems.length === 0">
                                            <div
                                                class="flex flex-col items-center justify-center h-full py-12 text-center">
                                                <div
                                                    class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                                                    <i data-lucide="shopping-bag" class="w-7 h-7 text-gray-300"></i>
                                                </div>
                                                <p class="font-semibold text-gray-500 mb-1">Your cart is empty</p>
                                                <p class="text-sm text-gray-400 mb-4">Add products to get started</p>
                                                <button @click="cartOpen = false"
                                                    class="text-sm font-bold px-4 py-2 rounded-xl text-white"
                                                    style="background: var(--brand-600);">
                                                    Continue Shopping
                                                </button>
                                            </div>
                                        </template>

                                        <template x-if="cartItems.length > 0">
                                            <div class="space-y-3">
                                                <template x-for="item in cartItems" :key="item.sku_id">
                                                    <div class="flex gap-3 bg-gray-50 rounded-xl p-3">
                                                        <img :src="item.image"
                                                            class="w-16 h-16 rounded-lg object-cover flex-shrink-0 bg-white border border-gray-100"
                                                            onerror="this.src='{{ asset('assets/images/no-product.png') }}'">
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-[13px] font-semibold text-gray-800 line-clamp-1 mb-0.5"
                                                                x-text="item.name"></p>
                                                            <p class="text-[11px] text-gray-400 mb-1.5"
                                                                x-show="item.variant" x-text="item.variant"></p>
                                                            <div class="flex items-center justify-between">
                                                                <div
                                                                    class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white">
                                                                    <button
                                                                        @click="changeQty(item.sku_id, item.qty - 1)"
                                                                        class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-50 text-sm font-bold">−</button>
                                                                    <span class="w-8 text-center text-[13px] font-bold"
                                                                        x-text="item.qty"></span>
                                                                    <button
                                                                        @click="changeQty(item.sku_id, item.qty + 1)"
                                                                        class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-50 text-sm font-bold">+</button>
                                                                </div>
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-[13px] font-bold text-gray-900"
                                                                        x-text="'₹' + (item.price * item.qty).toFixed(2)"></span>
                                                                    <button @click="removeItem(item.sku_id)"
                                                                        class="w-6 h-6 flex items-center justify-center text-red-400 hover:text-red-600">
                                                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="border-t border-gray-100 px-5 py-5 bg-gray-50/50 flex-shrink-0">
                                        <div class="space-y-2 mb-4">
                                            <div class="flex justify-between text-sm text-gray-500 font-medium">
                                                <span>Subtotal</span>
                                                <span class="text-gray-800 font-semibold font-mono"
                                                    x-text="'₹ ' + cartSubtotal"></span>
                                            </div>
                                            <div
                                                class="flex justify-between text-base font-bold text-gray-900 border-t border-gray-200 pt-2">
                                                <span>Total</span>
                                                <span class="font-mono" x-text="'₹ ' + cartTotal"></span>
                                            </div>
                                        </div>
                                        <button @click="goToCheckout()" :disabled="cartItems.length === 0"
                                            class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl text-[15px] font-bold text-white transition-all"
                                            style="background: var(--brand-600);"
                                            :class="cartItems.length === 0 ? 'opacity-50 cursor-not-allowed' :
                                                'hover:opacity-90'">
                                            Checkout Now <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            {{-- ════════ VIEW: CHECKOUT ════════ --}}
                            <template x-if="cartView === 'checkout'">
                                <div class="flex flex-col flex-1 min-h-0">
                                    <div class="flex-1 overflow-y-auto no-scrollbar px-5 py-4">

                                        {{-- Order summary strip ── --}}
                                        <div
                                            class="bg-gray-50 rounded-xl px-4 py-3 mb-5 flex items-center justify-between">
                                            <span class="text-sm text-gray-600 font-medium flex items-center gap-2">
                                                <i data-lucide="shopping-bag" class="w-4 h-4 text-gray-400"></i>
                                                <span
                                                    x-text="cartCount + (cartCount === 1 ? ' item' : ' items')"></span>
                                            </span>
                                            <span class="text-sm font-bold text-gray-900 font-mono"
                                                x-text="'₹ ' + cartTotal"></span>
                                        </div>

                                        {{-- Server error ── --}}
                                        <template x-if="formErrors.server">
                                            <div
                                                class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-start gap-2">
                                                <i data-lucide="alert-circle"
                                                    class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5"></i>
                                                <p class="text-[13px] font-semibold text-red-700"
                                                    x-text="formErrors.server"></p>
                                            </div>
                                        </template>

                                        {{-- Form ── --}}
                                        <div class="space-y-4">
                                            <div>
                                                <label class="co-label">Full Name <span
                                                        class="text-red-500">*</span></label>
                                                <input type="text" x-model="form.name"
                                                    placeholder="Your full name" class="co-input"
                                                    :class="formErrors.name ? 'error' : ''">
                                                <template x-if="formErrors.name">
                                                    <p class="text-[11px] font-semibold text-red-500 mt-1"
                                                        x-text="formErrors.name"></p>
                                                </template>
                                            </div>

                                            <div>
                                                <label class="co-label">Mobile Number <span
                                                        class="text-red-500">*</span></label>
                                                 <input 
                                                        type="tel"
                                                        x-model="form.phone"
                                                        placeholder="10-digit mobile number"
                                                        maxlength="10"
                                                        minlength="10"
                                                        pattern="[0-9]{10}"
                                                        inputmode="numeric"
                                                        class="co-input"
                                                        :class="formErrors.phone ? 'error' : ''"
                                                        @input="form.phone = form.phone.replace(/[^0-9]/g, '')"
                                                    >

                                                <template x-if="formErrors.phone">
                                                    <p class="text-[11px] font-semibold text-red-500 mt-1"
                                                        x-text="formErrors.phone"></p>
                                                </template>
                                            </div>

                                            <div>
                                                <label class="co-label">Delivery Address <span
                                                        class="text-red-500">*</span></label>
                                                <textarea x-model="form.address" placeholder="House/flat no, street, area, landmark" rows="2"
                                                    class="co-input resize-none" :class="formErrors.address ? 'error' : ''"></textarea>
                                                <template x-if="formErrors.address">
                                                    <p class="text-[11px] font-semibold text-red-500 mt-1"
                                                        x-text="formErrors.address"></p>
                                                </template>
                                            </div>                                         

                                            <div>
                                                <label class="co-label">Notes <span
                                                        class="text-gray-400 normal-case font-normal">(optional)</span></label>
                                                <textarea x-model="form.notes" placeholder="Special instructions, preferred delivery time..." rows="2"
                                                    class="co-input resize-none"></textarea>
                                            </div>

                                            <div
                                                class="flex items-center gap-3 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3">
                                                <i data-lucide="banknote"
                                                    class="w-4 h-4 text-blue-500 flex-shrink-0"></i>
                                                <div>
                                                    <p class="text-[12px] font-bold text-blue-800">Cash on Delivery</p>
                                                    <p class="text-[11px] text-blue-500 font-medium">Pay when you
                                                        receive your order</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-100 px-5 py-4 bg-gray-50/50 flex-shrink-0">
                                        <button @click="submitOrder()" :disabled="isSubmitting"
                                            class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl text-[15px] font-bold text-white transition-all"
                                            style="background: var(--brand-600);"
                                            :class="isSubmitting ? 'opacity-70 cursor-not-allowed' : 'hover:opacity-90'">
                                            <template x-if="isSubmitting">
                                                <span class="flex items-center gap-2">
                                                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                                    Placing Order...
                                                </span>
                                            </template>
                                            <template x-if="!isSubmitting">
                                                <span class="flex items-center gap-2">
                                                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                                                    Place Order
                                                </span>
                                            </template>
                                        </button>
                                        <p class="text-center text-[11px] text-gray-400 font-medium mt-2">
                                            We'll call you to confirm before dispatch
                                        </p>
                                    </div>
                                </div>
                            </template>

                            {{-- ════════ VIEW: SUCCESS ════════ --}}
                            <template x-if="cartView === 'success'">
                                <div class="flex flex-col flex-1 min-h-0">
                                    <div class="flex-1 overflow-y-auto no-scrollbar px-5 py-6">

                                        <div class="flex flex-col items-center text-center mb-6">
                                            <div
                                                class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mb-4">
                                                <i data-lucide="check" class="w-10 h-10 text-green-500"></i>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-900 mb-1">Order Placed!</h3>
                                            <p class="text-sm text-gray-500 font-medium">
                                                Thank you! We'll confirm your order shortly.
                                            </p>
                                        </div>

                                        <div class="bg-gray-50 rounded-2xl p-4 mb-5 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span
                                                    class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Order
                                                    Number</span>
                                                   <a :href="`{{ url($companySlug.'/orders') }}/${orderResult.order_number}`">
                                                        <span class="font-mono font-bold text-gray-900 text-sm"
                                                            x-text="orderResult.order_number"></span>
                                                    </a>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span
                                                    class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Total</span>
                                                <span class="font-bold text-gray-900 text-sm"
                                                    x-text="orderResult.total"></span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span
                                                    class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Payment</span>
                                                <span class="text-sm font-bold text-gray-700">Cash on Delivery</span>
                                            </div>
                                        </div>

                                        <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 mb-5">
                                            <p class="text-[13px] font-semibold text-blue-800 flex items-center gap-2">
                                                <i data-lucide="phone" class="w-4 h-4"></i>
                                                We'll contact <span x-text="form.phone"
                                                    class="font-mono font-bold mx-1"></span> to confirm
                                            </p>
                                        </div>

                                        <template x-if="orderResult.whatsapp_url">
                                            <a :href="orderResult.whatsapp_url" target="_blank" rel="noopener"
                                                class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-[14px] font-bold text-white bg-[#25d366] hover:bg-[#1eb858] transition-colors mb-3">
                                                <i data-lucide="message-circle" class="w-4 h-4 fill-current"></i>
                                                Notify Owner on WhatsApp
                                            </a>
                                        </template>
                                        <template x-if="orderResult.receipt_url">
                                            <a :href="orderResult.receipt_url" target="_blank" rel="noopener"
                                                class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-[14px] font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors mb-3">
                                                <i data-lucide="file-down" class="w-4 h-4"></i>
                                                Download Receipt
                                            </a>
                                        </template>
                                    </div>

                                    <div class="border-t border-gray-100 px-5 py-4 flex-shrink-0">
                                        <button @click="continueShopping()"
                                            class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl text-[15px] font-bold text-white hover:opacity-90 transition-all"
                                            style="background: var(--brand-600);">
                                            <i data-lucide="store" class="w-4 h-4"></i>
                                            Continue Shopping
                                        </button>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ MAIN ════════ --}}
    <main class="flex-1 flex flex-col min-w-0">
        @yield('content')
    </main>

    {{-- ════════ FOOTER ════════ --}}
    <footer class="bg-[#f8f9fa] border-t border-gray-200 pt-16 pb-8 mt-auto">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
               <div>
                    <h4 class="text-[15px] font-bold text-gray-900 mb-5">Contact Us</h4>
                    <ul class="space-y-4 text-[13px] text-gray-500 font-medium">
                        {{-- 🛡️ Fallback dummy data if settings are empty --}}
                        @php
                            $phone = get_setting('call_number') ?: '+91 98765 43210';
                            $email = get_setting('email') ?: 'support@' . ($companySlug ?? 'store') . '.com';
                            $address = get_setting('address') ?: '123 Commerce Avenue, Business District, 400001';
                        @endphp
                        
                        <li class="flex items-start gap-3">
                            <i data-lucide="phone" class="w-4 h-4 shrink-0 mt-0.5 text-gray-400"></i>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="mail" class="w-4 h-4 shrink-0 mt-0.5 text-gray-400"></i>
                            <a href="mailto:{{ $email }}">{{ $email }}</a>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="w-4 h-4 shrink-0 mt-0.5 text-gray-400"></i>
                            <span class="leading-relaxed">{{ $address }}</span>
                        </li>
                    </ul>
                    
                    {{-- Social Icons (Left as-is) --}}
                    <div class="flex items-center gap-3 mt-6">
                        @if (get_setting('whatsapp'))
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', get_setting('whatsapp')) }}" target="_blank"
                                class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-500 hover:border-brand-500 transition-colors">
                                <i data-lucide="message-circle" class="w-4 h-4"></i></a>
                        @endif
                        @if (get_setting('instagram'))
                            <a href="{{ get_setting('instagram') }}" target="_blank"
                                class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-500 hover:border-brand-500 transition-colors">
                                <i data-lucide="instagram" class="w-4 h-4"></i></a>
                        @endif
                    </div>
                </div>

                <div>
                    <h4 class="text-[15px] font-bold text-gray-900 mb-5">My Account</h4>
                    <ul class="space-y-3 text-[13px] text-gray-500 font-medium">
                        <li><a href="{{ route('storefront.portal.dashboard', ['slug' => $companySlug]) }}" class="hover:text-brand-500 transition-colors">Dashboard</a></li>
                        <li><a href="{{ route('storefront.portal.orders', ['slug' => $companySlug]) }}" class="hover:text-brand-500 transition-colors">My Orders</a></li>
                        <li><a href="{{ route('storefront.portal.profile', ['slug' => $companySlug]) }}" class="hover:text-brand-500 transition-colors">My Profile</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-[15px] font-bold text-gray-900 mb-5">Our Service</h4>
                    <ul class="space-y-3 text-[13px] text-gray-500 font-medium">
                        <li><a href="{{ route('storefront.page.show', [$company->slug, 'return-policy']) }}" class="hover:text-brand-500 transition-colors">Return Policy</a></li>
                        <li><a href="{{ route('storefront.page.show', [$company->slug, 'faq']) }}" class="hover:text-brand-500 transition-colors">FAQ</a></li>
                        <li><a href="{{ route('storefront.page.show', [$company->slug, 'privacy-policy']) }}" class="hover:text-brand-500 transition-colors">Privacy & Policy</a></li>
                        <li><a href="{{ route('storefront.page.show', [$company->slug, 'terms-and-conditions']) }}" class="hover:text-brand-500 transition-colors">Terms & Conditions</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-[15px] font-bold text-gray-900 mb-5">Information</h4>
                    <ul class="space-y-3 text-[13px] text-gray-500 font-medium">
                        <li>
                            <a href="{{ route('storefront.page.show', [$company->slug, 'about-us']) }}" 
                            class="hover:text-brand-500 transition-colors">
                            About Us
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('storefront.page.show', [$company->slug, 'contact-us']) }}" 
                            class="hover:text-brand-500 transition-colors">
                            Contact Us
                            </a>
                        </li>

                        <li>
                            <a href="" 
                            class="hover:text-brand-500 transition-colors">
                            Blogs
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-[12px] text-gray-500 font-medium tracking-wide">
                    &copy; {{ date('Y') }},
                    <span class="font-bold"
                        style="color: var(--brand-600);">{{ $company->name ?? config('app.name') }}</span>
                </p>
                <div class="flex items-center gap-4">
                    <div class="flex gap-2 opacity-80">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 374.685 35.78" class="h-6 w-auto max-w-full" aria-label="Payment Methods">
                            <g id="Pyment" transform="translate(-0.365 -0.365)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="374.685" height="35.78" viewBox="0 0 374.685 35.78">
                                    <g id="Pyment_inner" transform="translate(-0.365 -0.365)">
                                        <path id="Path_58" d="M139.174.7H76.1a1.909,1.909,0,0,0-1.472.631A1.909,1.909,0,0,0,74,2.8V33.707a2.156,2.156,0,0,0,2.1,2.1h63.072a2.156,2.156,0,0,0,2.1-2.1V2.8a1.909,1.909,0,0,0-.631-1.472A1.909,1.909,0,0,0,139.174.7Z" transform="translate(80.805 0)" fill="#fff" stroke="#e0e0e0" stroke-width="0.67"></path>
                                        <path id="Path_59" d="M92.012,25.134A11.3,11.3,0,0,0,97.9,23.452a9.1,9.1,0,0,0,3.784-4.625,10.9,10.9,0,0,0,.631-6.307A10.8,10.8,0,0,0,93.9,4.11a10.05,10.05,0,0,0-6.1.631,10.28,10.28,0,0,0-4.625,3.784,12.67,12.67,0,0,0-1.682,6.1,9.862,9.862,0,0,0,3.154,7.358,9.543,9.543,0,0,0,7.358,3.154Z" transform="translate(89.073 3.528)" fill="#007bdb"></path>
                                        <path id="Path_60" d="M99.012,25.024a10.3,10.3,0,0,0,5.887-1.892,10.864,10.864,0,0,0,3.784-4.835,9.227,9.227,0,0,0,.631-5.887,9.35,9.35,0,0,0-2.943-5.256A10.721,10.721,0,0,0,100.9,4.21a10.05,10.05,0,0,0-6.1.631,10.28,10.28,0,0,0-4.625,3.784A11.3,11.3,0,0,0,88.5,14.512a8.532,8.532,0,0,0,.841,3.995,9.606,9.606,0,0,0,2.313,3.364,12.016,12.016,0,0,0,3.364,2.313,9.11,9.11,0,0,0,3.995.841Z" transform="translate(96.79 3.638)" fill="#e42b00"></path>
                                        <path id="Path_61" d="M91.654,20.537a12.3,12.3,0,0,0,3.154-7.569A12.82,12.82,0,0,0,91.654,5.4a8.8,8.8,0,0,0-2.313,3.364,9.412,9.412,0,0,0-.841,4.2,10.021,10.021,0,0,0,.841,4.2,8.8,8.8,0,0,0,2.313,3.364Z" transform="translate(96.79 5.181)" fill="#1740ce" fill-rule="evenodd"></path>
                                        <path id="Path_62" d="M102.474.7H39.4a1.909,1.909,0,0,0-1.472.631A1.909,1.909,0,0,0,37.3,2.8V33.707a2.156,2.156,0,0,0,2.1,2.1h63.072a2.156,2.156,0,0,0,2.1-2.1V2.8a1.909,1.909,0,0,0-.631-1.472A1.909,1.909,0,0,0,102.474.7Z" transform="translate(40.347 0)" fill="#fff" stroke="#e0e0e0" stroke-width="0.67"></path>
                                        <path id="Path_63" d="M55.422,25.024a10.3,10.3,0,0,0,5.887-1.892A10.864,10.864,0,0,0,65.093,18.3a10.947,10.947,0,0,0,.42-5.887A9.351,9.351,0,0,0,62.57,7.154,10.721,10.721,0,0,0,57.1,4.21a10.05,10.05,0,0,0-6.1.631,10.28,10.28,0,0,0-4.625,3.784A11.3,11.3,0,0,0,44.7,14.512a8.532,8.532,0,0,0,.841,3.995,9.606,9.606,0,0,0,2.313,3.364,12.016,12.016,0,0,0,3.364,2.313,10.022,10.022,0,0,0,4.2.841Z" transform="translate(48.505 3.638)" fill="#c00"></path>
                                        <path id="Path_64" d="M62.412,25.134A11.3,11.3,0,0,0,68.3,23.452a9.1,9.1,0,0,0,3.784-4.625,10.9,10.9,0,0,0,.631-6.307A10.8,10.8,0,0,0,64.3,4.11a10.05,10.05,0,0,0-6.1.631,10.279,10.279,0,0,0-4.625,3.784,12.67,12.67,0,0,0-1.682,6.1,9.862,9.862,0,0,0,3.154,7.358,9.543,9.543,0,0,0,7.358,3.154Z" transform="translate(56.442 3.528)" fill="#f90"></path>
                                        <path id="Path_65" d="M54.954,20.537a12.3,12.3,0,0,0,3.154-7.569A12.82,12.82,0,0,0,54.954,5.4a8.8,8.8,0,0,0-2.313,3.364,9.413,9.413,0,0,0-.841,4.2,8.532,8.532,0,0,0,.841,3.995,10.493,10.493,0,0,0,2.313,3.574Z" transform="translate(56.332 5.181)" fill="#f16d27" fill-rule="evenodd"></path>
                                        <path id="Path_66" d="M65.874.7H2.8a1.909,1.909,0,0,0-1.472.631A1.909,1.909,0,0,0,.7,2.8V33.707a1.909,1.909,0,0,0,.631,1.472A1.909,1.909,0,0,0,2.8,35.81H65.874a2.156,2.156,0,0,0,2.1-2.1V2.8a1.909,1.909,0,0,0-.631-1.472A2.271,2.271,0,0,0,65.874.7Z" transform="translate(0 0)" fill="#fff" stroke="#e0e0e0" stroke-width="0.67"></path>
                                        <path id="Path_67" d="M22.819,5.91,16.932,19.786H13.148L10.415,8.643c0-.21-.21-.42-.42-.631,0-.21-.21-.42-.42-.631A22.972,22.972,0,0,0,6,6.331V5.91h6.1a1.606,1.606,0,0,1,1.051.42,4.6,4.6,0,0,1,.631,1.051l1.472,7.989L19.035,5.91Zm14.717,9.251c0-3.574-5.046-3.784-5.046-5.466,0-.42.42-1.051,1.472-1.051a5.452,5.452,0,0,1,3.574.631l.631-2.943A8.716,8.716,0,0,0,34.8,5.7c-3.574,0-6.1,1.892-6.1,4.625,0,1.892,1.682,3.154,3.154,3.784,1.261.631,1.892,1.051,1.892,1.682,0,.841-1.051,1.261-2.1,1.261a6.91,6.91,0,0,1-3.574-.841l-.631,2.943a8.432,8.432,0,0,0,3.995.631c3.784.21,6.1-1.682,6.1-4.625Zm9.251,4.625H50.15L47.207,5.91H44.263c-.42,0-.631,0-.841.21a2.9,2.9,0,0,0-.631.841L37.536,19.786H41.32l.841-2.1h4.625ZM42.792,14.74l1.892-5.256,1.051,5.256ZM27.865,5.91,24.921,19.786H21.347L24.291,5.91h3.574Z" transform="translate(5.843 5.512)" fill="#1a1f71"></path>
                                        <path id="Path_68" d="M211.774.7H148.7a1.908,1.908,0,0,0-1.472.631A1.909,1.909,0,0,0,146.6,2.8V33.707a2.156,2.156,0,0,0,2.1,2.1h63.072a2.156,2.156,0,0,0,2.1-2.1V2.8a1.909,1.909,0,0,0-.631-1.472A2.271,2.271,0,0,0,211.774.7Z" transform="translate(160.838 0)" fill="#fff" stroke="#e0e0e0" stroke-width="0.67"></path>
                                        <path id="Path_69" d="M175.474.7H112.4a1.909,1.909,0,0,0-1.472.631A1.909,1.909,0,0,0,110.3,2.8V33.707a2.156,2.156,0,0,0,2.1,2.1h63.072a2.156,2.156,0,0,0,2.1-2.1V2.8a1.909,1.909,0,0,0-.631-1.472A2.271,2.271,0,0,0,175.474.7Z" transform="translate(120.822 0)" fill="#fff" stroke="#e0e0e0" stroke-width="0.67"></path>
                                        <path id="Path_70" d="M151.2,15.561s.21-1.261,1.051-4.2c.631-2.313,1.261-4.415,1.261-4.625l.21-.631h2.523c2.943,0,3.364,0,3.784.42.841.42,1.051,1.051.841,2.1a4.408,4.408,0,0,1-1.682,2.313c-.21.21-.42.21-.42.42a.651.651,0,0,0,.21.42l.42.42a2.321,2.321,0,0,1,0,1.682c0,.42-.21.841-.21,1.261v.631h-2.523c-.21-.21-.21-.42,0-1.261s.21-1.261,0-1.472-.42-.42-1.261-.42h-.841a13.374,13.374,0,0,0-.631,1.892l-.42,1.261h-1.051c-.21-.21-.631-.21-1.261-.21Zm6.307-5.256c.631-.21.841-.42.841-1.261v-.42c-.21-.21-.631-.21-1.682-.21h-.841l-.21.42a13.392,13.392,0,0,0-.42,1.472c0,.21,0,.21.21.21a5.274,5.274,0,0,0,2.1-.21Zm4.2,5.466a2.9,2.9,0,0,1-.841-.631c-.21-.631,0-1.682.841-4.835l.42-1.261h2.313l-.21.631a16.663,16.663,0,0,0-.841,3.574v.631c0,.21.841.42,1.261.21s.631-.631,1.682-3.995a1.092,1.092,0,0,1,.42-.841l.21-.21h2.1s-1.261,4.625-1.682,6.517v.21h-2.1c0-.21,0-.21.21-.21.21-.631,0-.631-.841,0a2.387,2.387,0,0,1-1.682.631c-.631-.21-1.051-.21-1.261-.42Zm6.938-.21a16.7,16.7,0,0,1,1.261-4.625l1.261-4.625h2.1c2.733,0,3.364,0,3.784.42a1.641,1.641,0,0,1,.841.841,2.666,2.666,0,0,1,.21,1.261,1.575,1.575,0,0,1-.21,1.051,4.41,4.41,0,0,1-2.733,2.733c-.42,0-1.051.21-1.472.21-1.892.21-1.892,0-1.892.42a13.388,13.388,0,0,0-.42,1.472l-.42,1.261H169.7c-.21-.21-.631-.21-1.051-.42Zm5.676-4.835a.772.772,0,0,0,.631-.21c.21-.21.42-.21.42-.42a.772.772,0,0,0,.21-.631V8.833c-.21-.21-.42-.42-1.472-.42h-1.051a6.138,6.138,0,0,0-.42,1.892v.21a4.629,4.629,0,0,0,1.682.21Zm3.574,5.046q-.946-.315-.631-1.892c.42-1.472,1.051-1.892,3.574-2.313,1.472-.21,1.892-.42,2.1-1.051s-.21-.42-.841-.42h-.631a.452.452,0,0,0-.42.42l-.21.21H180c-.841,0-1.261,0-1.261-.21.21-.42.42-.631.631-1.051a1.909,1.909,0,0,1,1.472-.631,5.834,5.834,0,0,1,3.784,0,1.641,1.641,0,0,1,.841.841c.21.21.21.631-.631,3.154a19.148,19.148,0,0,1-.631,2.523v.21h-2.1l-.21-.21c-.21-.42-.21-.42-1.051,0a6.139,6.139,0,0,1-1.892.42c-.21.21-.42,0-1.051,0Zm3.574-1.261.631-.631a1.264,1.264,0,0,0,.21-.841v-.42h-.42a2.387,2.387,0,0,0-1.682.631c-.21,0-.21.21-.42.21,0,.21-.21.21-.21.42s0,.42.21.42a4.629,4.629,0,0,0,1.682.21Zm3.364,4.2c-.21,0-.21-.21,0-.841s.21-.841.841-1.051a1.235,1.235,0,0,0,1.051-.42,17.036,17.036,0,0,0,0-4.625V9.043h2.313v4.2c.21.21.631-.631,2.313-3.784l.21-.42h2.1s-1.472,2.733-2.943,5.256c-2.1,3.574-2.523,4.2-3.574,4.415Z" transform="translate(165.909 5.953)" fill="#2a2c83" fill-rule="evenodd" opacity="0.94"></path>
                                        <path id="Path_71" d="M171.7,17.322,174.643,6.6l2.733,5.466Z" transform="translate(188.508 6.504)" fill="#097a44" fill-rule="evenodd"></path>
                                        <path id="Path_72" d="M170.8,17.322,173.743,6.6l2.733,5.466Z" transform="translate(187.516 6.504)" fill="#f46f20" fill-rule="evenodd"></path>
                                        <g id="Group_21" transform="translate(236.903 7.638)">
                                            <g id="Group_20" transform="translate(23.652 1.261)">
                                                <path id="Path_73" d="M126.4,13.851v6.307h-2.1V4.6h5.256a4.775,4.775,0,0,1,3.364,1.261,4.961,4.961,0,0,1,1.472,3.364,4.183,4.183,0,0,1-1.472,3.364,4.775,4.775,0,0,1-3.364,1.261Zm0-7.358v5.466h3.364a2.049,2.049,0,0,0,1.892-.841,2.665,2.665,0,0,0,0-3.784h0a2.283,2.283,0,0,0-1.892-.841Zm12.614,2.733a4.945,4.945,0,0,1,3.574,1.261,4.279,4.279,0,0,1,1.261,3.154v6.517H141.96V18.686h0a3.788,3.788,0,0,1-3.364,1.892,4.557,4.557,0,0,1-2.943-1.051A3.286,3.286,0,0,1,134.391,17a3,3,0,0,1,1.261-2.523,5.349,5.349,0,0,1,3.364-1.051,6.105,6.105,0,0,1,2.943.631V13.43a1.912,1.912,0,0,0-.841-1.682c-.631-.42-1.261-.841-1.892-.631a3.064,3.064,0,0,0-2.733,1.472l-1.682-1.051a4.246,4.246,0,0,1,4.2-2.313ZM136.494,17a1.5,1.5,0,0,0,.631,1.261,1.394,1.394,0,0,0,1.472.42,4.409,4.409,0,0,0,2.313-.841,2.919,2.919,0,0,0,1.051-2.1,4.508,4.508,0,0,0-2.523-.841,3.552,3.552,0,0,0-2.1.631A1.587,1.587,0,0,0,136.494,17Zm18.291-7.358-6.728,15.347h-2.1l2.523-5.466-4.2-9.881h2.1l3.154,7.569h0l3.154-7.569h2.1Z" transform="translate(-124.3 -4.6)" fill="#5f6368"></path>
                                            </g>
                                            <path id="Path_74" d="M125.92,9.392a5.821,5.821,0,0,0-.21-1.892H117.3v3.364h4.836a4.674,4.674,0,0,1-1.682,2.733V15.91H123.4a8.4,8.4,0,0,0,2.523-6.517Z" transform="translate(-108.365 -0.142)" fill="#4285f4"></path>
                                            <path id="Path_75" d="M121.489,16.358a9.115,9.115,0,0,0,5.887-2.1l-2.943-2.313a4.827,4.827,0,0,1-2.943.841A5.362,5.362,0,0,1,116.443,9H113.5v2.313A9.015,9.015,0,0,0,121.489,16.358Z" transform="translate(-112.554 1.512)" fill="#34a853"></path>
                                            <path id="Path_76" d="M116.939,12.076a4.643,4.643,0,0,1,0-3.364V6.4H114a8.906,8.906,0,0,0,0,7.989Z" transform="translate(-113.05 -1.354)" fill="#fbbc04"></path>
                                            <path id="Path_77" d="M121.489,7.574a4.775,4.775,0,0,1,3.364,1.261l2.523-2.523A8.873,8.873,0,0,0,113.5,8.835l2.943,2.313A5.77,5.77,0,0,1,121.489,7.574Z" transform="translate(-112.554 -4)" fill="#ea4335"></path>
                                        </g>
                                    </g>
                                </svg>
                            </g>
                        </svg>
                    </div>
                    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
                        class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors ml-4"
                        title="Back to top">
                        <i data-lucide="arrow-up" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </footer>

    @if (get_setting('whatsapp'))
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', get_setting('whatsapp')) }}?text={{ urlencode("Hi, I'm interested in your products") }}"
            target="_blank"
            class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-[#25d366] rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-transform"
            title="Chat on WhatsApp">
            <i data-lucide="message-circle" class="w-7 h-7 text-white fill-current"></i>
        </a>
    @endif


    {{-- QR Scanner Modal --}}
    <div x-cloak x-show="qrScannerOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="stopScanner(); qrScannerOpen = false"></div>
        
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden relative z-10">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="scan" class="w-5 h-5 text-brand-500"></i>
                    Scan Product QR
                </h3>
                <button @click="stopScanner(); qrScannerOpen = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div id="reader" class="w-full rounded-2xl overflow-hidden bg-gray-50 border-2 border-dashed border-gray-200"></div>
                <p class="text-center text-xs text-gray-400 mt-4 font-medium">
                    Point your camera at a product QR code to redirect
                </p>
            </div>
        </div>
    </div>


{{-- Load QR Scanner Library --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        let html5QrCode;

        function startScanner() {
            // Initialize scanner after modal opens
            setTimeout(() => {
                html5QrCode = new Html5Qrcode("reader");
                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                html5QrCode.start(
                    { facingMode: "environment" }, // Use back camera
                    config,
                    (decodedText) => {
                        // 🎯 SUCCESS CALLBACK
                        console.log(`Scan result: ${decodedText}`);
                        
                        // Simple logic: if it's a URL, redirect
                        if (decodedText.startsWith('http')) {
                            stopScanner();
                            window.location.href = decodedText;
                        }
                    },
                    (errorMessage) => {
                        // Ignore constant "no QR found" noise in console
                    }
                ).catch((err) => {
                    console.error("Camera start error:", err);
                    alert("Camera permission denied or not found.");
                });
            }, 300);
        }

        function stopScanner() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                    console.log("Scanner stopped.");
                }).catch(err => console.error("Scanner stop error:", err));
            }
        }
</script>


    <script>
            document.addEventListener('DOMContentLoaded', () => {
                lucide.createIcons();
            });

            const companySlug = document.querySelector('meta[name="company-slug"]')?.content ?? '';

            function searchDropdown() {
                return {
                    query:       '',
                    results:     [],
                    open:        false,
                    loading:     false,
                    companySlug: companySlug,

                    async suggest() {
                        if (this.query.length < 2) {
                            this.results = [];
                            this.open    = false;
                            return;
                        }

                        this.loading = true;
                        this.open    = true;

                        try {
                            const url = `/${this.companySlug}/suggest?q=` + encodeURIComponent(this.query);
                            const res  = await fetch(url, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            const data = await res.json();
                            this.results = data.products ?? [];

                            // Re-run Lucide for arrow icons in results
                            this.$nextTick(() => lucide.createIcons());
                        } catch (e) {
                            console.error('[Search] Suggest error:', e);
                            this.results = [];
                        } finally {
                            this.loading = false;
                        }
                    },

                    goToSearch() {
                        if (!this.query.trim()) return;
                        this.open = false;
                        window.location.href = `/${this.companySlug}/search?q=` + encodeURIComponent(this.query);
                    },
                }
            }
    </script>  
    {{-- 🌟 Native JS Inclusions (Replacing Vite) --}}
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script>
        // Expose company slug for cart key isolation
        const slugMeta = document.querySelector('meta[name="company-slug"]');
        window.__COMPANY_SLUG__ = slugMeta?.content || "store";
        console.log("[Storefront] Loaded | Company:", window.__COMPANY_SLUG__);
        
        document.addEventListener("DOMContentLoaded", () => {
            if (typeof lucide !== "undefined") lucide.createIcons();
        });
    </script>

    @stack('scripts')
</body>

</html>
