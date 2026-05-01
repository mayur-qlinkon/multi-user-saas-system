<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo e($invoice->invoice_number); ?></title>
    <style>
        /* 🌟 STRICT 80mm THERMAL PRINTER CSS */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
        }

        .ticket {
            width: 80mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 10px;
            box-sizing: border-box;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 12px;
        }

        th,
        td {
            padding: 4px 0;
        }

        th {
            text-align: left;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        .items-table td {
            border-bottom: 1px dashed #eee;
        }

        .items-table .meta-row td {
            border-bottom: 1px dashed #000;
            padding-top: 0;
            padding-bottom: 6px;
            font-size: 10px;
            color: #444;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .totals td {
            padding: 3px 0;
            border: none;
        }

        /* 🌟 STRIP AWAY BROWSER MARGINS DURING ACTUAL PRINT */
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }

            body {
                margin: 0;
            }

            .ticket {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="ticket">
        <div class="text-center">
            
            <h2 class="font-bold" style="margin:0; font-size:18px;"><?php echo e($invoice->company->name ?? 'COMPANY NAME'); ?></h2>
            <h3 class="font-bold" style="margin:2px 0; font-size:14px;"><?php echo e($invoice->store->name ?? 'Branch Name'); ?></h3>

            <p style="margin:4px 0;"><?php echo e($invoice->store->address ?? 'Store Address'); ?></p>
            <p style="margin:2px 0;">Phone: <?php echo e($invoice->store->phone ?? 'N/A'); ?></p>

            <?php if(isset($invoice->store->gst_number)): ?>
                <p style="margin:2px 0;">GSTIN: <?php echo e($invoice->store->gst_number); ?></p>
            <?php endif; ?>
            <div class="divider"></div>
        </div>

        
        <div>
            <p style="margin:2px 0;">Receipt: <span class="font-bold"><?php echo e($invoice->invoice_number); ?></span></p>
            <p style="margin:2px 0;">Date: <?php echo e(\Carbon\Carbon::parse($invoice->created_at)->format('d M Y, h:i A')); ?></p>
            <p style="margin:2px 0;">Cashier: <?php echo e($invoice->creator->name ?? 'Admin'); ?></p>
            <p style="margin:2px 0;">Customer: <span
                    class="font-bold"><?php echo e($invoice->customer_name ?: $invoice->customer->name ?? 'Walk-in'); ?></span></p>

            
            <?php if(!empty($invoice->customer->gst_number) || !empty($invoice->customer_gstin)): ?>
                <p style="margin:2px 0;">Cust GST: <?php echo e($invoice->customer->gst_number ?? $invoice->customer_gstin); ?></p>
            <?php endif; ?>
        </div>

        
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Item</th>
                    <th style="width: 15%;">Qty</th>
                    <th class="text-right" style="width: 35%;">Total</th>
                </tr>
            </thead>
            <?php if(batch_enabled()): ?>
                <?php
                    $receiptBatchMovements = ($invoice->relationLoaded('stockMovements'))
                        ? $invoice->stockMovements->where('direction', 'out')->whereNotNull('batch_number')->groupBy('product_sku_id')
                        : collect();
                ?>
            <?php endif; ?>
            <tbody>
                <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td style="padding-bottom: 0;"><?php echo e($item->product_name); ?></td>
                        <td style="padding-bottom: 0;"><?php echo e((int) $item->quantity); ?></td>
                        <td class="text-right" style="padding-bottom: 0;"><?php echo e(number_format($item->total_amount, 2)); ?>

                        </td>
                    </tr>
                    
                    <tr class="meta-row">
                        <td colspan="3">
                            <?php
                                $taxPct = (float) $item->tax_percent;
                                $qty = (float) $item->quantity;
                                $unitPrice = (float) $item->unit_price;
                                $taxType = $item->tax_type ?? 'exclusive'; 

                                if ($taxType === 'inclusive') {
                                    // INCLUSIVE: Price already includes tax. Do not go higher.
                                    $lineTotal = $unitPrice * $qty;
                                    $taxableLine = $lineTotal / (1 + ($taxPct / 100));
                                    $taxAmtLine = $lineTotal - $taxableLine;
                                } else {
                                    // EXCLUSIVE: Tax is added ON TOP of the base price.
                                    $taxableLine = $unitPrice * $qty;
                                    $taxAmtLine = $taxableLine * ($taxPct / 100);
                                }
                            ?>

                            <?php if($item->hsn_code): ?>
                                HSN:<?php echo e($item->hsn_code); ?> |
                            <?php endif; ?>
                            Taxable:₹<?php echo e(number_format($taxableLine, 2)); ?>


                            <?php if($taxPct > 0): ?>
                                | <?php echo e($taxType === 'inclusive' ? 'Incl.' : '+'); ?><?php echo e($taxPct); ?>% GST:₹<?php echo e(number_format($taxAmtLine, 2)); ?>

                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        
        <?php
            $trueTaxableSubtotal = 0;
            $trueGstTotal = 0;

            foreach ($invoice->items as $item) {
                $taxPct = (float) $item->tax_percent;
                $qty = (float) $item->quantity;
                $unitPrice = (float) $item->unit_price;
                $taxType = $item->tax_type ?? 'exclusive';

                if ($taxType === 'inclusive') {
                    $lineTotal = $unitPrice * $qty;
                    $taxable = $lineTotal / (1 + ($taxPct / 100));
                    $gst = $lineTotal - $taxable;
                } else {
                    $taxable = $unitPrice * $qty;
                    $gst = $taxable * ($taxPct / 100);
                }

                $trueTaxableSubtotal += $taxable;
                $trueGstTotal += $gst;
            }

            // Split total GST equally for CGST/SGST
            $trueCgst = $trueGstTotal / 2;
            $trueSgst = $trueGstTotal / 2;
        ?>

        <table class="totals">
            
            <tr>
                <td>Taxable Value</td>
                <td class="text-right">₹<?php echo e(number_format($trueTaxableSubtotal, 2)); ?></td>
            </tr>

            
            <?php if($invoice->discount_amount > 0): ?>
                <tr>
                    <td class="font-bold">Discount</td>
                    <td class="text-right font-bold">-₹<?php echo e(number_format($invoice->discount_amount, 2)); ?></td>
                </tr>
            <?php endif; ?>

            
            <?php if($trueGstTotal > 0): ?>
                <tr>
                    <td>CGST</td>
                    <td class="text-right">₹<?php echo e(number_format($trueCgst, 2)); ?></td>
                </tr>
                <tr>
                    <td>SGST</td>
                    <td class="text-right">₹<?php echo e(number_format($trueSgst, 2)); ?></td>
                </tr>
            <?php endif; ?>

            
            <?php if($invoice->round_off != 0): ?>
                <tr>
                    <td>Round Off</td>
                    <td class="text-right">₹<?php echo e(number_format($invoice->round_off, 2)); ?></td>
                </tr>
            <?php endif; ?>

            
            <tr class="font-bold" style="font-size: 15px;">
                <td style="border-top: 1px dashed #000; padding-top: 5px;">GRAND TOTAL</td>
                <td class="text-right" style="border-top: 1px dashed #000; padding-top: 5px;">
                    ₹<?php echo e(number_format($invoice->grand_total, 2)); ?>

                </td>
            </tr>
        </table>    

        
        <?php
            $upiId = $invoice->store->upi_id ?? null;
            $showQr = false;

            // 🔥 CALCULATE ACTUAL PAYMENT STATUS
            $amountReceived = $payment->amount_received ?? 0;
            $grandTotal = $invoice->grand_total ?? 0;

            $isFullyPaid = $amountReceived >= $grandTotal;

            if (!empty($upiId)) {

                // ✅ SHOW QR IF NOT FULLY PAID
                if (!$isFullyPaid) {
                    $showQr = true;
                }

                // ✅ ALSO SHOW QR IF PAYMENT METHOD IS UPI (even if full paid)
                elseif (isset($payment) && isset($payment->paymentMethod)) {
                    $methodStr = strtolower(
                        ($payment->paymentMethod->name ?? '') . ' ' . ($payment->paymentMethod->slug ?? '')
                    );

                    if (
                        str_contains($methodStr, 'upi') ||
                        str_contains($methodStr, 'qr') ||
                        str_contains($methodStr, 'scan') ||
                        str_contains($methodStr, 'gpay') ||
                        str_contains($methodStr, 'phonepe')
                    ) {
                        $showQr = true;
                    }
                }
            }
        ?>

        <?php if($payment || $showQr): ?>
            <div class="divider"></div>
        <?php endif; ?>

        
        <?php if($payment): ?>
            <table class="totals" style="margin-top: 5px;">
                <tr>
                    <td>Paid via
                        (<?php echo e($payment->paymentMethod->name ?? ($payment->paymentMethod->title ?? ($payment->paymentMethod->label ?? 'Cash'))); ?>)
                    </td>
                    <td class="text-right font-bold">₹<?php echo e(number_format($payment->amount_received, 2)); ?></td>
                </tr>
                <?php if($payment->change_returned > 0): ?>
                    <tr>
                        <td>Change Returned</td>
                        <td class="text-right">₹<?php echo e(number_format($payment->change_returned, 2)); ?></td>
                    </tr>
                <?php endif; ?>
            </table>

            <?php if($showQr): ?>
                <div class="divider"></div>
            <?php endif; ?>
        <?php endif; ?>

        
        <?php if($showQr): ?>
            <?php
                $payeeName = rawurlencode($invoice->store->name ?? 'Store');
                $amount = number_format($invoice->grand_total, 2, '.', '');
                $upiString = "upi://pay?pa={$upiId}&pn={$payeeName}&am={$amount}&cu=INR";
                $qrApiUrl =
                    'https://api.qrserver.com/v1/create-qr-code/?size=120x120&margin=0&data=' . urlencode($upiString);
            ?>

            <div class="text-center" style="margin: 10px 0;">
                <p class="font-bold" style="font-size: 11px; margin-bottom: 5px;">Scan to Pay via UPI</p>
                <img src="<?php echo e($qrApiUrl); ?>" alt="UPI QR Code"
                    style="width: 110px; height: 110px; margin: 0 auto; display: block;">
                <p style="font-size: 10px; margin-top: 5px; font-family: monospace;">UPI ID: <?php echo e($upiId); ?></p>
            </div>

            <div class="divider"></div>
        <?php endif; ?>

        
        <div class="text-center">
            <?php if($invoice->discount_amount > 0): ?>
                <p class="font-bold" style="margin:10px 0 10px 0; border: 1px dashed #000; padding: 4px;">You Saved
                    ₹<?php echo e(number_format($invoice->discount_amount, 2)); ?>!</p>
            <?php endif; ?>
            <p class="font-bold" style="margin:5px 0;">Thank you for your visit!</p>
            <p style="margin:0; font-size:10px;">Powered by <?php echo e(config('app.name','Qlinkon.com')); ?></p>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/pos/receipt.blade.php ENDPATH**/ ?>