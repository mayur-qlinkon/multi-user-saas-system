<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice #<?php echo e($invoice->invoice_number); ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
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

    <?php
        $formatAmt = function ($amount) {
            return number_format((float) $amount, 2, '.', ',');
        };

        // Legal Entity & Operational Branch Details
        $company = $invoice->company ?? auth()->user()->company;
        $store   = $invoice->store;

        // Billing priority: invoice snapshot → store accessor (store accessors already fall back to get_setting)
        $billingGstin        = $invoice->gst_number     ?? $store->gst_number     ?? get_setting('gst_number');
        $billingUpiId        = $invoice->upi_id         ?? $store->upi_id;
        $billingBankName     = $invoice->bank_name      ?? $store->bank_name;
        $billingAccName      = $invoice->account_name   ?? $store->account_name;
        $billingAccNo        = $invoice->account_number ?? $store->account_number;
        $billingIfsc         = $invoice->ifsc_code      ?? $store->ifsc_code;
        $signaturePath = null;

        if ($invoice->signature) {
            $signaturePath = storage_path('app/public/' . $invoice->signature);
        } elseif (!empty($store->signature)) {
            $signaturePath = storage_path('app/public/' . $store->signature);
        }

        $billingSignatureUrl = null;

        if ($signaturePath && file_exists($signaturePath)) {
            $type = pathinfo($signaturePath, PATHINFO_EXTENSION);
            $data = file_get_contents($signaturePath);
            $billingSignatureUrl = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        $billingFooterNote   = $invoice->invoice_footer_note ?? $store->invoice_footer_note;
        $billingTerms        = $invoice->terms_conditions    ?? $store->invoice_terms;

        $customerName = $invoice->client ? $invoice->client->name : $invoice->customer_name ?? 'Guest Customer';
        $customerPhone = $invoice->client ? $invoice->client->phone : 'N/A';
        $customerAddress = $invoice->client ? $invoice->client->address : 'N/A';
        $paidAmt = $invoice->payments->where('status', 'completed')->sum('amount');
        $totalReceived = $invoice->payments->where('status', 'completed')->sum('amount_received');
        $totalChange = $invoice->payments->where('status', 'completed')->sum('change_returned');
        $balanceDue = $invoice->grand_total - $paidAmt;
    ?>

    
    <table style="margin-bottom: 30px;">
        <tr>
            <td style="width: 50%;">
                <div class="header-title">TAX INVOICE</div>
                <div class="font-bold text-gray mt-4"># <?php echo e($invoice->invoice_number); ?></div>
            </td>
            <td style="width: 50%;" class="text-right">
                
                <h2 style="margin:0; font-size: 18px;" class="uppercase"><?php echo e($company->name ?? 'Company Name'); ?></h2>
                <div class="text-gray" style="line-height: 1.4; margin-top: 6px;">
                    <?php if($billingGstin): ?>
                        GSTIN: <strong style="color: #333;"><?php echo e($billingGstin); ?></strong><br>
                    <?php endif; ?>
                    <?php if($company->email): ?>
                        Email: <?php echo e($company->email); ?><br>
                    <?php endif; ?>
                    <?php if($company->phone): ?>
                        Phone: <?php echo e($company->phone); ?>

                    <?php endif; ?>
                </div>

                
                <?php if($store): ?>
                    <div style="margin-top: 12px;">
                        <div
                            style="font-size: 10px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 2px;">
                            Branch / Store</div>
                        <div class="text-gray" style="line-height: 1.4;">
                            <span class="font-bold" style="color: #333;"><?php echo e($store->name); ?></span><br>
                            <?php if($store->address): ?>
                                <?php echo e($store->address); ?><br>
                            <?php endif; ?>
                            <?php echo e($store->city ?? ''); ?><?php if($store->city && $store->zip_code): ?>
                                ,
                            <?php endif; ?><?php echo e($store->zip_code ?? ''); ?><br>
                            <?php echo e($store->state->name ?? ($store->state_id ?? '')); ?>

                        </div>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    
    <table class="border-top border-bottom" style="margin-bottom: 30px; padding: 15px 0;">
        <tr>
            <td style="width: 50%;">
                <div
                    style="font-size: 10px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 5px;">
                    Billed To</div>
                <div style="font-size: 14px; font-weight: bold; margin-bottom: 3px;"><?php echo e($customerName); ?></div>
                <div class="text-gray" style="line-height: 1.4;">
                    <?php if($customerAddress !== 'N/A'): ?>
                        <?php echo e($customerAddress); ?><br>
                    <?php endif; ?>
                    <?php if($customerPhone !== 'N/A'): ?>
                        Phone: <?php echo e($customerPhone); ?><br>
                    <?php endif; ?>
                    <?php if($invoice->client && $invoice->client->gst_number): ?>
                        GSTIN: <strong><?php echo e($invoice->client->gst_number); ?></strong>
                    <?php endif; ?>
                </div>
            </td>
            <td style="width: 50%; line-height: 1.8;">
                <table>
                    <tr>
                        <td class="text-gray font-bold text-right" style="width: 60%;">Invoice Date:</td>
                        <td class="font-bold text-right">
                            <?php echo e(\Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y')); ?></td>
                    </tr>
                    <?php if($invoice->due_date): ?>
                        <tr>
                            <td class="text-gray font-bold text-right">Due Date:</td>
                            <td class="font-bold text-right">
                                <?php echo e(\Carbon\Carbon::parse($invoice->due_date)->format('d M Y')); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-gray font-bold text-right">Place of Supply:</td>
                        <td class="font-bold text-right"><?php echo e($invoice->supply_state); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    
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
            <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="font-bold"><?php echo e($item->product_name); ?></div>
                        <div style="font-size: 10px; color: #777;">SKU:
    <?php echo e($item->sku->sku_code ?? ($item->sku->sku ?? 'N/A')); ?></div>

<?php if($item->discount_amount > 0): ?>
    <div style="font-size: 10px; margin-top: 2px; color: #c2410c;">
        Disc:
        <?php if($item->discount_type === 'percentage' && (float) $item->discount_value > 0): ?>
            <?php echo e((float) $item->discount_value); ?>%
            <span style="color:#999;">(-₹<?php echo e($formatAmt($item->discount_amount)); ?>)</span>
        <?php else: ?>
            ₹<?php echo e($formatAmt($item->discount_amount)); ?>

        <?php endif; ?>
    </div>
<?php endif; ?>
                    </td>
                    <td class="text-center text-gray"><?php echo e($item->hsn_code ?? '-'); ?></td>
                    <td class="text-right text-gray"><?php echo e($formatAmt($item->unit_price)); ?></td>
                    <td class="text-center font-bold"><?php echo e((float) $item->quantity); ?></td>
                    <td class="text-right text-gray">
                        <?php echo e($formatAmt($item->tax_amount)); ?><br>
                        <span style="font-size: 9px;">(<?php echo e((float) $item->tax_percent); ?>%)</span>
                    </td>
                    <td class="text-right font-bold"><?php echo e($formatAmt($item->total_amount)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    
    <table>
        <tr>
            
            <td style="width: 50%; padding-right: 20px;">
                <?php if($invoice->notes): ?>
                    <div class="mb-2 text-gray"><strong style="color:#333;">Note:</strong> <?php echo e($invoice->notes); ?></div>
                <?php endif; ?>

                <?php if($billingBankName || $billingAccNo): ?>
                    <div style="margin-top: 20px;">
                        <div style="font-size: 10px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 5px;">
                            Bank Details</div>
                        <div class="text-gray" style="line-height: 1.5; font-size: 11px;">
                            <?php if($billingBankName): ?><strong>Bank:</strong> <?php echo e($billingBankName); ?><br><?php endif; ?>
                            <?php if($billingAccName): ?><strong>A/C Name:</strong> <?php echo e($billingAccName); ?><br><?php endif; ?>
                            <?php if($billingAccNo): ?><strong>A/C No:</strong> <?php echo e($billingAccNo); ?><br><?php endif; ?>
                            <?php if($billingIfsc): ?><strong>IFSC:</strong> <?php echo e($billingIfsc); ?><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                
            </td>

            
            <td style="width: 50%;">
                <table class="totals-table">
                    <tr>
                        <td class="text-gray font-bold text-right" style="width: 60%;">Taxable Amount</td>
                        <td class="font-bold text-right">₹ <?php echo e($formatAmt($invoice->taxable_amount)); ?></td>
                    </tr>

                    <?php if($invoice->igst_amount > 0): ?>
                        <tr>
                            <td class="text-gray font-bold text-right">IGST</td>
                            <td class="font-bold text-right">₹ <?php echo e($formatAmt($invoice->igst_amount)); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td class="text-gray font-bold text-right">CGST</td>
                            <td class="font-bold text-right">₹ <?php echo e($formatAmt($invoice->cgst_amount)); ?></td>
                        </tr>
                        <tr>
                            <td class="text-gray font-bold text-right">SGST</td>
                            <td class="font-bold text-right">₹ <?php echo e($formatAmt($invoice->sgst_amount)); ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php if($invoice->discount_amount > 0): ?>
                        <tr>
                            <td class="text-gray font-bold text-right">
                                Discount
                                <?php if($invoice->discount_type === 'percentage' && (float) $invoice->discount_value > 0): ?>
                                    (<?php echo e((float) $invoice->discount_value); ?>%)
                                <?php endif; ?>
                            </td>
                            <td class="font-bold text-right" style="color: red;">
                                <?php if($invoice->discount_type === 'percentage' && (float) $invoice->discount_value > 0): ?>
                                    (-₹<?php echo e($formatAmt($invoice->discount_amount)); ?>)
                                <?php else: ?>
                                    (-) ₹<?php echo e($formatAmt($invoice->discount_amount)); ?>

                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php if($invoice->shipping_charge > 0): ?>
                        <tr>
                            <td class="text-gray font-bold text-right">Shipping</td>
                            <td class="font-bold text-right">₹ <?php echo e($formatAmt($invoice->shipping_charge)); ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php if($invoice->round_off != 0): ?>
                        <tr>
                            <td class="text-gray font-bold text-right">Round Off</td>
                            <td class="font-bold text-right">₹ <?php echo e($formatAmt($invoice->round_off)); ?></td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td class="font-bold text-right border-top border-bottom"
                            style="font-size: 14px; padding: 10px;">Grand Total</td>
                        <td class="font-bold text-right border-top border-bottom"
                            style="font-size: 14px; padding: 10px;">₹ <?php echo e($formatAmt($invoice->grand_total)); ?></td>
                    </tr>

                    
                    <?php if($totalReceived > 0): ?>
                        <tr class="bg-light">
                            <td class="font-bold text-right">Amount Received</td>
                            <td class="font-bold text-right">₹ <?php echo e($formatAmt($totalReceived)); ?></td>
                        </tr>
                        <?php if($totalChange > 0): ?>
                            <tr class="bg-light">
                                <td class="font-bold text-right">Change Returned</td>
                                <td class="font-bold text-right">₹ <?php echo e($formatAmt($totalChange)); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="bg-light">
                            <td class="font-bold text-right border-bottom">Paid Against Bill</td>
                            <td class="font-bold text-right border-bottom">₹ <?php echo e($formatAmt($paidAmt)); ?></td>
                        </tr>
                        <?php if($balanceDue > 0): ?>
                            <tr>
                                <td class="font-bold text-right">Balance Due</td>
                                <td class="font-bold text-right">₹
                                    <?php echo e($formatAmt($balanceDue)); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                </table>

                <div class="text-right" style="margin-top: 50px;">
                    <?php if($billingSignatureUrl): ?>
                        <img src="<?php echo e($billingSignatureUrl); ?>" alt="Authorized Signature"
                            style="max-height: 80px; display: block; margin-left: auto; margin-bottom: 4px;">
                    <?php endif; ?>
                    <div
                        style="border-top: 1px solid #333; display: inline-block; padding-top: 5px; width: 150px; font-size: 10px; font-weight: bold; text-transform: uppercase;">
                        Authorized Signatory
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <?php if($billingFooterNote || $billingTerms): ?>
        <div style="margin-top: 40px; font-size: 10px; color: #666; border-top: 1px solid #eee; padding-top: 10px;">
            <?php if($billingFooterNote): ?>
                <div style="margin-bottom: 8px; line-height: 1.5;"><?php echo nl2br(e($billingFooterNote)); ?></div>
            <?php endif; ?>
            <?php if($billingTerms): ?>
                <div><strong>Terms & Conditions:</strong><br><?php echo nl2br(e($billingTerms)); ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</body>

</html>
<?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/invoices/pdf.blade.php ENDPATH**/ ?>