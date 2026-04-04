<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-gray {
            color: #666;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .border-bottom {
            border-bottom: 1px solid #ddd;
        }

        .border-top {
            border-top: 1px solid #ddd;
        }

        .bg-light {
            background-color: #f9f9f9;
        }

        .p-2 {
            padding: 8px;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        /* Specific elements */
        .header-title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .items-table th {
            background-color: #f3f4f6;
            padding: 10px;
            border-bottom: 2px solid #333;
            font-size: 10px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .totals-table td {
            padding: 6px 10px;
        }
    </style>
</head>

<body>

    @php
        $formatAmt = function ($amount) {
            return number_format((float) $amount, 2, '.', ',');
        };

        // 🌟 Legal Entity & Operational Branch Details
        $company = $invoice->company ?? auth()->user()->company;
        $store = $invoice->store;

        $customerName = $invoice->client ? $invoice->client->name : $invoice->customer_name ?? 'Guest Customer';
        $customerPhone = $invoice->client ? $invoice->client->phone : 'N/A';
        $customerAddress = $invoice->client ? $invoice->client->address : 'N/A';
        $paidAmt = $invoice->payments->where('status', 'completed')->sum('amount');
        $totalReceived = $invoice->payments->where('status', 'completed')->sum('amount_received');
        $totalChange = $invoice->payments->where('status', 'completed')->sum('change_returned');
        $balanceDue = $invoice->grand_total - $paidAmt;
    @endphp

    {{-- HEADER --}}
    <table style="margin-bottom: 30px;">
        <tr>
            <td style="width: 50%;">
                <div class="header-title">TAX INVOICE</div>
                <div class="font-bold text-gray mt-4"># {{ $invoice->invoice_number }}</div>
            </td>
            <td style="width: 50%;" class="text-right">
                {{-- 🌟 1. Legal Entity (Company) --}}
                <h2 style="margin:0; font-size: 18px;" class="uppercase">{{ $company->name ?? 'Company Name' }}</h2>
                <div class="text-gray" style="line-height: 1.4; margin-top: 6px;">
                    @if (isset($company->gst_number) || isset($company->gstin))
                        GSTIN: <strong style="color: #333;">{{ $company->gst_number ?? $company->gstin }}</strong><br>
                    @endif
                    @if ($company->email)
                        Email: {{ $company->email }}<br>
                    @endif
                    @if ($company->phone)
                        Phone: {{ $company->phone }}
                    @endif
                </div>

                {{-- 🌟 2. Operational Branch (Store) --}}
                @if ($store)
                    <div style="margin-top: 12px;">
                        <div
                            style="font-size: 10px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 2px;">
                            Branch / Store</div>
                        <div class="text-gray" style="line-height: 1.4;">
                            <span class="font-bold" style="color: #333;">{{ $store->name }}</span><br>
                            @if ($store->address)
                                {{ $store->address }}<br>
                            @endif
                            {{ $store->city ?? '' }}@if ($store->city && $store->zip_code)
                                ,
                            @endif{{ $store->zip_code ?? '' }}<br>
                            {{ $store->state->name ?? ($store->state_id ?? '') }}
                        </div>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- META INFO --}}
    <table class="border-top border-bottom" style="margin-bottom: 30px; padding: 15px 0;">
        <tr>
            <td style="width: 50%;">
                <div
                    style="font-size: 10px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 5px;">
                    Billed To</div>
                <div style="font-size: 14px; font-weight: bold; margin-bottom: 3px;">{{ $customerName }}</div>
                <div class="text-gray" style="line-height: 1.4;">
                    @if ($customerAddress !== 'N/A')
                        {{ $customerAddress }}<br>
                    @endif
                    @if ($customerPhone !== 'N/A')
                        Phone: {{ $customerPhone }}<br>
                    @endif
                    @if ($invoice->client && $invoice->client->gst_number)
                        GSTIN: <strong>{{ $invoice->client->gst_number }}</strong>
                    @endif
                </div>
            </td>
            <td style="width: 50%; line-height: 1.8;">
                <table>
                    <tr>
                        <td class="text-gray font-bold text-right" style="width: 60%;">Invoice Date:</td>
                        <td class="font-bold text-right">
                            {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                    </tr>
                    @if ($invoice->due_date)
                        <tr>
                            <td class="text-gray font-bold text-right">Due Date:</td>
                            <td class="font-bold text-right">
                                {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-gray font-bold text-right">Place of Supply:</td>
                        <td class="font-bold text-right">{{ $invoice->supply_state }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ITEMS TABLE --}}
    <table class="items-table" style="margin-bottom: 30px;">
        <thead>
            <tr>
                <th class="text-left">Product Details</th>
                <th class="text-center">HSN/SAC</th>
                <th class="text-right">Price</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>
                        <div class="font-bold">{{ $item->product_name }}</div>
                        <div style="font-size: 10px; color: #777;">SKU:
                            {{ $item->sku->sku_code ?? ($item->sku->sku ?? 'N/A') }}</div>
                    </td>
                    <td class="text-center text-gray">{{ $item->hsn_code ?? '-' }}</td>
                    <td class="text-right text-gray">{{ $formatAmt($item->unit_price) }}</td>
                    <td class="text-center font-bold">{{ (float) $item->quantity }}</td>
                    <td class="text-right text-gray">
                        {{ $formatAmt($item->tax_amount) }}<br>
                        <span style="font-size: 9px;">({{ (float) $item->tax_percent }}%)</span>
                    </td>
                    <td class="text-right font-bold">{{ $formatAmt($item->total_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- SUMMARY BLOCK --}}
    <table>
        <tr>
            {{-- Left Side: Notes & Bank --}}
            <td style="width: 50%; padding-right: 20px;">
                @if ($invoice->notes)
                    <div class="mb-2 text-gray"><strong style="color:#333;">Note:</strong> {{ $invoice->notes }}</div>
                @endif

                <div style="margin-top: 20px;">

                    <div
                        style="font-size: 10px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 5px;">
                        Bank Details</div>
                    <div class="text-gray" style="line-height: 1.5; font-size: 11px;">
                        <strong>Bank:</strong> HDFC Bank<br>
                        <strong>A/C Name:</strong> {{ $company->name }}<br>
                        <strong>A/C No:</strong> 50200012345678<br>
                        <strong>IFSC:</strong> HDFC0001234
                    </div>
                </div>

                @if ($balanceDue > 0)
                    <div style="margin-top: 15px;">
                        <div
                            style="font-size: 10px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 5px;">
                            Pay via UPI</div>
                        @php
                            $upiId = 'dev@okicici';
                            $merchantName = urlencode($company->name);
                            $upiString = "upi://pay?pa={$upiId}&pn={$merchantName}&am={$balanceDue}&cu=INR";

                            // 🌟 FIX: Generate as SVG, then encode to Base64 so DomPDF treats it as an image
                            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                                ->size(80)
                                ->generate($upiString);
                            $qrBase64 = base64_encode($qrSvg);
                        @endphp
                        <div>
                            {{-- 🌟 Wrap the Base64 string in an actual HTML <img> tag --}}
                            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="UPI QR Code"
                                style="width: 80px; height: 80px;">
                        </div>
                    </div>
                @endif
            </td>

            {{-- Right Side: Totals --}}
            <td style="width: 50%;">
                <table class="totals-table">
                    <tr>
                        <td class="text-gray font-bold text-right" style="width: 60%;">Taxable Amount</td>
                        <td class="font-bold text-right">Rs. {{ $formatAmt($invoice->taxable_amount) }}</td>
                    </tr>

                    @if ($invoice->igst_amount > 0)
                        <tr>
                            <td class="text-gray font-bold text-right">IGST</td>
                            <td class="font-bold text-right">Rs. {{ $formatAmt($invoice->igst_amount) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="text-gray font-bold text-right">CGST</td>
                            <td class="font-bold text-right">Rs. {{ $formatAmt($invoice->cgst_amount) }}</td>
                        </tr>
                        <tr>
                            <td class="text-gray font-bold text-right">SGST</td>
                            <td class="font-bold text-right">Rs. {{ $formatAmt($invoice->sgst_amount) }}</td>
                        </tr>
                    @endif

                    @if ($invoice->discount_amount > 0)
                        <tr>
                            <td class="text-gray font-bold text-right">Discount</td>
                            <td class="font-bold text-right" style="color: red;">(-) Rs.
                                {{ $formatAmt($invoice->discount_amount) }}</td>
                        </tr>
                    @endif

                    @if ($invoice->shipping_charge > 0)
                        <tr>
                            <td class="text-gray font-bold text-right">Shipping</td>
                            <td class="font-bold text-right">Rs. {{ $formatAmt($invoice->shipping_charge) }}</td>
                        </tr>
                    @endif

                    @if ($invoice->round_off != 0)
                        <tr>
                            <td class="text-gray font-bold text-right">Round Off</td>
                            <td class="font-bold text-right">Rs. {{ $formatAmt($invoice->round_off) }}</td>
                        </tr>
                    @endif

                    <tr>
                        <td class="font-bold text-right border-top border-bottom"
                            style="font-size: 14px; padding: 10px;">Grand Total</td>
                        <td class="font-bold text-right border-top border-bottom"
                            style="font-size: 14px; padding: 10px;">Rs. {{ $formatAmt($invoice->grand_total) }}</td>
                    </tr>

                    {{-- POS Payment Tracking --}}
                    @if ($totalReceived > 0)
                        <tr class="bg-light">
                            <td class="font-bold text-right">Amount Received</td>
                            <td class="font-bold text-right">Rs. {{ $formatAmt($totalReceived) }}</td>
                        </tr>
                        @if ($totalChange > 0)
                            <tr class="bg-light">
                                <td class="font-bold text-right">Change Returned</td>
                                <td class="font-bold text-right">Rs. {{ $formatAmt($totalChange) }}</td>
                            </tr>
                        @endif
                        <tr class="bg-light">
                            <td class="font-bold text-right border-bottom">Paid Against Bill</td>
                            <td class="font-bold text-right border-bottom">Rs. {{ $formatAmt($paidAmt) }}</td>
                        </tr>
                        @if ($balanceDue > 0)
                            <tr>
                                <td class="font-bold text-right">Balance Due</td>
                                <td class="font-bold text-right">Rs.
                                    {{ $formatAmt($balanceDue) }}</td>
                            </tr>
                        @endif
                    @endif
                </table>

                <div class="text-right" style="margin-top: 50px;">
                    <div
                        style="border-top: 1px solid #333; display: inline-block; padding-top: 5px; width: 150px; font-size: 10px; font-weight: bold; text-transform: uppercase;">
                        Authorized Signatory
                    </div>
                </div>
            </td>
        </tr>
    </table>

    @if ($invoice->terms_conditions)
        <div style="margin-top: 40px; font-size: 10px; color: #666; border-top: 1px solid #eee; padding-top: 10px;">
            <strong>Terms & Conditions:</strong><br>
            {!! nl2br(e($invoice->terms_conditions)) !!}
        </div>
    @endif

</body>

</html>
