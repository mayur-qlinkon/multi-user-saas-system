<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Purchase Order - <?php echo e($purchase->purchase_number); ?></title>
    <style>
        /* Standard Professional B&W Invoice Styling */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table {
            margin-bottom: 30px;
        }

        .header-table td {
            vertical-align: top;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #000;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .document-info {
            margin-top: 8px;
            font-size: 12px;
            line-height: 1.6;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-block {
            font-size: 12px;
            line-height: 1.5;
        }

        /* Strict Accounting Table */
        .items-table {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            margin-top: 15px;
        }

        .items-table th {
            padding: 10px 6px;
            font-size: 11px;
            text-transform: uppercase;
            text-align: left;
            border-bottom: 1px solid #000;
        }

        .items-table td {
            padding: 10px 6px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        /* Totals Block */
        .totals-wrapper {
            width: 100%;
            display: table;
            margin-top: 10px;
        }

        .totals-left {
            width: 50%;
            display: table-cell;
            vertical-align: bottom;
        }

        .totals-right {
            width: 50%;
            display: table-cell;
        }

        .totals-table {
            width: 100%;
            float: right;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .grand-total-row td {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000 !important;
            padding: 10px 8px;
        }

        .footer-notes {
            margin-top: 30px;
            font-size: 11px;
            line-height: 1.5;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }

        .page-break {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    <?php
        $companyInfo = $purchase->store ?? auth()->user()->company;

        // Helper function for clean currency formatting
        $formatAmt = function ($amount) {
            return number_format((float) $amount, 2, '.', ',');
        };
    ?>

    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <h1 class="title">Purchase Order</h1>
                <div class="document-info">
                    <strong>PO Number:</strong> <?php echo e($purchase->purchase_number); ?><br>
                    <strong>Order Date:</strong> <?php echo e($purchase->purchase_date->format('d M Y')); ?><br>
                    <strong>Status:</strong> <span
                        style="text-transform: uppercase;"><?php echo e(str_replace('_', ' ', $purchase->status)); ?></span>
                </div>
            </td>
            <td style="width: 50%; text-align: right;">
                <h2 style="margin:0; font-size: 16px; font-weight: bold; text-transform: uppercase;">
                    <?php echo e($companyInfo->name ?? 'Company Name'); ?></h2>
                <div style="margin-top: 5px; line-height: 1.5;">
                    <?php echo e($companyInfo->address); ?><br>
                    Ph: <?php echo e($companyInfo->phone); ?><br>
                    Email: <?php echo e($companyInfo->email); ?>

                </div>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td style="width: 48%; padding-right: 2%;">
                <div class="section-title">Supplier Details</div>
                <div class="info-block">
                    <strong><?php echo e($purchase->supplier->name ?? 'N/A'); ?></strong><br>
                    <?php echo e($purchase->supplier->address); ?><br>
                    <?php if($purchase->supplier->phone): ?>
                        Phone: <?php echo e($purchase->supplier->phone); ?><br>
                    <?php endif; ?>
                    <?php if($purchase->supplier->email): ?>
                        Email: <?php echo e($purchase->supplier->email); ?>

                    <?php endif; ?>
                </div>
            </td>
            <td style="width: 48%; padding-left: 2%;">
                <div class="section-title">Shipping Destination</div>
                <div class="info-block">
                    <strong><?php echo e($purchase->warehouse->name ?? 'N/A'); ?></strong><br>
                    Delivery Branch: <?php echo e($purchase->store->name ?? 'Default'); ?>

                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Product Description</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Unit Cost</th>
                <th class="text-right">Disc.</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $purchase->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $qty = (float) $item->quantity;
                    $cost = (float) $item->unit_cost;
                    $discPct = (float) ($item->discount_percent ?? 0);

                    // Core line math
                    $lineGross = $qty * $cost;
                    $lineDiscAmount = $lineGross * ($discPct / 100);
                    $lineTax = (float) $item->tax_amount;

                    // Fallback to strict math if item->total isn't perfectly mapped
$lineTotal =
    $item->total ?? $lineGross - $lineDiscAmount + ($item->tax_type === 'exclusive' ? $lineTax : 0);
                ?>
                <tr class="page-break">
                    <td>
                        <strong><?php echo e($item->product->name ?? 'Unknown'); ?></strong><br>
                        <span style="color:#555; font-size:10px;">SKU: <?php echo e($item->productSku->sku ?? 'N/A'); ?></span>
                    </td>
                    <td class="text-center"><?php echo e($qty); ?></td>
                    <td class="text-right"><?php echo e($formatAmt($cost)); ?></td>

                    <td class="text-right">
                        <?php if($discPct > 0): ?>
                            <?php echo e($discPct); ?>%<br>
                            <span style="font-size: 10px; color: #555;">(-<?php echo e($formatAmt($lineDiscAmount)); ?>)</span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>

                    <td class="text-right"><?php echo e($formatAmt($lineTax)); ?></td>
                    <td class="text-right"><strong>Rs. <?php echo e($formatAmt($lineTotal)); ?></strong></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="totals-wrapper page-break">
        <div class="totals-left">
            <?php if($purchase->notes): ?>
                <p><strong>Note:</strong><br> <?php echo e($purchase->notes); ?></p>
            <?php endif; ?>
        </div>
        <div class="totals-right">
            <table class="totals-table">
                <tr>
                    <td>Subtotal (Taxable)</td>
                    <td class="text-right">Rs. <?php echo e($formatAmt($purchase->taxable_amount)); ?></td>
                </tr>
                <tr>
                    <td>Total Tax</td>
                    <td class="text-right">Rs. <?php echo e($formatAmt($purchase->tax_amount)); ?></td>
                </tr>

                <?php if($purchase->discount_amount > 0): ?>
                    <tr>
                        <td>Global Discount</td>
                        <td class="text-right">(-) Rs. <?php echo e($formatAmt($purchase->discount_amount)); ?></td>
                    </tr>
                <?php endif; ?>

                <?php if($purchase->shipping_cost > 0): ?>
                    <tr>
                        <td>Shipping Charges</td>
                        <td class="text-right">Rs. <?php echo e($formatAmt($purchase->shipping_cost)); ?></td>
                    </tr>
                <?php endif; ?>

                <?php if($purchase->other_charges > 0): ?>
                    <tr>
                        <td>Other Charges</td>
                        <td class="text-right">Rs. <?php echo e($formatAmt($purchase->other_charges)); ?></td>
                    </tr>
                <?php endif; ?>

                <?php if($purchase->round_off != 0): ?>
                    <tr>
                        <td>Round Off</td>
                        <td class="text-right">Rs. <?php echo e($formatAmt($purchase->round_off)); ?></td>
                    </tr>
                <?php endif; ?>

                <tr class="grand-total-row">
                    <td>GRAND TOTAL</td>
                    <td class="text-right">Rs. <?php echo e($formatAmt($purchase->total_amount)); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <?php if($purchase->terms_and_conditions): ?>
        <div class="footer-notes page-break">
            <strong>Terms & Conditions:</strong><br>
            <?php echo nl2br(e($purchase->terms_and_conditions)); ?>

        </div>
    <?php endif; ?>

</body>

</html>
<?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/purchases/pdf.blade.php ENDPATH**/ ?>