@extends('layouts.admin')

@section('title', 'Invoice: ' . $invoice->invoice_number)

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Invoice Details</h1>
@endsection

@push('styles')
    <style>
        /* 🖨️ A4 PRINT OPTIMIZATION */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: white !important;
            }

            body * {
                visibility: hidden;
            }

            #print-area,
            #print-area * {
                visibility: visible;
            }

            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 0;
                border: none !important;
                box-shadow: none !important;
            }

            .no-print {
                display: none !important;
            }

            .page-break-avoid {
                page-break-inside: avoid;
            }
        }
    </style>
@endpush

@section('content')
    @php
        // Clean currency formatter
        $formatAmt = function ($amount) {
            return number_format((float) $amount, 2, '.', ',');
        };

        $defaults = "not set";

        // Company Details
        $company = $invoice->company ?? auth()->user()->company;
        $store   = $invoice->store;

        // Billing priority: invoice snapshot → store accessor (store accessors already fall back to get_setting)
        $billingGstin        = $invoice->gst_number     ?? $store->gst_number     ?? get_setting('gst_number');
        $billingUpiId        = $invoice->upi_id         ?? $store->upi_id ?? $defaults;
        $billingBankName     = $invoice->bank_name      ?? $store->bank_name ?? $defaults;
        $billingAccName      = $invoice->account_name   ?? $store->account_name ?? $defaults;
        $billingAccNo        = $invoice->account_number ?? $store->account_number ?? $defaults;
        $billingIfsc         = $invoice->ifsc_code      ?? $store->ifsc_code ?? $defaults;
        $billingSignatureUrl = $invoice->signature
            ? asset('storage/' . $invoice->signature)
            : $store->signature_url ?? $defaults;
        $billingFooterNote   = $invoice->invoice_footer_note ?? $store->invoice_footer_note ?? $defaults;
        $billingTerms        = $invoice->terms_conditions    ?? $store->invoice_terms ?? $defaults;

        // 🌟 NEW: Indian State Code Mapping
        $stateCodes = [
            'Andhra Pradesh' => '37',
            'Arunachal Pradesh' => '12',
            'Assam' => '18',
            'Bihar' => '10',
            'Chhattisgarh' => '22',
            'Goa' => '30',
            'Gujarat' => '24',
            'Haryana' => '06',
            'Himachal Pradesh' => '02',
            'Jharkhand' => '20',
            'Karnataka' => '29',
            'Kerala' => '32',
            'Madhya Pradesh' => '23',
            'Maharashtra' => '27',
            'Manipur' => '14',
            'Meghalaya' => '17',
            'Mizoram' => '15',
            'Nagaland' => '13',
            'Odisha' => '21',
            'Punjab' => '03',
            'Rajasthan' => '08',
            'Sikkim' => '11',
            'Tamil Nadu' => '33',
            'Telangana' => '36',
            'Tripura' => '16',
            'Uttar Pradesh' => '09',
            'Uttarakhand' => '05',
            'West Bengal' => '19',
            'Andaman and Nicobar Islands' => '35',
            'Chandigarh' => '04',
            'Dadra and Nagar Haveli and Daman and Diu' => '26',
            'Delhi' => '07',
            'Jammu and Kashmir' => '01',
            'Ladakh' => '38',
            'Lakshadweep' => '31',
            'Puducherry' => '34',
        ];

        // Customer Details
        $customerName = $invoice->client ? $invoice->client->name : $invoice->customer_name ?? 'Guest Customer';
        $customerPhone = $invoice->client ? $invoice->client->phone : 'N/A';
        $customerAddress = $invoice->client ? $invoice->client->address : 'N/A';
        $customerGSTIN = $invoice->client ? $invoice->client->gst_number : $invoice->customer_gstin ?? null;

        // 🌟 NEW: Determine Type (If GST exists, it is B2B)
        $invoiceType = !empty($customerGSTIN) ? 'B2B' : 'B2C';
        $stateCode = $stateCodes[$invoice->supply_state] ?? 'N/A';

        // Calculate paid amount and balance
        $paidAmt = $invoice->payments->where('status', 'completed')->sum('amount');
        $totalReceived = $invoice->payments->where('status', 'completed')->sum('amount_received');
        $totalChange = $invoice->payments->where('status', 'completed')->sum('change_returned');
        $balanceDue = $invoice->grand_total - $paidAmt;
    @endphp

    <div class="pb-10">
        {{-- ACTION BAR (Hidden on Print) --}}        
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
            <div class="w-full sm:w-auto">
                <x-admin.breadcrumb :items="[
                        ['label' => 'Invoices', 'url' => route('admin.invoices.index')],
                        ['label' => 'Invoice Details'],
                    ]" />
            </div>

            {{-- UI Fix: Allow buttons to wrap cleanly on mobile --}}
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-start sm:justify-end">
                <a href="{{ route('admin.invoices.index') }}"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 px-4 py-2 rounded text-sm transition-colors flex items-center shadow-sm font-medium flex-1 sm:flex-none justify-center">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i> Back
                </a>

                @if ($invoice->status !== 'cancelled' && $invoice->status !== 'confirmed' && has_permission('invoices.update'))
                    <a href="{{ route('admin.invoices.edit', $invoice->id) }}"
                        class="bg-white border border-gray-200 hover:bg-blue-50 hover:text-blue-600 text-gray-600 px-4 py-2 rounded text-sm transition-colors flex items-center shadow-sm font-medium flex-1 sm:flex-none justify-center">
                        <i data-lucide="pencil" class="w-4 h-4 mr-1.5"></i> Edit
                    </a>
                @endif

                <button onclick="window.print()"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-800 px-4 py-2 rounded text-sm transition-colors flex items-center gap-1.5 shadow-sm font-bold flex-1 sm:flex-none justify-center">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print
                </button>

                @if(has_permission('invoices.download_pdf'))
                <a href="{{ route('admin.invoices.pdf', $invoice->id) }}" target="_blank"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors flex items-center gap-1.5 shadow-sm font-bold flex-1 sm:flex-none justify-center">
                    <i data-lucide="download" class="w-4 h-4"></i> PDF
                </a>
                @endif

                @php
                    $waText = urlencode(
                        "Hello {$customerName},\nHere is your Invoice {$invoice->invoice_number} for Rs. " .
                            $formatAmt($invoice->grand_total) .
                            ".\nThank you for your business!",
                    );
                @endphp
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customerPhone) }}?text={{ $waText }}"
                    target="_blank"
                    class="bg-[#25D366] hover:bg-[#1da851] text-white px-4 py-2 rounded text-sm transition-colors flex items-center gap-1.5 shadow-sm font-bold w-full sm:w-auto justify-center mt-2 sm:mt-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.669.149-.198.297-.768.966-.941 1.164-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.058-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.148-.173.198-.297.297-.495.099-.198.05-.371-.025-.52-.074-.149-.669-1.612-.916-2.206-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.371-.273.297-1.04 1.016-1.04 2.479 0 1.463 1.064 2.876 1.213 3.074.148.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.693.626.711.226 1.358.194 1.87.118.571-.085 1.758-.718 2.007-1.411.248-.694.248-1.289.173-1.411-.074-.124-.272-.198-.57-.347z" />
                        <path d="M12.004 2C6.486 2 2 6.484 2 12c0 1.991.585 3.847 1.589 5.407L2 22l4.75-1.557A9.956 9.956 0 0012.004 22C17.522 22 22 17.516 22 12S17.522 2 12.004 2z" />
                    </svg>
                    WhatsApp
                </a>
            </div>
        </div>
        @if ($invoice->returns->count() > 0)
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 no-print">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-red-600 p-2 rounded-lg text-white">
                            <i data-lucide="undo-2" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-red-800">Linked Credit Notes Found</h4>
                            <p class="text-xs text-red-600 font-medium">Items from this invoice have been returned.</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @foreach ($invoice->returns as $ret)
                            <a href="{{ route('admin.invoice-returns.show', $ret->id) }}"
                                class="bg-white border border-red-200 text-red-700 px-3 py-1.5 rounded-lg text-xs font-black hover:bg-red-50 transition-colors shadow-sm">
                                VIEW {{ $ret->credit_note_number }} (₹{{ number_format($ret->grand_total, 2) }})
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        {{-- 📄 THE INVOICE UI (Full Width) --}}
        <div id="print-area"
            class="w-full bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden text-gray-800 print:grayscale print:shadow-none print:border-none">

            {{-- Header --}}
            <div class="p-5 sm:p-8 border-b-2 border-gray-800 flex flex-col md:flex-row print:flex-row justify-between gap-6 items-start">
                <div class="w-full md:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-black uppercase tracking-widest text-gray-900 mb-1">Tax Invoice</h1>
                    <div class="text-sm text-gray-600 font-bold mb-4"># {{ $invoice->invoice_number }}</div>

                    @if ($invoice->status === 'cancelled')
                        <div class="inline-block border-2 border-red-600 text-red-600 text-lg font-black uppercase px-3 py-1 mb-2 transform -rotate-6">
                            CANCELLED
                        </div>
                    @endif
                </div>
                
                {{-- UI Fix: Align left on mobile, right on desktop/print --}}
                <div class="w-full md:w-auto text-left md:text-right print:text-right text-sm flex flex-col items-start md:items-end print:items-end">
                    {{-- 🌟 1. Legal Entity (Company) --}}
                    <h2 class="text-lg sm:text-xl font-black text-gray-900 uppercase leading-none">{{ $company->name }}</h2>
                    <div class="text-gray-600 text-[12px] mt-1">
                        @if ($billingGstin)
                            GSTIN: <span class="font-bold text-gray-900 uppercase">{{ $billingGstin }}</span><br>
                        @endif
                        Email: {{ $company->email }}<br>
                        Phone: {{ $company->phone }}
                    </div>

                    {{-- 🌟 2. Operational Branch (Store) --}}
                    @if ($store)
                        <div class="mt-4 text-left md:text-right print:text-right">
                            <h3 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Branch: <span class="font-bold text-black">{{ $store->name }}</span></h3>
                            <div class="text-gray-800 text-[13px] leading-tight">
                                @if ($store->address)
                                    {{ $store->address }}<br>
                                @endif
                                {{ $store->city }}{{ $store->city && $store->zip_code ? ', ' : '' }}{{ $store->zip_code }}<br>
                                {{ $store->state->name ?? $store->state_id }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Meta & Address Block --}}
            {{-- UI Fix: Stack on mobile, 2 columns on md/print. Adaptive padding. --}}
            <div class="p-5 sm:p-8 grid grid-cols-1 md:grid-cols-2 print:grid-cols-2 gap-6 sm:gap-8 border-b border-gray-200">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Billed To</h3>
                        <div class="text-sm text-gray-800">
                            <div class="font-bold text-base mb-0.5">{{ $customerName }}</div>
                            @if ($customerGSTIN)
                                <div class="font-bold text-gray-900 uppercase">GSTIN: {{ $customerGSTIN }}</div>
                            @endif
                            @if ($customerAddress !== 'N/A')
                                <div class="text-gray-600 leading-tight mt-1">{{ $customerAddress }}</div>
                            @endif
                            @if ($customerPhone !== 'N/A')
                                <div class="text-gray-600 mt-1">Phone: {{ $customerPhone }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="space-y-1 bg-gray-50 md:bg-transparent print:bg-transparent p-4 md:p-0 rounded-lg md:rounded-none border md:border-none border-gray-100">
                    <div class="grid grid-cols-2 text-[13px]">
                        <span class="font-bold text-gray-500">Invoice Date:</span>
                        <span class="text-right font-semibold">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</span>
                    </div>
                    @if ($invoice->due_date)
                        <div class="grid grid-cols-2 text-[13px]">
                            <span class="font-bold text-gray-500">Due Date:</span>
                            <span class="text-right font-semibold">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</span>
                        </div>
                    @endif
                    <div class="grid grid-cols-2 text-[13px]">
                        <span class="font-bold text-gray-500">Place of Supply:</span>
                        <span class="text-right font-bold text-gray-900">{{ $invoice->supply_state }} ({{ $stateCode }})</span>
                    </div>
                    <div class="grid grid-cols-2 text-[13px]">
                        <span class="font-bold text-gray-500">Invoice Type:</span>
                        <span class="text-right font-bold text-gray-900 uppercase">{{ $invoiceType }}</span>
                    </div>
                    <div class="grid grid-cols-2 text-[13px] pt-2 mt-1 border-t border-gray-200 md:border-gray-100">
                        <span class="font-bold text-gray-500">Payment Status:</span>
                        <span class="text-right font-black uppercase {{ $invoice->payment_status === 'paid' ? 'text-green-600' : 'text-red-500' }}">
                            {{ $invoice->payment_status }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 text-[13px]">
                        <span class="font-bold text-gray-500">Reverse Charge:</span>
                        <span class="text-right font-medium">No</span>
                    </div>
                </div>
            </div>

            {{-- Line Items Table --}}
            <div class="px-5 sm:px-8 py-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr
                                class="border-b-2 border-gray-800 text-xs font-black text-gray-700 uppercase tracking-wider">
                                <th class="py-3 px-2">Description</th>
                                <th class="py-3 px-2 text-center">HSN/SAC</th>
                                <th class="py-3 px-2 text-center">Qty</th>
                                <th class="py-3 px-2 text-right">Rate</th>
                                <th class="py-3 px-2 text-right">Disc.</th>
                                <th class="py-3 px-2 text-right">Tax</th>
                                <th class="py-3 px-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        @if(batch_enabled())
                            @php
                                $batchMovements = $invoice->stockMovements
                                    ->where('direction', 'out')
                                    ->whereNotNull('batch_number')
                                    ->groupBy('product_sku_id');
                            @endphp
                        @endif
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="py-4 px-2">
                                        <div class="font-bold text-gray-900">{{ $item->product_name }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono mt-0.5">SKU:
                                            {{ $item->sku->sku_code ?? ($item->sku->sku ?? 'N/A') }}</div>
                                        @if(batch_enabled() && isset($batchMovements[$item->product_sku_id]))
                                            <div class="mt-1 space-y-0.5">
                                                @foreach($batchMovements[$item->product_sku_id] as $bm)
                                                    <span class="inline-block text-[10px] bg-blue-50 text-blue-700 border border-blue-200 px-1.5 py-0.5 rounded font-mono">
                                                        Batch: {{ $bm->batch_number }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-2 text-center text-gray-600">{{ $item->hsn_code ?? '-' }}</td>
                                    <td class="py-4 px-2 text-center font-semibold text-gray-800">
                                        {{ (float) $item->quantity }}</td>
                                    <td class="py-4 px-2 text-right text-gray-600">{{ $formatAmt($item->unit_price) }}</td>
                                    <td class="py-4 px-2 text-right text-gray-600">
                                        @if ($item->discount_amount > 0)
                                            @if ($item->discount_type === 'percentage' && (float) $item->discount_value > 0)
                                                {{ (float) $item->discount_value }}%
                                                <span class="text-[10px] text-gray-400 block">(-₹{{ $formatAmt($item->discount_amount) }})</span>
                                            @else
                                                ₹{{ $formatAmt($item->discount_amount) }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-4 px-2 text-right text-gray-600">
                                        {{ $formatAmt($item->tax_amount) }}<br>
                                        <span class="text-[10px] text-gray-400">({{ (float) $item->tax_percent }}%)</span>
                                    </td>
                                    <td class="py-4 px-2 text-right font-bold text-gray-900">
                                        {{ $formatAmt($item->total_amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Summary & Totals --}}
            {{-- UI Fix: Changed items-end to items-start for better mobile stacking, adaptive padding --}}
            <div class="px-5 sm:px-8 pb-8 page-break-avoid flex flex-col md:flex-row print:flex-row justify-between items-start md:items-end print:items-end gap-8 print:gap-4">

                {{-- Left Side: Notes, Bank Details & QR --}}
                {{-- Added print:w-1/2 to prevent it from expanding to 100% on paper --}}
                <div class="w-full md:w-1/2 print:w-1/2 mb-6 md:mb-0 print:mb-0 space-y-6">
                    @if ($invoice->notes)
                        <p class="text-[13px] text-gray-600"><strong class="text-gray-700">Note:</strong><br>
                            {{ $invoice->notes }}</p>
                    @endif

                    <div class="flex flex-wrap items-start gap-8 pt-4 border-t border-gray-100">
                        @if ($billingBankName || $billingAccNo)
                            <div>
                                <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Bank Details</h4>
                                <div class="text-[13px] text-gray-700 leading-relaxed">
                                    @if ($billingBankName)<strong>Bank:</strong> {{ $billingBankName }}<br>@endif
                                    @if ($billingAccName)<strong>A/C Name:</strong> {{ $billingAccName }}<br>@endif
                                    @if ($billingAccNo)<strong>A/C No:</strong> {{ $billingAccNo }}<br>@endif
                                    @if ($billingIfsc)<strong>IFSC:</strong> {{ $billingIfsc }}@endif
                                </div>
                            </div>
                        @endif

                        {{-- UPI QR: only shown when balance is due and UPI is configured --}}
                        @if ($balanceDue > 0 && $billingUpiId)
                            <div>
                                <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Pay via UPI</h4>
                                <div class="p-2 border border-gray-200 rounded inline-block bg-white">
                                    @php
                                        $upiString = 'upi://pay?pa=' . $billingUpiId . '&pn=' . urlencode($billingAccName ?: $company->name) . '&am=' . $balanceDue . '&cu=INR';
                                    @endphp
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(90)->generate($upiString) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right Side: Constrained Totals Table & Signature --}}
                <div class="w-full md:w-[350px] flex flex-col items-end">

                    <div class="w-full mb-8">
                        <table class="w-full text-[13px]">
                            <tbody>
                                <tr>
                                    <td class="py-1 text-gray-600 font-semibold">Taxable Amount</td>
                                    <td class="py-1 text-right text-gray-900 font-bold">
                                        ₹{{ $formatAmt($invoice->taxable_amount) }}</td>
                                </tr>

                                @if ($invoice->igst_amount > 0)
                                    <tr>
                                        <td class="py-1 text-gray-600 font-semibold">IGST</td>
                                        <td class="py-1 text-right text-gray-900 font-bold">
                                            ₹{{ $formatAmt($invoice->igst_amount) }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="py-1 text-gray-600 font-semibold">CGST (2.5%)</td>
                                        <td class="py-1 text-right text-gray-900 font-bold">
                                            ₹{{ $formatAmt($invoice->cgst_amount) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 text-gray-600 font-semibold">SGST (2.5%)</td>
                                        <td class="py-1 text-right text-gray-900 font-bold">
                                            ₹{{ $formatAmt($invoice->sgst_amount) }}</td>
                                    </tr>
                                @endif

                                @if ($invoice->discount_amount > 0)
                                    <tr>
                                        <td class="py-2 text-gray-600 font-medium">
                                            Discount
                                            @if($invoice->discount_type === 'percentage')
                                                <span class="text-xs text-gray-500 ml-1">({{ (float) $invoice->discount_value }}%)</span>
                                            @endif
                                        </td>
                                        <td class="py-2 text-right font-bold text-red-600">(-) ₹{{ $formatAmt($invoice->discount_amount) }}</td>
                                    </tr>
                                @endif

                                <tr class="border-t-2 border-gray-900">
                                    <td class="py-2 text-[15px] font-black text-gray-900 uppercase">Grand Total</td>
                                    <td class="py-2 text-right text-[16px] font-black text-gray-900">
                                        ₹{{ $formatAmt($invoice->grand_total) }}</td>
                                </tr>

                                {{-- 🌟 POS Ledger --}}
                                @if ($totalReceived > 0)
                                    <tr class="text-gray-500">
                                        <td class="pt-2 pb-1 font-bold">Amount Received</td>
                                        <td class="pt-2 pb-1 text-right font-bold">₹{{ $formatAmt($totalReceived) }}</td>
                                    </tr>
                                    @if ($totalChange > 0)
                                        <tr class="text-gray-500">
                                            <td class="py-1 font-bold">Change Returned</td>
                                            <td class="py-1 text-right font-bold">₹{{ $formatAmt($totalChange) }}</td>
                                        </tr>
                                    @endif
                                    <tr class="bg-gray-900 text-white">
                                        <td class="py-1.5 px-2 font-black uppercase text-[11px]">Paid Against Bill</td>
                                        <td class="py-1.5 px-2 text-right font-black text-[13px]">
                                            ₹{{ $formatAmt($paidAmt) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- Authorized Signature --}}
                    <div class="w-full text-right pr-2 mt-16">
                        @if ($billingSignatureUrl)
                            <img src="{{ $billingSignatureUrl }}" alt="Authorized Signature"
                                class="ml-auto mb-1 object-contain opacity-90" style="max-height:80px;">
                        @endif
                        <div
                            class="text-[11px] font-bold text-gray-800 uppercase tracking-wider border-t border-gray-400 pt-1 inline-block min-w-[150px] text-center">
                            Authorized Signatory
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer Note + Terms --}}
            @if ($billingFooterNote || $billingTerms)
                <div class="px-5 sm:px-8 py-6 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 page-break-avoid space-y-3">
                    @if ($billingFooterNote)
                        <div class="leading-relaxed">{!! nl2br(e($billingFooterNote)) !!}</div>
                    @endif
                    @if ($billingTerms)
                        <div>
                            <strong class="text-gray-700 uppercase tracking-widest">Terms & Conditions:</strong>
                            <div class="mt-1 leading-relaxed">{!! nl2br(e($billingTerms)) !!}</div>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
@endsection
