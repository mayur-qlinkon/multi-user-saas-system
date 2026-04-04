

<?php $__env->startSection('title', 'Invoice: ' . $invoice->invoice_number); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Sales / Invoices</h1>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        // Clean currency formatter
        $formatAmt = function ($amount) {
            return number_format((float) $amount, 2, '.', ',');
        };

        // Company Details
        $company = $invoice->company ?? auth()->user()->company;
        $store = $invoice->store;

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
    ?>

    <div class="pb-10">
        
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
            <div>
                <h1 class="text-xl font-bold text-[#212538] tracking-tight">Invoice Details</h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="<?php echo e(route('admin.invoices.index')); ?>"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 px-4 py-1.5 rounded text-sm transition-colors flex items-center shadow-sm font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i> Back
                </a>

                <?php if($invoice->status !== 'cancelled'): ?>
                    <a href="<?php echo e(route('admin.invoices.edit', $invoice->id)); ?>"
                        class="bg-white border border-gray-200 hover:bg-blue-50 hover:text-blue-600 text-gray-600 px-4 py-1.5 rounded text-sm transition-colors flex items-center shadow-sm font-medium">
                        <i data-lucide="pencil" class="w-4 h-4 mr-1.5"></i> Edit
                    </a>
                <?php endif; ?>

                <button onclick="window.print()"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-800 px-4 py-1.5 rounded text-sm transition-colors flex items-center gap-1.5 shadow-sm font-bold">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print Invoice
                </button>

                <a href="<?php echo e(route('admin.invoices.pdf', $invoice->id)); ?>" target="_blank"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded text-sm transition-colors flex items-center gap-1.5 shadow-sm font-bold">
                    <i data-lucide="download" class="w-4 h-4"></i> Download PDF
                </a>

                <?php
                    $waText = urlencode(
                        "Hello {$customerName},\nHere is your Invoice {$invoice->invoice_number} for Rs. " .
                            $formatAmt($invoice->grand_total) .
                            ".\nThank you for your business!",
                    );
                ?>
                <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $customerPhone)); ?>?text=<?php echo e($waText); ?>"
                    target="_blank"
                    class="bg-[#25D366] hover:bg-[#1da851] text-white px-4 py-1.5 rounded text-sm transition-colors flex items-center gap-1.5 shadow-sm font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.669.149-.198.297-.768.966-.941 1.164-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.058-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.148-.173.198-.297.297-.495.099-.198.05-.371-.025-.52-.074-.149-.669-1.612-.916-2.206-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.371-.273.297-1.04 1.016-1.04 2.479 0 1.463 1.064 2.876 1.213 3.074.148.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.693.626.711.226 1.358.194 1.87.118.571-.085 1.758-.718 2.007-1.411.248-.694.248-1.289.173-1.411-.074-.124-.272-.198-.57-.347z" />
                        <path
                            d="M12.004 2C6.486 2 2 6.484 2 12c0 1.991.585 3.847 1.589 5.407L2 22l4.75-1.557A9.956 9.956 0 0012.004 22C17.522 22 22 17.516 22 12S17.522 2 12.004 2z" />
                    </svg>
                    WhatsApp
                </a>
            </div>
        </div>
        <?php if($invoice->returns->count() > 0): ?>
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
                        <?php $__currentLoopData = $invoice->returns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ret): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('admin.invoice-returns.show', $ret->id)); ?>"
                                class="bg-white border border-red-200 text-red-700 px-3 py-1.5 rounded-lg text-xs font-black hover:bg-red-50 transition-colors shadow-sm">
                                VIEW <?php echo e($ret->credit_note_number); ?> (₹<?php echo e(number_format($ret->grand_total, 2)); ?>)
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div id="print-area"
            class="w-full bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden text-gray-800 print:grayscale print:shadow-none print:border-none">

            
            <div class="p-8 border-b-2 border-gray-800 grid grid-cols-2 gap-6 items-start">
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-widest text-gray-900 mb-1">Tax Invoice</h1>
                    <div class="text-sm text-gray-600 font-bold mb-4"># <?php echo e($invoice->invoice_number); ?></div>

                    <?php if($invoice->status === 'cancelled'): ?>
                        <div
                            class="inline-block border-2 border-red-600 text-red-600 text-lg font-black uppercase px-3 py-1 mb-2 transform -rotate-6">
                            CANCELLED
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-right text-sm flex flex-col items-end">
                    
                    <h2 class="text-xl font-black text-gray-900 uppercase leading-none"><?php echo e($company->name); ?></h2>
                    <div class="text-gray-600 text-[12px] mt-1">
                        <?php if($company->gst_number || $company->gstin): ?>
                            GSTIN: <span
                                class="font-bold text-gray-900 uppercase"><?php echo e($company->gst_number ?? $company->gstin); ?></span><br>
                        <?php endif; ?>
                        Email: <?php echo e($company->email); ?><br>
                        Phone: <?php echo e($company->phone); ?>

                    </div>

                    
                    <?php if($store): ?>
                        <div class="text-right mt-4">
                            <h3 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Branch: <span
                                    class="font-bold text-black"><?php echo e($store->name); ?></span></h3>
                            <div class="text-gray-800 text-[13px] leading-tight">
                                <?php if($store->address): ?>
                                    <?php echo e($store->address); ?><br>
                                <?php endif; ?>
                                <?php echo e($store->city); ?><?php echo e($store->city && $store->zip_code ? ', ' : ''); ?><?php echo e($store->zip_code); ?><br>
                                <?php echo e($store->state->name ?? $store->state_id); ?>

                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="p-8 grid grid-cols-2 gap-8 border-b border-gray-200">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Billed To</h3>
                        <div class="text-sm text-gray-800">
                            <div class="font-bold text-base mb-0.5"><?php echo e($customerName); ?></div>
                            
                            <?php if($customerGSTIN): ?>
                                <div class="font-bold text-gray-900 uppercase">GSTIN: <?php echo e($customerGSTIN); ?></div>
                            <?php endif; ?>
                            <?php if($customerAddress !== 'N/A'): ?>
                                <div class="text-gray-600 leading-tight"><?php echo e($customerAddress); ?></div>
                            <?php endif; ?>
                            <?php if($customerPhone !== 'N/A'): ?>
                                <div class="text-gray-600">Phone: <?php echo e($customerPhone); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="grid grid-cols-2 text-[13px]">
                        <span class="font-bold text-gray-500">Invoice Date:</span>
                        <span
                            class="text-right font-semibold"><?php echo e(\Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y')); ?></span>
                    </div>
                    <?php if($invoice->due_date): ?>
                        <div class="grid grid-cols-2 text-[13px]">
                            <span class="font-bold text-gray-500">Due Date:</span>
                            <span
                                class="text-right font-semibold"><?php echo e(\Carbon\Carbon::parse($invoice->due_date)->format('d M Y')); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="grid grid-cols-2 text-[13px]">
                        <span class="font-bold text-gray-500">Place of Supply:</span>
                        <span class="text-right font-bold text-gray-900"><?php echo e($invoice->supply_state); ?>

                            (<?php echo e($stateCode); ?>)</span>
                    </div>
                    <div class="grid grid-cols-2 text-[13px]">
                        <span class="font-bold text-gray-500">Invoice Type:</span>
                        <span class="text-right font-bold text-gray-900 uppercase"><?php echo e($invoiceType); ?></span>
                    </div>
                    <div class="grid grid-cols-2 text-[13px] pt-1 border-t border-gray-100">
                        <span class="font-bold text-gray-500">Payment Status:</span>
                        <span
                            class="text-right font-black uppercase <?php echo e($invoice->payment_status === 'paid' ? 'text-green-600' : 'text-red-500'); ?>">
                            <?php echo e($invoice->payment_status); ?>

                        </span>
                    </div>
                    <div class="grid grid-cols-2 text-[13px]">
                        <span class="font-bold text-gray-500">Reverse Charge:</span>
                        <span class="text-right font-medium">No</span>
                    </div>
                </div>
            </div>

            
            <div class="px-8 py-6">
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
                        <tbody class="divide-y divide-gray-200">
                            <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="py-4 px-2">
                                        <div class="font-bold text-gray-900"><?php echo e($item->product_name); ?></div>
                                        <div class="text-[11px] text-gray-500 font-mono mt-0.5">SKU:
                                            <?php echo e($item->sku->sku_code ?? ($item->sku->sku ?? 'N/A')); ?></div>
                                    </td>
                                    <td class="py-4 px-2 text-center text-gray-600"><?php echo e($item->hsn_code ?? '-'); ?></td>
                                    <td class="py-4 px-2 text-center font-semibold text-gray-800">
                                        <?php echo e((float) $item->quantity); ?></td>
                                    <td class="py-4 px-2 text-right text-gray-600"><?php echo e($formatAmt($item->unit_price)); ?></td>
                                    <td class="py-4 px-2 text-right text-gray-600">
                                        <?php if($item->discount_amount > 0): ?>
                                            <?php echo e($formatAmt($item->discount_amount)); ?>

                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-2 text-right text-gray-600">
                                        <?php echo e($formatAmt($item->tax_amount)); ?><br>
                                        <span class="text-[10px] text-gray-400">(<?php echo e((float) $item->tax_percent); ?>%)</span>
                                    </td>
                                    <td class="py-4 px-2 text-right font-bold text-gray-900">
                                        <?php echo e($formatAmt($item->total_amount)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div
                class="px-8 pb-8 page-break-avoid flex flex-col md:flex-row print:flex-row justify-between items-end gap-8 print:gap-4">

                
                
                <div class="w-full md:w-1/2 print:w-1/2 mb-6 md:mb-0 print:mb-0 space-y-6">
                    <?php if($invoice->notes): ?>
                        <p class="text-[13px] text-gray-600"><strong class="text-gray-700">Note:</strong><br>
                            <?php echo e($invoice->notes); ?></p>
                    <?php endif; ?>

                    <div class="flex flex-wrap items-start gap-8 pt-4 border-t border-gray-100">
                        
                        <div>
                            <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Bank Details
                            </h4>
                            <div class="text-[13px] text-gray-700 leading-relaxed">
                                <strong>Bank:</strong> HDFC Bank<br>
                                <strong>A/C Name:</strong> <?php echo e($company->name); ?><br>
                                <strong>A/C No:</strong> 50200012345678<br>
                                <strong>IFSC:</strong> HDFC0001234
                            </div>
                        </div>

                        
                        <?php if($balanceDue > 0): ?>
                            <div>
                                <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Pay via UPI
                                </h4>
                                <div class="p-2 border border-gray-200 rounded inline-block bg-white">
                                    <?php
                                        // Format: upi://pay?pa=UPI_ID&pn=NAME&am=AMOUNT&cu=INR
                                        $upiId = 'dev@okicici'; // Replace with actual UPI ID
                                        $merchantName = urlencode($company->name);
                                        $upiString = "upi://pay?pa={$upiId}&pn={$merchantName}&am={$balanceDue}&cu=INR";
                                    ?>
                                    <?php echo \SimpleSoftwareIO\QrCode\Facades\QrCode::size(90)->generate($upiString); ?>

                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="w-full md:w-[350px] flex flex-col items-end">

                    <div class="w-full mb-8">
                        <table class="w-full text-[13px]">
                            <tbody>
                                <tr>
                                    <td class="py-1 text-gray-600 font-semibold">Taxable Amount</td>
                                    <td class="py-1 text-right text-gray-900 font-bold">
                                        ₹<?php echo e($formatAmt($invoice->taxable_amount)); ?></td>
                                </tr>

                                <?php if($invoice->igst_amount > 0): ?>
                                    <tr>
                                        <td class="py-1 text-gray-600 font-semibold">IGST</td>
                                        <td class="py-1 text-right text-gray-900 font-bold">
                                            ₹<?php echo e($formatAmt($invoice->igst_amount)); ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td class="py-1 text-gray-600 font-semibold">CGST (2.5%)</td>
                                        <td class="py-1 text-right text-gray-900 font-bold">
                                            ₹<?php echo e($formatAmt($invoice->cgst_amount)); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 text-gray-600 font-semibold">SGST (2.5%)</td>
                                        <td class="py-1 text-right text-gray-900 font-bold">
                                            ₹<?php echo e($formatAmt($invoice->sgst_amount)); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <?php if($invoice->discount_amount > 0): ?>
                                    <tr>
                                        <td class="py-1 text-gray-600 font-semibold">Discount</td>
                                        <td class="py-1 text-right text-red-600 font-bold">(-)
                                            ₹<?php echo e($formatAmt($invoice->discount_amount)); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <tr class="border-t-2 border-gray-900">
                                    <td class="py-2 text-[15px] font-black text-gray-900 uppercase">Grand Total</td>
                                    <td class="py-2 text-right text-[16px] font-black text-gray-900">
                                        ₹<?php echo e($formatAmt($invoice->grand_total)); ?></td>
                                </tr>

                                
                                <?php if($totalReceived > 0): ?>
                                    <tr class="text-gray-500">
                                        <td class="pt-2 pb-1 font-bold">Amount Received</td>
                                        <td class="pt-2 pb-1 text-right font-bold">₹<?php echo e($formatAmt($totalReceived)); ?></td>
                                    </tr>
                                    <?php if($totalChange > 0): ?>
                                        <tr class="text-gray-500">
                                            <td class="py-1 font-bold">Change Returned</td>
                                            <td class="py-1 text-right font-bold">₹<?php echo e($formatAmt($totalChange)); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="bg-gray-900 text-white">
                                        <td class="py-1.5 px-2 font-black uppercase text-[11px]">Paid Against Bill</td>
                                        <td class="py-1.5 px-2 text-right font-black text-[13px]">
                                            ₹<?php echo e($formatAmt($paidAmt)); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    
                    <div class="w-full text-right pr-2 mt-16">
                        <img src="<?php echo e(asset('assets/images/signature.png')); ?>" alt="Signature"
                            class="h-14 ml-auto mb-1 opacity-80" onerror="this.style.display='none'">
                        <div
                            class="text-[11px] font-bold text-gray-800 uppercase tracking-wider border-t border-gray-400 pt-1 inline-block min-w-[150px] text-center">
                            Authorized Signatory
                        </div>
                    </div>

                </div>
            </div>

            
            <?php if($invoice->terms_conditions): ?>
                <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 page-break-avoid">
                    <strong class="text-gray-700 uppercase tracking-widest">Terms & Conditions:</strong><br>
                    <div class="mt-2 leading-relaxed">
                        <?php echo nl2br(e($invoice->terms_conditions)); ?>

                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/invoices/show.blade.php ENDPATH**/ ?>