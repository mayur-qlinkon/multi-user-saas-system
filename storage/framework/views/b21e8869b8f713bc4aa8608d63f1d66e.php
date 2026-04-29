<?php $__env->startSection('title', 'Purchase Return: ' . $purchaseReturn->return_number); ?>
<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Purchase Return Details</h1>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('styles'); ?>
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

            .printState {
                color: rgb(31 41 55 / var(--tw-text-opacity, 1)) !important;
                background: none !important;
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

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .page-break-avoid {
                page-break-inside: avoid;
            }

            .no-print {
                display: none !important;
            }

            .print-grid-3 {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 1rem !important;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $formatAmt = function ($amount) {
            $amount = (float) $amount;
            if ($amount == 0) {
                return '0';
            }
            return rtrim(rtrim(number_format($amount, 4, '.', ','), '0'), '.');
        };
    ?>

    <div class="pb-10">

        <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
            <div>
                
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="<?php echo e(route('admin.purchase-returns.index')); ?>"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 px-4 py-1.5 rounded text-sm transition-colors flex items-center shadow-sm">
                    Back
                </a>

                <?php if($purchaseReturn->status !== 'returned' && $purchaseReturn->status !== 'cancelled' && has_permission('purchase_returns.update')): ?>
                    <a href="<?php echo e(route('admin.purchase-returns.edit', $purchaseReturn->id)); ?>"
                        class="bg-white border border-gray-200 hover:bg-blue-50 hover:text-blue-600 text-gray-600 px-4 py-1.5 rounded text-sm transition-colors flex items-center shadow-sm">
                        Edit
                    </a>
                <?php endif; ?>

                <button onclick="window.print()"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 px-4 py-1.5 rounded text-sm transition-colors flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print
                </button>

                <?php if(has_permission('purchase_returns.download_pdf')): ?>
                    <a href="<?php echo e(route('admin.purchase-returns.pdf', $purchaseReturn->id)); ?>"
                        class="bg-white border border-gray-200 hover:bg-red-50 hover:text-red-600 text-gray-600 px-4 py-1.5 rounded text-sm transition-colors flex items-center gap-1.5 shadow-sm"
                        title="Download PDF">
                        <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                    </a>
                <?php endif; ?>

                <?php
                    $waText = urlencode(
                        "Purchase Return {$purchaseReturn->return_number} Details. Expected Refund: Rs. " .
                            number_format($purchaseReturn->total_amount, 2),
                    );
                ?>
                <a href="https://wa.me/?text=<?php echo e($waText); ?>" target="_blank"
                    class="bg-white border border-gray-200 hover:bg-[#e8fbf0] hover:text-[#1da851] text-gray-600 px-4 py-1.5 rounded text-sm transition-colors flex items-center gap-1.5 shadow-sm">

                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.669.149-.198.297-.768.966-.941 1.164-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.058-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.148-.173.198-.297.297-.495.099-.198.05-.371-.025-.52-.074-.149-.669-1.612-.916-2.206-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.371-.273.297-1.04 1.016-1.04 2.479 0 1.463 1.064 2.876 1.213 3.074.148.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.693.626.711.226 1.358.194 1.87.118.571-.085 1.758-.718 2.007-1.411.248-.694.248-1.289.173-1.411-.074-.124-.272-.198-.57-.347z" />
                        <path
                            d="M12.004 2C6.486 2 2 6.484 2 12c0 1.991.585 3.847 1.589 5.407L2 22l4.75-1.557A9.956 9.956 0 0012.004 22C17.522 22 22 17.516 22 12S17.522 2 12.004 2z" />
                    </svg>

                    Share
                </a>
            </div>
        </div>

        <div id="print-area" class="bg-white rounded shadow-sm border border-gray-200 overflow-hidden text-[#475569]">

            <div class="text-center py-6 border-b border-gray-100">
                <h2 class="text-[15px] font-bold text-gray-800">Debit Note / Purchase Return</h2>
                <p class="text-[12px] text-gray-500 mt-1">No: <?php echo e($purchaseReturn->return_number); ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 print-grid-3 gap-6 p-6 print:p-2">

                <div>
                    <div class="bg-[#f1f5f9] px-3 py-2 text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-3">
                        Supplier Info</div>
                    <div class="px-2 space-y-2 text-[13px]">
                        <div class="flex items-start gap-2">
                            <i data-lucide="user" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <span class="text-gray-800 font-bold"><?php echo e($purchaseReturn->supplier->name ?? 'N/A'); ?></span>
                        </div>
                        <?php if($purchaseReturn->supplier->email): ?>
                            <div class="flex items-start gap-2">
                                <i data-lucide="mail" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                <span><?php echo e($purchaseReturn->supplier->email); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if($purchaseReturn->supplier->phone): ?>
                            <div class="flex items-start gap-2">
                                <i data-lucide="phone" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                <span><?php echo e($purchaseReturn->supplier->phone); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <div class="bg-[#f1f5f9] px-3 py-2 text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-3">
                        Company Info</div>
                    <div class="px-2 space-y-2 text-[13px]">
                        <?php $companyInfo = $purchaseReturn->store ?? auth()->user()->company; ?>
                        <div class="flex items-start gap-2">
                            <i data-lucide="building" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <span class="text-gray-800 font-bold"><?php echo e($companyInfo->name ?? 'N/A'); ?></span>
                        </div>
                        <?php if($companyInfo->email ?? false): ?>
                            <div class="flex items-start gap-2">
                                <i data-lucide="mail" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                <span><?php echo e($companyInfo->email); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if($companyInfo->phone ?? false): ?>
                            <div class="flex items-start gap-2">
                                <i data-lucide="phone" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                <span><?php echo e($companyInfo->phone); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <div class="bg-[#f1f5f9] px-3 py-2 text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-3">
                        Return Info</div>
                    <div class="px-2 space-y-2 text-[13px]">
                        <div class="grid grid-cols-[100px_1fr] items-center">
                            <span class="text-gray-500">Orig. PO:</span>
                            <a href="<?php echo e(route('admin.purchases.show', $purchaseReturn->purchase_id)); ?>"
                                class="text-[#108c2a] font-bold no-print hover:underline"><?php echo e($purchaseReturn->purchase->purchase_number); ?></a>
                            <span
                                class="text-gray-800 font-bold hidden print:inline"><?php echo e($purchaseReturn->purchase->purchase_number); ?></span>
                        </div>
                        <div class="grid grid-cols-[100px_1fr] items-center">
                            <span class="text-gray-500">Date:</span>
                            <span class="text-gray-800"><?php echo e($purchaseReturn->return_date->format('d M Y')); ?></span>
                        </div>
                        <div class="grid grid-cols-[100px_1fr] items-center">
                            <span class="text-gray-500">Warehouse:</span>
                            <span class="text-gray-800"><?php echo e($purchaseReturn->warehouse->name ?? 'N/A'); ?></span>
                        </div>
                        <div class="grid grid-cols-[100px_1fr] items-center">
                            <span class="text-gray-500">Status:</span>
                            <div>
                                <?php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-600',
                                        'returned' => 'bg-[#dcfce7] text-[#16a34a]', // Green
                                        'cancelled' => 'bg-red-100 text-red-600',
                                    ];
                                    $sColor = $statusColors[$purchaseReturn->status] ?? $statusColors['draft'];
                                ?>
                                <span class="printState px-2 py-0.5 rounded text-[11px] font-medium <?php echo e($sColor); ?>">
                                    <?php echo e(ucfirst($purchaseReturn->status)); ?>

                                </span>
                            </div>
                        </div>
                        <div class="grid grid-cols-[100px_1fr] items-center">
                            <span class="text-gray-500">Refund:</span>
                            <div>
                                <?php
                                    $payColors = [
                                        'pending' => 'bg-orange-100 text-orange-700',
                                        'adjusted' => 'bg-blue-100 text-blue-700',
                                        'refunded' => 'bg-[#dcfce7] text-[#16a34a]',
                                    ];
                                    $pColor = $payColors[$purchaseReturn->payment_status] ?? $payColors['pending'];
                                ?>
                                <span class="printState px-2 py-0.5 rounded text-[11px] font-medium <?php echo e($pColor); ?>">
                                    <?php echo e(ucfirst($purchaseReturn->payment_status)); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="px-6 pb-2 print:px-2">
                <div class="bg-[#f1f5f9] px-3 py-2 text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-4">
                    Returned Items</div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px] whitespace-nowrap">
                        <thead>
                            <tr class="text-[11px] text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                <th class="pb-3 px-2 font-medium">Product</th>
                                <th class="pb-3 px-2 font-medium">Reason</th>
                                <th class="pb-3 px-2 font-medium text-center">Unit Cost</th>
                                <th class="pb-3 px-2 font-medium text-center">Rtn Qty</th>
                                <th class="pb-3 px-2 font-medium text-center">Tax</th>
                                <th class="pb-3 px-2 font-medium text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php $__currentLoopData = $purchaseReturn->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="py-3 px-2 text-gray-800">
                                        <div class="font-bold"><?php echo e($item->product->name ?? 'Unknown'); ?></div>
                                        <div class="text-[11px] text-gray-500 font-mono">
                                            <?php echo e($item->productSku->sku ?? 'N/A'); ?></div>
                                    </td>
                                    <td class="py-3 px-2 text-gray-600">
                                        <span
                                            class="px-2 py-0.5 rounded bg-gray-50 text-gray-500 text-[10px] uppercase tracking-wider border border-gray-200">
                                            <?php echo e(str_replace('_', ' ', $item->return_reason)); ?>

                                        </span>
                                    </td>
                                    <td class="py-3 px-2 text-center text-gray-600">₹ <?php echo e($formatAmt($item->unit_cost)); ?>

                                    </td>
                                    <td class="py-3 px-2 text-center font-bold text-red-500 printState">
                                        -<?php echo e($formatAmt($item->quantity)); ?></td>
                                    <td class="py-3 px-2 text-center text-gray-600">₹ <?php echo e($formatAmt($item->tax_amount)); ?>

                                    </td>
                                    <td class="py-3 px-2 text-right font-bold text-gray-800">₹
                                        <?php echo e($formatAmt($item->total_price)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-6 print:py-2 flex justify-end page-break-avoid border-t border-gray-50">
                <div class="w-full md:w-[380px] border border-gray-200 rounded p-4">
                    <table class="w-full text-[13px] text-gray-600">
                        <tbody>
                            <tr>
                                <td class="py-2 border-b border-gray-100">Taxable Value Return</td>
                                <td class="py-2 text-right border-b border-gray-100 font-medium text-gray-800">₹
                                    <?php echo e($formatAmt($purchaseReturn->taxable_amount)); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 border-b border-gray-100">Total Tax Reversal</td>
                                <td class="py-2 text-right border-b border-gray-100 font-medium text-gray-800">₹
                                    <?php echo e($formatAmt($purchaseReturn->tax_amount)); ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-[#108c2a] font-bold printState uppercase tracking-wider text-[11px]">
                                    Total Refund Expected</td>
                                <td class="py-3 text-right text-[#108c2a] font-bold printState text-[15px]">₹
                                    <?php echo e($formatAmt($purchaseReturn->total_amount)); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if($purchaseReturn->reason || $purchaseReturn->supplier_credit_note_number || $purchaseReturn->notes): ?>
                <div class="px-6 pb-8 print:pb-2 print:pt-2 text-[13px] text-gray-500 page-break-avoid">

                    <?php if($purchaseReturn->supplier_credit_note_number): ?>
                        <div class="mb-3 bg-blue-50/50 p-3 rounded border border-blue-100">
                            <span class="font-bold text-blue-800 text-[11px] uppercase tracking-wider block mb-1">Supplier
                                Credit Note Reference</span>
                            <span
                                class="text-blue-900 font-mono"><?php echo e($purchaseReturn->supplier_credit_note_number); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if($purchaseReturn->reason): ?>
                        <p class="mb-2"><strong class="text-gray-700">Reason for Return:</strong>
                            <?php echo e($purchaseReturn->reason); ?></p>
                    <?php endif; ?>

                    <?php if($purchaseReturn->notes): ?>
                        <p><strong class="text-gray-700">Additional Notes:</strong> <?php echo e($purchaseReturn->notes); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/purchase-returns/show.blade.php ENDPATH**/ ?>