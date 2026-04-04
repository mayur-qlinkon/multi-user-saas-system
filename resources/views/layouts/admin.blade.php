@php
    $primary = get_setting('primary_color', '#008a62');
    $hover = get_setting('primary_hover_color', '#007050');
    $currentStore = active_store(auth()->user());
    $stores = auth()->user()->stores ?? collect();
    $canSwitchStore = $stores->count() > 1 && has_permission('stores.switch');
    $subscription = tenant_subscription();
    $expiresAt = $subscription ? $subscription->expires_at : null;
    $isLifetime = $subscription && is_null($expiresAt);
    $daysLeft = $expiresAt ? (int) ceil(now()->floatDiffInDays(\Carbon\Carbon::parse($expiresAt))) : 0;

    $companySlug = auth()->user()->company?->slug;
@endphp

{{-- ════════════════════════════════════════════════════════════
     AJAX PARTIAL RESPONSE — only content zone returned
     Child views work exactly the same — zero changes needed
════════════════════════════════════════════════════════════ --}}
@if (request()->ajax())

    <title>@yield('title', 'Qlinkon')</title>

    <script type="text/plain" id="ajax-styles">@stack('styles')</script>

    <div id="ajax-header-content">@yield('header-title')</div>

    <div id="ajax-main-content">
        @yield('content')
        <footer class="mt-auto py-6 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} Powered by <span class="font-semibold text-gray-600">Qlinkon</span>
        </footer>

        @stack('scripts')
    </div>
