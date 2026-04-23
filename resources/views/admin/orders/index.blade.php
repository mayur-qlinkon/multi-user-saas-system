@extends('layouts.admin')

@section('title', 'Order Inquiries')

@section('header-title')
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Orders</h1>        
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .stat-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 14px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .stat-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .filter-input {
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 13px;
        color: #1f2937;
        outline: none;
        font-family: inherit;
        background: #fff;
        transition: border-color 150ms ease;
    }
    .search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-wrapper i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: #9ca3af;
        width: 14px;
        height: 14px;
    }  

    .filter-input:focus { border-color: var(--brand-600); }

    select.filter-input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 14px;
        padding-right: 30px;
        appearance: none;
        cursor: pointer;
    }

    .order-row {
        display: grid;
        grid-template-columns: 2fr 1.8fr 1.2fr 1fr 1fr auto;
        gap: 12px;
        align-items: center;
        padding: 14px 16px;
        border-bottom: 1px solid #f3f4f6;
        transition: background 120ms ease;
        min-width: 950px; /* 🌟 ADD THIS LINE */
    }
    .order-row:hover { background: #fafafa; }
    .order-row:last-child { border-bottom: none; }
    
    .status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 700;
        white-space: nowrap;
    }

    .status-dot {
        width: 6px; height: 6px;
        border-radius: 50%; flex-shrink: 0;
    }

    .pay-badge {
        display: inline-flex; align-items: center;
        padding: 2px 8px; border-radius: 20px;
        font-size: 11px; font-weight: 600;
    }

</style>
@endpush

