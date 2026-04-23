@extends('layouts.admin')

@section('title', 'Products Management - Qlinkon BIZNESS')
@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Products</h1>
@endsection
@section('content')
    <div class="space-y-6 pb-10" x-data="productTable()">

        {{-- Alerts --}}
        @if (session('success'))
            <div class="bg-green-50 text-green-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-green-100 mb-6 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> {{ session('success') }}
            </div>
              <script>
                document.addEventListener('DOMContentLoaded', () => BizAlert.toast("{{ session('success') }}", 'success'));
            </script>
        @endif
        @if (session('error'))
            <div class="bg-red-50 text-red-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6 flex items-center gap-2">
                <i data-lucide="alert-octagon" class="w-5 h-5"></i> {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-[#fee2e2] text-[#ef4444] px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix the following errors:</div>
                <ul class="list-disc list-inside pl-7 text-xs font-medium space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex justify-end mb-4">
            <form action="{{ route('admin.products.index') }}" method="GET"
                class="flex flex-col sm:flex-row flex-wrap w-full md:w-auto gap-3 items-stretch sm:items-center">

                {{-- Search Bar --}}
                <div class="flex w-full sm:w-auto sm:flex-1 lg:flex-none lg:w-[320px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search Product Name or SKU..."
                        class="min-w-0 w-full flex-1 border border-gray-200 rounded-l-lg px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none placeholder:text-gray-400 bg-white">
                    <button type="submit"
                        class="shrink-0 px-4 sm:px-5 py-2.5 text-sm font-semibold text-white bg-[#108c2a] hover:bg-[#0e7a24] rounded-r-lg border border-l-0 border-[#108c2a] transition-colors">
                        Search
                    </button>
                </div>

                {{-- Category Filter --}}
                <div class="relative w-full sm:w-auto sm:flex-1 lg:flex-none lg:w-[220px]">
                    <select name="category_id" onchange="this.form.submit()"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 pr-10 text-sm text-gray-600 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none appearance-none cursor-pointer bg-white shadow-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down"
                        class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>

                {{-- Reset Button --}}
                <a href="{{ route('admin.products.index') }}" title="Reset Filters"
                    class="shrink-0 w-full sm:w-auto bg-white hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-lg text-sm font-medium flex items-center justify-center transition-colors shadow-sm">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">

            <div class="px-4 sm:px-6 py-4 flex flex-col md:flex-row md:justify-between md:items-center border-b border-gray-100 gap-4 bg-white">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest">
                    Product Catalog <span class="text-gray-400 font-medium text-sm ml-1">({{ $products->total() }}
                        items)</span>
                </h2>

                <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2 w-full md:w-auto md:justify-end">
                    <button
                        x-show="selected.length > 0"
                        x-transition.opacity
                        x-cloak
                        @click="confirmBulkDelete()"
                        class="bg-[#ef4444] hover:bg-red-600 text-white px-4 py-2.5 rounded-lg text-sm font-bold flex items-center justify-center gap-1.5 transition-colors shadow-sm whitespace-nowrap w-full md:w-auto">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Bulk Delete (<span x-text="selected.length"></span>)
                    </button>

                    @if (check_plan_limit('products'))
                        @if(has_permission('products.create'))
                        <a href="{{ route('admin.products.create') }}"
                            class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2.5 rounded-lg text-sm font-bold flex items-center justify-center gap-1.5 transition-colors shadow-sm whitespace-nowrap w-full md:w-auto">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add Product
                        </a>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-red-50 text-red-600 text-xs font-bold border border-red-100">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            Product Limit Reached
                        </span>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto min-h-[400px]">
                <table class="w-full text-left text-sm whitespace-nowrap min-w-[1000px]">
                    <thead
                        class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-[#f8fafc]">
                        <tr
                            class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 bg-[#f8fafc]">
                            <th class="px-6 py-4 w-10">
                                <input type="checkbox"
                                    @change="selected = $event.target.checked ? [{{ $products->pluck('id')->join(',') }}] : []"
                                    :checked="selected.length > 0 && selected.length === {{ $products->count() }}"
                                    class="rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] w-4 h-4 cursor-pointer">
                            </th>
                            <th class="px-6 py-4">PRODUCT</th>
                            <th class="px-6 py-4">NAME</th>
                            <th class="px-6 py-4">VARIANTS</th>
                            <th class="px-6 py-4">CATEGORY</th>
                            <th class="px-6 py-4">PRICE</th>
                            <th class="px-6 py-4">PRODUCT UNIT</th>
                            <th class="px-6 py-4">IN STOCK</th>
                            <th class="px-6 py-4">CREATED ON</th>
                            <th class="px-6 py-4 text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($products as $product)
                            <tr
                                class="hover:bg-gray-50/50 transition-colors border-b border-gray-50 text-[13px] text-gray-600">
                                {{-- Checkbox --}}
                                <td class="px-6 py-4">
                                    <input type="checkbox"
                                        x-model.number="selected"
                                        value="{{ $product->id }}"
                                        class="rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] w-4 h-4 cursor-pointer">
                                </td>

                                {{-- Product Image --}}
                                <td class="px-6 py-4">
                                    <div
                                        class="w-10 h-10 rounded border border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center">
                                        <img src="{{ $product->primary_image_url }}" alt="Img"
                                            class="w-full h-full object-cover">
                                    </div>
                                </td>

                                {{-- Math Setup --}}
                                @php
                                    $hasSku = $product->skus->isNotEmpty();
                                    $minPrice = $hasSku ? ($product->skus->min('price') ?? 0) : 0;
                                    $maxPrice = $hasSku ? ($product->skus->max('price') ?? 0) : 0;
                                    $totalVariants = $product->skus->count();
                                    $totalStock = $hasSku ? $product->skus->sum('total_stock') : 0;
                                @endphp

                                {{-- Name --}}
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    {{ $product->name }}
                                </td>

                                {{-- Variants (Replacing Code) --}}
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $product->type === 'variable' ? $totalVariants . ' Variants' : 'Single' }}
                                </td>

                                {{-- Category (Replacing Brand) --}}
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $product->category->name ?? 'N/A' }}
                                </td>

                                {{-- Price --}}
                                <td class="px-6 py-4 font-medium text-gray-700">
                                    @if ($minPrice == $maxPrice)
                                        ₹{{ number_format($minPrice, 2) }}
                                    @else
                                        ₹{{ number_format($minPrice, 2) }} - ₹{{ number_format($maxPrice, 2) }}
                                    @endif
                                </td>

                                {{-- Product Unit --}}
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $product->productUnit->short_name ?? 'Pc' }}
                                </td>

                                {{-- In Stock --}}
                                <td
                                    class="px-6 py-4 font-medium {{ $totalStock > 0 ? 'text-[#108c2a]' : 'text-red-500' }}">
                                    {{ $totalStock }}
                                </td>

                                {{-- Created On --}}
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $product->created_at->format('d M, Y') }}
                                </td>

                                {{-- Action --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3 text-gray-400">
                                        
                                        @if(has_permission('products.view'))
                                        <a href="{{ route('admin.products.show', $product->id) }}"
                                            class="hover:text-indigo-500 transition-colors" title="View">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        @endif

                                        @if(has_permission('products.update'))
                                        <a href="{{ route('admin.products.edit', $product->id) }}"
                                            class="hover:text-blue-500 transition-colors" title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        @endif

                                        @if(has_permission('products.duplicate'))
                                        <form action="{{ route('admin.products.duplicate', $product->id) }}"
                                                method="POST" class="inline-block m-0 p-0">
                                                @csrf
                                                <button type="submit"
                                                    class="hover:text-amber-500 transition-colors" title="Duplicate">
                                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                                </button>
                                        </form>
                                        @endif

                                        @if(has_permission('products.delete'))
                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST"
                                            @submit.prevent="confirmDelete($event.target)" class="inline-block m-0 p-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="hover:text-red-500 transition-colors"
                                                title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                        @endif 
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="package-open" class="w-12 h-12 mb-3 opacity-20"></i>
                                        <p class="font-medium text-gray-500">No products found in your inventory.</p>
                                        <a href="{{ route('admin.products.create') }}"
                                            class="text-[#108c2a] font-bold mt-2 hover:underline">Add your first
                                            product</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (method_exists($products, 'links') && $products->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function productTable() {
            return {
                selected: [], // Array to hold selected product IDs

                // New Bulk Delete Function
                confirmBulkDelete() {
                    BizAlert.confirm(
                        'Delete Selected Products?',
                        `You are about to archive ${this.selected.length} products. This action cannot be undone.`,
                        'Yes, Archive Them'
                    ).then(async (result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Archiving...');
                            
                            try {
                                const response = await fetch('{{ route("admin.products.bulk-delete") }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ ids: this.selected })
                                });

                                const data = await response.json();

                                if (data.success) {
                                    // Let the session flash message handle the success alert on reload
                                    window.location.reload();
                                } else {
                                    BizAlert.toast(data.message || 'Failed to delete products', 'error');
                                }
                            } catch (error) {
                                console.error(error);
                                BizAlert.toast('Network error. Try again.', 'error');
                            }
                        }
                    });
                },

                confirmDelete(form) {
                    BizAlert.confirm(
                        'Delete Product?',
                        'This product will be archived. Historical sales data will remain intact.',
                        'Yes, Archive it'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Archiving...');
                            form.submit();
                        }
                    });
                },

                // The fully functional AJAX Toggle!
                async toggleStatus(productId, isActive) {
                    try {
                        const response = await fetch(`/admin/products/${productId}/toggle-status`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            const statusText = data.is_active ? 'Activated' : 'Deactivated';
                            BizAlert.toast(`Product has been ${statusText}`, 'success');
                        } else {
                            BizAlert.toast('Failed to update status', 'error');
                            // Revert the toggle visually if it failed on the server
                            event.target.checked = !isActive;
                        }
                    } catch (error) {
                        console.error(error);
                        BizAlert.toast('Network error. Try again.', 'error');
                        event.target.checked = !isActive;
                    }
                }
            }
        }
    </script>
@endpush
