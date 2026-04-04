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
                            <?php if($item->hsn_code): ?>
                                HSN:<?php echo e($item->hsn_code); ?> |
                            <?php endif; ?> Rate:₹<?php echo e(number_format($item->unit_price, 2)); ?> |
                            Tax:<?php echo e((float) $item->tax_percent); ?>%
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        
        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td class="text-right"><?php echo e(number_format($invoice->subtotal, 2)); ?></td>
            </tr>

            
            <?php if($invoice->discount_amount > 0): ?>
                <tr>
                    <td class="font-bold">Discount</td>
                    <td class="text-right font-bold">-<?php echo e(number_format($invoice->discount_amount, 2)); ?></td>
                </tr>
            <?php endif; ?>

            
            <?php if($invoice->igst_amount > 0): ?>
                <tr>
                    <td>IGST (Inter-state)</td>
                    <td class="text-right"><?php echo e(number_format($invoice->igst_amount, 2)); ?></td>
                </tr>
            <?php else: ?>
                <?php if($invoice->cgst_amount > 0 || $invoice->sgst_amount > 0): ?>
                    <tr>
                        <td>CGST</td>
                        <td class="text-right"><?php echo e(number_format($invoice->cgst_amount, 2)); ?></td>
                    </tr>
                    <tr>
                        <td>SGST</td>
                        <td class="text-right"><?php echo e(number_format($invoice->sgst_amount, 2)); ?></td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>

            
            <?php if($invoice->round_off != 0): ?>
                <tr>
                    <td>Round Off</td>
                    <td class="text-right"><?php echo e(number_format($invoice->round_off, 2)); ?></td>
                </tr>
            <?php endif; ?>

            <tr class="font-bold" style="font-size: 15px;">
                <td style="border-top: 1px dashed #000; padding-top: 5px;">GRAND TOTAL</td>
                <td class="text-right" style="border-top: 1px dashed #000; padding-top: 5px;">
                    ₹<?php echo e(number_format($invoice->grand_total, 2)); ?></td>
            </tr>
        </table>

        
        <?php
            $upiId = $invoice->store->upi_id ?? null;
            $paymentStatus = strtolower($invoice->payment_status ?? 'unpaid');
            $showQr = false;

            if (!empty($upiId)) {
                if ($paymentStatus !== 'paid') {
                    $showQr = true;
                } elseif (isset($payment) && isset($payment->paymentMethod)) {
                    $methodStr = strtolower(
                        ($payment->paymentMethod->name ?? '') . ' ' . ($payment->paymentMethod->slug ?? ''),
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
            <p style="margin:0; font-size:10px;">Powered by Qlinkon BIZNESS</p>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/pos/receipt.blade.php ENDPATH**/ ?>