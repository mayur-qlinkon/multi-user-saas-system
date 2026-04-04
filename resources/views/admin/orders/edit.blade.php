@extends('layouts.admin')

@section('title', 'Edit Order - Qlinkon BIZNESS')

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Orders / Edit</h1>
@endsection

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
@endpush

@section('content')
    {{-- 🌟 Pass defaults AND the existing order data to Alpine --}}
    <div class="pb-20" x-data="orderForm(@js($warehouses->where('is_default', 1)->pluck('id', 'store_id')), @js($order), @js($order->items))">
        
        {{-- ── HEADER ── --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-[1.5rem] font-bold text-[#212538] tracking-tight">Edit Order #{{ $order->order_number }}</h1>
                    @if($order->source === 'storefront')
                        <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-wider rounded">Storefront</span>
                    @else
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-[10px] font-bold uppercase tracking-wider rounded">Admin</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500">Update order details, logistics, or items.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.orders.show', $order->id) }}"
                    class="text-sm font-bold text-gray-500 hover:text-gray-800 transition-colors">Cancel</a>
                <button type="submit" form="mainOrderForm"
                    class="bg-[#108c2a] hover:bg-[#0c6b1f] text-white px-8 py-2.5 rounded-lg text-sm font-bold transition-all shadow-md flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Changes
                </button>
            </div>
        </div>

        {{-- ── ERRORS ── --}}
        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200 shadow-sm">
                <div class="font-bold mb-2 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix the following errors:
                </div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 🌟 Info Banner for Storefront Orders --}}
        <div x-show="isStorefront" x-cloak class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-6 flex items-start gap-3 text-sm">
            <i data-lucide="info" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
            <div>
                <strong>Customer Placed Order:</strong> Because this order was placed via the public storefront, the items, cart total, and financial details are locked to preserve accounting integrity. You can still update the status, notes, and address.
            </div>
        </div>

        <form id="mainOrderForm" action="{{ route('admin.orders.update', $order->id) }}" method="POST" @submit="BizAlert.loading('Updating Order...')">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                
                {{-- ── 1. CUSTOMER & LOGISTICS ── --}}
                <div class="lg:col-span-8 flex flex-col gap-6">
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-3">
                            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Customer Details</h2>
                            <button type="button" @click="toggleWalkIn()" x-show="!isStorefront"
                                :class="isWalkIn ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600'"
                                class="px-3 py-1.5 rounded text-xs font-bold uppercase tracking-widest transition-colors">
                                <span x-text="isWalkIn ? 'Walk-in Active' : 'Set as Walk-in'"></span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" :class="isWalkIn ? 'opacity-50 grayscale' : ''">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1.5">Name</label>
                                <input type="text" name="customer_name" x-model="formData.customer_name" :readonly="isWalkIn || isStorefront"
                                    class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-[#108c2a] outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1.5">Phone</label>
                                <input type="text" name="customer_phone" x-model="formData.customer_phone" :readonly="isWalkIn || isStorefront"
                                    class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-[#108c2a] outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1.5">Email</label>
                                <input type="email" name="customer_email" x-model="formData.customer_email" :readonly="isWalkIn || isStorefront"
                                    class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-[#108c2a] outline-none">
                            </div>
                        </div>

                        <h3 class="text-xs font-bold text-gray-700 mt-6 mb-3">Delivery Address</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" :class="isWalkIn ? 'opacity-50 grayscale' : ''">
                            <div class="md:col-span-3">
                                <input type="text" name="delivery_address" x-model="formData.delivery_address" placeholder="Street Address" :readonly="isWalkIn"
                                    class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-[#108c2a] outline-none">
                            </div>
                            <div>
                                <input type="text" name="delivery_city" x-model="formData.delivery_city" placeholder="City" :readonly="isWalkIn"
                                    class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-[#108c2a] outline-none">
                            </div>
                            <div>
                                <input type="text" name="delivery_state" x-model="formData.delivery_state" placeholder="State" :readonly="isWalkIn"
                                    class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-[#108c2a] outline-none">
                            </div>
                            <div>
                                <input type="text" name="delivery_pincode" x-model="formData.delivery_pincode" placeholder="Pincode" :readonly="isWalkIn"
                                    class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-[#108c2a] outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- ── 2. CART ITEMS & SEARCH ── --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Order Items</h2>

                            {{-- SKU Search (Hidden if Storefront) --}}
                            <div class="relative w-full sm:w-96" x-data="{ showResults: false }" x-show="!isStorefront">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                                </div>
                                <input type="text" x-model="globalSearch" @input.debounce.300ms="fetchGlobalSkus()"
                                    @focus="showResults = true" @click.away="showResults = false"
                                    placeholder="Search product to add..."
                                    class="w-full border border-gray-300 rounded shadow-sm pl-9 pr-4 py-2.5 text-sm focus:border-brand-500 outline-none bg-white">

                                <ul x-show="showResults && globalSearch.length > 1" x-cloak
                                    class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-2xl mt-1 max-h-60 overflow-y-auto top-full left-0">
                                    
                                    <li x-show="isSearching" class="px-4 py-3 text-xs text-gray-500 text-center flex justify-center gap-2">
                                        <i data-lucide="loader" class="w-4 h-4 animate-spin text-[#108c2a]"></i> Searching...
                                    </li>
                                    <li x-show="!isSearching && globalSearchResults.length === 0" class="px-4 py-4 text-center text-sm text-gray-500">
                                        No items found in selected warehouse.
                                    </li>

                                    <template x-for="result in globalSearchResults" :key="result.product_sku_id">
                                        <li @click="addSkuToTable(result); showResults = false"
                                            class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 transition-colors">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <div class="text-[13px] font-bold text-gray-800" x-text="result.product_name"></div>
                                                    <div class="text-[10px] text-gray-400 font-mono" x-text="result.sku_code"></div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-[12px] font-black text-[#108c2a]" x-text="'₹' + parseFloat(result.price).toFixed(2)"></div>
                                                    <div class="text-[10px] text-gray-500" x-text="'Stock: ' + (result.stock || 0)"></div>
                                                </div>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <div class="overflow-x-auto min-h-[150px]">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-white border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <tr>
                                        <th class="px-5 py-3 w-1/2">Product</th>
                                        <th class="px-4 py-3 text-right">Price</th>
                                        <th class="px-4 py-3 text-center">Qty</th>
                                        <th class="px-4 py-3 text-right">Total</th>
                                        <th class="px-4 py-3 text-center" x-show="!isStorefront"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr x-show="items.length === 0">
                                        <td colspan="5" class="text-center py-8 text-gray-400 text-sm">Cart is empty. Search above to add items.</td>
                                    </tr>
                                    <template x-for="(item, index) in items" :key="item.key">
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-5 py-3">
                                                <div class="text-[13px] font-bold text-gray-800" x-text="item.name"></div>
                                                <div class="text-[11px] text-gray-500 font-mono mt-0.5" x-text="item.sku"></div>
                                                
                                                <input type="hidden" :name="'items[' + index + '][sku_id]'" :value="item.sku_id">
                                                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id"> 
                                                <input type="hidden" :name="'items[' + index + '][unit_price]'" :value="item.price"> 
                                                <input type="hidden" :name="'items[' + index + '][product_name]'" :value="item.name"> 
                                                <input type="hidden" :name="'items[' + index + '][sku_code]'" :value="item.sku">                                              
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-bold text-gray-600" x-text="'₹' + item.price"></td>
                                            <td class="px-4 py-3">
                                                {{-- Interactive Qty for Admin --}}
                                                <div class="flex items-center justify-center" x-show="!isStorefront">
                                                    <button type="button" @click="item.qty = Math.max(1, item.qty - 1); calculate()" class="w-7 h-7 border border-gray-300 rounded-l bg-gray-50 text-gray-600">-</button>
                                                    <input type="number" :name="'items[' + index + '][qty]'" x-model="item.qty" @input="calculate()" class="w-12 h-7 border-y border-x-0 border-gray-300 text-center text-sm font-bold outline-none p-0">
                                                    <button type="button" @click="item.qty++; calculate()" class="w-7 h-7 border border-gray-300 rounded-r bg-gray-50 text-gray-600">+</button>
                                                </div>
                                                {{-- Static Qty for Storefront --}}
                                                <div class="text-center font-bold text-sm text-gray-800" x-show="isStorefront" x-text="item.qty"></div>
                                            </td>
                                            <td class="px-4 py-3 text-right font-black text-gray-800 text-sm" x-text="'₹' + (item.price * item.qty).toFixed(2)"></td>
                                            <td class="px-4 py-3 text-center" x-show="!isStorefront">
                                                <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ── 3. META & SUMMARY (SIDEBAR) ── --}}
                <div class="lg:col-span-4 flex flex-col gap-6">
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-4">
                        <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider border-b border-gray-100 pb-3">Order Configuration</h2>
                        
                        <div x-show="!isStorefront" class="space-y-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1.5">Branch / Store</label>
                                {{-- 🌟 Added @change to trigger the auto-warehouse logic --}}
                                <select name="store_id" x-model="formData.store_id" @change="autoSelectWarehouse()" class="w-full border border-gray-300 rounded px-3 py-2 text-sm outline-none bg-white">
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1.5">Warehouse (For Inventory)</label>
                                {{-- 🌟 Added the missing Warehouse dropdown --}}
                                <select name="warehouse_id" x-model="formData.warehouse_id" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm outline-none bg-white">
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach ($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1.5">Order Status</label>
                                <select name="status" x-model="formData.status" class="w-full border border-gray-300 rounded px-3 py-2 text-sm outline-none bg-white">
                                    <option value="inquiry">Inquiry</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1.5">Payment Status</label>
                                <select name="payment_status" x-model="formData.payment_status" class="w-full border border-gray-300 rounded px-3 py-2 text-sm outline-none bg-white">
                                    <option value="pending">Pending</option>
                                    <option value="partial">Partial</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1.5">Payment Method</label>
                                <select name="payment_method" x-model="formData.payment_method" class="w-full border border-gray-300 rounded px-3 py-2 text-sm outline-none bg-white">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card / POS</option>
                                    <option value="upi">UPI / Online</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cod">COD</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1.5">Delivery Type</label>
                                <select name="delivery_type" x-model="formData.delivery_type" class="w-full border border-gray-300 rounded px-3 py-2 text-sm outline-none bg-white">
                                    <option value="pickup">Store Pickup</option>
                                    <option value="delivery">Delivery</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider border-b border-gray-100 pb-3 mb-4">Financial Summary</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Cart Subtotal:</span>
                                <span class="font-bold" x-text="'₹' + totals.cart.toFixed(2)"></span>
                            </div>

                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Discount (-):</span>
                                <input type="number" step="0.01" name="discount_amount" x-model="totals.discount" @input="calculate()" :readonly="isStorefront"
                                    class="w-24 border border-gray-300 rounded px-2 py-1 text-right text-red-500 font-bold outline-none focus:border-[#108c2a]"
                                    :class="isStorefront ? 'bg-gray-50 border-transparent' : ''">
                            </div>

                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Shipping (+):</span>
                                <input type="number" step="0.01" name="shipping_amount" x-model="totals.shipping" @input="calculate()" :readonly="isStorefront"
                                    class="w-24 border border-gray-300 rounded px-2 py-1 text-right text-gray-800 font-bold outline-none focus:border-[#108c2a]"
                                    :class="isStorefront ? 'bg-gray-50 border-transparent' : ''">
                            </div>

                            <div class="pt-3 border-t border-gray-100 flex justify-between items-end">
                                <div>
                                    <div class="text-[10px] text-gray-400">Calculated Grand Total</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-black text-[#108c2a]" x-text="'₹' + Math.max(0, totals.grand).toFixed(2)"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1.5">Admin Notes (Internal)</label>
                        <textarea name="admin_notes" x-model="formData.admin_notes" rows="3" placeholder="Order instructions or reference..."
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-brand-500 outline-none resize-none"></textarea>
                    </div>

                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function orderForm(storeDefaults={}, initialOrder, initialItems) {
        
        // 1. Map existing items
        let mappedItems = initialItems.map((item, index) => ({
            key: index,
            product_id: item.product_id,
            sku_id: item.sku_id,
            name: item.product_name,
            sku: item.sku_code || item.sku_label || '-',
            price: parseFloat(item.unit_price) || 0,
            qty: parseFloat(item.qty) || 1
        }));

        // 2. Identify if this is a storefront order (Locks financials)
        let isStorefrontOrder = initialOrder.source === 'storefront';

        // 3. Identify if it was an Admin Walk-in (Empty or 'Walk-in Customer' name)
        let initWalkIn = false;
        if (initialOrder.source === 'admin') {
            if (!initialOrder.customer_name || initialOrder.customer_name === 'Walk-in Customer') {
                initWalkIn = true;
            }
        }

        return {
            storeDefaults: storeDefaults,
            isStorefront: isStorefrontOrder,
            isWalkIn: initWalkIn,
            globalSearch: '',
            isSearching: false,
            globalSearchResults: [],
            items: mappedItems,
            itemCounter: mappedItems.length,
            
            formData: { 
                customer_name: initWalkIn ? '' : (initialOrder.customer_name || ''),
                customer_phone: (initialOrder.customer_phone === '0000000000' || initWalkIn) ? '' : (initialOrder.customer_phone || ''),
                customer_email: initialOrder.customer_email || '',
                delivery_address: initialOrder.delivery_address || '',
                delivery_city: initialOrder.delivery_city || '',
                delivery_state: initialOrder.delivery_state || '',
                delivery_pincode: initialOrder.delivery_pincode || '',
                store_id: '{{ session("store_id") }}' || initialOrder.store_id,
                
                // 🌟 If the session store is different from the order's original store, 
                // clear the warehouse so the init() function auto-selects the new proper one!
                warehouse_id: ('{{ session("store_id") }}' == initialOrder.store_id) ? (initialOrder.warehouse_id || '') : '',
                status: initialOrder.status || 'confirmed',
                payment_status: initialOrder.payment_status || 'paid',
                payment_method: initialOrder.payment_method || 'cash',
                delivery_type: initialOrder.delivery_type || 'pickup',
                admin_notes: initialOrder.admin_notes || ''
            },

            totals: {
                cart: 0,
                discount: parseFloat(initialOrder.discount_amount) || 0,
                shipping: parseFloat(initialOrder.shipping_amount) || 0,
                grand: parseFloat(initialOrder.total_amount) || 0
            },

            init() {
                setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 100);
                this.calculate(); // Calculate initial cart totals
                // 🌟 4. Auto-select if no warehouse is currently saved
                this.$nextTick(() => {
                    if (!this.formData.warehouse_id) {
                        this.autoSelectWarehouse();
                    }
                });
            },
            // 🌟 5. Add the exact same auto-warehouse function from your create blade
            autoSelectWarehouse() {
                let sId = String(this.formData.store_id);
                if (this.storeDefaults[sId]) {
                    this.formData.warehouse_id = String(this.storeDefaults[sId]);
                } else {
                    this.formData.warehouse_id = ''; 
                }
            },

            toggleWalkIn() {
                if(this.isStorefront) return; // Prevent toggle if storefront
                
                this.isWalkIn = !this.isWalkIn;
                if(this.isWalkIn) {
                    this.formData.customer_name = '';
                    this.formData.customer_phone = '';
                    this.formData.customer_email = '';
                    this.formData.delivery_address = '';
                    this.formData.delivery_city = '';
                    this.formData.delivery_pincode = '';
                }
            },

            async fetchGlobalSkus() {
                if (!this.formData.warehouse_id) {
                    BizAlert.toast('Warehouse missing.', 'error');
                    return;
                }
                if (this.globalSearch.length < 2) return;

                this.isSearching = true;

                try {
                    let response = await fetch(`/admin/api/search-skus?term=${encodeURIComponent(this.globalSearch)}&warehouse_id=${this.formData.warehouse_id}&in_stock_only=1`);
                    if (!response.ok) throw new Error('Search failed');
                    this.globalSearchResults = await response.json();
                } catch (error) {
                    console.error("Search Error:", error);
                } finally {
                    this.isSearching = false;
                }
            },

            addSkuToTable(result) {
                this.items.push({
                    key: this.itemCounter++,
                    product_id: result.product_id,
                    sku_id: result.product_sku_id,
                    name: result.product_name,
                    sku: result.sku_code,
                    price: parseFloat(result.price) || 0,
                    qty: 1
                });
                
                this.globalSearch = '';
                this.calculate();
            },

            removeItem(index) {
                this.items.splice(index, 1);
                this.calculate();
            },

            calculate() {
                let sum = 0;
                this.items.forEach(item => {
                    sum += (parseFloat(item.price) * parseFloat(item.qty));
                });
                
                this.totals.cart = sum;
                
                let disc = parseFloat(this.totals.discount) || 0;
                let ship = parseFloat(this.totals.shipping) || 0;
                
                // If it's a storefront order, we don't recalculate the grand total
                // based on the UI math, we just preserve what's already there
                if(!this.isStorefront) {
                    this.totals.grand = this.totals.cart - disc + ship;
                }
            }
        }
    }
</script>
@endpush