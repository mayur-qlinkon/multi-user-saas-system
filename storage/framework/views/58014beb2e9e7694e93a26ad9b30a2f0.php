<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Challan #<?php echo e($challan->challan_number); ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #111827; /* Darker gray for better print contrast */
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            vertical-align: top;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        
        .font-black { font-weight: 900; }
        .font-bold { font-weight: bold; }
        .font-medium { font-weight: 500; }
        
        .text-gray { color: #4b5563; }
        .text-light-gray { color: #9ca3af; }
        
        .uppercase { text-transform: uppercase; }
        .tracking-widest { letter-spacing: 0.1em; }
        .tracking-tighter { letter-spacing: -0.05em; }
        
        .border-bottom-thick { border-bottom: 2px solid #111827; }
        .border-top-thick { border-top: 2px solid #111827; }
        .border-bottom-thin { border-bottom: 1px solid #f3f4f6; }
        .border-top-thin { border-top: 1px solid #f3f4f6; }

        .bg-light { background-color: #f9fafb; }

        /* Header Badges */
        .type-badge {
            background-color: #1f2937; /* Gray 800 */
            color: #ffffff;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .status-badge {
            font-size: 11px;
            font-weight: 900;
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            display: inline-block;
            border-radius: 4px;
            text-transform: uppercase;
        }

        /* Items Table */
        .items-table {
            margin-top: 20px;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background-color: #f3f4f6; /* Gray 100 */
            padding: 12px;
            border-bottom: 2px solid #111827;
            font-size: 11px;
            font-weight: bold;
            color: #374151; /* Gray 700 */
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
        }

        /* Totals Table */
        .totals-table {
            width: 300px;
            float: right;
        }

        .totals-table td {
            padding: 10px 0;
        }

        /* Colors for Item Statuses */
        .text-orange { color: #ea580c; }
        .text-green { color: #16a34a; }
        .text-blue { color: #2563eb; }
        .text-red { color: #dc2626; }
        
        .page-break-avoid { page-break-inside: avoid; }
    </style>
</head>

<body>

    <?php
        $formatAmt = function ($amount) {
            return number_format((float) $amount, 2, '.', ',');
        };

        // 🌟 Legal Entity & Operational Branch Details
        $company = $challan->company ?? auth()->user()->company;
        $store = $challan->store;

        // 🌟 Party Details (Using Snapshot Data for stability)
        $partyName = $challan->party_name ?? 'Unknown Party';
        $partyPhone = $challan->party_phone ?? 'N/A';
        $partyAddress = $challan->party_address ?? 'N/A';
        $partyGSTIN = $challan->party_gst ?? null;
        $partyState = $challan->toState->name ?? $challan->party_state ?? 'N/A';
    ?>

    
    <table style="margin-bottom: 20px;">
        <tr>
            <td style="width: 50%;">
                <div class="type-badge"><?php echo e($challan->type_label); ?></div>
                <div style="font-size: 28px; font-weight: 900; line-height: 1; letter-spacing: -1px;">
                    # <?php echo e($challan->challan_number); ?>

                </div>
                <div class="font-bold text-gray" style="margin-top: 8px; font-size: 12px;">
                    Date: <?php echo e($challan->challan_date->format('d M Y')); ?>

                </div>
            </td>
            
            <td style="width: 50%;" class="text-right">
                <div style="font-size: 20px; font-weight: 900; text-transform: uppercase; line-height: 1;">
                    <?php echo e($company->name ?? 'Company Name'); ?>

                </div>
                <div class="text-gray font-medium" style="line-height: 1.4; margin-top: 6px; font-size: 11px;">
                    <?php if(isset($company->gst_number) || isset($company->gstin)): ?>
                        GSTIN: <strong style="color: #111827; text-transform: uppercase;"><?php echo e($company->gst_number ?? $company->gstin); ?></strong><br>
                    <?php endif; ?>
                    <?php if($company->email): ?>
                        Email: <?php echo e($company->email); ?><br>
                    <?php endif; ?>
                    <?php if($company->phone): ?>
                        Phone: <?php echo e($company->phone); ?>

                    <?php endif; ?>
                </div>

                
                <?php if($store): ?>
                    <div style="margin-top: 15px;">
                        <div style="font-size: 9px; font-weight: 900; color: #4b5563; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px;">
                            Dispatched From: <span style="color: #111827;"><?php echo e($store->name); ?></span>
                        </div>
                        <div class="text-gray font-medium" style="line-height: 1.3; font-size: 11px;">
                            <?php if($store->address): ?>
                                <?php echo e($store->address); ?><br>
                            <?php endif; ?>
                            <?php echo e($store->city ?? ''); ?><?php if($store->city && $store->zip_code): ?>, <?php endif; ?><?php echo e($store->zip_code ?? ''); ?><br>
                            <?php echo e($store->state->name ?? ($store->state_id ?? '')); ?>

                        </div>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    
    <div class="border-top-thick" style="margin-bottom: 30px;"></div>

    
    <table style="margin-bottom: 30px;">
        <tr>
            <td style="width: 50%; padding-right: 20px;">
                <div style="font-size: 10px; font-weight: 900; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                    <?php echo e($challan->direction === 'outward' ? 'Dispatched To / Billed To' : 'Received From'); ?>

                </div>
                <div style="font-size: 14px; font-weight: 900; margin-bottom: 4px; text-transform: uppercase;"><?php echo e($partyName); ?></div>
                
                <div class="text-gray font-medium" style="line-height: 1.5; font-size: 12px;">
                    <?php if($partyGSTIN): ?>
                        <strong style="color: #111827; text-transform: uppercase;">GSTIN: <?php echo e($partyGSTIN); ?></strong><br>
                    <?php endif; ?>
                    <?php if($partyAddress !== 'N/A'): ?>
                        <?php echo e($partyAddress); ?><br>
                    <?php endif; ?>
                    <?php if($partyState !== 'N/A'): ?>
                        State: <?php echo e($partyState); ?><br>
                    <?php endif; ?>
                    <?php if($partyPhone !== 'N/A'): ?>
                        Phone: <?php echo e($partyPhone); ?>

                    <?php endif; ?>
                </div>
            </td>
            
            <td style="width: 50%; line-height: 1.8;">
                <table style="font-size: 12px;">
                    <tr>
                        <td class="text-gray font-bold">Direction:</td>
                        <td class="font-black text-right uppercase"><?php echo e($challan->direction); ?></td>
                    </tr>
                    <tr>
                        <td class="text-gray font-bold">Status:</td>
                        <td class="font-black text-right uppercase"><?php echo e($challan->status_label); ?></td>
                    </tr>
                    <?php if($challan->transport_name): ?>
                        <tr>
                            <td class="text-gray font-bold">Transporter:</td>
                            <td class="font-bold text-right uppercase"><?php echo e($challan->transport_name); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if($challan->vehicle_number): ?>
                        <tr>
                            <td class="text-gray font-bold">Vehicle No:</td>
                            <td class="font-bold text-right uppercase"><?php echo e($challan->vehicle_number); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if($challan->eway_bill_number): ?>
                        <tr>
                            <td class="text-gray font-bold">E-Way Bill:</td>
                            <td class="font-bold text-right"><?php echo e($challan->eway_bill_number); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if($challan->is_returnable && $challan->return_due_date): ?>
                        <tr>
                            <td class="text-gray font-bold border-top-thin" style="padding-top: 5px;">Return Due:</td>
                            <td class="font-black text-right border-top-thin <?php echo e($challan->is_return_overdue ? 'text-red' : ''); ?>" style="padding-top: 5px;">
                                <?php echo e($challan->return_due_date->format('d M Y')); ?>

                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </td>
        </tr>
    </table>

    
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left">Description</th>
                <th class="text-center">HSN/SAC</th>
                <?php if(function_exists('batch_enabled') && batch_enabled()): ?>
                    <th class="text-center">Batch #</th>
                    <th class="text-center">Expiry</th>
                <?php endif; ?>
                <th class="text-right">Qty Sent</th>
                <th class="text-right text-orange">Returned</th>
                <th class="text-right text-green">Invoiced</th>
                <th class="text-right text-blue">Pending</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $challan->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="font-bold text-gray" style="color: #111827;"><?php echo e($item->product_name); ?></div>
                        <div style="font-size: 10px; font-family: monospace; color: #6b7280; margin-top: 4px;">SKU: <?php echo e($item->sku_code ?? '-'); ?></div>
                    </td>
                    <td class="text-center text-gray"><?php echo e($item->hsn_code ?? '-'); ?></td>
                    
                    <?php if(function_exists('batch_enabled') && batch_enabled()): ?>
                        <td class="text-center" style="font-family: monospace; font-size: 11px; color: #374151;"><?php echo e($item->batch_number ?? '-'); ?></td>
                        <td class="text-center" style="font-size: 11px;">
                            <?php if($item->expiry_date): ?>
                                <span class="<?php echo e($item->expiry_date->isPast() ? 'text-red font-bold' : 'text-gray'); ?>">
                                    <?php echo e($item->expiry_date->format('d M Y')); ?>

                                </span>
                            <?php else: ?>
                                <span style="color: #9ca3af;">-</span>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>

                    <td class="text-right font-bold" style="color: #111827;"><?php echo e((float) $item->qty_sent); ?></td>
                    
                    <td class="text-right font-bold <?php echo e($item->qty_returned > 0 ? 'text-orange' : 'text-light-gray'); ?>">
                        <?php echo e((float) $item->qty_returned); ?>

                    </td>
                    
                    <td class="text-right font-bold <?php echo e($item->qty_invoiced > 0 ? 'text-green' : 'text-light-gray'); ?>">
                        <?php echo e((float) $item->qty_invoiced); ?>

                    </td>
                    
                    <td class="text-right font-black <?php echo e($item->qty_pending > 0 ? 'text-blue' : 'text-light-gray'); ?>">
                        <?php echo e((float) $item->qty_pending); ?>

                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    
    <table class="page-break-avoid border-top-thin" style="padding-top: 20px; width: 100%;">
        <tr>
            
            <td style="width: 50%; padding-right: 30px;">
                <?php if($challan->purpose_note): ?>
                    <div style="margin-bottom: 15px;">
                        <div style="font-size: 9px; font-weight: 900; color: #4b5563; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">
                            Purpose of Challan
                        </div>
                        <div style="color: #374151; line-height: 1.5; font-size: 12px; font-weight: 500;">
                            <?php echo e($challan->purpose_note); ?>

                        </div>
                    </div>
                <?php endif; ?>
            </td>

            
            <td style="width: 50%; text-align: right;">
                <table class="totals-table">
                    
                    <tr class="border-top-thick">
                        <td class="text-left font-black uppercase" style="font-size: 13px; color: #111827; padding-top: 12px;">Total Quantity</td>
                        <td class="text-right font-black" style="font-size: 16px; color: #111827; padding-top: 12px;">
                            <?php echo e((float) $challan->total_qty); ?>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    
    <table style="width: 100%; margin-top: 70px; page-break-inside: avoid;">
        <tr>
            <td style="width: 50%; text-align: left;">
                <div style="border-top: 1px solid #9ca3af; display: inline-block; padding-top: 6px; width: 160px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; text-align: center; color: #111827;">
                    Receiver Sign
                </div>
            </td>
            <td style="width: 50%; text-align: right;">
                <div style="display: inline-block; text-align: center; width: 160px;">
                    <div style="border-top: 1px solid #9ca3af; padding-top: 6px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #111827;">
                        Authorized Sign
                    </div>
                    <div style="margin-top: 4px; font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; color: #9ca3af;">
                        <?php echo e(config('app.name')); ?> ERP
                    </div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/challans/pdf.blade.php ENDPATH**/ ?>