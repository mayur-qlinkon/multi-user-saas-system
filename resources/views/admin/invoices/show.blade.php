@extends('layouts.admin')

@section('title', 'Invoice: ' . $invoice->invoice_number)

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Invoice Details</h1>
@endsection

@push('styles')
<style>
/* ============================================================
   🖨️  PRINT OPTIMIZATION — A4 PORTRAIT
   ============================================================ */
@media print {
    @page {
        size: A4 portrait;
        margin: 12mm 10mm;
    }

    /* ── 1. Reset html/body ──────────────────────────────── */
    html, body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        background: white !important;
        height: auto !important;
        overflow: visible !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* ── 2. Hide admin chrome ────────────────────────────── */
    #main-sidebar,
    #sidebar-overlay,
    #nav-progress,
    #page-cover,
    header { display: none !important; }

    /* ── 3. Break the overflow-hidden/h-screen chains ───── */
    /*    These are the containers that physically clip the  */
    /*    content and stop pagination working               */
    body > div,
    body > div > div { 
        display: block !important;
        height: auto !important;
        overflow: visible !important;
        flex: none !important;
    }

    /* ── 4. Reset the <main> scroll container ───────────── */
    #page-content {
        display: block !important;
        height: auto !important;
        overflow: visible !important;
        padding: 0 !important;
        flex: none !important;
    }

    /* ── 5. Hide the Qlinkon branding footer ─────────────── */
    #page-content > footer { display: none !important; }

    /* ── 6. #print-area stays in NORMAL FLOW ────────────── */
    /*    Static position = browser paginates correctly     */
    #print-area {
        display: block !important;
        position: static !important;
        width: 100% !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
    }

    /* ── 7. Visibility toggles ───────────────────────────── */
    .no-print   { display: none !important; }
    .print-only { display: block !important; }

    /* ── 8. Payment history: respect toggle state ────────── */
    .print-hidden { display: none !important; }

    /* ── 9. Page break controls ──────────────────────────── */
    .page-break-before { page-break-before: always; break-before: page; }
    .page-break-after  { page-break-after: always;  break-after: page;  }
    .avoid-break       { page-break-inside: avoid;  break-inside: avoid; }

    /* ── 10. Table + typography ──────────────────────────── */
    .print-table-full       { width: 100% !important; }
    .invoice-header-title   { font-size: 20pt !important; }
}

/* ============================================================
   🖥️  SCREEN — Utility helpers
   ============================================================ */
.print-only { display: none; }