@section('content')
<div class="pb-10" x-data="{ showFilters: false }">

    {{-- ── Stats bar ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">

        <div class="stat-card">
            <div class="stat-icon bg-blue-50">
                <i data-lucide="package" class="w-5 h-5 text-blue-600"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total</p>
                <p class="text-xl font-black text-gray-900">{{ number_format($stats['total']) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-amber-50">
                <i data-lucide="inbox" class="w-5 h-5 text-amber-600"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Inquiries</p>
                <p class="text-xl font-black text-gray-900">{{ number_format($stats['inquiries']) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-green-50">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Confirmed</p>
                <p class="text-xl font-black text-gray-900">{{ number_format($stats['confirmed']) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-purple-50">
                <i data-lucide="truck" class="w-5 h-5 text-purple-600"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Shipped</p>
                <p class="text-xl font-black text-gray-900">{{ number_format($stats['shipped']) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-emerald-50">
                <i data-lucide="indian-rupee" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Revenue</p>
                <p class="text-xl font-black text-gray-900">₹{{ number_format($stats['revenue'], 0) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-orange-50">
                <i data-lucide="clock" class="w-5 h-5 text-orange-500"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Today</p>
                <p class="text-xl font-black text-gray-900">{{ number_format($stats['today']) }}</p>
            </div>
        </div>

    </div>

    {{-- ── Main card ── --}}
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">

        {{-- ── Toolbar ── --}}
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4">
            
            {{-- Title --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <h2 class="text-sm font-bold text-gray-700">
                    All Orders
                    <span class="text-gray-400 font-medium ml-1">({{ $orders->total() }})</span>
                </h2>
            </div>
            
            {{-- Actions Wrapper --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full xl:w-auto flex-wrap justify-end">
                
                <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto flex-wrap">
                    
                    {{-- Search --}}
                    <div class="search-wrapper w-full sm:w-auto">
                        <input type="text" name="q" value="{{ request('q') }}"
                            placeholder="Search order, name..."
                            class="filter-input w-full sm:w-[220px]">
                    </div>

                    {{-- Status Dropdown --}}
                    <select name="status" class="filter-input w-full sm:w-auto" onchange="this.form.submit()">
                        <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                        @foreach($statusColors as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Buttons Group (Search + Create 50/50 on mobile) --}}
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button type="submit"
                            class="flex-1 sm:flex-none px-4 py-2 rounded-xl text-sm font-bold text-white transition-colors hover:opacity-90"
                            style="background: var(--brand-600);">
                            Search
                        </button>
                        @if(has_permission('orders.create'))
                        <a href="{{ route('admin.orders.create') }}"
                            class="flex-1 sm:flex-none px-4 py-2 text-center rounded-xl text-sm font-bold text-white transition-colors hover:opacity-90"
                            style="background: var(--brand-600);">
                            Create
                        </a>
                        @endif
                    </div>

                    {{-- Clear Filters Link --}}
                    @if(request()->hasAny(['q', 'status', 'from', 'to', 'payment_status', 'source']))
                        <a href="{{ route('admin.orders.index') }}"
                            class="w-full sm:w-auto px-3 py-2 text-center rounded-xl text-sm font-semibold text-gray-500 hover:text-gray-700 border border-gray-200 hover:bg-gray-50 transition-colors flex items-center justify-center gap-1">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i> Clear
                        </a>
                    @endif
                </form>

                {{-- Toggle Filters Button --}}
                <button @click="showFilters = !showFilters"
                    class="w-full sm:w-auto justify-center px-4 py-2 rounded-xl text-sm font-semibold text-gray-500 hover:text-gray-700 border border-gray-200 hover:bg-gray-50 transition-colors flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                    Filters
                </button>

            </div>
        </div>

        {{-- ── Advanced filters ── --}}
        <div x-show="showFilters" x-cloak x-transition
            class="px-5 py-3 border-b border-gray-100 bg-gray-50/50">
            <form method="GET" action="{{ route('admin.orders.index') }}"
                class="flex flex-wrap items-end gap-3">
                @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Payment</label>
                    <select name="payment_status" class="filter-input">
                        <option value="">Any Payment</option>
                        @foreach(array_keys(\App\Models\Order::PAYMENT_STATUS_COLORS) as $ps)
                            <option value="{{ $ps }}" {{ request('payment_status') === $ps ? 'selected' : '' }}>
                                {{ ucfirst($ps) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Source</label>
                    <select name="source" class="filter-input">
                        <option value="">Any Source</option>
                        <option value="storefront" {{ request('source') === 'storefront' ? 'selected' : '' }}>Storefront</option>
                        <option value="whatsapp"   {{ request('source') === 'whatsapp'   ? 'selected' : '' }}>WhatsApp</option>
                        <option value="admin"      {{ request('source') === 'admin'      ? 'selected' : '' }}>Admin</option>
                        <option value="pos"        {{ request('source') === 'pos'        ? 'selected' : '' }}>POS</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">From Date</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="filter-input">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">To Date</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="filter-input">
                </div>

                <button type="submit"
                    class="px-4 py-2 rounded-xl text-sm font-bold text-white hover:opacity-90 transition-colors"
                    style="background: var(--brand-600);">
                    Apply
                </button>
            </form>
        </div>
        <div class="overflow-x-auto">
        {{-- ── Table header ── --}}
        <div class="order-row bg-gray-50/80 border-b border-gray-100">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Order / Customer</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Items & Address</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Payment</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Action</span>
        </div>

        {{-- ── Order rows ── --}}
        @forelse($orders as $order)
            @php
                $sc = $order->status_color;
                $pc = $order->payment_status_color;
            @endphp
            <div class="order-row">

                {{-- Order + Customer ── --}}
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="font-mono text-[13px] font-bold text-gray-900">{{ $order->order_number }}</span>
                        @if($order->source === 'storefront')
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 uppercase tracking-wide">Web</span>
                        @elseif($order->source === 'whatsapp')
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-green-50 text-green-600 uppercase tracking-wide">WA</span>
                        @endif
                    </div>
                    <p class="text-[13px] font-semibold text-gray-700 truncate">{{ $order->customer_name }}</p>
                    <p class="text-[11px] text-gray-400 font-medium font-mono">{{ $order->customer_phone }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                </div>

                {{-- Items + Address ── --}}
                <div class="min-w-0">
                    <p class="text-[12px] text-gray-600 font-medium mb-1">
                        {{ $order->items_count }} item{{ $order->items_count !== 1 ? 's' : '' }}
                        · {{ $order->items_qty }} qty
                    </p>
                    @if($order->items->isNotEmpty())
                        <p class="text-[11px] text-gray-400 truncate">
                            {{ $order->items->first()->product_name }}
                            @if($order->items->count() > 1)
                                +{{ $order->items->count() - 1 }} more
                            @endif
                        </p>
                    @endif
                    @if($order->delivery_city)
                        <p class="text-[11px] text-gray-400 flex items-center gap-1 mt-0.5">
                            <i data-lucide="map-pin" class="w-3 h-3"></i>
                            {{ $order->delivery_city }}{{ $order->delivery_state ? ', ' . $order->delivery_state : '' }}
                        </p>
                    @endif
                </div>

                {{-- Total ── --}}
                <div>
                    <p class="text-[14px] font-bold text-gray-900">₹{{ number_format($order->total_amount, 2) }}</p>
                    <p class="text-[11px] text-gray-400 font-medium uppercase">{{ $order->payment_method ?? 'COD' }}</p>
                </div>

                {{-- Status badge ── --}}
                <div>
                    <span class="status-badge"
                        style="background: {{ $sc['bg'] }}; color: {{ $sc['text'] }}">
                        <span class="status-dot" style="background: {{ $sc['dot'] }}"></span>
                        {{ $order->status_label }}
                    </span>
                </div>

                {{-- Payment badge ── --}}
                <div>
                    <span class="pay-badge"
                        style="background: {{ $pc['bg'] }}; color: {{ $pc['text'] }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>

                {{-- Actions ── --}}
                <div class="flex items-center gap-2">
                    @if(has_permission('orders.view'))
                    <a href="{{ route('admin.orders.show', $order->id) }}"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                        title="View Details">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </a>
                    @endif

                    {{-- 🌟 NEW: Edit Button (Only for Admin orders that are not fulfilled/cancelled) --}}
                    @if($order->source === 'admin' && in_array($order->status, ['inquiry', 'confirmed', 'processing']))
                        <a href="{{ route('admin.orders.edit', $order->id) }}"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                            title="Edit Order">
                            <i data-lucide="edit" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @if($order->customer_phone && get_setting('whatsapp'))
                        <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $order->customer_phone) }}?text={{ urlencode('Hi ' . $order->customer_name . ', your order #' . $order->order_number . ' has been received. We will confirm shortly.') }}"
                            target="_blank"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-green-600 hover:bg-green-50 transition-colors"
                            title="WhatsApp Customer">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-20 text-center text-gray-400">
                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                    <i data-lucide="package-x" class="w-7 h-7 text-gray-300"></i>
                </div>
                <p class="font-semibold text-gray-500 mb-1">No orders found</p>
                <p class="text-sm">
                    @if(request()->hasAny(['q', 'status', 'from', 'to']))
                        Try clearing your filters
                    @else
                        Orders from your storefront will appear here
                    @endif
                </p>
                @if(request()->hasAny(['q', 'status', 'from', 'to']))
                    <a href="{{ route('admin.orders.index') }}"
                        class="mt-3 text-sm font-bold px-4 py-2 rounded-xl text-white"
                        style="background: var(--brand-600);">
                        Clear Filters
                    </a>
                @endif
            </div>
        @endforelse
        </div>

    </div>

    {{-- ── Pagination ── --}}
    @if($orders->hasPages())
        <div class="flex items-center justify-between mt-5 flex-wrap gap-3">
            <p class="text-[12px] text-gray-400 font-medium">
                Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }} orders
            </p>
            <div class="flex items-center gap-1.5">
                @if($orders->onFirstPage())
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </span>
                @else
                    <a href="{{ $orders->previousPageUrl() }}"
                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                @endif

                @foreach($orders->getUrlRange(max(1, $orders->currentPage() - 2), min($orders->lastPage(), $orders->currentPage() + 2)) as $page => $url)
                    @if($page == $orders->currentPage())
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-bold text-white"
                            style="background: var(--brand-600);">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}"
                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                @else
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </span>
                @endif
            </div>
        </div>
    @endif

</div>
@endsection