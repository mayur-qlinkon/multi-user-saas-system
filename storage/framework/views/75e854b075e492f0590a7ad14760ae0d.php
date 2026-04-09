

<?php $__env->startSection('title', 'Quotation: ' . $quotation->quotation_number); ?>

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

            /* Hide everything outside the print area */
            body * {
                visibility: hidden;
            }

            /* Make print area and its children visible */
            #print-area,
            #print-area * {
                visibility: visible;
            }

            /* Reset print area positioning */
            #print-area {
                filter: grayscale(100%) !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0;
                border: none !important;
                box-shadow: none !important;
                box-sizing: border-box !important;
            }

            /* Utility classes for print */
            .no-print {
                display: none !important;
            }

            .page-break-avoid {
                page-break-inside: avoid;
            }

            /* Force specific elements to behave on paper */
            .print-grid-2 {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 1.5rem !important;
            }

            .print-flex-row {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
            }

            .print-text-right {
                text-align: right !important;
            }

            .print-w-half {
                width: 50% !important;
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
        $company = $quotation->company ?? auth()->user()->company;
        $store = $quotation->store;

        // Indian State Code Mapping
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
        $customerName = $quotation->customer
            ? $quotation->customer->name
            : $quotation->customer_name ?? 'Guest/Prospect';
        $customerPhone = $quotation->customer ? $quotation->customer->phone : $quotation->customer_phone ?? 'N/A';
        $customerAddress = $quotation->customer ? $quotation->customer->address : 'N/A';
        $customerGSTIN = $quotation->customer ? $quotation->customer->gst_number : $quotation->customer_gstin ?? null;

        // Determine Type (If GST exists, it is B2B)
        $quoteType = !empty($customerGSTIN) ? 'B2B' : 'B2C';
        $stateCode = $stateCodes[$quotation->supply_state] ?? 'N/A';

        // Expiry Status Check
        $isExpired =
            $quotation->valid_until && \Carbon\Carbon::now()->startOfDay()->greaterThan($quotation->valid_until);
    ?>

    <div class="pb-10" x-data="quotationShow()">

        
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
            <div>
                <h1 class="text-2xl font-bold text-[#212538] tracking-tight">Quotation Details</h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="<?php echo e(route('admin.quotations.index')); ?>"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold transition-colors flex items-center shadow-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back
                </a>

                <?php if($quotation->status !== 'converted'): ?>
                    <a href="<?php echo e(route('admin.quotations.edit', $quotation->id)); ?>"
                        class="bg-white border border-gray-200 hover:bg-blue-50 hover:border-blue-200 hover:text-blue-600 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold transition-colors flex items-center shadow-sm">
                        <i data-lucide="pencil" class="w-4 h-4 mr-2"></i> Edit
                    </a>
                <?php endif; ?>

                <button onclick="window.print()"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-800 px-4 py-2 rounded-lg text-sm font-bold transition-colors flex items-center gap-2 shadow-sm">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print
                </button>

                <?php
                    $waText = urlencode(
                        "Hello {$customerName},\nHere is your Quotation {$quotation->quotation_number} for Rs. " .
                            $formatAmt($quotation->grand_total) .
                            ".\nPlease let us know if you have any questions!",
                    );
                ?>
                <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $customerPhone)); ?>?text=<?php echo e($waText); ?>"
                    target="_blank"
                    class="bg-[#25D366] hover:bg-[#1da851] text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors flex items-center gap-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.669.149-.198.297-.768.966-.941 1.164-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.058-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.148-.173.198-.297.297-.495.099-.198.05-.371-.025-.52-.074-.149-.669-1.612-.916-2.206-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.371-.273.297-1.04 1.016-1.04 2.479 0 1.463 1.064 2.876 1.213 3.074.148.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.693.626.711.226 1.358.194 1.87.118.571-.085 1.758-.718 2.007-1.411.248-.694.248-1.289.173-1.411-.074-.124-.272-.198-.57-.347z" />
                        <path
                            d="M12.004 2C6.486 2 2 6.484 2 12c0 1.991.585 3.847 1.589 5.407L2 22l4.75-1.557A9.956 9.956 0 0012.004 22C17.522 22 22 17.516 22 12S17.522 2 12.004 2z" />
                    </svg>
                    WhatsApp
                </a>

                <?php if($quotation->status !== 'converted'): ?>
                    <form action="<?php echo e(route('admin.quotations.convert', $quotation->id)); ?>" method="POST"
                        @submit.prevent="confirmConvert($event.target)" class="inline-block">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                            class="bg-gray-900 hover:bg-black text-white px-5 py-2 rounded-lg text-sm font-bold transition-colors flex items-center gap-2 shadow-sm">
                            <i data-lucide="file-check-2" class="w-4 h-4"></i> Convert to Invoice
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        
        <div id="print-area"
            class="w-full bg-white rounded-xl shadow-sm border border-gray-200 text-[#212538] font-sans print:shadow-none print:border-none print:rounded-none">

            <div class="p-6 md:p-10 print:px-8 print:py-4">

                
                <div
                    class="flex flex-col md:flex-row print-flex-row justify-between items-start border-b-2 border-gray-900 pb-6 mb-8 gap-6">
                    <div>
                        <h1 class="text-3xl font-black uppercase tracking-widest text-gray-900 mb-1">QUOTATION</h1>
                        <div class="text-[15px] font-bold text-gray-800 mb-2"># <?php echo e($quotation->quotation_number); ?></div>

                        
                        <div class="text-[13px] text-gray-700 leading-relaxed">
                            <span class="font-bold text-gray-500">Date:</span>
                            <?php echo e(\Carbon\Carbon::parse($quotation->quotation_date)->format('d M Y')); ?><br>
                            <?php if($quotation->valid_until): ?>
                                <span class="font-bold text-gray-500">Valid Until:</span>
                                <span
                                    class="<?php echo e($isExpired && $quotation->status !== 'converted' ? 'text-red-600 font-bold' : ''); ?>">
                                    <?php echo e(\Carbon\Carbon::parse($quotation->valid_until)->format('d M Y')); ?>

                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if($quotation->status === 'converted'): ?>
                            <div
                                class="inline-block border-2 border-indigo-600 text-indigo-600 text-xs font-black uppercase px-2 py-0.5 mt-3 transform -rotate-2 no-print">
                                CONVERTED
                            </div>
                        <?php elseif($isExpired): ?>
                            <div
                                class="inline-block border-2 border-red-600 text-red-600 text-xs font-black uppercase px-2 py-0.5 mt-3 transform -rotate-2 no-print">
                                EXPIRED
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="text-left md:text-right print-text-right text-sm text-gray-800">
                        <div class="text-xl font-black uppercase text-gray-900"><?php echo e($company->name); ?></div>
                        <div class="mt-1 text-[13px] text-gray-600 leading-relaxed">
                            <?php if($company->gst_number || $company->gstin): ?>
                                GSTIN: <span
                                    class="text-gray-900 font-bold uppercase"><?php echo e($company->gst_number ?? $company->gstin); ?></span><br>
                            <?php endif; ?>
                            Email: <?php echo e($company->email); ?><br>
                            Phone: <?php echo e($company->phone); ?>

                        </div>

                        <?php if($store): ?>
                            <div class="mt-4 text-[13px] leading-relaxed">
                                <div class="font-bold text-gray-900">Branch:</div>
                                <div class="text-gray-600">
                                    <?php echo e($store->name); ?><?php echo e($store->city ? ' - ' . $store->city : ''); ?><br>
                                    <?php echo e($store->state->name ?? $store->state_id); ?><?php echo e($store->zip_code ? ' - ' . $store->zip_code : ''); ?>

                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="grid grid-cols-1 md:grid-cols-2 print-grid-2 gap-8 mb-10 text-sm text-gray-800">
                    <div>
                        <div class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Quotation To</div>
                        <div class="font-bold text-base text-gray-900 mb-1"><?php echo e($customerName); ?></div>
                        <div class="text-[13px] text-gray-600 leading-relaxed">
                            <?php if($customerGSTIN): ?>
                                <span class="font-bold text-gray-900 uppercase">GSTIN: <?php echo e($customerGSTIN); ?></span><br>
                            <?php endif; ?>
                            <?php if($customerAddress !== 'N/A'): ?>
                                <?php echo e($customerAddress); ?><br>
                            <?php endif; ?>
                            <?php if($customerPhone !== 'N/A'): ?>
                                Phone: <?php echo e($customerPhone); ?><br>
                            <?php endif; ?>
                            <?php if($quotation->customer_email): ?>
                                Email: <?php echo e($quotation->customer_email); ?>

                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="md:text-right print-text-right space-y-1.5 text-[13px]">
                        <div><span class="font-bold text-gray-500">Place of Supply:</span> <span
                                class="font-bold"><?php echo e($quotation->supply_state); ?> (<?php echo e($stateCode); ?>)</span></div>
                        <div><span class="font-bold text-gray-500">Quotation Type:</span> <span
                                class="font-bold"><?php echo e($quoteType); ?></span></div>
                        <div><span class="font-bold text-gray-500">Status:</span> <span
                                class="font-bold uppercase <?php echo e($quotation->status === 'converted' ? 'text-indigo-600' : ($quotation->status === 'rejected' ? 'text-red-500' : 'text-[#108c2a]')); ?>"><?php echo e($quotation->status); ?></span>
                        </div>
                        <?php if($quotation->reference_number): ?>
                            <div><span class="font-bold text-gray-500">Reference / PO:</span> <span
                                    class="font-bold"><?php echo e($quotation->reference_number); ?></span></div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="overflow-x-auto mb-10 print:overflow-visible">
                    <table class="w-full text-sm border-collapse min-w-[700px] print:min-w-0">
                        <thead class="bg-gray-900 text-white">
                            <tr>
                                <th class="py-3 px-4 text-left font-bold rounded-tl-sm">Description</th>
                                <th class="py-3 px-4 text-center font-bold">HSN</th>
                                <th class="py-3 px-4 text-center font-bold">Qty</th>
                                <th class="py-3 px-4 text-right font-bold">Rate</th>
                                <th class="py-3 px-4 text-center font-bold">Disc</th>
                                <th class="py-3 px-4 text-center font-bold">GST</th>
                                <th class="py-3 px-4 text-right font-bold rounded-tr-sm">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="border-b border-gray-200">
                            <?php $__currentLoopData = $quotation->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-b border-gray-100 last:border-0">
                                    <td class="py-4 px-4 align-top">
                                        <div class="font-bold text-gray-900"><?php echo e($item->product_name); ?></div>
                                        <div class="text-[11px] text-gray-500 font-mono mt-0.5">SKU:
                                            <?php echo e($item->sku_code ?? ($item->sku->sku ?? 'N/A')); ?></div>
                                    </td>
                                    <td class="py-4 px-4 text-center text-gray-600 align-top"><?php echo e($item->hsn_code ?? '-'); ?>

                                    </td>
                                    <td class="py-4 px-4 text-center font-bold text-gray-800 align-top">
                                        <?php echo e((float) $item->quantity); ?></td>
                                    <td class="py-4 px-4 text-right text-gray-600 align-top">
                                        ₹<?php echo e($formatAmt($item->unit_price)); ?></td>
                                    <td class="py-4 px-4 text-center text-gray-600 align-top">
                                        <?php if($item->discount_amount > 0): ?>
                                            <?php echo e($formatAmt($item->discount_amount)); ?>

                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-4 text-center text-gray-600 align-top">
                                        <?php echo e((float) $item->tax_percent); ?>%</td>
                                    <td class="py-4 px-4 text-right text-gray-900 font-bold align-top">
                                        ₹<?php echo e($formatAmt($item->total_amount)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="flex flex-col md:flex-row print-flex-row justify-end mb-12">
                    <div class="w-full md:w-80 md:ml-auto print:w-[300px] print:ml-auto">
                        <table class="w-full text-sm text-gray-800">
                            <tbody>
                                <tr>
                                    <td class="py-2 text-gray-600 font-medium">Subtotal</td>
                                    <td class="py-2 text-right font-bold">₹<?php echo e($formatAmt($quotation->subtotal)); ?></td>
                                </tr>

                                <?php if($quotation->igst_amount > 0): ?>
                                    <tr>
                                        <td class="py-2 text-gray-600 font-medium">IGST</td>
                                        <td class="py-2 text-right font-bold">₹<?php echo e($formatAmt($quotation->igst_amount)); ?>

                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php if($quotation->cgst_amount > 0 || $quotation->sgst_amount > 0): ?>
                                        <tr>
                                            <td class="py-2 text-gray-600 font-medium">CGST</td>
                                            <td class="py-2 text-right font-bold">
                                                ₹<?php echo e($formatAmt($quotation->cgst_amount)); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 text-gray-600 font-medium">SGST</td>
                                            <td class="py-2 text-right font-bold">
                                                ₹<?php echo e($formatAmt($quotation->sgst_amount)); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if($quotation->shipping_charge > 0): ?>
                                    <tr>
                                        <td class="py-2 text-gray-600 font-medium">Shipping / Other</td>
                                        <td class="py-2 text-right font-bold">
                                            ₹<?php echo e($formatAmt($quotation->shipping_charge)); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <?php if($quotation->discount_amount > 0): ?>
                                    <tr>
                                        <td class="py-2 text-gray-600 font-medium">Discount</td>
                                        <td class="py-2 text-right font-bold text-red-600">(-)
                                            ₹<?php echo e($formatAmt($quotation->discount_amount)); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <tr class="border-t-2 border-gray-900">
                                    <td class="py-3 text-base font-black uppercase text-gray-900">Grand Total</td>
                                    <td class="py-3 text-right text-[17px] font-black text-[#108c2a]">
                                        ₹<?php echo e($formatAmt($quotation->grand_total)); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div class="flex flex-col md:flex-row print-flex-row justify-between items-end gap-8 page-break-avoid">
                    <div class="w-full md:w-2/3 print-w-half text-sm text-gray-800">
                        <?php if($quotation->notes): ?>
                            <div class="mb-5">
                                <h4 class="font-black text-gray-900 mb-1">Notes:</h4>
                                <p class="text-gray-600 leading-relaxed"><?php echo e($quotation->notes); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if($quotation->terms_conditions): ?>
                            <div>
                                <h4 class="font-black text-gray-900 mb-1">Terms & Conditions:</h4>
                                <div class="text-gray-600 leading-relaxed text-[13px]">
                                    <?php echo nl2br(e($quotation->terms_conditions)); ?>

                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="w-full md:w-1/3 print-w-half text-right pt-16">
                        <div
                            class="border-t border-gray-300 inline-block pt-2 text-[13px] font-bold text-gray-500 uppercase tracking-widest min-w-[200px] text-center">
                            Authorized Signatory
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('quotationShow', () => ({
                confirmConvert(form) {
                    BizAlert.confirm(
                        'Convert to Invoice?',
                        'This will generate a Draft Invoice with the exact details of this quotation. The quotation will be locked.',
                        'Yes, Convert it',
                        '#108c2a'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            BizAlert.loading('Converting to Invoice...');
                            form.submit();
                        }
                    });
                }
            }));
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/quotations/show.blade.php ENDPATH**/ ?>