@else
    {{-- ════════════════════════════════════════════════════════════
     FULL LAYOUT — first load only
════════════════════════════════════════════════════════════ --}}
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Qlinkon')</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
            rel="stylesheet">
        <link rel="icon" type="image/png" href="{{ get_setting('favicon') ? asset('storage/' . get_setting('favicon')) : 'https://fav.farm/🔷' }}">

        {{-- 1. Load Tailwind FIRST --}}
        <script src="{{ asset('assets/js/tailwind.min.js') }}"></script>

        {{-- 2. Configure Tailwind SECOND --}}
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
                        },
                        fontFamily: {
                            sans: ['Poppins', 'sans-serif'],
                        }
                    }
                }
            }
        </script>

        {{-- 3. Load other libraries --}}
        <script src="{{ asset('assets/js/lucide.min.js') }}"></script>
        <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
        <script defer src="{{ asset('assets/js/alpinejs.min.js') }}"></script>

        {{-- 4. CSS Variables --}}   
        <style>   
            :root {
                /* Core Brand Colors */
                --brand-500: {{ $primary }};
                --brand-600: {{ $hover }};
                --brand-700: {{ $hover }};
                
                /* Light Brand Variations (Calculated automatically by browser!) */
                --color-brand-50: color-mix(in srgb, var(--brand-500) 10%, white);
                --color-brand-100: color-mix(in srgb, var(--brand-500) 20%, white);
                
                /* Layout Variables */
                --bg-page: #f4f6f9;
                --ease: cubic-bezier(0.4, 0, 0.2, 1);
            }

            [x-cloak] {
                display: none !important;
            }

            #page-cover {
                position: fixed;
                inset: 0;
                background: var(--bg-page);
                z-index: 9999;
                opacity: 1;
                pointer-events: none;
                transition: opacity 220ms ease;
            }

            /* ── Progress Bar ── */
            #nav-progress {
                position: fixed;
                top: 0;
                left: 0;
                height: 2px;
                width: 0%;
                background: var(--brand-600);
                z-index: 99999;
                opacity: 0;
                transition: width 200ms ease, opacity 300ms ease;
            }

            /* ── Scrollbar ── */
            .nav-scroll::-webkit-scrollbar {
                width: 3px;
            }

            .nav-scroll::-webkit-scrollbar-track {
                background: transparent;
            }

            .nav-scroll::-webkit-scrollbar-thumb {
                background: transparent;
                border-radius: 4px;
            }

            .nav-scroll:hover::-webkit-scrollbar-thumb {
                background: #e5e7eb;
            }

            /* ── Sidebar Overlay ── */
            #sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.42);
                backdrop-filter: blur(3px);
                z-index: 30;
                opacity: 0;
                pointer-events: none;
                transition: opacity 280ms var(--ease);
            }

            #sidebar-overlay.active {
                opacity: 1;
                pointer-events: auto;
            }

            /* ── Sidebar ── */
            #main-sidebar {
                transition: transform 280ms var(--ease), box-shadow 280ms var(--ease);
            }

            @media (max-width: 1023px) {
                #main-sidebar {
                    position: fixed;
                    top: 0;
                    left: 0;
                    height: 100%;
                    transform: translateX(-100%);
                    z-index: 40;
                    box-shadow: none;
                }

                #main-sidebar.sidebar-open {
                    transform: translateX(0);
                    box-shadow: 20px 0 60px rgba(0, 0, 0, 0.13);
                }

                .sidebar-close-btn {
                    display: flex !important;
                }
            }

            .sidebar-close-btn {
                display: none;
            }

            /* ── Nav Items ── */
            .nav-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                padding: 0.55rem 0.75rem;
                border-radius: 0.625rem;
                font-size: 0.875rem;
                font-weight: 500;
                color: #6b7280;
                text-decoration: none;
                border: none;
                background: transparent;
                text-align: left;
                cursor: pointer;
                outline: none;
                transition: background 140ms var(--ease), color 140ms var(--ease), transform 80ms ease;
            }

            .nav-item:hover {
                background: #f0f7f4;
                color: var(--brand-600);
            }

            .nav-item:hover .nav-icon {
                color: var(--brand-600);
            }

            .nav-item:active {
                transform: scale(0.984);
            }

            .nav-item.active,
            .nav-item.active:hover {
                background: var(--brand-600);
                color: #fff;
            }

            .nav-item.active .nav-icon,
            .nav-item.active:hover .nav-icon {
                color: #fff;
            }

            .nav-item.active .nav-chevron,
            .nav-item.active:hover .nav-chevron {
                color: rgba(255, 255, 255, 0.6);
            }

            .nav-item.acc-open:not(.active) {
                background: #f0f7f4;
                color: var(--brand-600);
            }

            .nav-item.acc-open:not(.active) .nav-icon {
                color: var(--brand-600);
            }

            .nav-item.acc-open:not(.active) .nav-chevron {
                color: var(--brand-600);
            }

            .nav-icon {
                color: #9ca3af;
                flex-shrink: 0;
                transition: color 140ms var(--ease);
            }

            .nav-chevron {
                width: 14px;
                height: 14px;
                color: #d1d5db;
                flex-shrink: 0;
                transition: color 140ms var(--ease), transform 240ms var(--ease);
            }

            .nav-chevron.rotated {
                transform: rotate(90deg);
            }

            /* ── Accordion ── */
            .acc-wrap {
                overflow: hidden;
                max-height: 0;
                opacity: 0;
                transition: max-height 255ms var(--ease), opacity 200ms ease;
            }

            .sub-menu {
                margin: 3px 0 4px 0.9rem;
                padding: 2px 0 2px 0.85rem;
                border-left: 1.5px solid #e5e7eb;
            }

            .sub-item {
                display: flex;
                align-items: center;
                gap: 0.45rem;
                padding: 0.4rem 0.6rem;
                border-radius: 0.5rem;
                font-size: 0.8125rem;
                font-weight: 500;
                color: #6b7280;
                text-decoration: none;
                transition: background 130ms var(--ease), color 130ms var(--ease), padding-left 150ms ease;
            }

            .sub-item:hover {
                background: #f0f7f4;
                color: var(--brand-600);
                padding-left: 0.9rem;
            }

            .sub-item.active {
                background: var(--brand-50);
                color: var(--brand-600);
                font-weight: 600;
            }

            .sub-item.active::before {
                content: '';
                display: inline-block;
                width: 5px;
                height: 5px;
                border-radius: 50%;
                background: var(--brand-600);
                flex-shrink: 0;
            }
            /* ── Nav Section Labels ── */
            .nav-section-label {
                padding: 0.85rem 0.75rem 0.25rem;
                font-size: 0.65rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #9ca3af;
            }

            .nav-section-label:first-child {
                padding-top: 0.25rem;
            }
            /* ── Hamburger ── */
            .hb-line {
                display: block;
                width: 20px;
                height: 2px;
                background: #212538;
                border-radius: 2px;
                transform-origin: center;
                transition: transform 250ms var(--ease), opacity 200ms ease, width 200ms ease;
            }

            #hamburger-btn.is-open .hb-line:nth-child(1) {
                transform: translateY(7px) rotate(45deg);
            }

            #hamburger-btn.is-open .hb-line:nth-child(2) {
                opacity: 0;
                width: 0;
            }

            #hamburger-btn.is-open .hb-line:nth-child(3) {
                transform: translateY(-7px) rotate(-45deg);
            }
        </style>

        @yield('styles')
        @stack('styles')
    </head>

    <body class="bg-[#f4f6f9] text-gray-800 font-sans">

        <div id="nav-progress"></div>
        <div id="page-cover"></div>

        @php
            $isActive = fn(string|array $r) => request()->routeIs($r);
            $navCls = fn(string|array $r) => $isActive($r) ? 'active' : '';
            $subCls = fn(string|array $r) => $isActive($r) ? 'active' : '';
            $accOpen = fn(string|array $r) => $isActive($r) ? 'true' : 'false';
        @endphp

        <div id="sidebar-overlay" onclick="closeSidebar()"></div>

        <div class="flex h-screen w-full overflow-hidden">

            {{-- ═══════════════ SIDEBAR ═══════════════ --}}
            <aside id="main-sidebar" class="w-64 bg-white border-r border-gray-100 flex flex-col flex-shrink-0">

                @php
                    $logo = get_setting('logo'); // returns path relative to storage, e.g. "logos/site-logo.png"
                    $siteName = get_setting('site_name', 'Qlinkon');
                @endphp

                <div class="h-[60px] flex items-center justify-between px-5 border-b border-gray-100 flex-shrink-0">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                        @if ($logo)
                            <img src="{{ asset('storage/' . $logo) }}" alt="{{ $siteName }} logo"
                                class="h-7 md:h-8 lg:h-9 max-w-[150px] w-auto object-contain" loading="lazy" decoding="async" />
                        @else
                            <div
                                class="w-8 h-8 bg-brand-600 rounded-lg text-white flex items-center justify-center shadow-sm">
                                <i data-lucide="leaf" class="w-[18px] h-[18px] fill-current"></i>
                            </div>
                        @endif
                    </a>

                    <button onclick="closeSidebar()"
                        class="sidebar-close-btn items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>


                <nav id="sidebar-nav" class="flex-1 overflow-y-auto nav-scroll py-3 px-2.5 space-y-0.5">

                    <div class="nav-section-label">Operations</div>

                    {{-- Dashboard: employee sees their own dashboard, others see admin dashboard --}}
                    @if(auth()->user()->hasRole('employee'))
                        <a href="{{ route('admin.hrm.employee.dashboard') }}"
                            class="nav-item {{ $navCls('admin.hrm.employee.dashboard') }}">
                            <span class="flex items-center gap-3"><i data-lucide="home"
                                    class="nav-icon w-[18px] h-[18px]"></i> Dashboard</span>
                        </a>

                        {{-- Employee self-service links --}}
                        <a href="{{ route('admin.hrm.my-leaves.index') }}"
                            class="nav-item {{ $navCls('admin.hrm.my-leaves.*') }}">
                            <span class="flex items-center gap-3"><i data-lucide="calendar-off"
                                    class="nav-icon w-[18px] h-[18px]"></i> My Leaves</span>
                        </a>

                        <a href="{{ route('admin.hrm.my-attendance.index') }}"
                            class="nav-item {{ $navCls('admin.hrm.my-attendance.*') }}">
                            <span class="flex items-center gap-3"><i data-lucide="clock"
                                    class="nav-icon w-[18px] h-[18px]"></i> My Attendance</span>
                        </a>

                        <a href="{{ route('admin.hrm.my-tasks.index') }}"
                            class="nav-item {{ $navCls('admin.hrm.my-tasks.*') }}">
                            <span class="flex items-center gap-3"><i data-lucide="check-square"
                                    class="nav-icon w-[18px] h-[18px]"></i> My Tasks</span>
                        </a>

                        <a href="{{ route('admin.hrm.my-work-logs.index') }}"
                            class="nav-item {{ $navCls('admin.hrm.my-work-logs.*') }}">
                            <span class="flex items-center gap-3"><i data-lucide="clipboard-list"
                                    class="nav-icon w-[18px] h-[18px]"></i> My Work Logs</span>
                        </a>

                        <a href="{{ route('admin.hrm.my-salary-slips.index') }}"
                            class="nav-item {{ $navCls('admin.hrm.my-salary-slips.*') }}">
                            <span class="flex items-center gap-3"><i data-lucide="banknote"
                                    class="nav-icon w-[18px] h-[18px]"></i> My Salary Slips</span>
                        </a>
                    @else
                        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ $navCls('admin.dashboard') }}">
                            <span class="flex items-center gap-3"><i data-lucide="home"
                                    class="nav-icon w-[18px] h-[18px]"></i> Dashboard</span>
                        </a>
                    @endif

                    @if (has_module('inquiry') && has_permission('inquiries.view'))
                        <a href="{{ route('admin.orders.index') }}" class="nav-item {{ $navCls('admin.orders.*') }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="list-ordered" class="nav-icon w-[18px] h-[18px]"></i>
                                Order Process
                            </span>
                        </a>
                    @endif

                    {{-- Challans --}}
                    @if (has_module('challan') && has_permission('challans.view'))
                        <div class="acc-group" data-open="{{ $accOpen('admin.challans.*') }}">
                            <button class="nav-item acc-trigger {{ $navCls('admin.challans.*') }}">
                                <span class="flex items-center gap-3">
                                    <i data-lucide="file-text" class="nav-icon w-[18px] h-[18px]"></i> Challans
                                </span>
                                <i data-lucide="chevron-right" class="nav-chevron"></i>
                            </button>

                            <div class="acc-wrap">
                                <div class="sub-menu">

                                    {{-- All Challans --}}
                                    <a href="{{ route('admin.challans.index') }}" class="sub-item {{ $subCls('admin.challans.*') }} flex items-center gap-2">
                                        <i data-lucide="list" class="w-4 h-4"></i>
                                        All Challans
                                    </a>

                                    {{-- Returns --}}
                                    @if (has_permission('challans.returns'))
                                        <a href="{{ route('admin.challan-returns.index') }}" class="sub-item {{ $subCls('admin.challan-returns.*') }} flex items-center gap-2">
                                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                            Returns
                                        </a>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @endif
                    
                    
                    @if (has_module('pos') && has_permission('pos.access'))
                        <a href="{{ url('/admin/pos') }}" class="nav-item" target="_blank" data-no-spa>
                            <span class="flex items-center gap-3"><i data-lucide="monitor"
                                    class="nav-icon w-[18px] h-[18px]"></i> POS</span>
                        </a>
                    @endif                   

                    
                    @if (has_module('invoicing') && has_permission('quotations.view'))
                        <div class="nav-section-label">Sales & Finance</div>    
                        
                        <a href="{{ route('admin.quotations.index') }}"
                            class="nav-item {{ $navCls('admin.quotations.*') }}">
                            <span class="flex items-center gap-3"><i data-lucide="quote"
                                    class="nav-icon w-[18px] h-[18px]"></i> Quotations</span>
                        </a>
                    @endif
                    
                    {{-- Invoices --}}
                    @if (has_module('invoicing') && has_permission('invoices.view'))
                        <div class="acc-group"
                            data-open="{{ $accOpen(['admin.invoices.*', 'admin.invoice-returns.*']) }}">
                            <button
                                class="nav-item acc-trigger {{ $navCls(['admin.invoices.*', 'admin.invoice-returns.*']) }}">
                                <span class="flex items-center gap-3"><i data-lucide="file-text"
                                        class="nav-icon w-[18px] h-[18px]"></i> Invoices</span>
                                <i data-lucide="chevron-right" class="nav-chevron"></i>
                            </button>
                            <div class="acc-wrap">
                                <div class="sub-menu">
                                    <a href="{{ route('admin.invoices.index') }}"
                                        class="sub-item {{ $subCls('admin.invoices.*') }}">Sales</a>
                                    <a href="{{ route('admin.invoice-returns.index') }}"
                                        class="sub-item {{ $subCls('admin.invoice-returns.*') }}">Sales Returns</a>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if (has_module('reports') && has_permission('reports.view'))
                         <a href="{{ route('admin.reports.index') }}"
                             class="nav-item {{ $navCls('admin.reports.*') }}">
                             <span class="flex items-center gap-3"><i data-lucide="bar-chart-2"
                                     class="nav-icon w-[18px] h-[18px]"></i> Analytics</span>
                         </a>
                     @endif

                    {{-- Purchases --}}
                    @if (has_module('purchases') && has_permission(['purchases.view', 'purchase_returns.view']))
                        <div class="acc-group"
                            data-open="{{ $accOpen(['admin.purchases.*', 'admin.purchase-returns.*']) }}">
                            <button
                                class="nav-item acc-trigger {{ $navCls(['admin.purchases.*', 'admin.purchase-returns.*']) }}">
                                <span class="flex items-center gap-3"><i data-lucide="shopping-bag"
                                        class="nav-icon w-[18px] h-[18px]"></i> Purchases</span>
                                <i data-lucide="chevron-right" class="nav-chevron"></i>
                            </button>
                            <div class="acc-wrap">
                                <div class="sub-menu">
                                    <a href="{{ route('admin.purchases.index') }}"
                                        class="sub-item {{ $subCls('admin.purchases.*') }}">Purchases</a>
                                    <a href="{{ route('admin.purchase-returns.index') }}"
                                        class="sub-item {{ $subCls('admin.purchase-returns.*') }}">Purchase
                                        Returns</a>
                                </div>
                            </div>
                        </div>
                    @endif

                     @if (has_module('invoicing') &&
                            has_permission(['expenses.view']))
                        {{-- Expenses --}}
                        <div class="acc-group" data-open="{{ $accOpen(['admin.expenses.*', 'admin.expense-categories.*']) }}">
                            <button class="nav-item acc-trigger {{ $navCls('admin.expenses.*') }}">
                                <span class="flex items-center gap-3">
                                    <i data-lucide="indian-rupee" class="nav-icon w-[18px] h-[18px]"></i>
                                    Expenses
                                </span>
                                <i data-lucide="chevron-right" class="nav-chevron"></i>
                            </button>

                            <div class="acc-wrap">
                                <div class="sub-menu">
                                    <a href="{{ route('admin.expenses.index') }}"
                                        class="sub-item {{ $subCls('admin.expenses.*') }} flex items-center gap-2">
                                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                                        All Expenses
                                    </a>

                                    <a href="{{ route('admin.expense-categories.index') }}"
                                        class="sub-item flex {{ $subCls('admin.expense-categories.*') }} items-center gap-2">
                                        <i data-lucide="layers" class="w-4 h-4"></i>
                                        Categories
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                      
                    
                    
                    {{-- Products --}}
                    @if (has_module('inventory') &&
                            has_permission(['products.view', 'attributes.view', 'categories.view', 'units.view', 'labels.view']))
                    <div class="nav-section-label">Inventory</div>
                        <div class="acc-group"
                            data-open="{{ $accOpen(['admin.products.*', 'admin.attributes.*', 'admin.categories.*', 'admin.units.*', 'admin.labels.*']) }}">
                            <button
                                class="nav-item acc-trigger {{ $navCls(['admin.products.*', 'admin.attributes.*', 'admin.categories.*', 'admin.units.*', 'admin.labels.*']) }}">
                                <span class="flex items-center gap-3">
                                    <i data-lucide="package" class="nav-icon w-[18px] h-[18px]"></i> Products
                                </span>
                                <i data-lucide="chevron-right" class="nav-chevron"></i>
                            </button>
                            <div class="acc-wrap">
                                <div class="sub-menu">
                                    @if (has_permission('products.view'))
                                        <a href="{{ route('admin.products.index') }}"
                                            class="sub-item {{ $subCls('admin.products.*') }}">All Products</a>
                                    @endif
                                    @if (has_permission('attributes.view'))
                                        <a href="{{ route('admin.attributes.index') }}"
                                            class="sub-item {{ $subCls('admin.attributes.*') }}">Attributes</a>
                                    @endif
                                    @if (has_permission('categories.view'))
                                        <a href="{{ route('admin.categories.index') }}"
                                            class="sub-item {{ $subCls('admin.categories.*') }}">Categories</a>
                                    @endif
                                    @if (has_permission('units.view'))
                                        <a href="{{ route('admin.units.index') }}"
                                            class="sub-item {{ $subCls('admin.units.*') }}">Units</a>
                                    @endif
                                    @if (has_permission('labels.view'))
                                        <a href="{{ route('admin.labels.index') }}"
                                            class="sub-item {{ $subCls('admin.labels.*') }}">Print Barcode</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 🌟 NEW: Inventory Report Link --}}
                    @if (has_module('inventory'))
                        <a href="{{ route('admin.inventory.reports.index') }}"
                            class="nav-item {{ $navCls('admin.inventory.reports.*') }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="clipboard-list" class="nav-icon w-[18px] h-[18px]"></i>
                                Inventory Report
                            </span>
                        </a>
                    @endif

                    @if (has_module('inventory') && has_permission('warehouses.view'))
                        <a href="{{ route('admin.warehouses.index') }}"
                            class="nav-item {{ $navCls('admin.warehouses.*') }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="warehouse" class="nav-icon w-[18px] h-[18px]"></i>
                                Warehouses
                            </span>
                        </a>
                    @endif                   
                      

                    
                    {{-- CRM --}}
                    @if (has_module('crm') && has_permission(['crm_leads.view', 'crm_pipelines.view', 'crm_sources.view', 'crm_tags.view']))
                        <div class="nav-section-label">Relationships</div>                        
                        
                        <div class="acc-group" data-open="{{ $accOpen(['admin.crm.*']) }}">
                            <button class="nav-item acc-trigger {{ $navCls(['admin.crm.*']) }}">
                                <span class="flex items-center gap-3">
                                    <i data-lucide="contact" class="nav-icon w-[18px] h-[18px]"></i>
                                    CRM
                                </span>
                                <i data-lucide="chevron-right" class="nav-chevron"></i>
                            </button>
                            <div class="acc-wrap">
                                <div class="sub-menu">
                                     <a href="{{ route('admin.crm.dashboard') }}"
                                        class="sub-item {{ $subCls('admin.crm.dashboard') }}">Dashboard</a>
                                    <a href="{{ route('admin.crm.leads.index') }}"
                                        class="sub-item {{ $subCls('admin.crm.leads.*') }}">All Leads</a>
                                    <a href="{{ route('admin.crm.pipelines.index') }}"
                                        class="sub-item {{ $subCls('admin.crm.pipelines.*') }}">Pipelines</a>
                                    <a href="{{ route('admin.crm.sources.index') }}"
                                        class="sub-item {{ $subCls('admin.crm.sources.*') }}">Lead Sources</a>
                                    <a href="{{ route('admin.crm.tags.index') }}?tab=tags"
                                        class="sub-item {{ $subCls('admin.crm.tags.*') }}">Tags</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Peoples --}}
                    @if (has_permission(['clients.view', 'suppliers.view']))
                        <div class="acc-group"
                            data-open="{{ $accOpen(['admin.clients.*', 'admin.suppliers.*']) }}">
                            <button
                                class="nav-item acc-trigger {{ $navCls(['admin.clients.*', 'admin.suppliers.*']) }}">
                                <span class="flex items-center gap-3">
                                    <i data-lucide="users" class="nav-icon w-[18px] h-[18px]"></i> Contacts
                                </span>
                                <i data-lucide="chevron-right" class="nav-chevron"></i>
                            </button>
                            <div class="acc-wrap">
                                <div class="sub-menu">
                                    @if (has_permission('clients.view'))
                                        <a href="{{ route('admin.clients.index') }}"
                                            class="sub-item {{ $subCls('admin.clients.*') }}">Clients</a>
                                    @endif
                                    @if (has_permission('suppliers.view'))
                                        <a href="{{ route('admin.suppliers.index') }}"
                                            class="sub-item {{ $subCls('admin.suppliers.*') }}">Suppliers</a>
                                    @endif                                    
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    
                    {{-- HRM --}}
                    @if (has_module('hrm'))
                    @if(!auth()->user()->hasRole('employee'))
                    <div class="nav-section-label">Team</div>                        

                        {{-- Employees --}}
                        <a href="{{ route('admin.hrm.employees.index') }}" class="nav-item {{ $navCls('admin.hrm.employees.*') }}">
                            <span class="flex items-center gap-3"><i data-lucide="users" class="nav-icon w-[18px] h-[18px]"></i> Employees</span>
                        </a>

                        {{-- Attendance --}}
                        <div class="acc-group" data-open="{{ $accOpen(['admin.hrm.attendance.*', 'admin.hrm.attendance-settings.*', 'admin.hrm.office-locations.*']) }}">
                            <button class="nav-item acc-trigger {{ $navCls(['admin.hrm.attendance.*', 'admin.hrm.attendance-settings.*', 'admin.hrm.office-locations.*']) }}">
                                <span class="flex items-center gap-3"><i data-lucide="clock" class="nav-icon w-[18px] h-[18px]"></i> Attendance</span>
                                <i data-lucide="chevron-right" class="nav-chevron"></i>
                            </button>
                            <div class="acc-wrap">
                                <div class="sub-menu">
                                    <a href="{{ route('admin.hrm.attendance.today') }}" class="sub-item {{ $subCls('admin.hrm.attendance.today') }}">Today</a>
                                    <a href="{{ route('admin.hrm.attendance.report') }}" class="sub-item {{ $subCls('admin.hrm.attendance.report') }}">Report</a>
                                    <a href="{{ route('admin.hrm.office-locations.index') }}" class="sub-item {{ $subCls('admin.hrm.office-locations.*') }}">Office Locations</a>
                                    <a href="{{ route('admin.hrm.attendance-settings.index') }}" class="sub-item {{ $subCls('admin.hrm.attendance-settings.*') }}">Settings</a>
                                </div>
                            </div>
                        </div>

                        {{-- Leaves --}}
                        <div class="acc-group" data-open="{{ $accOpen(['admin.hrm.leaves.*', 'admin.hrm.leave-types.*', 'admin.hrm.leave-balances.*']) }}">
                            <button class="nav-item acc-trigger {{ $navCls(['admin.hrm.leaves.*', 'admin.hrm.leave-types.*', 'admin.hrm.leave-balances.*']) }}">
                                <span class="flex items-center gap-3"><i data-lucide="calendar-off" class="nav-icon w-[18px] h-[18px]"></i> Leaves</span>
                                <i data-lucide="chevron-right" class="nav-chevron"></i>
                            </button>
                            <div class="acc-wrap">
                                <div class="sub-menu">
                                    <a href="{{ route('admin.hrm.leaves.index') }}" class="sub-item {{ $subCls('admin.hrm.leaves.*') }}">All Requests</a>
                                    <a href="{{ route('admin.hrm.leave-types.index') }}" class="sub-item {{ $subCls('admin.hrm.leave-types.*') }}">Leave Types</a>
                                    <a href="{{ route('admin.hrm.leave-balances.index') }}" class="sub-item {{ $subCls('admin.hrm.leave-balances.*') }}">Leave Balances</a>
                                </div>
                            </div>
                        </div>

                        {{-- Payroll --}}
                        <div class="acc-group" data-open="{{ $accOpen(['admin.hrm.salary-slips.*', 'admin.hrm.salary-components.*']) }}">
                            <button class="nav-item acc-trigger {{ $navCls(['admin.hrm.salary-slips.*', 'admin.hrm.salary-components.*']) }}">
                                <span class="flex items-center gap-3"><i data-lucide="banknote" class="nav-icon w-[18px] h-[18px]"></i> Payroll</span>
                                <i data-lucide="chevron-right" class="nav-chevron"></i>
                            </button>
                            <div class="acc-wrap">
                                <div class="sub-menu">
                                    <a href="{{ route('admin.hrm.salary-slips.index') }}" class="sub-item {{ $subCls('admin.hrm.salary-slips.*') }}">Salary Slips</a>
                                    <a href="{{ route('admin.hrm.salary-components.index') }}" class="sub-item {{ $subCls('admin.hrm.salary-components.*') }}">Components</a>
                                </div>
                            </div>
                        </div>

                        {{-- Tasks --}}
                        <a href="{{ route('admin.hrm.tasks.index') }}" class="nav-item {{ $navCls('admin.hrm.tasks.*') }}">
                            <span class="flex items-center gap-3"><i data-lucide="check-square" class="nav-icon w-[18px] h-[18px]"></i> Tasks</span>
                        </a>
                         {{-- Work Logs --}}
                        <a href="{{ route('admin.hrm.work-logs.index') }}" class="nav-item {{ $navCls('admin.hrm.work-logs.*') }}">
                            <span class="flex items-center gap-3"><i data-lucide="timer" class="nav-icon w-[18px] h-[18px]"></i> Work Logs</span>
                        </a>
                        {{-- Announcements --}}
                        <a href="{{ route('admin.hrm.announcements.index') }}" class="nav-item {{ $navCls('admin.hrm.announcements.*') }}">
                            <span class="flex items-center gap-3"><i data-lucide="megaphone" class="nav-icon w-[18px] h-[18px]"></i> Announcements</span>
                        </a>                       

                        {{-- Setup --}}
                        <div class="acc-group" data-open="{{ $accOpen(['admin.hrm.departments.*', 'admin.hrm.designations.*', 'admin.hrm.shifts.*', 'admin.hrm.holidays.*', 'admin.hrm.attendance-rules.*']) }}">
                            <button class="nav-item acc-trigger {{ $navCls(['admin.hrm.departments.*', 'admin.hrm.designations.*', 'admin.hrm.shifts.*', 'admin.hrm.holidays.*', 'admin.hrm.attendance-rules.*']) }}">
                                <span class="flex items-center gap-3"><i data-lucide="settings-2" class="nav-icon w-[18px] h-[18px]"></i> HRM Setup</span>
                                <i data-lucide="chevron-right" class="nav-chevron"></i>
                            </button>
                            <div class="acc-wrap">
                                <div class="sub-menu">
                                    <a href="{{ route('admin.hrm.departments.index') }}" class="sub-item {{ $subCls('admin.hrm.departments.*') }}">Departments</a>
                                    <a href="{{ route('admin.hrm.designations.index') }}" class="sub-item {{ $subCls('admin.hrm.designations.*') }}">Designations</a>
                                    <a href="{{ route('admin.hrm.shifts.index') }}" class="sub-item {{ $subCls('admin.hrm.shifts.*') }}">Shifts</a>
                                    <a href="{{ route('admin.hrm.holidays.index') }}" class="sub-item {{ $subCls('admin.hrm.holidays.*') }}">Holidays</a>
                                    <a href="{{ route('admin.hrm.attendance-rules.index') }}" class="sub-item {{ $subCls('admin.hrm.attendance-rules.*') }}">Attendance Rules</a>
                                </div>
                            </div>
                        </div>
                        
                        @if (has_permission('roles.view'))
                        <a href="{{ route('admin.roles.index') }}" class="nav-item {{ $navCls('admin.roles.*') }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="shield-check" class="nav-icon w-[18px] h-[18px]"></i>
                                Roles & Permissions
                            </span>
                        </a>
                        @endif
                        
                        @if (has_permission('users.view'))
                        <a href="{{ route('admin.users.index') }}" class="nav-item {{ $navCls('admin.users.*') }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="shield-user" class="nav-icon w-[18px] h-[18px]"></i>
                                Users
                            </span>
                        </a>                                        
                        @endif

                    @endif
                    @endif


                    
                    @if (has_permission('stores.view'))
                        <div class="nav-section-label">Store Management</div>
                        
                        <a href="{{ route('admin.stores.index') }}"
                            class="nav-item {{ $navCls('admin.stores.*') }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="store" class="nav-icon w-[18px] h-[18px]"></i>
                                Stores
                            </span>
                        </a>
                    @endif

                                     

                    @if (has_permission('payment-methods.view'))
                        <a href="{{ route('admin.payment-methods.index') }}"
                            class="nav-item {{ $navCls('admin.payment-methods.*') }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="credit-card" class="nav-icon w-[18px] h-[18px]"></i>
                                Payment Methods
                            </span>
                        </a>
                    @endif

                    @if (has_module('storefront') && has_permission('merchandising.view'))
                        <a href="{{ route('admin.merchandising.index') }}"
                            class="nav-item {{ $navCls('admin.merchandising.*') }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="layout-grid" class="nav-icon w-[18px] h-[18px]"></i>
                                Merchandising
                            </span>
                        </a>
                    @endif
                   
                    @if (has_module('storefront') && has_permission('storefront_sections.view'))
                        <a href="{{ route('admin.pages.index') }}"
                            class="nav-item {{ $navCls('admin.pages.*') }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="layout-grid" class="nav-icon w-[18px] h-[18px]"></i>
                                Storefront Pages
                            </span>
                        </a>
                    @endif

                    @if (has_module('storefront') && has_permission('storefront_sections.view'))
                        <a href="{{ route('admin.storefront-sections.index') }}"
                            class="nav-item {{ $navCls('admin.storefront-sections.*') }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="layout-dashboard" class="nav-icon w-[18px] h-[18px]"></i>
                                Storefront Sections
                            </span>
                        </a>
                    @endif

                    <div class="nav-section-label">System</div>

                    @if (has_permission('settings.view'))
                        <a href="{{ route('admin.settings.index') }}"
                            class="nav-item {{ $navCls(['admin.settings.*', 'admin.banners.*']) }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="settings" class="nav-icon w-[18px] h-[18px]"></i>
                                Settings
                            </span>
                        </a>
                    @endif

                    <a href="{{ route('help') }}" class="nav-item {{ $navCls('help') }}">
                        <span class="flex items-center gap-3"><i data-lucide="help-circle"
                                class="nav-icon w-[18px] h-[18px]"></i> Help Center</span>
                    </a>

                    @if (has_module('storefront') && $companySlug)
                            <a href="{{ route('storefront.index', ['slug' => $companySlug]) }}"
                            target="_blank"
                            data-no-spa
                            class="nav-item">
                                <span class="flex items-center gap-3">
                                    <i data-lucide="external-link" class="nav-icon w-[18px] h-[18px]"></i>
                                    Visit Site
                                </span>
                            </a>
                    @endif

                </nav>

                @if ($subscription)
                    <div class="px-3 py-3 flex-shrink-0">
                        <div class="bg-[#fff4ed] border border-[#fce4d6] rounded-xl p-3.5 text-center">
                            @if ($isLifetime)
                                <p class="text-[11px] font-semibold text-gray-600">Plan: <span
                                        class="uppercase tracking-wider">{{ $subscription->plan->name ?? 'Lifetime' }}</span>
                                </p>
                                <p class="text-sm font-bold text-[#e06623] mt-0.5">Never Expires</p>
                            @else
                                <p class="text-[11px] font-semibold text-gray-600">Expires:
                                    {{ \Carbon\Carbon::parse($expiresAt)->format('d M, Y') }}</p>
                                <p class="text-sm font-bold text-[#e06623] mt-0.5">{{ $daysLeft }} days left</p>
                            @endif
                        </div>
                    </div>
                @endif
            </aside>

  {{-- ═══════════════ MAIN AREA ═══════════════ --}}
            <div class="flex-1 flex flex-col overflow-hidden relative min-w-0">

                <header
                    class="bg-white border-b border-gray-100 h-[60px] flex items-center justify-between px-5 z-30 sticky top-0">
                    
                    {{-- 🟢 LEFT SIDE: Menu Toggle & Title --}}
                    <div class="flex items-center gap-3">
                        <button id="hamburger-btn" onclick="toggleSidebar()"
                            class="lg:hidden flex flex-col gap-[5px] items-center justify-center w-9 h-9 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none"
                            aria-label="Toggle menu">
                            <span class="hb-line"></span>
                            <span class="hb-line"></span>
                            <span class="hb-line"></span>
                        </button>
                        <div id="page-header-title" class="hidden sm:block">@yield('header-title')</div>
                    </div>

                    {{-- 🟢 RIGHT SIDE: Action Buttons Group --}}
                    <div class="flex items-center gap-2 sm:gap-4 ml-auto">

                        {{-- 1. Store Switcher (Hidden on Mobile, Visible on iPad/PC) --}}
                        @if ($canSwitchStore)
                            <div x-data="{ open: false }" class="relative sm:block">
                                <button @click="open = !open" type="button"
                                    class="flex items-center gap-1 sm:gap-2 px-2 py-1 sm:px-3 sm:py-1.5 text-[11px] sm:text-sm font-semibold bg-gray-50 hover:bg-gray-100 border border-gray-100 text-gray-700 rounded-lg transition-colors">
                                    <i data-lucide="store" class="w-3 h-3 sm:w-[15px] sm:h-[15px] text-gray-400"></i>
                                    <span class="truncate max-w-[80px] sm:max-w-none">{{ $currentStore->name ?? 'Select Store' }}</span>
                                    <i data-lucide="chevron-down" class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400"></i>
                                </button>
                                <div x-cloak x-show="open" @click.away="open = false" x-transition
                                    class="absolute right-0 mt-2 w-52 bg-white border border-gray-100 rounded-lg shadow-xl z-50 overflow-hidden">
                                    @foreach ($stores as $store)
                                        <form method="POST" action="{{ route('admin.store.switch') }}">
                                            @csrf
                                            <input type="hidden" name="store_id" value="{{ $store->id }}">
                                            <button type="submit"
                                                class="w-full text-left px-4 py-2.5 text-[13px] hover:bg-gray-50 {{ $currentStore && $currentStore->id == $store->id ? 'bg-brand-50/50 text-brand-700 font-bold border-l-2 border-brand-500' : 'text-gray-700 font-medium' }}">
                                                {{ $store->name }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @elseif ($currentStore)
                            <div class="hidden sm:flex px-3 py-1.5 text-sm font-semibold bg-gray-50 border border-gray-100 text-gray-600 rounded-lg items-center gap-2">
                                <i data-lucide="store" class="w-[15px] h-[15px] text-gray-400"></i>
                                <span>{{ $currentStore->name }}</span>
                            </div>
                        @endif

                        {{-- 2. Notification Bell (Static Mockup) --}}
                        <div x-data="{ 
                                open: false,
                                unreadCount: {{ $unreadCount }},
                                async markAsRead(id, link) {
                                    try {
                                        await fetch(`/admin/notifications/${id}/read`, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        });
                                        window.location.href = link;
                                    } catch (e) {
                                        window.location.href = link; // Redirect anyway if AJAX fails
                                    }
                                }
                            }" class="relative flex items-center">
                            <button @click="open = !open" @click.away="open = false" type="button"
                                class="relative flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition-colors focus:outline-none">
                                <i data-lucide="bell" class="w-[18px] h-[18px]"></i>
                                
                                {{-- Pulsing Red Dot --}}
                                @if($unreadCount > 0)
                                    <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 border border-white text-[9px] text-white font-bold items-center justify-center">
                                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                        </span>
                                    </span>
                                @endif
                            </button>

                            {{-- Dropdown --}}
                            <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                class="absolute right-0 top-full mt-2.5 w-72 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50 origin-top-right">
                                
                                <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                    <h3 class="text-[11px] font-black text-gray-500 uppercase tracking-wider">Notifications</h3>
                                    @if($unreadCount > 0)
                                        <span class="text-[9px] font-bold bg-brand-50 text-brand-600 px-2 py-0.5 rounded-full border border-brand-100">
                                            {{ $unreadCount }} New
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="max-h-[320px] overflow-y-auto nav-scroll">
                                    @forelse($unreadNotifications as $notification)
                                        @php $data = $notification->data; @endphp
                                        <a href="javascript:void(0)" 
                                        @click="markAsRead('{{ $notification->id }}', '{{ $data['link'] }}')"
                                        class="block px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                            <div class="flex gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-{{ $data['color'] ?? 'blue' }}-50 flex items-center justify-center flex-shrink-0">
                                                    <i data-lucide="{{ $data['icon'] ?? 'bell' }}" class="w-4 h-4 text-{{ $data['color'] ?? 'blue' }}-600"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-[12px] text-gray-800 font-semibold leading-snug truncate">{{ $data['title'] }}</p>
                                                    <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-2 leading-relaxed">{{ $data['message'] }}</p>
                                                    <p class="text-[9px] text-gray-400 mt-1.5 flex items-center gap-1 font-medium">
                                                        <i data-lucide="clock" class="w-3 h-3"></i> {{ $notification->created_at->diffForHumans() }}
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="py-10 text-center">
                                            <div class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-2">
                                                <i data-lucide="check-circle" class="w-5 h-5 text-gray-300"></i>
                                            </div>
                                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">All caught up!</p>
                                        </div>
                                    @endforelse
                                </div>

                                <a href="{{ route('admin.notifications.index') }}" class="block px-4 py-2.5 border-t border-gray-100 text-center bg-gray-50/50 hover:bg-gray-100 transition-colors">
                                    <span class="text-[11px] font-bold text-gray-600">View All History</span>
                                </a>
                            </div>
                        </div>

                        {{-- 3. Profile Dropdown --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.away="open = false"
                                class="flex items-center focus:outline-none group">
                                <div
                                    class="w-9 h-9 rounded-full text-white flex items-center justify-center shadow-sm relative ring-2 ring-transparent group-hover:ring-[#82c43c]/30 transition-all duration-200 text-sm font-bold select-none"
                                    style="background: var(--brand-600)">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    <span
                                        class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-[#4ade80] border-2 border-white rounded-full"></span>
                                </div>
                            </button>
                            <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                class="absolute right-0 mt-2.5 w-56 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden origin-top-right z-50">
                                <div class="px-4 py-3.5 border-b border-gray-100 flex items-center gap-3 bg-gray-50/60">
                                    <div
                                        class="w-9 h-9 rounded-full text-white flex items-center justify-center flex-shrink-0 text-sm font-bold select-none"
                                        style="background: var(--brand-600)">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 font-medium">
                                            {{ auth()->user()->roles->first()->name ?? 'User' }}</p>
                                    </div>
                                </div>
                                <div class="py-1.5">                                
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-[#ef4444] hover:bg-red-50 transition-colors">
                                            <i data-lucide="power" class="w-4 h-4"></i> Log Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </header>

                <main id="page-content" class="flex-1 overflow-x-hidden overflow-y-auto p-5 flex flex-col">
                    @yield('content')
                    <footer class="mt-auto py-6 text-center text-xs text-gray-400">
                        &copy; {{ date('Y') }} Powered by <span
                            class="font-semibold text-gray-600">Qlinkon</span>
                    </footer>
                </main>
            </div>

        <script>
            /* ── Lucide global init ── */
            window.initIcons = function(scope) {
                if (typeof lucide === 'undefined') {
                    setTimeout(() => window.initIcons(scope), 80);
                    return;
                }
                lucide.createIcons(scope ? {
                    nodes: scope.querySelectorAll('[data-lucide]')
                } : undefined);
            };


            /* ── Page cover fade ── */
            (function() {
                const cover = document.getElementById('page-cover');
                document.addEventListener('DOMContentLoaded', () => {
                    window.initIcons();
                    requestAnimationFrame(() => {
                        cover.style.opacity = '0';
                        setTimeout(() => cover.remove(), 240);
                    });
                });
            })();

            /* ── Accordion — functions exposed globally for navigate() ── */
            (function() {
                const groups = Array.from(document.querySelectorAll('.acc-group'));

                window.accOpen = function(group, instant) {
                    const wrap = group.querySelector('.acc-wrap');
                    const chevron = group.querySelector('.nav-chevron');
                    const trigger = group.querySelector('.acc-trigger');
                    if (instant) {
                        wrap.style.transition = 'none';
                        wrap.style.maxHeight = wrap.scrollHeight + 'px';
                        wrap.style.opacity = '1';
                        requestAnimationFrame(() => requestAnimationFrame(() => {
                            wrap.style.transition = '';
                        }));
                    } else {
                        wrap.style.maxHeight = wrap.scrollHeight + 'px';
                        wrap.style.opacity = '1';
                    }
                    chevron?.classList.add('rotated');
                    trigger?.classList.add('acc-open');
                    group._open = true;
                };

                window.accClose = function(group) {
                    const wrap = group.querySelector('.acc-wrap');
                    const chevron = group.querySelector('.nav-chevron');
                    const trigger = group.querySelector('.acc-trigger');
                    wrap.style.maxHeight = '0';
                    wrap.style.opacity = '0';
                    chevron?.classList.remove('rotated');
                    trigger?.classList.remove('acc-open');
                    group._open = false;
                };

                groups.forEach(group => {
                    const shouldOpen = group.dataset.open === 'true';
                    group._open = shouldOpen;
                    shouldOpen ? window.accOpen(group, true) : window.accClose(group);

                    group.querySelector('.acc-trigger')?.addEventListener('click', () => {
                        const isOpen = group._open;
                        groups.forEach(g => {
                            if (g !== group && g._open) window.accClose(g);
                        });
                        isOpen ? window.accClose(group) : window.accOpen(group, false);
                    });
                });
            })();

            /* ── Sidebar (mobile) ── */
            function toggleSidebar() {
                document.getElementById('main-sidebar').classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
            }

            function openSidebar() {
                document.getElementById('main-sidebar').classList.add('sidebar-open');
                document.getElementById('sidebar-overlay').classList.add('active');
                document.getElementById('hamburger-btn').classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                document.getElementById('main-sidebar').classList.remove('sidebar-open');
                document.getElementById('sidebar-overlay').classList.remove('active');
                document.getElementById('hamburger-btn')?.classList.remove('is-open');
                document.body.style.overflow = '';
            }
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) closeSidebar();
            });

            /* ══════════════════════════════════════════════════════
               SPA NAVIGATION ENGINE
            ══════════════════════════════════════════════════════ */
            (function() {
                    const getContent = () => document.getElementById('page-content');
                    const getHeaderTitle = () => document.getElementById('page-header-title');
                    const progress = document.getElementById('nav-progress');

                    function progressStart() {
                        progress.style.opacity = '1';
                        progress.style.width = '40%';
                    }

                    function progressDone() {
                        progress.style.width = '100%';
                        setTimeout(() => {
                            progress.style.opacity = '0';
                            setTimeout(() => {
                                progress.style.width = '0%';
                            }, 300);
                        }, 200);
                    }

                    /* Re-execute <script> tags inside swapped content */
                    function rerunScripts(container) {
                        container.querySelectorAll('script').forEach(old => {
                            const s = document.createElement('script');
                            [...old.attributes].forEach(a => s.setAttribute(a.name, a.value));
                            s.textContent = old.textContent;
                            old.replaceWith(s);
                        });
                    }

                    /* Update sidebar active state after navigation */
                    function updateActiveNav(url) {
                        const path = new URL(url, location.origin).pathname;

                        document.querySelectorAll('#sidebar-nav .active').forEach(el => el.classList.remove('active'));
                        document.querySelectorAll('#sidebar-nav .acc-open').forEach(el => el.classList.remove('acc-open'));
                        document.querySelectorAll('.acc-group').forEach(g => window.accClose(g));

                        document.querySelectorAll('#sidebar-nav a[href]').forEach(el => {
                            try {
                                const href = el.getAttribute('href');
                                if (!href || href === '#') return;
                                const elPath = new URL(el.href, location.origin).pathname;

                                // ── Exact match OR prefix match (covers create/edit/show) ──
                                // path.startsWith(elPath + '/') ensures /admin/products matches
                                // /admin/products/create but NOT /admin/products-returns
                                const isMatch = path === elPath ||
                                    (elPath.length > 7 && path.startsWith(elPath + '/'));

                                if (!isMatch) return;

                                el.classList.add('active');
                                const group = el.closest('.acc-group');
                                if (group) {
                                    window.accOpen(group, false);
                                    group.querySelector('.acc-trigger')?.classList.add('acc-open');
                                }
                            } catch (_) {}
                        });
                    }

                    /* Core navigate function */
                    let isNavigating = false;

                    async function navigate(url, pushState = true) {
                            if (isNavigating) return;
                            isNavigating = true;
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            try {
                                const t = new URL(url, location.origin);
                                if (t.origin !== location.origin) {
                                    isNavigating = false; // ✅ reset
                                    return;
                                }
                            } catch (_) {
                                isNavigating = false; // ✅ reset
                                return;
                            }

                            progressStart();

                            try {
                                const res = await fetch(url, {
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
                                    }
                                });

                                // Advance progress bar after network response
                                progress.style.width = '70%';

                                // Session expired / redirected to login
                                if (res.redirected && res.url.includes('login')) {
                                    location.href = res.url;
                                    return;
                                }

                                if (!res.ok) throw new Error('HTTP ' + res.status);

                                const html = await res.text();
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');

                                // Extract partial response zones
                                const newContent = doc.getElementById('ajax-main-content');
                                const newHeader = doc.getElementById('ajax-header-content');
                                const newTitle = doc.querySelector('title');

                                // Safety — if partial zones missing, fall back to hard navigation
                                if (!newContent) {
                                    location.href = url;
                                    return;
                                }

                                // Inject any page-specific <style> blocks into <head> (avoid duplicates)
                                // Extract raw style content from <script type="text/plain"> — DOMParser never moves these
                                const ajaxStylesEl = doc.getElementById('ajax-styles');
                                if (ajaxStylesEl) {
                                    const rawStyles = ajaxStylesEl.textContent.trim();
                                    if (rawStyles) {
                                        // Hash based on content to avoid duplicates
                                        let hash = 0;
                                        for (let i = 0; i < rawStyles.length; i++) {
                                            hash = ((hash << 5) - hash) + rawStyles.charCodeAt(i);
                                            hash |= 0;
                                        }
                                        const key = 'dyn-style-' + Math.abs(hash);
                                        if (!document.getElementById(key)) {
                                            // Remove any previously injected page styles
                                            document.querySelectorAll('style[data-spa-page]').forEach(s => s.remove());
                                            // Parse and inject fresh
                                            const tmp = document.createElement('div');
                                            tmp.innerHTML = rawStyles;
                                            tmp.querySelectorAll('style').forEach(s => {
                                                s.id  = key;
                                                s.setAttribute('data-spa-page', '1');
                                                document.head.appendChild(s);
                                            });
                                        }
                                    }
                                }

                        // Destroy Alpine trees on old content
                        if (window.Alpine) {
                            getContent().querySelectorAll('[x-data]').forEach(el => {
                                try {
                                    if (typeof window.Alpine.destroyTree === 'function') {
                                        window.Alpine.destroyTree(el);
                                    } else if (el._x_dataStack) {
                                        // Manual cleanup for older Alpine
                                        delete el._x_dataStack;
                                        delete el._x_effects;
                                    }
                                } catch (_) {}
                            });
                        }

                        // Swap content
                        getContent().innerHTML = newContent.innerHTML;

                        // Swap header title
                        if (newHeader && getHeaderTitle()) {
                            getHeaderTitle().innerHTML = newHeader.innerHTML;
                        }

                        // Update tab title
                        if (newTitle) document.title = newTitle.textContent;

                        // Push URL to browser history
                        if (pushState) history.pushState({
                            url
                        }, '', url);

                        // Scroll content to top
                        getContent().scrollTop = 0;

                        // Re-execute page <script> tags (registers Alpine.data / window components)
                        rerunScripts(getContent());

                        // Re-dispatch alpine:init so any page scripts that used
                        // document.addEventListener('alpine:init', ...) get a chance to run.
                        // alpine:init only fires once at startup — AJAX navigations miss it.
                        document.dispatchEvent(new CustomEvent('alpine:init'));

                        // Init icons before Alpine (catches static data-lucide)
                        window.initIcons(getContent());
                        
                        // Init Alpine synchronously BEFORE the browser paints the new DOM
                        if (window.Alpine) {
                            window.Alpine.initTree(getContent());
                        }

                        // Re-run icons immediately after Alpine processes the DOM
                        window.initIcons(getContent());

                        // Update nav active state
                        updateActiveNav(url);

                        // Close mobile sidebar
                        if (window.innerWidth < 1024) closeSidebar();

                        // 🌟 THE FIX: Give the browser a split second to paint the new DOM 
                        // and run the injected scripts before removing the loading bar.
                        setTimeout(() => {
                            // Notify announcement popup to re-check            
                            window.dispatchEvent(new CustomEvent('spa:navigated'));
                            document.dispatchEvent(new CustomEvent('spa:page-loaded'));
                            progressDone();
                            isNavigating = false;
                        }, 100);


                        progressDone();
                        isNavigating = false;

                    } catch (err) {
                        console.error('[navigate] error:', err);
                        progressDone();
                        isNavigating = false;
                        location.href = url; // hard fallback
                    }
                }

                /* Intercept sidebar link clicks */
                document.addEventListener('click', function(e) {
                        const link = e.target.closest('a[href]');
                        if (!link) return;

                        // Let browser handle modifier clicks (Ctrl+click, Cmd+click = new tab)
                        if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
                        if (e.button !== 0) return;

                        const href = link.getAttribute('href');

                        if (!href || href === '#') return;
                        if (link.target === '_blank') return;
                        if (href.startsWith('mailto:')) return;
                        if (href.startsWith('tel:')) return;
                        if (href.startsWith('javascript:')) return;

                        try {
                            const url = new URL(href, location.origin);
                            if (url.origin !== location.origin) return;
                        } catch (_) {
                            return;
                        }

                        if (link.hasAttribute('data-no-spa')) return;

                        e.preventDefault();
                        navigate(link.href);
                    });            

                /* Browser back / forward */
                // window.addEventListener('popstate', function(e) {
                //     // Whether or not state exists, navigate to current URL
                //     const url = e.state?.url || location.href;
                //     navigate(url, false);
                // });
                // Browser back / forward
                window.addEventListener('popstate', function(e) {
                    const url = e.state?.url || location.href;

                    // If navigating back to/from the POS (or any standalone page),
                    // do a hard reload instead of AJAX — they don't share the admin layout
                    const standaloneRoutes = ['/admin/pos'];
                    const currentIsSpa = !standaloneRoutes.some(r => location.pathname.startsWith(r));
                    const targetIsSpa  = !standaloneRoutes.some(r => new URL(url, location.origin).pathname.startsWith(r));

                    if (!currentIsSpa || !targetIsSpa) {
                        location.href = url;
                        return;
                    }

                    navigate(url, false);
                });
                /* Set initial history state */               
                history.replaceState({ url: location.href }, '', location.href);

            })();
            
        </script>
                              
          <script src="{{ asset('assets/js/swal.js') }}"></script>

        {{-- ══════════════════════════════════════════════════════
            ANNOUNCEMENT POPUP — auto-loads on page ready
        ══════════════════════════════════════════════════════ --}}
        <div x-data="announcementPopup()" x-init="init()" x-cloak>
            <template x-if="announcements.length > 0">
                <div class="fixed inset-0 z-[9999] flex items-center justify-center"
                     @keydown.escape.window="dismissCurrent()">

                    {{-- Backdrop --}}
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                         :class="currentAnnouncement?.requires_acknowledgement ? '' : 'cursor-pointer'"
                         @click="!currentAnnouncement?.requires_acknowledgement && dismissCurrent()"></div>

                    {{-- Modal --}}
                    <div class="relative w-full max-w-lg mx-4 sm:mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden animate-[slideUp_0.3s_ease-out]"
                         style="max-height: 90vh;">

                        {{-- Counter badge --}}
                        <div x-show="announcements.length > 1"
                             class="absolute top-4 right-4 z-10 bg-gray-900/80 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">
                            <span x-text="currentIndex + 1"></span> / <span x-text="announcements.length"></span>
                        </div>

                        {{-- Type ribbon --}}
                        <div class="px-6 pt-5 pb-3" :style="'background:' + (currentAnnouncement?.type_color?.bg || '#f3f4f6')">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                                     :style="'background:' + (currentAnnouncement?.type_color?.text || '#374151') + '15'">
                                    <svg x-show="currentAnnouncement?.requires_acknowledgement" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                         :style="'color:' + (currentAnnouncement?.type_color?.text || '#374151')">
                                        <path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <svg x-show="!currentAnnouncement?.requires_acknowledgement" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                         :style="'color:' + (currentAnnouncement?.type_color?.text || '#374151')">
                                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider"
                                          :style="'color:' + (currentAnnouncement?.type_color?.text || '#374151')"
                                          x-text="currentAnnouncement?.type_label"></span>
                                    <span x-show="currentAnnouncement?.requires_acknowledgement"
                                          class="ml-1.5 text-[9px] font-bold uppercase tracking-wider text-red-600 bg-red-50 px-1.5 py-0.5 rounded">
                                        Mandatory
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="px-6 py-4 overflow-y-auto" style="max-height: 55vh;">
                            <h2 class="text-base font-bold text-gray-900 leading-snug mb-1"
                                x-text="currentAnnouncement?.title"></h2>
                            <p class="text-[11px] text-gray-400 mb-4" x-text="currentAnnouncement?.published_at"></p>

                            <div class="prose prose-sm max-w-none text-gray-600 text-[13px] leading-relaxed"
                                 x-html="currentAnnouncement?.content"></div>

                            {{-- Attachment --}}
                            <template x-if="currentAnnouncement?.attachment_url">
                                <a :href="currentAnnouncement.attachment_url" target="_blank"
                                   class="mt-4 inline-flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg text-[12px] font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
                                    </svg>
                                    <span x-text="currentAnnouncement.attachment_name || 'Attachment'"></span>
                                </a>
                            </template>
                        </div>

                        {{-- Priority indicator --}}
                        <template x-if="currentAnnouncement?.priority === 'critical' || currentAnnouncement?.priority === 'high'">
                            <div class="mx-6 mb-3 px-3 py-2 rounded-lg text-[11px] font-bold"
                                 :class="currentAnnouncement.priority === 'critical' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'">
                                <span x-text="currentAnnouncement.priority === 'critical' ? '⚠ Critical Priority' : '⚡ High Priority'"></span>
                            </div>
                        </template>

                        {{-- Actions --}}
                        <div class="px-6 py-4 border-t border-gray-100 flex items-center gap-3"
                             :class="currentAnnouncement?.requires_acknowledgement ? 'justify-center' : 'justify-between'">

                            {{-- Dismiss (non-mandatory only) --}}
                            <button x-show="!currentAnnouncement?.requires_acknowledgement"
                                    @click="dismissCurrent()"
                                    :disabled="processing"
                                    class="px-4 py-2.5 text-[12px] font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
                                Dismiss
                            </button>

                            {{-- Acknowledge / Accept --}}
                            <button @click="acknowledgeCurrent()"
                                    :disabled="processing"
                                    class="px-6 py-2.5 rounded-xl text-[12px] font-bold text-white transition-all"
                                    :class="processing ? 'bg-gray-400 cursor-wait' : (currentAnnouncement?.requires_acknowledgement ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700')">
                                <span x-show="!processing" x-text="currentAnnouncement?.requires_acknowledgement ? 'I Accept & Acknowledge' : 'Got It'"></span>
                                <span x-show="processing" class="flex items-center gap-2">
                                    <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"/>
                                        <path fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <script>
        window.announcementPopup = function() {
            return {
                announcements: [],
                currentIndex: 0,
                processing: false,
                loaded: false,

                get currentAnnouncement() {
                    return this.announcements[this.currentIndex] || null;
                },

                async init() {
                    // Small delay to not block initial page paint
                    await new Promise(r => setTimeout(r, 800));
                    await this.fetchPending();

                    // Re-check after every SPA navigation
                    window.addEventListener('spa:navigated', () => {
                        // Only re-fetch if popup is not currently showing
                        if (this.announcements.length === 0) {
                            this.fetchPending();
                        }
                    });

                    // Re-check when attendance scanner requests it
                    window.addEventListener('announcements:recheck', () => {
                        this.fetchPending();
                    });
                },

                async fetchPending() {
                    try {
                        const res = await fetch('{{ url("admin/announcements-popup/pending") }}', {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (!res.ok) return;
                        const json = await res.json();
                        this.announcements = json.data || [];

                        // Mark first one as read
                        if (this.announcements.length > 0) {
                            this.markRead(this.announcements[0].id);
                        }
                    } catch (e) {
                        console.error('Announcement popup fetch failed:', e);
                    }
                },

                async markRead(id) {
                    try {
                        await fetch(`{{ url("admin/announcements-popup") }}/${id}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                    } catch (_) {}
                },

                async acknowledgeCurrent() {
                    const ann = this.currentAnnouncement;
                    if (!ann || this.processing) return;

                    this.processing = true;
                    try {
                        const endpoint = ann.requires_acknowledgement ? 'acknowledge' : 'dismiss';
                        const res = await fetch(`{{ url("admin/announcements-popup") }}/${ann.id}/${endpoint}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (res.ok) {
                            this.removeCurrentAndAdvance();
                        }
                    } catch (e) {
                        console.error('Acknowledge failed:', e);
                    } finally {
                        this.processing = false;
                    }
                },

                async dismissCurrent() {
                    const ann = this.currentAnnouncement;
                    if (!ann || this.processing) return;

                    // Mandatory cannot be dismissed
                    if (ann.requires_acknowledgement) return;

                    this.processing = true;
                    try {
                        const res = await fetch(`{{ url("admin/announcements-popup") }}/${ann.id}/dismiss`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (res.ok) {
                            this.removeCurrentAndAdvance();
                        }
                    } catch (e) {
                        console.error('Dismiss failed:', e);
                    } finally {
                        this.processing = false;
                    }
                },

                removeCurrentAndAdvance() {
                    this.announcements.splice(this.currentIndex, 1);

                    if (this.announcements.length === 0) {
                        this.currentIndex = 0;
                        return;
                    }

                    if (this.currentIndex >= this.announcements.length) {
                        this.currentIndex = 0;
                    }

                    // Mark next one as read
                    if (this.announcements[this.currentIndex]) {
                        this.markRead(this.announcements[this.currentIndex].id);
                    }
                }
            };
        };
        </script>

        <style>
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(30px) scale(0.97); }
                to   { opacity: 1; transform: translateY(0)    scale(1); }
            }
        </style>

        @stack('scripts')
    </body>

    </html>
@endif