/* Status pill colours */
.status-paid     { background: #dcfce7; color: #166534; border-color: #86efac; }
.status-partial  { background: #fef9c3; color: #854d0e; border-color: #fde047; }
.status-unpaid   { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
.status-cancelled{ background: #f1f5f9; color: #64748b; border-color: #cbd5e1; }

/* Payment timeline card */
.payment-card { transition: box-shadow .15s, transform .15s; }
.payment-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); transform: translateY(-1px); }

/* Balance indicator gradient */
.balance-bar { height: 4px; border-radius: 9999px; }

/* Smooth toggle */
#payment-history-body { transition: max-height .35s ease, opacity .25s ease; overflow: hidden; }
</style>
@endpush


@section('content')
@php
    /* ── Helpers ──────────────────────────────────────────── */
    $fmt = fn($v) => '₹' . number_format((float)$v, 2, '.', ',');

    /* ── Company & Store ──────────────────────────────────── */
    $company = $invoice->company ?? auth()->user()->company;
    $store   = $invoice->store;

    $billingGstin        = $invoice->gst_number     ?? $store?->gst_number     ?? get_setting('gst_number');
    $billingUpiId        = $invoice->upi_id         ?? $store?->upi_id;
    $billingBankName     = $invoice->bank_name      ?? $store?->bank_name;
    $billingAccName      = $invoice->account_name   ?? $store?->account_name;
    $billingAccNo        = $invoice->account_number ?? $store?->account_number;
    $billingIfsc         = $invoice->ifsc_code      ?? $store?->ifsc_code;
    $billingSignatureUrl = $invoice->signature
        ? asset('storage/' . $invoice->signature)
        : $store?->signature_url;
    $billingFooterNote   = $invoice->invoice_footer_note ?? $store?->invoice_footer_note;
    $billingTerms        = $invoice->terms_conditions    ?? $store?->invoice_terms;

    /* ── Indian State Code Map ────────────────────────────── */
    $stateCodes = [
        'Andhra Pradesh'=>'37','Arunachal Pradesh'=>'12','Assam'=>'18','Bihar'=>'10',
        'Chhattisgarh'=>'22','Goa'=>'30','Gujarat'=>'24','Haryana'=>'06',
        'Himachal Pradesh'=>'02','Jharkhand'=>'20','Karnataka'=>'29','Kerala'=>'32',
        'Madhya Pradesh'=>'23','Maharashtra'=>'27','Manipur'=>'14','Meghalaya'=>'17',
        'Mizoram'=>'15','Nagaland'=>'13','Odisha'=>'21','Punjab'=>'03',
        'Rajasthan'=>'08','Sikkim'=>'11','Tamil Nadu'=>'33','Telangana'=>'36',
        'Tripura'=>'16','Uttar Pradesh'=>'09','Uttarakhand'=>'05','West Bengal'=>'19',
        'Andaman and Nicobar Islands'=>'35','Chandigarh'=>'04',
        'Dadra and Nagar Haveli and Daman and Diu'=>'26','Delhi'=>'07',
        'Jammu and Kashmir'=>'01','Ladakh'=>'38','Lakshadweep'=>'31','Puducherry'=>'34',
    ];
    $stateCode = $stateCodes[$invoice->supply_state] ?? 'N/A';

    /* ── Customer ─────────────────────────────────────────── */
    $customerName    = $invoice->client?->name    ?? $invoice->customer_name ?? 'Guest Customer';
    $customerPhone   = $invoice->client?->phone   ?? null;
    $customerAddress = $invoice->client?->address ?? null;
    $customerGSTIN   = $invoice->client?->gst_number ?? $invoice->customer_gstin ?? null;
    $invoiceType     = !empty($customerGSTIN) ? 'B2B' : 'B2C';

    /* ── Payments ─────────────────────────────────────────── */
    $completedPayments = $invoice->payments->where('status', 'completed');
    $paidAmt       = $completedPayments->sum('amount');
    $totalReceived = $completedPayments->sum('amount_received');
    $totalChange   = $completedPayments->sum('change_returned');
    $balanceDue    = max(0, $invoice->grand_total - $paidAmt);
    $paymentCount  = $completedPayments->count();

    /* Payment status helper */
    $pStatusClass = match($invoice->payment_status) {
        'paid'    => 'status-paid',
        'partial' => 'status-partial',
        default   => 'status-unpaid',
    };
    if($invoice->status === 'cancelled') $pStatusClass = 'status-cancelled';

    /* Balance percentage for bar */
    $paidPct = $invoice->grand_total > 0
        ? min(100, round(($paidAmt / $invoice->grand_total) * 100))
        : 0;

    /* ── Batch (if enabled) ───────────────────────────────── */
    $batchMovements = collect();
    if (function_exists('batch_enabled') && batch_enabled()) {
        $batchMovements = $invoice->stockMovements
            ->where('direction', 'out')
            ->whereNotNull('batch_number')
            ->groupBy('product_sku_id');
    }

    /* ── WA message ───────────────────────────────────────── */
    $waText = urlencode(
        "Hello {$customerName},\nYour Invoice {$invoice->invoice_number} for " .
        number_format($invoice->grand_total, 2) . " is ready.\nBalance due: " .
        number_format($balanceDue, 2) . ".\nThank you!"
    );
@endphp

<div class="pb-10 space-y-4">

    {{-- ══════════════════════════════════════════════════════
         A. ACTION BAR  (screen only)
    ══════════════════════════════════════════════════════ --}}
    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-3">

        <x-admin.breadcrumb :items="[
            ['label' => 'Invoices', 'url' => route('admin.invoices.index')],
            ['label' => 'Invoice Details'],
        ]" />

        <div class="flex flex-wrap items-center gap-2 justify-start sm:justify-end">
            {{-- Back --}}
            <a href="{{ route('admin.invoices.index') }}"
               class="btn-outline flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
            </a>

            {{-- Edit --}}
            @if($invoice->status !== 'cancelled' && $invoice->status !== 'confirmed' && has_permission('invoices.update'))
            <a href="{{ route('admin.invoices.edit', $invoice->id) }}"
               class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 text-sm font-medium transition-colors shadow-sm">
                <i data-lucide="pencil" class="w-4 h-4"></i> Edit
            </a>
            @endif

            {{-- Print --}}
            <button onclick="window.print()"
                    class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 text-sm font-semibold transition-colors shadow-sm">
                <i data-lucide="printer" class="w-4 h-4"></i> Print
            </button>

            {{-- PDF --}}
            @if(has_permission('invoices.download_pdf'))
            <a href="{{ route('admin.invoices.pdf', $invoice->id) }}" target="_blank"
               class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors shadow-sm">
                <i data-lucide="download" class="w-4 h-4"></i> PDF
            </a>
            @endif

            {{-- WhatsApp --}}
            @if($customerPhone)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customerPhone) }}?text={{ $waText }}"
               target="_blank"
               class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-[#25D366] hover:bg-[#1da851] text-white text-sm font-semibold transition-colors shadow-sm">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.669.149-.198.297-.768.966-.941 1.164-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.058-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.148-.173.198-.297.297-.495.099-.198.05-.371-.025-.52-.074-.149-.669-1.612-.916-2.206-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.371-.273.297-1.04 1.016-1.04 2.479 0 1.463 1.064 2.876 1.213 3.074.148.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.693.626.711.226 1.358.194 1.87.118.571-.085 1.758-.718 2.007-1.411.248-.694.248-1.289.173-1.411-.074-.124-.272-.198-.57-.347z"/>
                    <path d="M12.004 2C6.486 2 2 6.484 2 12c0 1.991.585 3.847 1.589 5.407L2 22l4.75-1.557A9.956 9.956 0 0012.004 22C17.522 22 22 17.516 22 12S17.522 2 12.004 2z"/>
                </svg>
                WhatsApp
            </a>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         B. CREDIT NOTE ALERT  (screen only)
    ══════════════════════════════════════════════════════ --}}
    @if($invoice->returns->count() > 0)
    <div class="no-print bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 bg-red-600 p-2 rounded-lg text-white">
                    <i data-lucide="undo-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-red-800">Linked Credit Notes</h4>
                    <p class="text-xs text-red-600">Items from this invoice have been returned.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($invoice->returns as $ret)
                <a href="{{ route('admin.invoice-returns.show', $ret->id) }}"
                   class="bg-white border border-red-200 text-red-700 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-red-50 transition-colors shadow-sm">
                    VIEW {{ $ret->credit_note_number }} ({{ $fmt($ret->grand_total) }})
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         C. FINANCIAL SUMMARY BANNER  (screen only)
    ══════════════════════════════════════════════════════ --}}
    <div class="no-print grid grid-cols-2 lg:grid-cols-4 gap-3">

        {{-- Grand Total --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Grand Total</div>
            <div class="text-xl font-black text-gray-900">{{ $fmt($invoice->grand_total) }}</div>
        </div>

        {{-- Total Received --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Received</div>
            <div class="text-xl font-black text-green-600">{{ $fmt($paidAmt) }}</div>
        </div>

        {{-- Balance Due --}}
        <div class="bg-white border {{ $balanceDue > 0 ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50' }} rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold {{ $balanceDue > 0 ? 'text-red-400' : 'text-green-500' }} uppercase tracking-wider mb-1">Balance Due</div>
            <div class="text-xl font-black {{ $balanceDue > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $fmt($balanceDue) }}</div>
        </div>

        {{-- Status + Payments Count --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex flex-col justify-between">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Status</div>
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border uppercase {{ $pStatusClass }}">
                    {{ $invoice->payment_status }}
                </span>
                @if($paymentCount > 0)
                <span class="text-xs text-gray-500 font-medium">{{ $paymentCount }} payment{{ $paymentCount > 1 ? 's' : '' }}</span>
                @endif
            </div>
            {{-- Progress bar --}}
            <div class="mt-3 bg-gray-100 balance-bar">
                <div class="balance-bar bg-green-500 transition-all" style="width: {{ $paidPct }}%"></div>
            </div>
            <div class="text-[10px] text-gray-400 mt-1 text-right">{{ $paidPct }}% settled</div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         D. INVOICE DOCUMENT  (screen + print)
    ══════════════════════════════════════════════════════ --}}
    <div id="print-area"
         class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden text-gray-800 print:shadow-none print:border-none print:rounded-none">

        {{-- ─── SECTION 1: HEADER ──────────────────────────── --}}
        <div class="p-6 sm:p-8 border-b-2 border-gray-800 flex flex-col md:flex-row print:flex-row justify-between gap-6">

            {{-- Left: Invoice branding --}}
            <div class="flex-1">
                <h1 class="invoice-header-title text-2xl sm:text-3xl font-black uppercase tracking-widest text-gray-900 mb-0.5">
                    Tax Invoice
                </h1>
                <div class="text-sm text-gray-500 font-bold mb-3"># {{ $invoice->invoice_number }}</div>

                {{-- Status badge (screen) --}}
                <div class="no-print flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border uppercase {{ $pStatusClass }}">
                        @if($invoice->payment_status === 'paid')
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                        @elseif($invoice->payment_status === 'partial')
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        @else
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        @endif
                        {{ $invoice->payment_status }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>

                {{-- CANCELLED stamp --}}
                @if($invoice->status === 'cancelled')
                <div class="mt-3 inline-block border-2 border-red-600 text-red-600 text-base font-black uppercase px-4 py-1 -rotate-6 select-none">
                    CANCELLED
                </div>
                @endif
            </div>

            {{-- Right: Company info --}}
            <div class="text-left md:text-right print:text-right text-sm flex flex-col items-start md:items-end print:items-end gap-1">
                <h2 class="text-lg sm:text-xl font-black text-gray-900 uppercase leading-tight">{{ $company->name }}</h2>
                @if($billingGstin)
                    <div class="text-xs text-gray-500">GSTIN: <span class="font-bold text-gray-800 uppercase">{{ $billingGstin }}</span></div>
                @endif
                <div class="text-xs text-gray-500">{{ $company->email }}</div>
                <div class="text-xs text-gray-500">{{ $company->phone }}</div>

                @if($store)
                <div class="mt-3 text-left md:text-right print:text-right border-t border-gray-100 pt-3">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Branch: <span class="text-gray-700">{{ $store->name }}</span>
                    </div>
                    <div class="text-[12px] text-gray-600 leading-snug mt-0.5">
                        @if($store->address){{ $store->address }},<br>@endif
                        {{ $store->city }}{{ $store->city && $store->zip_code ? ', ' : '' }}{{ $store->zip_code }}<br>
                        {{ $store->state->name ?? '' }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ─── SECTION 2: CUSTOMER & INVOICE META ─────────── --}}
        <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 print:grid-cols-2 gap-6 border-b border-gray-200 avoid-break">

            {{-- Billed To --}}
            <div>
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Billed To</h3>
                <div class="font-bold text-base text-gray-900 leading-tight">{{ $customerName }}</div>
                @if($customerGSTIN)
                    <div class="text-xs font-bold text-gray-700 uppercase mt-0.5">GSTIN: {{ $customerGSTIN }}</div>
                @endif
                @if($customerAddress)
                    <div class="text-sm text-gray-500 mt-1 leading-snug">{{ $customerAddress }}</div>
                @endif
                @if($customerPhone)
                    <div class="text-sm text-gray-500 mt-1">
                        <i data-lucide="phone" class="w-3 h-3 inline mr-1 no-print"></i>{{ $customerPhone }}
                    </div>
                @endif
            </div>

            {{-- Invoice Details --}}
            <div class="bg-gray-50 md:bg-transparent print:bg-transparent p-4 md:p-0 rounded-lg md:rounded-none border md:border-none border-gray-100">
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Invoice Details</h3>
                <dl class="space-y-1">
                    <div class="flex justify-between text-[13px]">
                        <dt class="text-gray-500 font-medium">Invoice Date</dt>
                        <dd class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</dd>
                    </div>
                    @if($invoice->due_date)
                    <div class="flex justify-between text-[13px]">
                        <dt class="text-gray-500 font-medium">Due Date</dt>
                        <dd class="font-semibold {{ now()->greaterThan($invoice->due_date) && $balanceDue > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
                        </dd>
                    </div>
                    @endif
                    <div class="flex justify-between text-[13px]">
                        <dt class="text-gray-500 font-medium">Place of Supply</dt>
                        <dd class="font-bold text-gray-900">{{ $invoice->supply_state }} ({{ $stateCode }})</dd>
                    </div>
                    <div class="flex justify-between text-[13px]">
                        <dt class="text-gray-500 font-medium">Invoice Type</dt>
                        <dd class="font-bold text-gray-900 uppercase">{{ $invoiceType }}</dd>
                    </div>
                    <div class="flex justify-between text-[13px]">
                        <dt class="text-gray-500 font-medium">Reverse Charge</dt>
                        <dd class="font-medium text-gray-900">No</dd>
                    </div>
                    <div class="flex justify-between text-[13px] pt-2 mt-1 border-t border-gray-200">
                        <dt class="font-bold text-gray-500">Payment Status</dt>
                        <dd class="font-black uppercase {{ $invoice->payment_status === 'paid' ? 'text-green-600' : ($invoice->payment_status === 'partial' ? 'text-amber-600' : 'text-red-600') }}">
                            {{ $invoice->payment_status }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- ─── SECTION 3: LINE ITEMS TABLE ────────────────── --}}
        <div class="px-6 sm:px-8 py-5">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm print-table-full">
                    <thead>
                        <tr class="border-b-2 border-gray-800 text-[11px] font-black text-gray-600 uppercase tracking-wider">
                            <th class="pb-3 pr-3">Description</th>
                            <th class="pb-3 px-2 text-center hidden sm:table-cell print:table-cell">HSN/SAC</th>
                            <th class="pb-3 px-2 text-center">Qty</th>
                            <th class="pb-3 px-2 text-right">Rate</th>
                            <th class="pb-3 px-2 text-right hidden sm:table-cell print:table-cell">Disc.</th>
                            <th class="pb-3 px-2 text-right">Tax</th>
                            <th class="pb-3 pl-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($invoice->items as $item)
                        <tr class="avoid-break">
                            <td class="py-3 pr-3">
                                <div class="font-bold text-gray-900 text-sm">{{ $item->product_name }}</div>
                                <div class="text-[11px] text-gray-400 font-mono mt-0.5">
                                    SKU: {{ $item->sku->sku_code ?? $item->sku->sku ?? 'N/A' }}
                                </div>
                                @if(!$batchMovements->isEmpty() && isset($batchMovements[$item->product_sku_id]))
                                    @foreach($batchMovements[$item->product_sku_id] as $bm)
                                    <span class="inline-block text-[10px] bg-blue-50 text-blue-700 border border-blue-200 px-1.5 py-0.5 rounded font-mono mt-1">
                                        Batch: {{ $bm->batch_number }}
                                    </span>
                                    @endforeach
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center text-gray-500 text-[13px] hidden sm:table-cell print:table-cell">
                                {{ $item->hsn_code ?? '—' }}
                            </td>
                            <td class="py-3 px-2 text-center font-semibold text-gray-800 text-[13px]">
                                {{ (float) $item->quantity }}
                            </td>
                            <td class="py-3 px-2 text-right text-gray-600 text-[13px]">
                                {{ $fmt($item->unit_price) }}
                            </td>
                            <td class="py-3 px-2 text-right text-[13px] text-gray-600 hidden sm:table-cell print:table-cell">
                                @if($item->discount_amount > 0)
                                    @if($item->discount_type === 'percentage' && (float)$item->discount_value > 0)
                                        {{ (float)$item->discount_value }}%
                                        <div class="text-[10px] text-gray-400">(-₹{{ number_format($item->discount_amount, 2) }})</div>
                                    @else
                                        {{ $fmt($item->discount_amount) }}
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3 px-2 text-right text-[13px] text-gray-600">
                                {{ $fmt($item->tax_amount) }}
                                <div class="text-[10px] text-gray-400">({{ (float)$item->tax_percent }}%)</div>
                            </td>
                            <td class="py-3 pl-2 text-right font-bold text-gray-900 text-[13px]">
                                {{ $fmt($item->total_amount) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── SECTION 4: BANKING & TOTALS (Stays on Page 1) ──────────────────── --}}
        <div class="px-6 sm:px-8 py-4 border-t border-gray-100 avoid-break">
            <div class="flex flex-col md:flex-row print:flex-row gap-6 justify-between">
                
                {{-- Left: Bank Details & UPI --}}
                <div class="flex-1 space-y-3">
                    @if($billingBankName || $billingAccNo)
                    <div>
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Bank Details</h4>
                        <div class="text-[12px] text-gray-700 space-y-0.5">
                            @if($billingBankName)<div><span class="font-semibold">Bank:</span> {{ $billingBankName }}</div>@endif
                            @if($billingAccName)<div><span class="font-semibold">A/C Name:</span> {{ $billingAccName }}</div>@endif
                            @if($billingAccNo)<div><span class="font-semibold">A/C No:</span> {{ $billingAccNo }}</div>@endif
                            @if($billingIfsc)<div><span class="font-semibold">IFSC:</span> {{ $billingIfsc }}</div>@endif
                        </div>
                    </div>
                    @endif

                    {{-- UPI QR (Slightly smaller for compact printing) --}}
                    @if($billingUpiId)
                    <div>
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pay via UPI</h4>
                        @php
                            $upiAmount = $balanceDue > 0 ? $balanceDue : $invoice->grand_total;
                            $upiString = 'upi://pay?pa=' . $billingUpiId 
                                . '&pn=' . urlencode($billingAccName ?: $company->name) 
                                . '&am=' . $upiAmount . '&cu=INR';
                        @endphp
                        <div class="inline-block p-1.5 border border-gray-200 rounded-lg bg-white shadow-sm">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate($upiString) !!}
                        </div>
                        <div class="text-[10px] text-gray-400 mt-0.5">{{ $billingUpiId }}</div>
                    </div>
                    @endif
                </div>

                {{-- Right: Totals Table --}}
                <div class="w-full md:w-72 print:w-72 flex-shrink-0">
                    <table class="w-full text-[12px] sm:text-[13px]">
                        <tbody>
                            <tr>
                                <td class="py-1 text-gray-500 font-medium">Subtotal</td>
                                <td class="py-1 text-right text-gray-800 font-semibold">{{ $fmt($invoice->subtotal) }}</td>
                            </tr>
                            @if($invoice->discount_amount > 0)
                            <tr>
                                <td class="py-1 text-gray-500 font-medium">
                                    Discount
                                    @if($invoice->discount_type === 'percentage')
                                        <span class="text-[10px] text-gray-400">({{ (float)$invoice->discount_value }}%)</span>
                                    @endif
                                </td>
                                <td class="py-1 text-right font-semibold text-red-600">(−) {{ $fmt($invoice->discount_amount) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="py-1 text-gray-500 font-medium">Taxable Amount</td>
                                <td class="py-1 text-right font-semibold text-gray-800">{{ $fmt($invoice->taxable_amount) }}</td>
                            </tr>
                            @if($invoice->igst_amount > 0)
                            <tr>
                                <td class="py-1 text-gray-500 font-medium">IGST</td>
                                <td class="py-1 text-right font-semibold text-gray-800">{{ $fmt($invoice->igst_amount) }}</td>
                            </tr>
                            @else
                            <tr>
                                <td class="py-1 text-gray-500 font-medium">CGST</td>
                                <td class="py-1 text-right font-semibold text-gray-800">{{ $fmt($invoice->cgst_amount) }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 text-gray-500 font-medium">SGST</td>
                                <td class="py-1 text-right font-semibold text-gray-800">{{ $fmt($invoice->sgst_amount) }}</td>
                            </tr>
                            @endif
                            @if(isset($invoice->shipping_charge) && $invoice->shipping_charge > 0)
                            <tr>
                                <td class="py-1 text-gray-500 font-medium">Shipping</td>
                                <td class="py-1 text-right font-semibold text-gray-800">{{ $fmt($invoice->shipping_charge) }}</td>
                            </tr>
                            @endif
                            @if(isset($invoice->round_off) && $invoice->round_off != 0)
                            <tr>
                                <td class="py-1 text-gray-500 font-medium">Round Off</td>
                                <td class="py-1 text-right font-semibold text-gray-800">{{ $fmt($invoice->round_off) }}</td>
                            </tr>
                            @endif
                            {{-- Grand Total --}}
                            <tr class="border-t-2 border-gray-900">
                                <td class="py-1.5 text-[14px] font-black text-gray-900 uppercase">Grand Total</td>
                                <td class="py-1.5 text-right text-[15px] font-black text-gray-900">{{ $fmt($invoice->grand_total) }}</td>
                            </tr>
                            {{-- Payment Ledger --}}
                            @if($totalReceived > 0)
                            <tr class="text-gray-500 border-t border-gray-100">
                                <td class="pt-1.5 pb-0.5 font-medium">Amt. Received</td>
                                <td class="pt-1.5 pb-0.5 text-right font-semibold text-green-700">{{ $fmt($totalReceived) }}</td>
                            </tr>
                            @if($totalChange > 0)
                            <tr class="text-gray-500">
                                <td class="py-0.5 font-medium">Change Returned</td>
                                <td class="py-0.5 text-right font-semibold">{{ $fmt($totalChange) }}</td>
                            </tr>
                            @endif
                            <tr class="text-gray-500 border-t border-gray-100">
                                <td class="py-1.5 font-black uppercase text-[10px]">Paid Against Bill</td>
                                <td class="py-1.5 text-right font-semibold text-green-700">{{ $fmt($paidAmt) }}</td>
                            </tr>
                            @if($balanceDue > 0)
                            <tr class="text-gray-500 border-t border-gray-100">
                                <td class="py-1.5 font-black text-red-700 uppercase text-[10px]">Balance Due</td>
                                <td class="py-1.5 text-right font-black text-red-700 text-[12px]">{{ $fmt($balanceDue) }}</td>
                            </tr>
                            @endif
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ─── SECTION 5: NOTES, TERMS & SIGNATURE (Can break to next page) ─────────────── --}}
        <div class="px-6 sm:px-8 py-5 border-t border-gray-200">
            <div class="flex flex-col md:flex-row print:flex-row justify-between gap-6 items-end">
                
                {{-- Left: Notes and Terms --}}
                <div class="flex-1 space-y-3 text-xs text-gray-500">
                    @if($invoice->notes)
                        <div>
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Note</h4>
                            <p class="leading-relaxed text-gray-700">{{ $invoice->notes }}</p>
                        </div>
                    @endif
                    {{-- @if($billingFooterNote)
                        <div class="leading-relaxed">{!! nl2br(e($billingFooterNote)) !!}</div>
                    @endif --}}
                    {{-- @if($billingTerms)
                        <div>
                            <strong class="text-gray-600 uppercase tracking-wider block mb-1">Terms & Conditions:</strong>
                            <div class="leading-relaxed">{!! nl2br(e($billingTerms)) !!}</div>
                        </div>
                    @endif --}}
                </div>

                {{-- Right: Authorized Signature --}}
                <div class="w-full md:w-64 print:w-64 text-right flex-shrink-0 avoid-break">
                    @if($billingSignatureUrl)
                        <img src="{{ $billingSignatureUrl }}" alt="Authorized Signature"
                             class="ml-auto mb-2 object-contain opacity-90 max-h-16">
                    @else
                        <div class="h-12"></div> {{-- Spacer if no signature image --}}
                    @endif
                    <div class="text-[11px] font-bold text-gray-700 uppercase tracking-wider border-t border-gray-400 pt-1.5 inline-block min-w-[160px] text-center mt-2">
                        Authorized Signatory
                    </div>
                </div>

            </div>
        </div>

        {{-- ─── SECTION 5: FOOTER NOTE + TERMS ─────────────── --}}
        @if($billingFooterNote || $billingTerms)
        <div class="px-6 sm:px-8 py-5 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 avoid-break space-y-3">
            @if($billingFooterNote)
                <div class="leading-relaxed">{!! nl2br(e($billingFooterNote)) !!}</div>
            @endif
            @if($billingTerms)
                <div>
                    <strong class="text-gray-600 uppercase tracking-wider">Terms & Conditions:</strong>
                    <div class="mt-1 leading-relaxed">{!! nl2br(e($billingTerms)) !!}</div>
                </div>
            @endif
        </div>
        @endif

        {{-- ─── SECTION 6: PAYMENT HISTORY ─────────────────── --}}
        @if($invoice->payments->count() > 0)
        <div id="payment-history-section"
             class="border-t-2 border-dashed border-gray-300 page-break-before">

            {{-- Section header + toggle (screen only) --}}
            <div class="px-6 sm:px-8 py-4 flex items-center justify-between bg-gray-50 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 text-white p-1.5 rounded-lg no-print">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-wide">Payment History</h3>
                        <p class="text-[11px] text-gray-400">{{ $paymentCount }} transaction{{ $paymentCount > 1 ? 's' : '' }} recorded</p>
                    </div>
                </div>
                {{-- Toggle button (screen only) --}}
                <button id="toggle-payment-history"
                        onclick="togglePaymentHistory()"
                        class="no-print flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-3 py-1.5 rounded-lg transition-colors">
                    <i data-lucide="eye-off" id="toggle-icon" class="w-3.5 h-3.5"></i>
                    <span id="toggle-label">Hide</span>
                </button>
            </div>

            {{-- Payment cards container --}}
            <div id="payment-history-body" class="px-6 sm:px-8 py-5 space-y-3">

                {{-- Print-only summary row --}}
                <div class="print-only mb-4 grid grid-cols-3 gap-4 text-[12px] bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div><span class="font-bold text-gray-500 block">Total Billed</span> <span class="font-black text-gray-900">{{ $fmt($invoice->grand_total) }}</span></div>
                    <div><span class="font-bold text-gray-500 block">Total Received</span> <span class="font-black text-green-700">{{ $fmt($paidAmt) }}</span></div>
                    <div><span class="font-bold text-gray-500 block">Balance</span> <span class="font-black {{ $balanceDue > 0 ? 'text-red-700' : 'text-green-700' }}">{{ $fmt($balanceDue) }}</span></div>
                </div>

                @foreach($invoice->payments->sortByDesc('payment_date') as $payment)
                @php
                    $isCompleted = $payment->status === 'completed';
                    $isCancelled = $payment->status === 'cancelled';
                    $methodLabel = $payment->paymentMethod?->label ?? 'N/A';
                    $methodSlug  = $payment->paymentMethod?->slug ?? '';
                    $creatorName = $payment->creator?->name ?? 'System';

                    $methodIcon = match(true) {
                        str_contains($methodSlug, 'cash')   => 'banknote',
                        str_contains($methodSlug, 'upi')    => 'scan-qr-code',
                        str_contains($methodSlug, 'card')   => 'credit-card',
                        str_contains($methodSlug, 'bank') || str_contains($methodSlug, 'neft') || str_contains($methodSlug, 'rtgs') => 'landmark',
                        str_contains($methodSlug, 'cheque') => 'file-text',
                        default                              => 'wallet',
                    };
                    $borderColor = $isCompleted ? 'border-l-green-500' : ($isCancelled ? 'border-l-gray-300' : 'border-l-amber-400');
                @endphp

                <div class="payment-card border border-gray-200 border-l-4 {{ $borderColor }} rounded-lg bg-white p-4 avoid-break">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">

                        {{-- Left: Icon + Main Info --}}
                        <div class="flex items-start gap-3">
                            <div class="no-print flex-shrink-0 w-9 h-9 rounded-full {{ $isCompleted ? 'bg-green-100 text-green-700' : ($isCancelled ? 'bg-gray-100 text-gray-400' : 'bg-amber-100 text-amber-700') }} flex items-center justify-center mt-0.5">
                                <i data-lucide="{{ $methodIcon }}" class="w-4 h-4"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-sm font-black text-gray-900"># {{ $payment->payment_number }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border
                                        {{ $isCompleted ? 'bg-green-50 text-green-700 border-green-200' : ($isCancelled ? 'bg-gray-100 text-gray-500 border-gray-300' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                        {{ $payment->status }}
                                    </span>
                                    <span class="text-[11px] text-gray-400 font-mono">
                                        {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-x-4 gap-y-0.5 text-[12px] text-gray-500">
                                    <span>
                                        <span class="font-semibold text-gray-600">Method:</span> {{ $methodLabel }}
                                    </span>
                                    @if($payment->reference)
                                    <span>
                                        <span class="font-semibold text-gray-600">Ref:</span>
                                        <span class="font-mono text-gray-700">{{ $payment->reference }}</span>
                                    </span>
                                    @endif
                                    <span>
                                        <span class="font-semibold text-gray-600">By:</span> {{ $creatorName }}
                                    </span>
                                </div>
                                @if($payment->notes)
                                <p class="text-[12px] text-gray-400 mt-1.5 italic leading-snug">{{ $payment->notes }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Right: Amounts --}}
                        <div class="text-left sm:text-right flex-shrink-0 pl-12 sm:pl-0">
                            <div class="text-base font-black text-gray-900">{{ $fmt($payment->amount) }}</div>
                            @if($payment->amount_received > 0 && $payment->amount_received != $payment->amount)
                            <div class="text-[11px] text-gray-400 mt-0.5">
                                Received: <span class="font-semibold text-gray-600">{{ $fmt($payment->amount_received) }}</span>
                            </div>
                            @endif
                            @if($payment->change_returned > 0)
                            <div class="text-[11px] text-gray-400">
                                Change: <span class="font-semibold text-gray-600">{{ $fmt($payment->change_returned) }}</span>
                            </div>
                            @endif
                        </div>

                    </div>
                </div>
                @endforeach

                {{-- Screen-only summary footer --}}
                <div class="no-print mt-4 flex flex-wrap gap-4 justify-end border-t border-gray-100 pt-4 text-[13px]">
                    <div class="text-gray-500">
                        Grand Total: <span class="font-black text-gray-900">{{ $fmt($invoice->grand_total) }}</span>
                    </div>
                    <div class="text-gray-500">
                        Total Received: <span class="font-black text-green-700">{{ $fmt($paidAmt) }}</span>
                    </div>
                    @if($balanceDue > 0)
                    <div class="text-gray-500">
                        Balance Due: <span class="font-black text-red-600">{{ $fmt($balanceDue) }}</span>
                    </div>
                    @else
                    <div class="flex items-center gap-1 text-green-700 font-bold">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i> Fully Settled
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /print-area --}}

</div>{{-- /pb-10 --}}
@endsection


@push('scripts')
<script>
    /**
     * Toggle payment history section visibility (screen only).
     * In print, the section is always shown via CSS.
     */
    function togglePaymentHistory() {
        const body    = document.getElementById('payment-history-body');
        const section = document.getElementById('payment-history-section'); // whole section
        const icon    = document.getElementById('toggle-icon');
        const label   = document.getElementById('toggle-label');
        const isHidden = body.style.display === 'none';

        if (isHidden) {
            body.style.display = '';
            section.classList.remove('print-hidden'); // ← show in print
            icon.setAttribute('data-lucide', 'eye-off');
            label.textContent = 'Hide';
        } else {
            body.style.display = 'none';
            section.classList.add('print-hidden');    // ← hide in print
            icon.setAttribute('data-lucide', 'eye');
            label.textContent = 'Show';
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
</script>
@endpush