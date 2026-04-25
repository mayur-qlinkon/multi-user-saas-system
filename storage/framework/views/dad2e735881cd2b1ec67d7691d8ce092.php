<?php $__env->startSection('title', 'Edit Sales Invoice'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Edit Invoice</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        [x-cloak] {
            display: none !important;
        }

        body.item-modal-open {
            overflow: hidden;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

    
    <div class="pb-20" x-data="invoiceForm(<?php echo \Illuminate\Support\Js::from($units ?? [])->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($companyState)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($invoice)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($clients ?? [])->toHtml() ?>)">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-[1.5rem] font-bold text-gray-500 uppercase tracking-widest">Edit Invoice:
                    <?php echo e($invoice->invoice_number); ?></h1>
            </div>
            
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0 sm:justify-end">
                <a href="<?php echo e(route('admin.invoices.index')); ?>"
                    class="text-sm font-bold text-gray-500 hover:text-gray-800 transition-colors px-2 self-center sm:self-auto">Cancel</a>
                <button type="submit" form="mainInvoiceForm" name="status" value="draft"
                    class="w-full sm:w-auto bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-3 sm:py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Save as Draft
                </button>
                <button type="submit" form="mainInvoiceForm" name="status" value="confirmed"
                    class="w-full sm:w-auto bg-[#108c2a] hover:bg-[#0c6b1f] text-white px-8 py-3 sm:py-2.5 rounded-lg text-sm font-bold transition-all shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Save &amp; Confirm
                </button>
            </div>
        </div>

        <?php if($invoice->status === 'draft'): ?>
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 mb-6 flex items-start gap-3">
                <i data-lucide="file-edit" class="w-5 h-5 mt-0.5 flex-shrink-0"></i>
                <div class="text-sm">
                    <div class="font-bold">This invoice is a draft</div>
                    <div class="text-xs">Stock has not been deducted yet. Use <strong>Save &amp; Confirm</strong> to finalize, or keep editing as a draft.</div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($errors->any()): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200 shadow-sm">
                <div class="font-bold mb-2 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    Please fix the following errors:
                </div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    if (typeof Swal !== 'undefined') Swal.close();
                });
            </script>
        <?php endif; ?>

        
        <form id="mainInvoiceForm" action="<?php echo e(route('admin.invoices.update', $invoice->id)); ?>" method="POST"
            @submit="BizAlert.loading('Updating Invoice...')">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <input type="hidden" name="source" value="<?php echo e($invoice->source); ?>">

            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    
                    
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Customer <span class="text-red-500">*</span></label>
                        <div class="flex gap-2 items-start">
                            <div class="relative flex-1">
                                
                                <div class="flex gap-1 w-full relative" @click.away="isClientDropdownOpen = false">
                                    <div class="relative flex-1">
                                        <input type="text" x-model="clientSearchTerm" 
                                            @focus="isClientDropdownOpen = true"
                                            @input="isClientDropdownOpen = true; formData.customer_id = ''"
                                            placeholder="Search customer by name or phone..."
                                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none bg-white font-bold text-gray-700">
                                        <i data-lucide="chevron-down" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                                    </div>

                                    
                                    <input type="hidden" name="customer_id" x-model="formData.customer_id">

                                    
                                    <ul x-show="isClientDropdownOpen" x-cloak x-transition
                                        class="absolute z-[60] w-full bg-white border border-gray-200 rounded-lg shadow-2xl mt-1 max-h-60 overflow-y-auto overscroll-contain top-full left-0 custom-scrollbar">
                                        
                                        <li x-show="filteredClientList.length === 0" class="px-4 py-4 text-sm text-gray-500 text-center font-medium">
                                            No matching customers found.
                                        </li>

                                        <template x-for="client in filteredClientList" :key="client.id">
                                            <li @click="selectCustomer(client)"
                                                class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                                <div class="font-bold text-[13px] text-gray-800" x-text="client.name"></div>
                                                <div class="text-[11px] text-gray-500 mt-0.5 flex flex-wrap items-center gap-2">
                                                    <span x-show="client.phone" x-text="'📞 ' + client.phone"></span>
                                                    <span x-show="client.gst_number || client.gstin" class="bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200 text-[9px] font-bold text-gray-600" x-text="'GST: ' + (client.gst_number || client.gstin)"></span>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>

                                
                                <div x-show="formData.customer_gstin" x-cloak class="mt-1.5 pl-1 text-[11px] font-bold text-gray-500">
                                    GSTIN: <span class="text-blue-600" x-text="formData.customer_gstin"></span>
                                </div>
                            </div>
                            
                            
                            <button type="button" @click="toggleGuestMode()"
                                :class="isGuest ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600'"
                                class="px-4 py-4 rounded text-xs font-bold uppercase tracking-widest transition-colors border border-transparent shrink-0">
                                <span x-text="isGuest ? 'Guest Active' : 'Guest'"></span>
                            </button>
                        </div>

                        
                        <div x-show="isGuest" x-cloak class="mt-3">
                            <input type="text" name="customer_name" x-model="formData.customer_name"
                                placeholder="Enter Guest Name..."
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-brand-500 outline-none">
                        </div>
                    </div>

                    
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Invoice Date</label>
                        <input type="date" name="invoice_date" x-model="formData.invoice_date" required
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Due Date</label>
                        <input type="date" name="due_date" x-model="formData.due_date"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">GST Treatment</label>
                        <select name="gst_treatment" x-model="formData.gst_treatment"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none bg-white font-medium">
                            <option value="unregistered">Unregistered Business (B2C)</option>
                            <option value="registered">Registered Business (B2B)</option>
                            <option value="composition">Composition</option>
                            <option value="overseas">Overseas (Export)</option>
                            <option value="sez">SEZ</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Store Branch</label>
                        <select name="store_id" required x-model="formData.store_id" @change="updateStoreData($event)"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none bg-white">
                            <?php $__currentLoopData = $stores ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($store->id); ?>" data-state="<?php echo e($store->state->name ?? $companyState); ?>"><?php echo e($store->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Stock Warehouse</label>
                        <select name="warehouse_id" required x-model="formData.warehouse_id"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm focus:border-brand-500 outline-none bg-white">
                            <option value="">-- Select Warehouse --</option>
                            <?php $__currentLoopData = $warehouses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($warehouse->id); ?>"><?php echo e($warehouse->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="mt-[-1px]">
                        <?php if (isset($component)) { $__componentOriginal25028d1e070da787b324eb3ef2d05d03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25028d1e070da787b324eb3ef2d05d03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.state-select','data' => ['name' => 'supply_state','label' => 'Place of Supply (State)','xModel' => 'formData.supply_state','@change' => 'calculate()','class' => '!bg-white !border-gray-300 !font-bold !text-[#108c2a]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('state-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'supply_state','label' => 'Place of Supply (State)','x-model' => 'formData.supply_state','@change' => 'calculate()','class' => '!bg-white !border-gray-300 !font-bold !text-[#108c2a]']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25028d1e070da787b324eb3ef2d05d03)): ?>
<?php $attributes = $__attributesOriginal25028d1e070da787b324eb3ef2d05d03; ?>
<?php unset($__attributesOriginal25028d1e070da787b324eb3ef2d05d03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25028d1e070da787b324eb3ef2d05d03)): ?>
<?php $component = $__componentOriginal25028d1e070da787b324eb3ef2d05d03; ?>
<?php unset($__componentOriginal25028d1e070da787b324eb3ef2d05d03); ?>
<?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 flex flex-col">
                <div
                    class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h2 class="text-lg font-bold text-gray-800 tracking-tight">Billing Items</h2>

                    <div class="relative w-full sm:w-96">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        <input type="text" x-model="globalSearch" @input.debounce.300ms="fetchGlobalSkus()"
                            @focus="showResults = true" @click.away="showResults = false"
                            placeholder="Type product name or scan barcode..."
                            class="w-full border border-gray-300 rounded shadow-sm pl-9 pr-4 py-2.5 text-sm focus:border-brand-500 outline-none bg-white">

                        <ul x-show="showResults && globalSearch.length > 1" x-cloak
                            class="absolute z-[60] w-full bg-white border border-gray-200 rounded-lg shadow-2xl mt-1 max-h-60 overflow-y-auto top-full left-0">

                            <li x-show="isSearching"
                                class="px-4 py-3 text-xs text-gray-500 text-center font-medium flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-[#108c2a]" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Searching inventory...
                            </li>

                            <li x-show="!isSearching && globalSearchResults.length === 0" class="px-4 py-4 text-center">
                                <span class="block text-sm font-bold text-gray-700">No products found in this
                                    warehouse</span>
                                <span class="block text-[11px] text-gray-400 mt-0.5">Try a different name or ensure the
                                    item has stock.</span>
                            </li>

                            <template x-for="result in globalSearchResults" :key="result.product_sku_id">
                                <li @click="addSkuToTable(result); showResults = false"
                                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="text-[13px] font-bold text-gray-800" x-text="result.product_name">
                                            </div>
                                            <div class="text-[10px] text-gray-400 font-mono mt-0.5"
                                                x-text="result.sku_code"></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[12px] font-black text-[#108c2a]"
                                                x-text="result.price ? '₹' + parseFloat(result.price).toFixed(2) : '₹0.00'">
                                            </div>
                                        </div>
                                        <div class="text-[10px] text-gray-500" x-text="'Stock: ' + (result.stock || 0)">
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                
                <div class="hidden md:block overflow-x-auto min-h-[200px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-4 min-w-[250px]">PRODUCT</th>
                                <th class="px-4 py-4 min-w-[140px] w-[160px]">BASE UNIT PRICE</th>
                                <th class="px-4 py-4 min-w-[140px] w-[180px] text-center">QTY</th>
                                <th class="px-5 py-4 min-w-[120px] w-[160px] text-right">TOTAL (INC TAX)</th>
                                <th class="px-4 py-4 w-[60px] text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(item, index) in items" :key="item.key">
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="text-[13px] font-bold text-gray-800" x-text="item.product_name"></div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span
                                                class="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded font-mono font-bold border border-gray-200"
                                                x-text="item.sku_code"></span>

                                            <button type="button" @click="openItemModal(index)"
                                                class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1 rounded transition-colors"
                                                title="Edit Tax, Discount & Unit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                    </path>
                                                </svg>
                                            </button>

                                            <span class="text-[10px] font-bold text-gray-400 uppercase"
                                                x-text="item.tax_percent + '% ' + item.tax_type.substring(0,3)"></span>

                                            <span x-show="item.discount_value > 0"
                                                class="bg-amber-100 text-amber-700 text-[10px] px-2 py-0.5 rounded font-bold border border-amber-200">
                                                Disc: <span
                                                    x-text="item.discount_type === 'percentage' ? item.discount_value + '%' : '₹' + item.discount_value"></span>
                                            </span>

                                            
                                            <input type="hidden" :name="'items[' + index + '][tax_type]'"
                                                :value="item.tax_type">
                                            <input type="hidden" :name="'items[' + index + '][tax_percent]'"
                                                :value="item.tax_percent">
                                            <input type="hidden" :name="'items[' + index + '][discount_type]'"
                                                :value="item.discount_type">
                                            <input type="hidden" :name="'items[' + index + '][discount_value]'"
                                                :value="item.discount_value">
                                            <input type="hidden" :name="'items[' + index + '][product_sku_id]'"
                                                :value="item.product_sku_id">
                                            <input type="hidden" :name="'items[' + index + '][unit_id]'"
                                                :value="item.unit_id">
                                            <input type="hidden" :name="'items[' + index + '][unit_price]'"
                                                :value="item.unit_price">
                                            <input type="hidden" :name="'items[' + index + '][quantity]'"
                                                :value="item.quantity">
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 align-middle">
                                        <div class="relative w-full min-w-[100px]">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-bold">₹</span>
                                            <input type="number" step="0.01" x-model="item.unit_price"
                                                @input="calculate()"
                                                class="w-full h-10 md:h-9 border border-gray-300 rounded px-2 pl-7 text-sm focus:border-brand-500 outline-none font-bold text-gray-700 shadow-sm transition-all">
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 align-middle">
                                        <div class="flex items-center justify-center min-w-[120px]">
                                            <button type="button"
                                                @click="item.quantity = Math.max(1, parseFloat(item.quantity || 0) - 1); calculate()"
                                                class="w-10 h-10 md:w-8 md:h-9 border border-gray-300 rounded-l flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 active:bg-gray-200 transition-colors">-</button>
                                            <input type="number" step="0.0001" x-model="item.quantity"
                                                @input="calculate()"
                                                class="w-16 h-10 md:h-9 border-y border-x-0 border-gray-300 text-center text-sm font-bold focus:ring-0 focus:border-brand-500 outline-none p-0 text-gray-700 shadow-inner">
                                            <button type="button"
                                                @click="item.quantity = parseFloat(item.quantity || 0) + 1; calculate()"
                                                class="w-10 h-10 md:w-8 md:h-9 border border-gray-300 rounded-r flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 active:bg-gray-200 transition-colors">+</button>
                                        </div>
                                    </td>

                                    <td class="px-5 py-3 text-right">
                                        <span class="font-black text-gray-800 text-[14px]"
                                            x-text="formatCurrency(item.line_total)"></span>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <button type="button" @click="removeItem(index)"
                                            class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded transition-colors"
                                            title="Remove Item">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                </path>
                                                <line x1="10" y1="11" x2="10" y2="17">
                                                </line>
                                                <line x1="14" y1="11" x2="14" y2="17">
                                                </line>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                
                <div class="md:hidden divide-y divide-gray-100 border-t border-gray-100">
                    <template x-for="(item, index) in items" :key="item.key">
                        <div class="flex flex-col gap-3 p-4 bg-white relative">
                            
                            
                            <div class="flex justify-between items-start pr-8">
                                <div>
                                    <div class="text-[13px] font-bold text-gray-800" x-text="item.product_name"></div>
                                    <div class="text-[11px] text-gray-500 font-mono mt-0.5" x-text="'SKU: ' + item.sku_code"></div>
                                    <span x-show="item.discount_value > 0"
                                        class="bg-amber-100 text-amber-700 text-[10px] px-1.5 py-0.5 rounded font-bold mt-1 inline-block border border-amber-200">
                                        Disc: <span x-text="item.discount_type === 'percentage' ? item.discount_value + '%' : '₹' + item.discount_value"></span>
                                    </span>
                                </div>
                                <button type="button" @click="removeItem(index)"
                                    class="absolute top-4 right-4 text-red-400 hover:text-red-600 bg-red-50 p-1.5 rounded transition-colors"
                                    title="Remove Item">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            </div>

                            
                            <div class="flex justify-between items-center text-[11px] text-gray-500 bg-gray-50 px-2 py-1.5 rounded border border-gray-100">
                                <span x-show="item.hsn_code">HSN: <span class="font-mono font-bold text-gray-700" x-text="item.hsn_code"></span></span>
                                <span>Tax: <span class="font-bold text-gray-700" x-text="item.tax_percent + '%'"></span> <span class="uppercase" x-text="item.tax_type.substring(0,3)"></span></span>
                            </div>

                            
                            <div class="flex items-end gap-3 mt-1">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Unit Price</label>
                                    <div class="relative w-full">
                                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-bold">₹</span>
                                        <input type="number" step="0.01" x-model="item.unit_price" @input="calculate()"
                                            class="w-full h-10 border border-gray-300 rounded px-2 pl-7 text-sm focus:border-brand-500 outline-none font-bold text-gray-700 shadow-sm transition-all">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 text-center">Qty</label>
                                    <div class="flex items-center justify-center min-w-[110px]">
                                        <button type="button" @click="item.quantity = Math.max(1, parseFloat(item.quantity || 0) - 1); calculate()"
                                            class="w-9 h-10 border border-gray-300 rounded-l flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 active:bg-gray-200">-</button>
                                        <input type="number" step="0.0001" x-model="item.quantity" @input="calculate()"
                                            class="w-14 h-10 border-y border-x-0 border-gray-300 text-center text-sm font-bold focus:ring-0 focus:border-brand-500 outline-none p-0 text-gray-700 shadow-inner">
                                        <button type="button" @click="item.quantity = parseFloat(item.quantity || 0) + 1; calculate()"
                                            class="w-9 h-10 border border-gray-300 rounded-r flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 active:bg-gray-200">+</button>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="flex justify-between items-center pt-3 border-t border-gray-50 mt-1">
                                <button type="button" @click="openItemModal(index)"
                                    class="text-blue-600 hover:text-blue-800 bg-blue-50 px-2.5 py-1.5 rounded text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg>
                                    Edit Settings
                                </button>
                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Line Total</span>
                                    <span class="font-black text-[#108c2a] text-[16px]" x-text="formatCurrency(item.line_total)"></span>
                                </div>
                            </div>

                        </div>
                    </template>
                    
                    
                    <div x-show="items.length === 0" class="p-8 text-center text-gray-400 text-sm bg-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto mb-2 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        No items added yet.
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
                <div class="lg:col-span-1 bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col gap-4">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b pb-3">Notes & Terms</h3>
                    <textarea name="notes" rows="2" placeholder="Internal notes..."
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-brand-500 outline-none resize-none"><?php echo e(old('notes', $invoice->notes)); ?></textarea>
                    <textarea name="terms_conditions" rows="2" placeholder="Customer terms..."
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:border-brand-500 outline-none resize-none"><?php echo e(old('terms_conditions', $invoice->terms_conditions)); ?></textarea>
                </div>

                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b pb-3 mb-4">Payment Receipt
                    </h3>
                    <div class="space-y-4">
                        
                        <?php if (isset($component)) { $__componentOriginal6074b4137b8006ef4d1b8340d0976388 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6074b4137b8006ef4d1b8340d0976388 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.payment-method-select','data' => ['name' => 'payment_method_id','label' => 'Payment Mode','xModel' => 'formData.payment_method_id',':required' => 'global.amount_paid > 0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('payment-method-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'payment_method_id','label' => 'Payment Mode','x-model' => 'formData.payment_method_id',':required' => 'global.amount_paid > 0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6074b4137b8006ef4d1b8340d0976388)): ?>
<?php $attributes = $__attributesOriginal6074b4137b8006ef4d1b8340d0976388; ?>
<?php unset($__attributesOriginal6074b4137b8006ef4d1b8340d0976388); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6074b4137b8006ef4d1b8340d0976388)): ?>
<?php $component = $__componentOriginal6074b4137b8006ef4d1b8340d0976388; ?>
<?php unset($__componentOriginal6074b4137b8006ef4d1b8340d0976388); ?>
<?php endif; ?>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Amount
                                Received (₹)</label>
                            <input type="number" step="0.01" name="amount_paid" x-model="global.amount_paid"
                                @input="calculate()"
                                class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm font-black text-[#108c2a] focus:border-[#108c2a] outline-none">
                        </div>
                        <div class="pt-3 border-t border-gray-50 flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400 uppercase"
                                x-text="balance.is_change ? 'Return Change:' : 'Due Amount:'"></span>
                            <span :class="balance.is_change ? 'text-blue-600' : 'text-red-600'" class="text-lg font-black"
                                x-text="formatCurrency(balance.value)"></span>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3
                        class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-3 mb-4">
                        Financials</h3>

                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">Subtotal (Taxable):</span>
                            <span class="font-bold text-gray-800" x-text="formatCurrency(totals.subtotal)"></span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="font-semibold">Total Tax (GST):</span>
                            <span class="font-bold text-gray-800" x-text="formatCurrency(totals.tax)"></span>
                        </div>

                        
                        
                        <div class="flex flex-wrap xl:flex-nowrap justify-between items-center pt-2 gap-2">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-600">Discount:</span>
                                <select name="discount_type" x-model="global.discount_type" @change="calculate()"
                                    class="border border-gray-300 rounded px-2 py-0.5 text-[11px] font-bold text-gray-600 focus:border-[#108c2a] outline-none bg-gray-50 cursor-pointer">
                                    <option value="fixed">Flat (₹)</option>
                                    <option value="percentage">Percent (%)</option>
                                </select>
                            </div>
                            <input type="number" step="0.01" name="discount_value" x-model="global.discount_value"
                                @input="calculate()"
                                class="w-24 sm:w-32 border border-gray-300 rounded px-3 py-1 text-right font-bold text-red-500 focus:border-[#108c2a] outline-none ml-auto"
                                placeholder="0.00">
                        </div>

                        <div class="flex justify-between items-center pt-2 border-b border-gray-100 pb-4">
                            <span class="font-semibold">Shipping Cost (₹):</span>
                            <input type="number" step="0.01" name="shipping_charge" x-model="global.shipping"
                                @input="calculate()"
                                class="w-32 border border-gray-300 rounded px-3 py-1 text-right font-bold text-gray-800 focus:border-[#108c2a] outline-none">
                        </div>

                        <div class="flex justify-between items-end pt-2">
                            <div>
                                <div class="text-[11px] font-bold text-gray-500 uppercase">Auto Round Off</div>
                                <div class="text-sm font-bold text-gray-600 mt-1" x-text="global.round_off"></div>
                                <input type="hidden" name="round_off" :value="global.round_off">
                            </div>
                            <div class="text-right">
                                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Net Payable
                                </div>
                                <div class="text-2xl font-black text-[#108c2a]"
                                    x-text="formatCurrency(totals.grand_total)"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        
        <div x-show="isItemModalOpen" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-end bg-black/50 backdrop-blur-sm transition-opacity">
            <div class="bg-white w-full max-w-md h-full shadow-2xl flex flex-col" x-show="isItemModalOpen" x-transition
                @click.away="closeItemModal()">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-[15px] font-bold text-gray-800" x-text="activeEditData.product_name"></h3>
                    <button @click="closeItemModal()" class="text-gray-400 hover:text-red-500"><i data-lucide="x"
                            class="w-5 h-5"></i></button>
                </div>
                <div class="p-6 space-y-5 flex-1 overflow-y-auto custom-scrollbar">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Tax
                            Type</label>
                        <select x-model="activeEditData.tax_type"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm outline-none bg-white">
                            <option value="exclusive">Exclusive (Price + Tax)</option>
                            <option value="inclusive">Inclusive (Price includes Tax)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Discount
                            Type</label>
                        <select x-model="activeEditData.discount_type"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm outline-none bg-white">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₹)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Discount
                            Value</label>
                        <input type="number" step="0.01" x-model="activeEditData.discount_value"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">GST
                            (%)</label>
                        <input type="number" step="0.01" x-model="activeEditData.tax_percent"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Sales
                            Unit</label>
                        <select x-model="activeEditData.unit_id"
                            class="w-full border border-gray-300 rounded px-3 py-2.5 text-sm outline-none bg-white">
                            <template x-for="u in units" :key="u.id">
                                <option :value="u.id" x-text="u.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="p-4 sm:p-5 border-t border-gray-100 bg-white grid gap-3 grid-cols-2 sm:grid-cols-2"> 
                <!-- Note: I kept the existing classes but added 'sm:p-5' to ensure padding adapts smoothly -->

                    <button type="button" @click="closeItemModal()"
                        class="col-span-1 bg-gray-100 text-gray-700 font-bold text-sm py-2.5 rounded-lg">Cancel</button>
                    
                    <button type="button" @click="saveItemModal()"
                        class="col-span-1 bg-[#108c2a] text-white font-bold text-sm py-2.5 rounded-lg">Save Changes</button>
                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function invoiceForm(allUnits = [], companyState = '', invoiceData = null, allClients = []) {

            // 🌟 Map old line items from database
            let initialItems = [];
            // 🌟 Grab the first payment record if it exists
            let initialPayment = (invoiceData && invoiceData.payments && invoiceData.payments.length > 0) ?
                invoiceData.payments[0] :
                null;
            if (invoiceData && invoiceData.items) {
                initialItems = invoiceData.items.map((item, index) => ({
                    key: index,
                    product_id: item.product_id,
                    product_sku_id: item.product_sku_id,
                    unit_id: item.unit_id,
                    product_name: item.product_name,
                    // 🌟 Read the eager-loaded SKU code
                    sku_code: item.sku?.sku_code || item.sku?.sku || 'N/A',
                    hsn_code: item.hsn_code || '', // 🌟 Added HSN for Edit Mode
                    quantity: parseFloat(item.quantity) || 1,
                    unit_price: parseFloat(item.unit_price) || 0,
                    tax_percent: parseFloat(item.tax_percent) || 0,
                    tax_type: item.tax_type || 'exclusive',
                    discount_type: item.discount_type || 'percentage',
                    discount_value: parseFloat(item.discount_value) || 0,
                    line_total: parseFloat(item.total_amount) || 0,
                }));
            }

            return {
                units: allUnits,
                company_state: companyState,
                items: initialItems,
                itemCounter: initialItems.length,
                globalSearch: '',
                isSearching: false,
                globalSearchResults: [],
                showResults: false,
                clientsList: allClients,
                clientSearchTerm: invoiceData ? (invoiceData.customer_name || '') : '',
                isClientDropdownOpen: false,

                get filteredClientList() {
                    if (this.clientSearchTerm.trim() === '') {
                        return this.clientsList;
                    }
                    const term = this.clientSearchTerm.toLowerCase();
                    return this.clientsList.filter(client => {
                        return client.name.toLowerCase().includes(term) || 
                            (client.phone && client.phone.includes(term));
                    });
                },

                isGuest: invoiceData ? !invoiceData.customer_id : false,

                formData: {
                    customer_id: invoiceData ? (invoiceData.customer_id || '') : '',
                    customer_name: invoiceData ? (invoiceData.customer_name || '') : '',
                    customer_gstin: invoiceData && invoiceData.client ? (invoiceData.client.gst_number || '') :
                    '', // 🌟 Track GSTIN
                    gst_treatment: invoiceData ? (invoiceData.gst_treatment || 'unregistered') :
                    'unregistered', // 🌟 Track Treatment
                    supply_state: invoiceData ? invoiceData.supply_state : companyState,
                    payment_method_id: initialPayment ? initialPayment.payment_method_id : '',
                    invoice_date: invoiceData ? invoiceData.invoice_date.split('T')[0] : new Date().toISOString().split(
                        'T')[0],
                    due_date: invoiceData && invoiceData.due_date ? invoiceData.due_date.split('T')[0] : new Date(new Date()
                        .setDate(new Date().getDate() + 7)).toISOString().split('T')[0],
                    store_id: invoiceData ? invoiceData.store_id : "<?php echo e(auth()->user()->store_id ?? ''); ?>",
                    warehouse_id: invoiceData ? invoiceData.warehouse_id : '',
                },

                global: {
                    shipping: invoiceData ? parseFloat(invoiceData.shipping_charge) || 0 : 0,
                    // 🌟 Map to amount_received so the cashier sees the physical cash handed over
                    amount_paid: initialPayment ? parseFloat(initialPayment.amount_received || initialPayment.amount) : 0,
                    // 🌟 Fix global discount mapping too
                    discount_type: (invoiceData && invoiceData.discount_type) ? (invoiceData.discount_type ===
                        'percentage' ? 'percent' : invoiceData.discount_type) : 'fixed',
                    discount_value: invoiceData ? parseFloat(invoiceData.discount_amount) || 0 : 0,
                    round_off: invoiceData ? invoiceData.round_off : '0.00'
                },
                totals: {
                    subtotal: invoiceData ? parseFloat(invoiceData.subtotal) || 0 : 0,
                    tax: invoiceData ? parseFloat(invoiceData.tax_amount) || 0 : 0,
                    cgst: invoiceData ? parseFloat(invoiceData.cgst_amount) || 0 : 0,
                    sgst: invoiceData ? parseFloat(invoiceData.sgst_amount) || 0 : 0,
                    igst: invoiceData ? parseFloat(invoiceData.igst_amount) || 0 : 0,
                    cgst_rate: 0,
                    sgst_rate: 0,
                    igst_rate: 0,
                    grand_total: invoiceData ? parseFloat(invoiceData.grand_total) || 0 : 0
                },
                balance: {
                    value: 0,
                    is_change: false
                },

                isItemModalOpen: false,
                activeEditIndex: null,
                activeEditData: {},

                init() {
                    if (this.items.length > 0) {
                        this.calculate();
                    }
                },

                toggleGuestMode() {
                    this.isGuest = !this.isGuest;
                    if (this.isGuest) {
                        this.formData.customer_id = '';
                        this.formData.customer_name = '';
                        this.clientSearchTerm = ''; // <-- ADD THIS LINE
                        this.formData.supply_state = this.company_state;
                    }
                    this.calculate();
                },

                selectCustomer(client) {
                    this.isGuest = false;
                    
                    // Update Alpine State
                    this.formData.customer_id = client.id;
                    this.clientSearchTerm = client.name; 
                    
                    // Update Snapshot Data
                    this.formData.supply_state = client.state_name_only || client.state?.name || this.company_state;
                    this.formData.customer_name = client.name;
                    this.formData.customer_gstin = client.gst_number || client.gstin || '';

                    this.formData.gst_treatment = this.formData.customer_gstin ? 'registered' : 'unregistered';

                    this.isClientDropdownOpen = false;
                    this.calculate();
                },
                updateStoreData(e) {
                    const opt = e.target.options[e.target.selectedIndex];
                    if (opt && opt.dataset.state) {
                        this.company_state = opt.dataset.state;
                    }
                    this.calculate();
                },

                async fetchGlobalSkus() {
                    let warehouseId = this.formData.warehouse_id;

                    if (!warehouseId) {
                        BizAlert.toast('Please select a warehouse first', 'error');
                        this.globalSearch = '';
                        return;
                    }

                    if (this.globalSearch.length < 2) return;

                    this.isSearching = true;

                    try {
                        let response = await fetch(
                            `/admin/api/search-skus?term=${encodeURIComponent(this.globalSearch)}&warehouse_id=${warehouseId}&in_stock_only=1`
                        );

                        if (!response.ok) {
                            let errorData = await response.text();
                            console.error("Backend Error:", errorData);
                            BizAlert.toast('Error searching products. Check console.', 'error');
                            return;
                        }

                        this.globalSearchResults = await response.json();
                    } catch (error) {
                        console.error("Network or Parsing Error:", error);
                    } finally {
                        this.isSearching = false;
                    }
                },

                addSkuToTable(result) {
                    this.items.push({
                        key: this.itemCounter++,
                        product_id: result.product_id,
                        product_sku_id: result.product_sku_id,
                        unit_id: result.unit_id,
                        product_name: result.product_name,
                        sku_code: result.sku_code,
                        quantity: 1,
                        unit_price: parseFloat(result.price) || 0,
                        tax_percent: parseFloat(result.tax_percent) || 18,
                        tax_type: result.tax_type || 'exclusive',
                        discount_type: 'fixed',
                        discount_value: 0,
                        line_total: 0
                    });
                    this.globalSearch = '';
                    this.calculate();
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                    this.calculate();
                },

                openItemModal(index) {
                    this.activeEditIndex = index;
                    this.activeEditData = JSON.parse(JSON.stringify(this.items[index]));
                    this.isItemModalOpen = true;
                },

                saveItemModal() {
                    this.activeEditData.tax_percent = parseFloat(this.activeEditData.tax_percent) || 0;
                    this.activeEditData.discount_value = parseFloat(this.activeEditData.discount_value) || 0;

                    this.items[this.activeEditIndex] = Object.assign(this.items[this.activeEditIndex], this.activeEditData);
                    this.calculate();
                    this.closeItemModal();
                },

                closeItemModal() {
                    this.isItemModalOpen = false;
                },

                calculate() {
                    let subtotalAcc = 0;
                    let taxAcc = 0;

                    this.items.forEach(item => {
                        let qty = parseFloat(item.quantity) || 0;
                        let price = parseFloat(item.unit_price) || 0;
                        let taxPct = parseFloat(item.tax_percent) || 0;
                        let discVal = parseFloat(item.discount_value) || 0;

                        let baseVal = qty * price;

                        // 🌟 1. Apply Line Discount First
                        let discountAmount = 0;
                        if (item.discount_type === 'percent' || item.discount_type === 'percentage') {
                            discountAmount = baseVal * (discVal / 100);
                        } else {
                            discountAmount = discVal; // Flat amount deduction
                        }

                        let afterDiscount = Math.max(0, baseVal - discountAmount);

                        let taxable = 0,
                            tax = 0;
                        if (item.tax_type === 'inclusive') {
                            taxable = afterDiscount / (1 + (taxPct / 100));
                            tax = afterDiscount - taxable;
                        } else {
                            taxable = afterDiscount;
                            tax = taxable * (taxPct / 100);
                        }

                        item.line_total = taxable + tax;
                        subtotalAcc += taxable;
                        taxAcc += tax;
                    });

                    this.totals.subtotal = subtotalAcc;
                    this.totals.tax = taxAcc;

                    // 🌟 GST Mixed Rate Logic
                    let uniqueRates = [...new Set(this.items.map(item => parseFloat(item.tax_percent) || 0))];
                    let isMixed = uniqueRates.length > 1;
                    let baseRate = uniqueRates.length === 1 ? uniqueRates[0] : 0;

                    const isInterState = (this.formData.supply_state || '').trim().toLowerCase() !== (this.company_state ||
                        '').trim().toLowerCase();

                    if (isInterState) {
                        this.totals.igst = taxAcc;
                        this.totals.cgst = 0;
                        this.totals.sgst = 0;
                        this.totals.igst_rate = isMixed ? 'Mixed' : baseRate;
                        this.totals.cgst_rate = 0;
                        this.totals.sgst_rate = 0;
                    } else {
                        this.totals.igst = 0;
                        this.totals.cgst = taxAcc / 2;
                        this.totals.sgst = taxAcc / 2;
                        this.totals.igst_rate = 0;
                        this.totals.cgst_rate = isMixed ? 'Mixed' : (baseRate / 2);
                        this.totals.sgst_rate = isMixed ? 'Mixed' : (baseRate / 2);
                    }

                    let shipping = parseFloat(this.global.shipping) || 0;
                    let globalDiscVal = parseFloat(this.global.discount_value) || 0;

                    let itemsSum = subtotalAcc + taxAcc;

                    let globalDiscountAmount = 0;
                    if (this.global.discount_type === 'percent' || this.global.discount_type === 'percentage') {
                        globalDiscountAmount = itemsSum * (globalDiscVal / 100);
                    } else {
                        globalDiscountAmount = globalDiscVal;
                    }

                    let totalBeforeRound = Math.max(0, itemsSum - globalDiscountAmount + shipping);

                    this.totals.grand_total = Math.round(totalBeforeRound);
                    this.global.round_off = (this.totals.grand_total - totalBeforeRound).toFixed(2);

                    let paid = parseFloat(this.global.amount_paid) || 0;
                    let diff = paid - this.totals.grand_total;
                    this.balance.is_change = diff > 0;
                    this.balance.value = Math.abs(diff);
                },

                formatCurrency(val) {
                    return '₹' + parseFloat(val).toLocaleString('en-IN', {
                        minimumFractionDigits: 2
                    });
                }
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas\resources\views/admin/invoices/edit.blade.php ENDPATH**/ ?>