

<?php $__env->startSection('title', 'Challan: ' . $challan->challan_number); ?>

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
        // Resolve Company and Store
        $company = $challan->company ?? auth()->user()->company;
        $store = $challan->store;

        // Party Details (Using the Snapshotted Data)
        $partyName = $challan->party_name ?? 'Unknown Party';
        $partyPhone = $challan->party_phone ?? 'N/A';
        $partyAddress = $challan->party_address ?? 'N/A';
        $partyGSTIN = $challan->party_gst ?? null;
        $partyState = $challan->toState->name ?? $challan->party_state ?? 'N/A';

        // Badge Color Mapping
        $colorMap = [
            'gray'   => 'bg-gray-100 text-gray-700 border-gray-200',
            'blue'   => 'bg-blue-50 text-blue-700 border-blue-200',
            'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'cyan'   => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            'amber'  => 'bg-amber-50 text-amber-700 border-amber-200',
            'teal'   => 'bg-teal-50 text-teal-700 border-teal-200',
            'green'  => 'bg-green-50 text-green-700 border-green-200',
            'lime'   => 'bg-lime-50 text-lime-700 border-lime-200',
            'slate'  => 'bg-slate-100 text-slate-700 border-slate-300',
            'red'    => 'bg-red-50 text-red-700 border-red-200',
        ];
        $statusColorClass = $colorMap[$challan->status_color] ?? $colorMap['gray'];
    ?>

    <div class="pb-10">
        
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
            <div>
                <h1 class="text-xl font-bold text-[#212538] tracking-tight">Challan Details</h1>
            </div>

            
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 w-full md:w-auto">
                <a href="<?php echo e(route('admin.challans.index')); ?>"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 px-4 py-1.5 rounded text-sm transition-colors flex items-center shadow-sm font-medium">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i> Back
                </a>

                <?php if($challan->status !== 'cancelled'): ?>
                    <a href="<?php echo e(route('admin.challans.edit', $challan->id)); ?>"
                        class="bg-white border border-gray-200 hover:bg-blue-50 hover:text-blue-600 text-gray-600 px-4 py-1.5 rounded text-sm transition-colors flex items-center shadow-sm font-medium">
                        <i data-lucide="pencil" class="w-4 h-4 mr-1.5"></i> Edit
                    </a>
                <?php endif; ?>

                <button onclick="window.print()"
                    class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-800 px-4 py-1.5 rounded text-sm transition-colors flex items-center gap-1.5 shadow-sm font-bold">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print Challan
                </button>

                <a href="<?php echo e(route('admin.challans.pdf', $challan->id)); ?>" target="_blank"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded text-sm transition-colors flex items-center gap-1.5 shadow-sm font-bold">
                    <i data-lucide="download" class="w-4 h-4"></i> Download PDF
                </a>

                <?php
                    $waText = urlencode(
                        "Hello {$partyName},\nHere is your {$challan->type_label} Document #{$challan->challan_number} dated " .
                        $challan->challan_date->format('d M Y') .
                        ".\nThank you for your business!",
                    );
                    // Clean phone number for WA link
                    $waPhone = preg_replace('/[^0-9]/', '', $partyPhone);
                ?>
                <?php if(strlen($waPhone) >= 10): ?>
                    <a href="https://wa.me/<?php echo e($waPhone); ?>?text=<?php echo e($waText); ?>" target="_blank"
                        class="bg-[#25D366] hover:bg-[#1da851] text-white px-4 py-1.5 rounded text-sm transition-colors flex items-center gap-1.5 col-span-2 sm:col-span-1 justify-center shadow-sm font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.669.149-.198.297-.768.966-.941 1.164-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.058-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.148-.173.198-.297.297-.495.099-.198.05-.371-.025-.52-.074-.149-.669-1.612-.916-2.206-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.371-.273.297-1.04 1.016-1.04 2.479 0 1.463 1.064 2.876 1.213 3.074.148.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.693.626.711.226 1.358.194 1.87.118.571-.085 1.758-.718 2.007-1.411.248-.694.248-1.289.173-1.411-.074-.124-.272-.198-.57-.347z" />
                            <path d="M12.004 2C6.486 2 2 6.484 2 12c0 1.991.585 3.847 1.589 5.407L2 22l4.75-1.557A9.956 9.956 0 0012.004 22C17.522 22 22 17.516 22 12S17.522 2 12.004 2z" />
                        </svg>
                        WhatsApp
                    </a>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($challan->is_returnable && $challan->is_return_overdue): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 no-print flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-red-600 p-2 rounded-lg text-white">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-red-800">Return Overdue</h4>
                        <p class="text-xs text-red-600 font-medium">This challan was due for return on <?php echo e($challan->return_due_date->format('d M, Y')); ?>.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div id="print-area" class="w-full bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden text-gray-800 print:grayscale print:shadow-none print:border-none">

            
            
            <div class="p-5 sm:p-8 border-b-2 border-gray-800 grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-widest text-gray-900 mb-1"><?php echo e($challan->type_label); ?></h1>
                    <div class="text-sm text-gray-600 font-bold mb-3"># <?php echo e($challan->challan_number); ?></div>

                    
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-block border text-[11px] font-black uppercase px-2.5 py-1 rounded <?php echo e($statusColorClass); ?>">
                            <?php echo e($challan->status_label); ?>

                        </span>
                        
                        <span class="inline-block border <?php echo e($challan->direction === 'outward' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-purple-200 bg-purple-50 text-purple-700'); ?> text-[11px] font-black uppercase px-2.5 py-1 rounded">
                            <i data-lucide="<?php echo e($challan->direction === 'outward' ? 'arrow-up-right' : 'arrow-down-left'); ?>" class="w-3 h-3 inline pb-0.5"></i> 
                            <?php echo e($challan->direction); ?>

                        </span>
                    </div>

                    <?php if($challan->status === 'cancelled'): ?>
                        <div class="inline-block border-2 border-red-600 text-red-600 text-lg font-black uppercase px-3 py-1 mt-3 transform -rotate-6">
                            CANCELLED
                        </div>
                    <?php endif; ?>
                </div>

                
                <div class="text-left md:text-right text-sm flex flex-col items-start md:items-end">
                    
                    <h2 class="text-xl font-black text-gray-900 uppercase leading-none"><?php echo e($company->name); ?></h2>
                    <div class="text-gray-600 text-[12px] mt-1">
                        <?php if($company->gst_number || $company->gstin): ?>
                            GSTIN: <span class="font-bold text-gray-900 uppercase"><?php echo e($company->gst_number ?? $company->gstin); ?></span><br>
                        <?php endif; ?>
                        Email: <?php echo e($company->email); ?><br>
                        Phone: <?php echo e($company->phone); ?>

                    </div>

                    
                    <?php if($store): ?>
                        <div class="text-right mt-4">
                            <h3 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Dispatched From: <span class="font-bold text-black"><?php echo e($store->name); ?></span></h3>
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

                        
            <div class="p-5 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-8 border-b border-gray-200">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">
                            <?php echo e($challan->direction === 'outward' ? 'Dispatched To / Billed To' : 'Received From'); ?>

                        </h3>
                        <div class="text-sm text-gray-800">
                            <div class="font-bold text-base mb-0.5"><?php echo e($partyName); ?></div>
                            
                            <?php if($partyGSTIN): ?>
                                <div class="font-bold text-gray-900 uppercase">GSTIN: <?php echo e($partyGSTIN); ?></div>
                            <?php endif; ?>
                            <?php if($partyAddress !== 'N/A'): ?>
                                <div class="text-gray-600 leading-tight mt-1"><?php echo e($partyAddress); ?></div>
                            <?php endif; ?>
                            <?php if($partyState !== 'N/A'): ?>
                                <div class="text-gray-600 font-medium">State: <?php echo e($partyState); ?></div>
                            <?php endif; ?>
                            <?php if($partyPhone !== 'N/A'): ?>
                                <div class="text-gray-600 mt-1">Phone: <?php echo e($partyPhone); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="space-y-1 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="grid grid-cols-2 text-[13px]">
                        <span class="font-bold text-gray-500">Challan Date:</span>
                        <span class="text-right font-semibold"><?php echo e($challan->challan_date->format('d M Y')); ?></span>
                    </div>
                    
                    <?php if($challan->transport_name): ?>
                        <div class="grid grid-cols-2 text-[13px]">
                            <span class="font-bold text-gray-500">Transporter:</span>
                            <span class="text-right font-semibold text-gray-900"><?php echo e($challan->transport_name); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($challan->vehicle_number): ?>
                        <div class="grid grid-cols-2 text-[13px]">
                            <span class="font-bold text-gray-500">Vehicle No:</span>
                            <span class="text-right font-bold text-gray-900 uppercase"><?php echo e($challan->vehicle_number); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($challan->eway_bill_number): ?>
                        <div class="grid grid-cols-2 text-[13px]">
                            <span class="font-bold text-gray-500">E-Way Bill:</span>
                            <span class="text-right font-semibold text-gray-900"><?php echo e($challan->eway_bill_number); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if($challan->is_returnable && $challan->return_due_date): ?>
                        <div class="grid grid-cols-2 text-[13px] pt-2 mt-2 border-t border-gray-200">
                            <span class="font-bold text-gray-500">Return Due Date:</span>
                            <span class="text-right font-bold <?php echo e($challan->is_return_overdue ? 'text-red-600' : 'text-blue-600'); ?>">
                                <?php echo e($challan->return_due_date->format('d M Y')); ?>

                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

                        
        <div class="px-4 sm:px-8 py-6">
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-left text-sm whitespace-nowrap table-auto min-w-[650px]">
                    <table class="w-full text-left text-sm whitespace-nowrap table-auto">
                        <thead>
                            <tr class="border-b-2 border-gray-800 text-xs font-black text-gray-700 uppercase tracking-wider">
                                <th class="py-3 px-2 w-[50%]">Description</th>
                                <th class="py-3 px-2 text-center w-[20%]">HSN/SAC</th>
                                <th class="py-3 px-2 text-right w-[15%]">Qty</th>
                                <?php if($challan->is_returnable): ?>
                                    <th class="py-3 px-2 text-right w-[15%] text-blue-600">Pending</th>
                                <?php endif; ?>                                
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php $__currentLoopData = $challan->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="py-4 px-2">
                                        <div class="font-bold text-gray-900"><?php echo e($item->product_name); ?></div>
                                        <div class="text-[11px] text-gray-500 font-mono mt-0.5">SKU: <?php echo e($item->sku_code ?? 'N/A'); ?></div>
                                    </td>
                                    <td class="py-4 px-2 text-center text-gray-600"><?php echo e($item->hsn_code ?? '-'); ?></td>
                                    
                                    
                                    <td class="py-4 px-2 text-right font-bold text-gray-900"><?php echo e((float) $item->qty_sent); ?></td>
                                    
                                    <?php if($challan->is_returnable): ?>
                                        <td class="py-4 px-2 text-right font-bold <?php echo e($item->qty_pending > 0 ? 'text-blue-600' : 'text-green-600'); ?>">
                                            <?php echo e((float) $item->qty_pending); ?>

                                        </td>
                                    <?php endif; ?>                                  
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="px-8 pb-10 page-break-avoid flex flex-col justify-between border-t border-gray-200 pt-6">
                
                
                <div class="flex flex-col md:flex-row print:flex-row justify-between items-start gap-8 print:gap-4 w-full">
                    
                    
                    <div class="w-full md:w-1/2 print:w-1/2 space-y-4">
                        <?php if($challan->purpose_note): ?>
                            <div>
                                <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1">Purpose of Challan</h4>
                                <p class="text-[13px] text-gray-700 font-medium"><?php echo e($challan->purpose_note); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($challan->internal_notes): ?>
                            <div class="no-print bg-yellow-50 p-3 rounded border border-yellow-200 mt-4">
                                <h4 class="text-[11px] font-black text-yellow-700 uppercase tracking-widest mb-1">Internal Notes (Hidden on Print)</h4>
                                <p class="text-[12px] text-yellow-800"><?php echo e($challan->internal_notes); ?></p>
                            </div>
                        <?php endif; ?>                      
                    </div>

                    
                    <div class="w-full md:w-[250px] print:w-[250px] flex flex-col items-end mt-4 md:mt-0 print:mt-0">
                        <table class="w-full text-[14px]">
                            <tbody>
                                <tr class="border-t-2 border-gray-800">
                                    <td class="py-3 text-gray-600 font-bold uppercase tracking-wider text-xs">Total Quantity</td>
                                    <td class="py-3 text-right text-gray-900 font-black text-xl"><?php echo e((float) $challan->total_qty); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                                
                <div class="w-full flex flex-col sm:flex-row justify-between items-center gap-12 sm:gap-0 mt-16 sm:mt-24">
                    <div class="text-center sm:text-left">
                        <div class="border-t-2 border-gray-400 pt-2 inline-block min-w-[200px] text-[11px] font-bold text-gray-800 uppercase tracking-wider text-center">
                            Receiver's Signature
                        </div>
                    </div>
                    <div class="text-center sm:text-right">
                        <div class="border-t-2 border-gray-400 pt-2 inline-block min-w-[200px] text-[11px] font-bold text-gray-800 uppercase tracking-wider text-center">
                            Authorized Signatory
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/challans/show.blade.php ENDPATH**/ ?>