@extends('layouts.admin')

@section('title', 'Inventory Report - Qlinkon BIZNESS')

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">REPORTS / Inventory</h1>
@endsection

@section('content')
    <div class="pb-20" x-data="{ 
        activeTab: new URLSearchParams(window.location.search).get('tab') || 'master',
        expandedRows: [],
        toggleRow(id) {
            if (this.expandedRows.includes(id)) {
                this.expandedRows = this.expandedRows.filter(rowId => rowId !== id);
            } else {
                this.expandedRows.push(id);
            }
        },
        setTab(tab) {
            this.activeTab = tab;
            // Update URL without reloading so pagination links grab the correct tab
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }
    }">

        {{-- 1. TOP METRICS OVERVIEW --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4">
                <div class="bg-blue-50 p-4 rounded-lg text-blue-600">
                    <i data-lucide="boxes" class="w-8 h-8"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Valuation</p>
                    <h2 class="text-3xl font-black text-gray-900">₹ {{ number_format($totalValuation, 2) }}</h2>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4">
                <div class="bg-red-50 p-4 rounded-lg text-red-600">
                    <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Low Stock Alerts</p>
                    <h2 class="text-3xl font-black text-gray-900">{{ $lowStockAlerts->total() }} <span class="text-sm font-medium text-gray-500">Items</span></h2>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4">
                <div class="bg-green-50 p-4 rounded-lg text-green-600">
                    <i data-lucide="arrow-left-right" class="w-8 h-8"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Recent Movements</p>
                    <h2 class="text-3xl font-black text-gray-900">{{ $movements->total() }} <span class="text-sm font-medium text-gray-500">Logs</span></h2>
                </div>
            </div>
        </div>

        {{-- 2. REPORT TABS --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            
            {{-- Tab Headers --}}
            <div class="flex border-b border-gray-200 bg-gray-50 px-2 pt-2 gap-2 overflow-x-auto no-scrollbar">
                <button @click="setTab('master')" 
                    :class="activeTab === 'master' ? 'bg-white text-blue-600 border-t-2 border-t-blue-600 shadow-sm' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                    class="px-6 py-3 text-sm font-bold uppercase tracking-wider rounded-t-lg transition-all whitespace-nowrap">
                    <i data-lucide="database" class="w-4 h-4 inline-block mr-1.5 pb-0.5"></i> Master Stock
                </button>
                <button @click="setTab('alerts')" 
                    :class="activeTab === 'alerts' ? 'bg-white text-red-600 border-t-2 border-t-red-600 shadow-sm' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                    class="px-6 py-3 text-sm font-bold uppercase tracking-wider rounded-t-lg transition-all whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 inline-block pb-0.5"></i> 
                    Reorder Alerts
                    @if($lowStockAlerts->total() > 0)
                        <span class="bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full">{{ $lowStockAlerts->total() }}</span>
                    @endif
                </button>
                <button @click="setTab('ledger')" 
                    :class="activeTab === 'ledger' ? 'bg-white text-gray-900 border-t-2 border-t-gray-800 shadow-sm' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                    class="px-6 py-3 text-sm font-bold uppercase tracking-wider rounded-t-lg transition-all whitespace-nowrap">
                    <i data-lucide="history" class="w-4 h-4 inline-block mr-1.5 pb-0.5"></i> Movement Ledger
                </button>
            </div>

            {{-- 3. TAB CONTENT: MASTER STOCK --}}
            <div x-show="activeTab === 'master'" x-cloak class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-white border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4 w-12"></th>
                                <th class="px-6 py-4">Product & SKU</th>
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4 text-right">Unit COGS</th>
                                <th class="px-6 py-4 text-right">Total Qty</th>
                                <th class="px-6 py-4 text-right">Total Value</th>
                                <th class="px-6 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($masterStock as $sku)
                                @php
                                    $isLow = $sku->total_qty <= $sku->stock_alert;
                                    $isOut = $sku->total_qty <= 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" @click="toggleRow({{ $sku->id }})">
                                    <td class="px-6 py-4 text-center">
                                        <i data-lucide="chevron-down" 
                                           class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                           :class="expandedRows.includes({{ $sku->id }}) ? 'rotate-180' : ''"></i>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 text-sm">{{ $sku->product?->name ?? 'Unknown Product' }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono mt-0.5">SKU: {{ $sku->sku }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-gray-600">
                                        {{ $sku->product->category->name ?? 'Uncategorized' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-gray-700">
                                        ₹ {{ number_format($sku->cost, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-black {{ $isOut ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ (float) $sku->total_qty }} 
                                        </span>
                                        <span class="text-[10px] text-gray-500 ml-1">{{ $sku->product->productUnit->short_name ?? 'Units' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-brand-600">
                                        ₹ {{ number_format($sku->total_qty * $sku->cost, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($isOut)
                                            <span class="bg-red-50 text-red-700 border border-red-200 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">Out of Stock</span>
                                        @elseif ($isLow)
                                            <span class="bg-orange-50 text-orange-700 border border-orange-200 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">Low Stock</span>
                                        @else
                                            <span class="bg-green-50 text-green-700 border border-green-200 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">Healthy</span>
                                        @endif
                                    </td>
                                </tr>
                                
                                {{-- EXPANDABLE: Warehouse Breakdown --}}
                                <tr x-show="expandedRows.includes({{ $sku->id }})" x-cloak class="bg-gray-50 border-b border-gray-200">
                                    <td colspan="7" class="px-14 py-4">
                                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                                            <div class="bg-gray-100 px-4 py-2 border-b border-gray-200 text-[10px] font-bold text-gray-500 uppercase tracking-widest">
                                                Warehouse Breakdown
                                            </div>
                                            <table class="w-full text-left text-xs">
                                                <tbody class="divide-y divide-gray-100">
                                                    @forelse($sku->stocks as $stock)
                                                        <tr>
                                                            <td class="px-4 py-2 font-medium text-gray-800">
                                                                {{ $stock->warehouse->name }}
                                                                <span class="text-gray-400 ml-1">({{ $stock->warehouse->store->name ?? 'Primary' }})</span>
                                                            </td>
                                                            <td class="px-4 py-2 text-right font-bold text-gray-900">
                                                                {{ (float) $stock->qty }}
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="2" class="px-4 py-3 text-center text-gray-400 italic">No physical stock recorded in any warehouse.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 font-medium">No inventory data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $masterStock->appends(['tab' => 'master'])->links() }}
                </div>
            </div>

            {{-- 4. TAB CONTENT: LOW STOCK ALERTS --}}
            <div x-show="activeTab === 'alerts'" x-cloak class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-red-50 border-b border-red-100 text-[11px] font-bold text-red-800 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Product & SKU</th>
                                <th class="px-6 py-4 text-center">Alert Threshold</th>
                                <th class="px-6 py-4 text-center">Current Qty</th>
                                <th class="px-6 py-4 text-center">Deficit</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($lowStockAlerts as $alert)
                                @php $deficit = $alert->stock_alert - $alert->current_qty; @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 text-sm">{{ $alert->product->name }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono mt-0.5">SKU: {{ $alert->sku }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-semibold text-gray-600">
                                        {{ (float) $alert->stock_alert }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-black text-lg {{ $alert->current_qty <= 0 ? 'text-red-600' : 'text-orange-600' }}">
                                            {{ (float) $alert->current_qty }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">
                                            -{{ (float) $deficit }} Short
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        {{-- Placeholder for future Purchase Order feature --}}
                                        <a href="{{ route('admin.purchases.create', ['sku_id' => $alert->id, 'qty' => $deficit]) }}" 
                                        class="inline-block bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1.5 rounded text-xs font-bold transition-colors shadow-sm text-center">
                                            Reorder
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center text-green-600">
                                            <i data-lucide="check-circle" class="w-12 h-12 mb-3 opacity-50"></i>
                                            <p class="font-bold text-lg">All Stock is Healthy!</p>
                                            <p class="text-sm text-gray-500 mt-1">No items are currently below their alert thresholds.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $lowStockAlerts->appends(['tab' => 'alerts'])->links() }}
                </div>
            </div>

            {{-- 5. TAB CONTENT: MOVEMENT LEDGER --}}
            <div x-show="activeTab === 'ledger'" x-cloak class="p-0">
                
                {{-- Optional Search Bar for Ledger --}}
                <div class="p-4 border-b border-gray-100 bg-white flex justify-end">
                    <form method="GET" class="relative w-full md:w-72">
                        <input type="hidden" name="tab" value="ledger">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="search_movement" value="{{ request('search_movement') }}" placeholder="Search SKU or Ref ID..."
                            class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-gray-500 outline-none">
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Date & Time</th>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3">Warehouse</th>
                                <th class="px-4 py-3">Movement Type</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-right">Balance</th>
                                <th class="px-4 py-3">User</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($movements as $log)
                                <tr class="hover:bg-gray-50 transition-colors text-sm">
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                        <div class="font-bold text-gray-800">{{ $log->created_at->format('d M Y') }}</div>
                                        <div class="text-[10px]">{{ $log->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900 line-clamp-1">{{ $log->sku->product->name ?? 'Unknown' }}</div>
                                        <div class="text-[10px] text-gray-500 font-mono mt-0.5">SKU: {{ $log->sku->sku ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-700">
                                        {{ $log->warehouse->name ?? 'Unknown' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $badgeClass = match($log->movement_type) {
                                                'transfer_in', 'opening' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                'transfer_out' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                'sale' => 'bg-green-50 text-green-700 border-green-200',
                                                'adjustment' => 'bg-orange-50 text-orange-700 border-orange-200',
                                                default => 'bg-gray-50 text-gray-700 border-gray-200',
                                            };
                                        @endphp
                                        <span class="inline-block border px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest {{ $badgeClass }}">
                                            {{ str_replace('_', ' ', $log->movement_type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-lg {{ $log->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $log->quantity > 0 ? '+' : '' }}{{ (float) $log->quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900">
                                        {{ (float) $log->balance_after }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 font-medium">
                                        {{ $log->user->name ?? 'System' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 font-medium">No movements recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $movements->appends(['tab' => 'ledger', 'search_movement' => request('search_movement')])->links() }}
                </div>
            </div>

        </div>
    </div>
@endsection