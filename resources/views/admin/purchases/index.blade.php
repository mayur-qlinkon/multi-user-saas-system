@extends('layouts.admin')

@section('title', 'Purchases - Qlinkon BIZNESS')

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Purchases</h1>
@endsection

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="pb-10" x-data="purchaseIndex()">

        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("{{ session('success') }}", 'success'));
            </script>
        @endif
        @if (session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("{{ session('error') }}", 'error'));
            </script>
        @endif    
        {{-- SEARCH & FILTER BAR --}}
        <div class="bg-white rounded-t-xl shadow-sm border border-gray-100 p-4 border-b-0">
            <form action="{{ route('admin.purchases.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">

                {{-- 1. Search Group (Input + Search + Clear) --}}
                <div class="flex flex-row items-center gap-2 flex-1 max-w-lg w-full">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search PO Number, Supplier..."
                            class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all placeholder-gray-400">
                    </div>

                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm shrink-0">
                        Search
                    </button>

                    @if (request()->hasAny(['search', 'status', 'payment_status']))
                        <a href="{{ route('admin.purchases.index') }}" 
                            class="bg-red-50 hover:bg-red-100 text-red-500 w-10 h-10 rounded-lg flex items-center justify-center shrink-0 transition-colors" 
                            title="Clear Filters">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <div x-data="{ filterOpen: false }" @click.away="filterOpen = false" class="relative">
                    <button type="button" @click="filterOpen = !filterOpen"
                        class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors flex items-center gap-2 h-full">
                        <i data-lucide="filter" class="w-4 h-4 text-gray-500"></i> Filters
                        @if (request('status') || request('payment_status'))
                            <span class="w-2 h-2 rounded-full bg-red-500 ml-1"></span>
                        @endif
                    </button>

                    <div x-show="filterOpen" x-cloak x-transition
                        class="absolute right-0 mt-2 w-64 bg-white border border-gray-100 rounded-xl shadow-xl z-50 p-4">

                        <div class="mb-3">
                            <label
                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                            <select name="status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                <option value="">All Statuses</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Ordered
                                </option>
                                <option value="partially_received"
                                    {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partially Received
                                </option>
                                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received
                                </option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label
                                class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Payment</label>
                            <select name="payment_status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                <option value="">All Payments</option>
                                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid
                                </option>
                                <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>
                                    Partial</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid
                                </option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.purchases.index') }}"
                                class="px-3 py-1.5 text-xs font-bold text-gray-500 hover:text-gray-800 transition-colors">Clear</a>
                            <button type="submit"
                                class="bg-[#108c2a] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-[#0c6b1f] transition-colors">Apply</button>
                        </div>
                    </div>
                </div>

              {{-- 3. Create PO Button (Pushed to the right) --}}
                <div class="ml-auto flex shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                    @if(has_permission('purchases.create'))
                    <a href="{{ route('admin.purchases.create') }}"
                        class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2 whitespace-nowrap">
                        <i data-lucide="plus" class="w-4 h-4"></i> Create PO
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- DATA TABLE --}}
        <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            
            {{-- 🖥️ DESKTOP VIEW (TABLE) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-6 py-4">PO DETAILS</th>
                            <th class="px-6 py-4 hidden md:table-cell">SUPPLIER</th>
                            <th class="px-6 py-4 hidden md:table-cell">DESTINATION</th>
                            <th class="px-6 py-4 text-center hidden md:table-cell">STATUS</th>
                            <th class="px-6 py-4 text-center">PAYMENT</th>
                            <th class="px-6 py-4 text-right">TOTAL AMOUNT</th>
                            <th class="px-6 py-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($purchases as $purchase)
                            <tr class="hover:bg-gray-50/50 transition-colors group">

                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <a href="{{ route('admin.purchases.show', $purchase->id) }}"
                                            class="font-extrabold text-[#108c2a] text-[13px] hover:underline">
                                            {{ $purchase->purchase_number }}
                                        </a>
                                        <span class="text-[11px] text-gray-500 mt-0.5 font-medium">
                                            {{ $purchase->purchase_date->format('d M, Y') }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 hidden md:table-cell">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-gray-800 text-[13px]">{{ $purchase->supplier->name ?? 'Unknown' }}</span>
                                        @if ($purchase->supplier_invoice_number)
                                            <span class="text-[11px] text-gray-400 mt-0.5 font-mono">Inv:
                                                {{ $purchase->supplier_invoice_number }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 hidden md:table-cell">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-semibold text-gray-700 text-[12px]">{{ $purchase->warehouse->name ?? 'N/A' }}</span>
                                        @if ($purchase->store)
                                            <span
                                                class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">{{ $purchase->store->name }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center hidden md:table-cell">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
                                            'ordered' => 'bg-blue-50 text-blue-600 border-blue-200',
                                            'partially_received' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                            'received' => 'bg-green-50 text-green-700 border-green-200',
                                            'cancelled' => 'bg-red-50 text-red-600 border-red-200',
                                        ];
                                        $color = $statusColors[$purchase->status] ?? $statusColors['draft'];
                                    @endphp
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider border {{ $color }}">
                                        {{ str_replace('_', ' ', $purchase->status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @php
                                        $payColors = [
                                            'unpaid' => 'bg-red-50 text-red-600',
                                            'partial' => 'bg-orange-50 text-orange-600',
                                            'paid' => 'bg-green-50 text-green-700',
                                        ];
                                        $pColor = $payColors[$purchase->payment_status] ?? $payColors['unpaid'];
                                    @endphp
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider {{ $pColor }}">
                                        {{ $purchase->payment_status }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span
                                            class="font-extrabold text-gray-800">₹{{ number_format($purchase->total_amount, 2) }}</span>
                                        @if ($purchase->balance_amount > 0)
                                            <span class="text-[10px] font-bold text-red-500 mt-0.5">Bal:
                                                ₹{{ number_format($purchase->balance_amount, 2) }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 transition-opacity">

                                        @if(has_permission('purchases.view'))
                                        <a href="{{ route('admin.purchases.show', $purchase->id) }}"
                                            class="w-8 h-8 rounded border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                            title="View PO">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        @endif

                                        @if ($purchase->status !== 'received' && $purchase->status !== 'cancelled' && has_permission('purchases.update'))
                                            <a href="{{ route('admin.purchases.edit', $purchase->id) }}"
                                                class="w-8 h-8 rounded border border-blue-200 text-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors"
                                                title="Edit PO">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                        @endif

                                        @if ($purchase->status !== 'cancelled' && has_permission('purchases.add_payment'))
                                            <button type="button"
                                                @click="openPaymentModal({{ $purchase->id }}, '{{ $purchase->payment_status }}', {{ $purchase->total_amount }}, {{ $purchase->balance_amount }})"
                                                class="w-8 h-8 rounded border border-green-200 text-green-600 hover:bg-green-50 flex items-center justify-center transition-colors"
                                                title="Update Payment Status">
                                                <i data-lucide="indian-rupee" class="w-4 h-4"></i>
                                            </button>
                                        @endif

                                        @if ($purchase->status !== 'received')
                                            @if (has_permission('purchases.delete'))
                                                <form action="{{ route('admin.purchases.destroy', $purchase->id) }}"
                                                    method="POST" @submit.prevent="confirmDelete($event.target)"
                                                    class="inline-block">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="w-8 h-8 rounded border border-red-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"
                                                        title="Delete PO">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <div class="w-8 h-8 rounded border border-gray-100 text-gray-300 flex items-center justify-center cursor-not-allowed"
                                                title="Cannot delete received stock">
                                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                            </div>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="shopping-cart" class="w-10 h-10 mb-3 opacity-20"></i>
                                        <p class="text-sm font-medium">No purchase orders found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 📱 MOBILE VIEW (CARDS) --}}
            <div class="md:hidden divide-y divide-gray-50 border-t border-gray-50">
                @forelse ($purchases as $purchase)
                    @php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
                            'ordered' => 'bg-blue-50 text-blue-600 border-blue-200',
                            'partially_received' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            'received' => 'bg-green-50 text-green-700 border-green-200',
                            'cancelled' => 'bg-red-50 text-red-600 border-red-200',
                        ];
                        $color = $statusColors[$purchase->status] ?? $statusColors['draft'];
                        
                        $payColors = [
                            'unpaid' => 'bg-red-50 text-red-600',
                            'partial' => 'bg-orange-50 text-orange-600',
                            'paid' => 'bg-green-50 text-green-700',
                        ];
                        $pColor = $payColors[$purchase->payment_status] ?? $payColors['unpaid'];
                    @endphp
                    <div class="p-4 hover:bg-gray-50/50 transition-colors flex flex-col gap-3">
                        
                        {{-- Header: Supplier & Total --}}
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-gray-800 text-[14px] truncate">
                                    {{ $purchase->supplier->name ?? 'Unknown' }}
                                </p>
                                @if ($purchase->supplier_invoice_number)
                                    <p class="text-[11px] text-gray-400 mt-0.5 font-mono truncate">
                                        Inv: {{ $purchase->supplier_invoice_number }}
                                    </p>
                                @else
                                    <p class="text-[11px] text-gray-400 mt-0.5 font-medium truncate">
                                        {{ $purchase->warehouse->name ?? 'N/A' }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right shrink-0 flex flex-col items-end">
                                <span class="font-black text-gray-800 text-[15px]">₹{{ number_format($purchase->total_amount, 2) }}</span>
                                @if ($purchase->balance_amount > 0)
                                    <span class="text-[10px] font-bold text-red-500 mt-0.5">Bal: ₹{{ number_format($purchase->balance_amount, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- PO Details & Badges --}}
                        <div class="flex flex-col gap-2 bg-gray-50/80 px-3 py-2.5 rounded-lg border border-gray-100">
                            <div class="flex justify-between items-center">
                                <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="font-extrabold text-[#108c2a] text-[13px] hover:underline">
                                    {{ $purchase->purchase_number }}
                                </a>
                                <span class="text-[11px] text-gray-500 font-medium">
                                    {{ $purchase->purchase_date->format('d M, Y') }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-gray-100/50">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider border {{ $color }}">
                                    {{ str_replace('_', ' ', $purchase->status) }}
                                </span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider {{ $pColor }}">
                                    {{ $purchase->payment_status }}
                                </span>
                                @if($purchase->store)
                                    <span class="text-gray-300">|</span>
                                    <span class="text-[9px] text-gray-500 uppercase tracking-widest font-semibold">
                                        {{ $purchase->store->name }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-2 pt-1 flex-wrap">
                            @if(has_permission('purchases.view'))
                                <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors" title="View PO">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                            @endif

                            @if ($purchase->status !== 'received' && $purchase->status !== 'cancelled' && has_permission('purchases.update'))
                                <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="w-8 h-8 rounded-lg border border-blue-200 text-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors" title="Edit PO">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </a>
                            @endif

                            @if ($purchase->status !== 'cancelled' && has_permission('purchases.add_payment'))
                                <button type="button" @click="openPaymentModal({{ $purchase->id }}, '{{ $purchase->payment_status }}', {{ $purchase->total_amount }}, {{ $purchase->balance_amount }})" class="w-8 h-8 rounded-lg border border-green-200 text-green-600 hover:bg-green-50 flex items-center justify-center transition-colors" title="Update Payment Status">
                                    <i data-lucide="indian-rupee" class="w-4 h-4"></i>
                                </button>
                            @endif

                            @if ($purchase->status !== 'received')
                                @if (has_permission('purchases.delete'))
                                    <form action="{{ route('admin.purchases.destroy', $purchase->id) }}" method="POST" @submit.prevent="confirmDelete($event.target)" class="inline-block m-0 p-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors" title="Delete PO">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            @else
                                <div class="w-8 h-8 rounded-lg border border-gray-100 text-gray-300 flex items-center justify-center cursor-not-allowed" title="Cannot delete received stock">
                                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-400 bg-white">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="shopping-cart" class="w-10 h-10 mb-3 opacity-20"></i>
                            <p class="font-medium text-gray-500 text-[13px]">No purchase orders found.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($purchases->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
        {{-- PAYMENT MODAL --}}
        <div x-show="paymentModalOpen" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all"
                @click.away="paymentModalOpen = false">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Update Payment</h3>
                    <button @click="paymentModalOpen = false" class="text-gray-400 hover:text-red-500"><i data-lucide="x"
                            class="w-5 h-5"></i></button>
                </div>

                <form @submit.prevent="submitPaymentForm($event)">
                    @csrf @method('PATCH')

                    <div class="p-6">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Payment
                            Status</label>
                        <select name="payment_status" x-model="paymentStatus" @change="handleStatusChange()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:border-[#108c2a] outline-none font-bold text-gray-700">
                            <option value="unpaid">Unpaid</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid (Full)</option>
                        </select>

                        <div x-show="paymentStatus === 'partial'" x-transition
                            class="mt-5 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Total
                                Amount Paid So Far</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">₹</span>
                                <input type="number" step="0.01" name="amount_paid" x-model="totalPaidInput"
                                    @input="handleAmountChange()"
                                    class="w-full border border-gray-300 rounded pl-7 pr-3 py-2 text-sm focus:border-[#108c2a] outline-none font-bold text-gray-800 bg-white">
                            </div>

                            <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-200">
                                <span class="text-[11px] font-bold text-gray-500 uppercase">PO Total:</span>
                                <span class="text-[12px] font-extrabold text-gray-800"
                                    x-text="'₹' + formatCurrency(totalAmount)"></span>
                            </div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-[11px] font-bold text-gray-500 uppercase">New Balance:</span>
                                <span class="text-[12px] font-extrabold text-red-600"
                                    x-text="'₹' + formatCurrency(Math.max(0, totalAmount - totalPaidInput))"></span>
                            </div>
                        </div>

                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="paymentModalOpen = false"
                            class="px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-bold text-white bg-[#108c2a] hover:bg-[#0c6b1f] rounded-lg transition-colors shadow-sm">Save
                            Update</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function purchaseIndex() {
            return {
                paymentModalOpen: false,
                selectedPurchaseId: null,
                paymentStatus: '',

                totalAmount: 0,
                balanceAmount: 0,
                totalPaidInput: 0,

                // Generates an environment-safe URL
                get paymentActionUrl() {
                    if (!this.selectedPurchaseId) return '#';
                    return "{{ route('admin.purchases.index') }}/" + this.selectedPurchaseId + "/payment";
                },

                openPaymentModal(id, status, total, balance) {
                    this.selectedPurchaseId = id;
                    this.paymentStatus = status;
                    this.totalAmount = parseFloat(total);
                    this.balanceAmount = parseFloat(balance);

                    // Calculate what they have already paid
                    this.totalPaidInput = this.totalAmount - this.balanceAmount;

                    this.paymentModalOpen = true;
                },

                handleStatusChange() {
                    if (this.paymentStatus === 'paid') {
                        this.totalPaidInput = this.totalAmount;
                    } else if (this.paymentStatus === 'unpaid') {
                        this.totalPaidInput = 0;
                    }
                },

                handleAmountChange() {
                    let input = parseFloat(this.totalPaidInput) || 0;

                    // If user types the full amount or more, auto-switch to Paid!
                    if (input >= this.totalAmount) {
                        this.paymentStatus = 'paid';
                        this.totalPaidInput = this.totalAmount;
                    } else if (input <= 0) {
                        this.paymentStatus = 'unpaid';
                    }
                },
                handleAmountChange() {
                    let input = parseFloat(this.totalPaidInput) || 0;

                    if (input >= this.totalAmount) {
                        this.paymentStatus = 'paid';
                        this.totalPaidInput = this.totalAmount;
                    } else if (input <= 0) {
                        this.paymentStatus = 'unpaid';
                    }
                },

                // 🌟 NEW: Highly resilient AJAX submission
                async submitPaymentForm(e) {
                    BizAlert.loading('Updating Payment...');
                    let token = e.target.querySelector('input[name="_token"]').value;

                    try {
                        let response = await fetch(this.paymentActionUrl, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                payment_status: this.paymentStatus,
                                amount_paid: this.totalPaidInput
                            })
                        });

                        let data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Something went wrong on the server.');
                        }

                        BizAlert.toast('Payment successfully updated!', 'success');

                        // Reload the page smoothly after 1 second to show updated data
                        setTimeout(() => window.location.reload(), 1000);

                    } catch (error) {
                        BizAlert.toast(error.message, 'error');
                    }
                },

                formatCurrency(value) {
                    return parseFloat(value).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                confirmDelete(form) {
                    BizAlert.confirm(
                        'Delete Purchase Order?',
                        'This action cannot be undone. Any drafted items will be permanently removed.',
                        'Yes, Delete'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Deleting...');
                            form.submit();
                        }
                    });
                }
            }
        }
    </script>
@endpush
