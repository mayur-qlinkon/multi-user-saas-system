@extends('layouts.admin')

@section('title', 'Purchase Returns - Qlinkon BIZNESS')

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush
@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Purchase / Returns</h1>
@endsection

@section('content')
    <div class="pb-10" x-data="purchaseReturnIndex()">

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

        {{-- HEADER & ACTIONS --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>                
                <p class="text-[13px] text-gray-500 font-medium mt-1">Manage return to vendor records and expected refunds</p>
            </div>
        </div>

        {{-- SEARCH & FILTER BAR --}}
        <div class="bg-white rounded-t-xl shadow-sm border border-gray-100 p-4 border-b-0">
            <form action="{{ route('admin.purchase-returns.index') }}" method="GET"
                class="flex flex-col sm:flex-row gap-3">

                {{-- 1. Search Group (Input + Search + Clear) --}}
                <div class="flex flex-row items-center gap-2 flex-1 max-w-lg w-full">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search Return No, PO No, Supplier..."
                            class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all placeholder-gray-400">
                    </div>

                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm shrink-0">
                        Search
                    </button>

                    @if (request()->hasAny(['search', 'status', 'payment_status']))
                        <a href="{{ route('admin.purchase-returns.index') }}" 
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
                                <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned
                                </option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Refund
                                Status</label>
                            <select name="payment_status"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#108c2a] outline-none bg-white">
                                <option value="">All Payments</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>
                                    Pending</option>
                                <option value="adjusted" {{ request('payment_status') == 'adjusted' ? 'selected' : '' }}>
                                    Adjusted</option>
                                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>
                                    Refunded</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.purchase-returns.index') }}"
                                class="px-3 py-1.5 text-xs font-bold text-gray-500 hover:text-gray-800 transition-colors">Clear</a>
                            <button type="submit"
                                class="bg-[#108c2a] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-[#0c6b1f] transition-colors">Apply</button>
                        </div>
                    </div>
                </div>

               {{-- 3. Create Return Button (Pushed to the right) --}}
                <div class="ml-auto flex shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                    <a href="{{ route('admin.purchase-returns.create') }}"
                        class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2 whitespace-nowrap">
                        <i data-lucide="plus" class="w-4 h-4"></i> Create Return
                    </a>
                </div>
            </form>
        </div>

        {{-- DATA TABLE --}}
        <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead
                        class="text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-6 py-4">RETURN DETAILS</th>
                            <th class="px-6 py-4">SUPPLIER</th>
                            <th class="px-6 py-4">DESTINATION</th>
                            <th class="px-6 py-4 text-center">STATUS</th>
                            <th class="px-6 py-4 text-center">REFUND STATUS</th>
                            <th class="px-6 py-4 text-right">EXPECTED REFUND</th>
                            <th class="px-6 py-4 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($purchaseReturns as $return)
                            <tr class="hover:bg-gray-50/50 transition-colors group">

                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <a href="{{ route('admin.purchase-returns.show', $return->id) }}"
                                            class="font-extrabold text-[#108c2a] text-[13px] hover:underline">
                                            {{ $return->return_number }}
                                        </a>
                                        <span class="text-[11px] text-gray-500 mt-0.5 font-medium">
                                            {{ $return->return_date->format('d M, Y') }}
                                        </span>
                                        @if ($return->purchase)
                                            <a href="{{ route('admin.purchases.show', $return->purchase_id) }}"
                                                class="text-[10px] text-blue-500 hover:text-blue-700 mt-1 uppercase">
                                                Ref: {{ $return->purchase->purchase_number }}
                                            </a>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-gray-800 text-[13px]">{{ $return->supplier->name ?? 'Unknown' }}</span>
                                        @if ($return->supplier_credit_note_number)
                                            <span class="text-[11px] text-gray-400 mt-0.5 font-mono">CN:
                                                {{ $return->supplier_credit_note_number }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-semibold text-gray-700 text-[12px]">{{ $return->warehouse->name ?? 'N/A' }}</span>
                                        @if ($return->store)
                                            <span
                                                class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">{{ $return->store->name }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-600 border-gray-200',
                                            'returned' => 'bg-green-50 text-green-700 border-green-200',
                                            'cancelled' => 'bg-red-50 text-red-600 border-red-200',
                                        ];
                                        $color = $statusColors[$return->status] ?? $statusColors['draft'];
                                    @endphp
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider border {{ $color }}">
                                        {{ str_replace('_', ' ', $return->status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @php
                                        $payColors = [
                                            'pending' => 'bg-orange-50 text-orange-600',
                                            'adjusted' => 'bg-blue-50 text-blue-600',
                                            'refunded' => 'bg-green-50 text-green-700',
                                        ];
                                        $pColor = $payColors[$return->payment_status] ?? $payColors['pending'];
                                    @endphp
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider {{ $pColor }}">
                                        {{ $return->payment_status }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span
                                            class="font-extrabold text-gray-800">₹{{ number_format($return->total_amount, 2) }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">

                                        <a href="{{ route('admin.purchase-returns.show', $return->id) }}"
                                            class="w-8 h-8 rounded border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                            title="View Return">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>

                                        @if ($return->status !== 'cancelled')
                                            <button type="button"
                                                @click="openPaymentModal({{ $return->id }}, '{{ $return->payment_status }}')"
                                                class="w-8 h-8 rounded border border-green-200 text-green-600 hover:bg-green-50 flex items-center justify-center transition-colors"
                                                title="Update Refund Status">
                                                <i data-lucide="indian-rupee" class="w-4 h-4"></i>
                                            </button>
                                        @endif

                                        @if ($return->status !== 'returned' && $return->status !== 'cancelled')
                                            <a href="{{ route('admin.purchase-returns.edit', $return->id) }}"
                                                class="w-8 h-8 rounded border border-blue-200 text-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors"
                                                title="Edit Return">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                        @endif

                                        @if ($return->status !== 'returned')
                                            <form action="{{ route('admin.purchase-returns.destroy', $return->id) }}"
                                                method="POST" @submit.prevent="confirmDelete($event.target)"
                                                class="inline-block">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded border border-red-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors"
                                                    title="Delete Return">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @else
                                            <div class="w-8 h-8 rounded border border-gray-100 text-gray-300 flex items-center justify-center cursor-not-allowed"
                                                title="Cannot delete finalized return">
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
                                        <i data-lucide="corner-up-left" class="w-10 h-10 mb-3 opacity-20"></i>
                                        <p class="text-sm font-medium">No purchase returns found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($purchaseReturns->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $purchaseReturns->links() }}
                </div>
            @endif
        </div>
        {{-- REFUND / PAYMENT MODAL --}}
        <div x-show="paymentModalOpen" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all"
                @click.away="paymentModalOpen = false">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Update Refund Status</h3>
                    <button @click="paymentModalOpen = false" class="text-gray-400 hover:text-red-500"><i data-lucide="x"
                            class="w-5 h-5"></i></button>
                </div>

                <form @submit.prevent="submitPaymentForm($event)">
                    @csrf
                    <div class="p-6">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Select
                            Status</label>
                        <select name="payment_status" x-model="paymentStatus"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:border-[#108c2a] outline-none font-bold text-gray-700">
                            <option value="pending">Pending (Waiting for Supplier)</option>
                            <option value="adjusted">Adjusted (Credit Note Applied)</option>
                            <option value="refunded">Refunded (Cash/Bank Received)</option>
                        </select>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="paymentModalOpen = false"
                            class="px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-bold text-white bg-[#108c2a] hover:bg-[#0c6b1f] rounded-lg transition-colors shadow-sm">Save
                            Status</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function purchaseReturnIndex() {
            return {
                paymentModalOpen: false,
                selectedReturnId: null,
                paymentStatus: '',

                get paymentActionUrl() {
                    if (!this.selectedReturnId) return '#';
                    return "{{ route('admin.purchase-returns.index') }}/" + this.selectedReturnId + "/payment";
                },

                openPaymentModal(id, status) {
                    this.selectedReturnId = id;
                    this.paymentStatus = status;
                    this.paymentModalOpen = true;
                },

                async submitPaymentForm(e) {
                    BizAlert.loading('Updating Status...');
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
                                payment_status: this.paymentStatus
                            })
                        });

                        let data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Something went wrong on the server.');
                        }

                        BizAlert.toast('Status successfully updated!', 'success');
                        setTimeout(() => window.location.reload(), 1000);

                    } catch (error) {
                        BizAlert.toast(error.message, 'error');
                    }
                },

                confirmDelete(form) {
                    BizAlert.confirm(
                        'Delete Purchase Return?',
                        'This action cannot be undone. Any drafted return data will be permanently removed.',
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
