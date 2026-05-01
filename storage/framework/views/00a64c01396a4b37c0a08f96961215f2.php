<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Quotation <?php echo e($quotation->quotation_number); ?></title>
    <style>
        /* DOMPDF highly compatible CSS */
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-left {
            text-align: left;
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

        .text-uppercase {
            text-transform: uppercase;
        }

        .header-table {
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .company-title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .document-title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .info-table {
            margin-bottom: 25px;
        }

        .info-table td {
            vertical-align: top;
        }

        .meta-label {
            color: #555;
            font-weight: bold;
        }

        .items-table {
            margin-bottom: 25px;
        }

        .items-table th {
            background-color: #000;
            color: #fff;
            padding: 8px;
            font-size: 11px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }

        .totals-table {
            width: 300px;
            float: right;
            border-top: 1px solid #000;
            margin-bottom: 30px;
        }

        .totals-table td {
            padding: 6px 0;
        }

        .grand-total {
            border-top: 2px solid #000;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            clear: both;
            margin-top: 30px;
        }

        .signature-box {
            float: right;
            width: 200px;
            text-align: center;
            margin-top: 50px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            color: #555;
        }

        /* Utility */
        .page-break {
            page-break-after: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    <?php
        $formatAmt = function ($amount) {
            return number_format((float) $amount, 2, '.', ',');
        };
        $customerName = $quotation->customer ? $quotation->customer->name : $quotation->customer_name ?? 'Guest';
        $customerPhone = $quotation->customer ? $quotation->customer->phone : $quotation->customer_phone ?? 'N/A';
        $customerGST = $quotation->customer ? $quotation->customer->gst_number : $quotation->customer_gstin ?? null;
        $quoteType = !empty($customerGST) ? 'B2B' : 'B2C';
    ?>

    <table class="header-table">
        <tr>
            <td class="text-left" style="width: 50%; vertical-align: top;">
                <div class="document-title">QUOTATION</div>
                <div class="font-bold"># <?php echo e($quotation->quotation_number); ?></div>
                <div style="margin-top: 5px;">
                    <span class="meta-label">Date:</span>
                    <?php echo e(\Carbon\Carbon::parse($quotation->quotation_date)->format('d M Y')); ?><br>
                    <?php if($quotation->valid_until): ?>
                        <span class="meta-label">Valid Until:</span>
                        <?php echo e(\Carbon\Carbon::parse($quotation->valid_until)->format('d M Y')); ?>

                    <?php endif; ?>
                </div>
            </td>
            <td class="text-right" style="width: 50%; vertical-align: top;">
                <div class="company-title"><?php echo e($company->name); ?></div>
                <div>
                    <?php if($company->gst_number || $company->gstin): ?>
                        GSTIN: <?php echo e($company->gst_number ?? $company->gstin); ?><br>
                    <?php endif; ?>
                    Email: <?php echo e($company->email); ?><br>
                    Phone: <?php echo e($company->phone); ?>

                </div>
                <?php if($quotation->store): ?>
                    <div style="margin-top: 8px;">
                        <span class="font-bold">Branch:</span><br>
                        <?php echo e($quotation->store->name); ?><br>
                        <?php echo e($quotation->store->city ?? ''); ?> <?php echo e($quotation->store->zip_code ?? ''); ?>

                    </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="text-left" style="width: 50%;">
                <div class="meta-label text-uppercase" style="font-size: 10px; margin-bottom: 3px;">Quotation To</div>
                <div class="font-bold" style="font-size: 14px;"><?php echo e($customerName); ?></div>
                <?php if($customerGST): ?>
                    <div><span class="font-bold">GSTIN:</span> <?php echo e($customerGST); ?></div>
                <?php endif; ?>
                <?php if($quotation->billing_address): ?>
                    <div><?php echo e($quotation->billing_address); ?></div>
                <?php endif; ?>
                <?php if($customerPhone !== 'N/A'): ?>
                    <div>Phone: <?php echo e($customerPhone); ?></div>
                <?php endif; ?>
            </td>
            <td class="text-right" style="width: 50%;">
                <div><span class="meta-label">Place of Supply:</span> <span
                        class="font-bold"><?php echo e($quotation->supply_state); ?></span></div>
                <div><span class="meta-label">Quotation Type:</span> <span class="font-bold"><?php echo e($quoteType); ?></span>
                </div>
                <div><span class="meta-label">Status:</span> <span
                        class="font-bold text-uppercase"><?php echo e($quotation->status); ?></span></div>
                <?php if($quotation->reference_number): ?>
                    <div><span class="meta-label">Ref/PO:</span> <span
                            class="font-bold"><?php echo e($quotation->reference_number); ?></span></div>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left">Description</th>
                <th class="text-center">HSN</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Rate</th>
                <th class="text-center">Disc</th>
                <th class="text-center">GST</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $quotation->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="text-left">
                        <div class="font-bold"><?php echo e($item->product_name); ?></div>
                        <div style="font-size: 9px; color: #666;">SKU: <?php echo e($item->sku_code ?? 'N/A'); ?></div>
                    </td>
                    <td class="text-center"><?php echo e($item->hsn_code ?? '-'); ?></td>
                    <td class="text-center font-bold"><?php echo e((float) $item->quantity); ?></td>
                    <td class="text-right"><?php echo e($formatAmt($item->unit_price)); ?></td>
                    <td class="text-center">
                        <?php if($item->discount_amount > 0): ?>
                            <?php if($item->discount_type === 'percentage'): ?>
                                <div class="font-bold"><?php echo e((float) $item->discount_value); ?>%</div>
                                <div style="font-size: 9px; color: #666; margin-top: 2px;">(-<?php echo e($formatAmt($item->discount_amount)); ?>)</div>
                            <?php else: ?>
                                <div class="font-bold"><?php echo e($formatAmt($item->discount_amount)); ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo e((float) $item->tax_percent); ?>%</td>
                    <td class="text-right font-bold"><?php echo e($formatAmt($item->total_amount)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="avoid-break">
        <table class="totals-table">
            <tr>
                <td class="text-left">Subtotal</td>
                <td class="text-right font-bold"><?php echo e($formatAmt($quotation->subtotal)); ?></td>
            </tr>
            <?php if($quotation->igst_amount > 0): ?>
                <tr>
                    <td class="text-left">IGST</td>
                    <td class="text-right font-bold"><?php echo e($formatAmt($quotation->igst_amount)); ?></td>
                </tr>
            <?php else: ?>
                <?php if($quotation->cgst_amount > 0 || $quotation->sgst_amount > 0): ?>
                    <tr>
                        <td class="text-left">CGST</td>
                        <td class="text-right font-bold"><?php echo e($formatAmt($quotation->cgst_amount)); ?></td>
                    </tr>
                    <tr>
                        <td class="text-left">SGST</td>
                        <td class="text-right font-bold"><?php echo e($formatAmt($quotation->sgst_amount)); ?></td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>

            <?php if($quotation->shipping_charge > 0): ?>
                <tr>
                    <td class="text-left">Shipping / Other</td>
                    <td class="text-right font-bold"><?php echo e($formatAmt($quotation->shipping_charge)); ?></td>
                </tr>
            <?php endif; ?>

            <?php if($quotation->discount_amount > 0): ?>
                <tr>
                    <td class="text-left">
                        Discount
                        <?php if($quotation->discount_type === 'percentage'): ?>
                            <span style="font-size: 10px; color: #555;">(<?php echo e((float) $quotation->discount_value); ?>%)</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right font-bold">(-) <?php echo e($formatAmt($quotation->discount_amount)); ?></td>
                </tr>
            <?php endif; ?>  

            <tr>
                <td class="text-left grand-total text-uppercase">Grand Total</td>
                <td class="text-right grand-total"><?php echo e($formatAmt($quotation->grand_total)); ?></td>
            </tr>
        </table>

        <div class="footer">
            <?php if($quotation->notes || $quotation->terms_conditions): ?>
                <div style="width: 60%; float: left;">
                    <?php if($quotation->notes): ?>
                        <div style="margin-bottom: 15px;">
                            <div class="font-bold text-uppercase" style="font-size: 11px; margin-bottom: 3px;">Notes:
                            </div>
                            <div><?php echo e($quotation->notes); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if($quotation->terms_conditions): ?>
                        <div>
                            <div class="font-bold text-uppercase" style="font-size: 11px; margin-bottom: 3px;">Terms &
                                Conditions:</div>
                            <div style="font-size: 11px;"><?php echo nl2br(e($quotation->terms_conditions)); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="signature-box">
                Authorized Signatory
            </div>
        </div>
    </div>

</body>

</html>
<?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/quotations/pdf.blade.php ENDPATH**/ ?>