<?php $__env->startSection('title', 'Label Print - Qlinkon BIZNESS'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Labels</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="space-y-6 pb-10" x-data="labelGenerator()" x-init="init()">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                
                <p class="text-sm text-gray-500 mt-1 font-medium">Generate QR & Barcode labels — select products, configure,
                    preview and print</p>
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto bg-white border border-gray-200 rounded-xl p-1.5 shadow-sm">
                <button @click="setLabelType('qr')"
                    :class="labelType === 'qr' ? 'bg-gray-100 text-gray-800 border-gray-200' :
                        'bg-transparent text-gray-500 hover:text-gray-700 border-transparent'"
                    class="flex-1 md:flex-none justify-center px-4 py-1.5 rounded-lg text-sm font-bold flex items-center gap-2 transition-all border">
                    <i data-lucide="qr-code" class="w-4 h-4"></i> QR Code
                </button>
                <button @click="setLabelType('barcode')"
                    :class="labelType === 'barcode' ? 'bg-brand-500 text-white border-brand-500 shadow-sm' :
                        'bg-transparent text-gray-500 hover:text-gray-700 border-transparent'"
                    class="flex-1 md:flex-none justify-center px-4 py-1.5 rounded-lg text-sm font-bold flex items-center gap-2 transition-all border">
                    <i data-lucide="barcode" class="w-4 h-4"></i> Barcode
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex flex-wrap gap-3 items-center">
                <div class="relative flex-1 min-w-[250px]">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" x-model="search" @keydown.enter="fetchProducts(1)"
                        placeholder="Search by product name, SKU or barcode..."
                        class="w-full border border-gray-200 rounded-lg pl-9 pr-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none transition-all placeholder:text-gray-400 bg-gray-50/50">
                </div>

                <div class="relative w-full md:w-56">
                    <select x-model="categoryId" @change="fetchProducts(1)"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-[#108c2a]/20 focus:border-[#108c2a] outline-none appearance-none cursor-pointer font-medium bg-gray-50/50">
                        <option value="">All Categories</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>"><?php echo e($cat->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <i data-lucide="chevron-down"
                        class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>

                <button @click="fetchProducts(1)"
                    class="shrink-0 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold px-5 py-2.5 rounded-lg shadow-sm transition-all flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4"></i> Search
                </button>

                <button @click="resetFilters()"
                    class="shrink-0 bg-white border border-gray-200 text-gray-600 hover:text-gray-800 hover:bg-gray-50 text-sm font-bold px-4 py-2.5 rounded-lg transition-colors flex items-center gap-2">
                    <i data-lucide="x-circle" class="w-4 h-4"></i> Reset
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex flex-wrap gap-x-8 gap-y-5 items-center">

                <div class="flex flex-col gap-1.5 min-w-[200px]">
                    <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Paper Size
                    </label>
                    <select x-model="cfg.pageSize"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 outline-none focus:border-[#108c2a]">
                        <option value="thermal_2x1">Thermal Label — 2×1 inch</option>
                        <option value="thermal_3x2">Thermal Label — 3×2 inch</option>
                        <option value="thermal_4x3">Thermal Label — 4×3 inch</option>
                        <option value="a5">A5 Sheet</option>
                        <option value="a4">A4 Sheet</option>
                    </select>
                </div>

                <div class="hidden lg:block w-px h-10 bg-gray-200"></div>

                <template
                    x-for="toggle in [
                    { id: 'showStore', label: 'Store Name' },
                    { id: 'showName', label: 'Product Name' },
                    { id: 'showPrice', label: 'Price' },
                    { id: 'showBorder', label: 'Border' }
                ]"
                    :key="toggle.id">
                    <div class="flex flex-col gap-2">
                        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider"
                            x-text="toggle.label"></span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" x-model="cfg[toggle.id]">
                            <div
                                class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#108c2a]">
                            </div>
                            <span class="ml-2 text-xs font-bold text-gray-600"
                                x-text="cfg[toggle.id] ? 'Show' : 'Hide'"></span>
                        </label>
                    </div>
                </template>

                <div class="hidden lg:block w-px h-10 bg-gray-200"></div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Price Font Size</label>
                    <input type="number" x-model="cfg.fontSize" min="8" max="24"
                        class="w-16 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center outline-none focus:border-[#108c2a]">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-t-xl border border-gray-200 border-b-0 px-4 sm:px-5 py-3 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button @click="selectAll(true)"
                    class="text-xs font-bold text-[#108c2a] hover:underline flex items-center gap-1.5">
                    <i data-lucide="check-square" class="w-4 h-4"></i> Select All
                </button>
                <span class="text-gray-300">|</span>
                <button @click="selectAll(false)"
                    class="text-xs font-bold text-gray-400 hover:text-gray-600 hover:underline flex items-center gap-1.5">
                    <i data-lucide="square" class="w-4 h-4"></i> Deselect All
                </button>

                <div x-show="selectedCount > 0" x-cloak
                    class="ml-2 flex items-center gap-1.5 bg-[#e6f4ea] text-[#108c2a] px-3 py-1 rounded-full text-xs font-bold border border-[#bce3c6]">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                    <span x-text="selectedCount"></span> selected
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center md:justify-end gap-3 w-full md:w-auto">
                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 w-full sm:w-auto justify-center">
                    <span class="text-xs font-bold text-gray-500">Set all copies:</span>
                    <input type="number" x-model="globalCopy" @change="applyGlobalCopy()" min="1" max="99"
                        class="w-14 border border-gray-300 rounded px-1 py-1 text-center text-xs font-bold outline-none focus:border-[#108c2a]">
                </div>

                <button @click="triggerPrint(true)"
                    class="flex-1 sm:flex-none justify-center bg-[#6366f1] hover:bg-[#4f46e5] text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm flex items-center gap-2 transition-colors">
                    <i data-lucide="eye" class="w-4 h-4"></i> Preview
                </button>
                <?php if(has_permission('labels.print')): ?>
                <button @click="triggerPrint(false)"
                    class="flex-1 sm:flex-none justify-center bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm flex items-center gap-2 transition-colors">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-b-xl shadow-sm border border-gray-200 overflow-hidden">
            
            
            <div class="hidden md:block overflow-x-auto min-h-[400px]">
                <table class="w-full text-left text-sm whitespace-nowrap min-w-[850px]">
                    <thead
                        class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-5 py-4 w-10">
                                <input type="checkbox"
                                    class="rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] w-4 h-4 cursor-pointer"
                                    :checked="selectedCount > 0 && selectedCount === products.length"
                                    :indeterminate="selectedCount > 0 && selectedCount < products.length"
                                    @change="selectAll($event.target.checked)">
                            </th>
                            <th class="px-4 py-4 text-center w-12">#</th>
                            <th class="px-4 py-4">Product</th>
                            <th class="px-4 py-4 text-center">SKU</th>
                            <th class="px-4 py-4">Category</th>
                            <th class="px-4 py-4 text-right">Price</th>
                            <th class="px-4 py-4 text-center w-24">Copies</th>
                            <th class="px-4 py-4 text-center w-24">Label</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">

                        <tr x-show="isLoading" x-cloak>
                            <td colspan="8" class="text-center py-20">
                                <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-[#108c2a] mx-auto mb-3"></i>
                                <p class="text-gray-500 font-medium">Loading products...</p>
                            </td>
                        </tr>

                        <tr x-show="!isLoading && products.length === 0" x-cloak>
                            <td colspan="8" class="text-center py-20">
                                <i data-lucide="package-x" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                                <p class="text-gray-500 font-medium">No products found.</p>
                            </td>
                        </tr>

                        <template x-for="(product, index) in products" :key="product.unique_id">
                            <tr class="hover:bg-gray-50/50 transition-colors" x-show="!isLoading"
                                :class="product._selected ? 'bg-[#f0fdf4]' : ''">
                                <td class="px-5 py-3">
                                    <input type="checkbox" x-model="product._selected"
                                        class="rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] w-4 h-4 cursor-pointer">
                                </td>
                                <td class="px-4 py-3 text-center text-gray-400 font-bold text-xs"
                                    x-text="((pagination.current_page - 1) * pagination.per_page) + index + 1"></td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-[#212538] text-[13px]" x-text="product.name"></div>
                                    <div class="text-[11px] text-gray-400 mt-0.5"
                                        x-show="product.attributes && product.attributes.length > 0"
                                        x-text="formatAttrs(product.attributes)"></div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span x-show="product.sku"
                                        class="font-mono text-[11px] text-blue-600 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md font-bold tracking-wide"
                                        x-text="product.sku"></span>
                                    <span x-show="!product.sku"
                                        class="font-mono text-[11px] text-gray-400 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-md">-</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 font-medium" x-text="product.category_name">
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800 text-[13px]"
                                    x-text="'₹' + parseFloat(product.display_price).toFixed(2)"></td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" min="1" max="99" x-model.number="product._copies"
                                        class="w-16 border border-gray-200 rounded-md px-2 py-1 text-center text-xs font-bold outline-none focus:border-[#108c2a] bg-gray-50 focus:bg-white">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div @click="triggerPrint(true, product)" title="Click to Quick Preview"
                                        class="w-14 h-10 border border-gray-200 rounded mx-auto overflow-hidden bg-white p-1 hover:border-[#108c2a] transition-colors cursor-pointer shadow-sm hover:shadow">
                                        <img :src="getLabelUrl(product.label_value, 80)"
                                            class="w-full h-full object-contain">
                                    </div>
                                </td>
                            </tr>
                        </template>

                    </tbody>
                </table>
            </div>

            
            <div class="md:hidden divide-y divide-gray-50 border-t border-gray-50 bg-white">
                
                
                <div x-show="isLoading" x-cloak class="text-center py-16">
                    <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-[#108c2a] mx-auto mb-3"></i>
                    <p class="text-gray-500 font-medium text-sm">Loading products...</p>
                </div>

                
                <div x-show="!isLoading && products.length === 0" x-cloak class="text-center py-16">
                    <i data-lucide="package-x" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-gray-500 font-medium text-sm">No products found.</p>
                </div>

                
                <template x-for="(product, index) in products" :key="product.unique_id">
                    <div class="p-4 hover:bg-gray-50/50 transition-colors flex flex-col gap-3" 
                         x-show="!isLoading" 
                         :class="product._selected ? 'bg-[#f0fdf4]' : ''">
                        
                        
                        <div class="flex items-start gap-3">
                            <div class="pt-0.5">
                                <input type="checkbox" x-model="product._selected"
                                    class="rounded border-gray-300 text-[#108c2a] focus:ring-[#108c2a] w-4 h-4 cursor-pointer">
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-[#212538] text-[13.5px] leading-tight" x-text="product.name"></div>
                                <div class="text-[11px] text-gray-500 mt-0.5" x-show="product.attributes && product.attributes.length > 0" x-text="formatAttrs(product.attributes)"></div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-black text-gray-800 text-[14px]" x-text="'₹' + parseFloat(product.display_price).toFixed(2)"></div>
                            </div>
                        </div>

                        
                        <div class="flex flex-wrap items-center gap-2 pl-7">
                            <span x-show="product.sku" class="font-mono text-[10px] text-blue-600 bg-blue-50 border border-blue-100 px-1.5 py-0.5 rounded font-bold tracking-wide" x-text="product.sku"></span>
                            <span x-show="!product.sku" class="font-mono text-[10px] text-gray-400 bg-gray-50 border border-gray-200 px-1.5 py-0.5 rounded">-</span>
                            <span class="text-[10px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded" x-text="product.category_name"></span>
                        </div>

                        
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100/50 pl-7 mt-1">
                            <div class="flex items-center gap-2">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Copies:</label>
                                <input type="number" min="1" max="99" x-model.number="product._copies"
                                    class="w-16 border border-gray-200 rounded-md px-2 py-1 text-center text-xs font-bold outline-none focus:border-[#108c2a] bg-gray-50 focus:bg-white transition-colors shadow-inner">
                            </div>
                            
                            <div @click="triggerPrint(true, product)" title="Click to Quick Preview"
                                class="w-16 h-10 border border-gray-200 rounded overflow-hidden bg-white p-1 hover:border-[#108c2a] transition-colors cursor-pointer shadow-sm hover:shadow shrink-0">
                                <img :src="getLabelUrl(product.label_value, 80)" class="w-full h-full object-contain">
                            </div>
                        </div>

                    </div>
                </template>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between"
                x-show="!isLoading && pagination.total_pages > 1" x-cloak>
                <span class="text-sm text-gray-500 font-medium"
                    x-text="`Showing ${products.length} of ${pagination.total} SKUs`"></span>
                <div class="flex gap-1">
                    <button @click="fetchProducts(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                        class="px-3 py-1 border border-gray-200 rounded text-sm disabled:opacity-50 bg-white hover:bg-gray-100 font-medium">Prev</button>
                    <span class="px-3 py-1 text-sm font-bold text-gray-700"
                        x-text="`Page ${pagination.current_page} of ${pagination.total_pages}`"></span>
                    <button @click="fetchProducts(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.total_pages"
                        class="px-3 py-1 border border-gray-200 rounded text-sm disabled:opacity-50 bg-white hover:bg-gray-100 font-medium">Next</button>
                </div>
            </div>
        </div>

    </div>

    <template id="print-template">
        <!DOCTYPE html>
        <html>

        <head>
            <title>Label Print</title>
            <style>
                /* Reset & Base Setup */
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                }

                body {
                    background: #f3f4f6; /* Gray background for screen preview */
                    padding: 20px;
                }

                /* Print-Specific Rules */
                @media print {
                    body {
                        background: #fff;
                        padding: 0;
                        -webkit-print-color-adjust: exact;
                    }
                    /* Forces standard A4 or Thermal padding without browser defaults */
                    @page {
                        margin: __PADDING__; 
                    }
                }

                /* The Grid Container (Handles both A4 and Thermal) */
                .label-container {
                    display: grid;
                    /* For A4: Usually 3 columns. Thermal will naturally stay 1 column if width is small */
                    grid-template-columns: repeat(auto-fit, minmax(__LABEL_WIDTH__, 1fr));
                    gap: 12px;
                    justify-content: center;
                }

                /* Individual Label Box */
                .label {
                    background: #fff;
                    border: __BORDER__;
                    border-radius: 4px;
                    padding: 10px;
                    text-align: center;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    align-items: center;
                    
                    /* CRITICAL: Prevents the printer from cutting a label in half */
                    break-inside: avoid;
                    page-break-inside: avoid;
                    
                    /* Fixed sizing to ensure alignment */
                    width: __LABEL_WIDTH__;
                    height: auto;
                    min-height: 120px;
                    overflow: hidden;
                }

                /* Typography & Hierarchy */
                .store-name {
                    font-size: 9px;
                    font-weight: 700;
                    color: #6b7280;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 4px;
                }

                .product-name {
                    font-size: 12px;
                    font-weight: 800;
                    color: #111827;
                    line-height: 1.2;
                    /* Truncate long names to 2 lines max */
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }

                .variant-name {
                    font-size: 10px;
                    font-weight: 600;
                    color: #4b5563;
                    margin-top: 2px;
                }

                .barcode-wrapper {
                    margin: 6px 0;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    width: 100%;
                }

                .barcode-img {
                    height: __IMAGE_PX__px;
                    max-width: 90%;
                    object-fit: contain;
                }

                /* 🌟 This displays the actual encoded number (Barcode or Fallback SKU) */
                .barcode-number {
                    font-size: 10px;
                    font-family: monospace;
                    letter-spacing: 1.5px;
                    margin-top: 3px;
                    color: #000;
                }

                .price-wrapper {
                    margin-top: 4px;
                }

                .price {
                    font-size: 16px; /* Can be overridden by JS config */
                    font-weight: 900;
                    color: #000;
                }
            </style>
            </head>
            <body>
                <div class="label-container">
                    __BODY__
                </div>
            </body>

        </html>
    </template>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        window.LABEL_APP_GLOBALS = {
            storeName: <?php echo json_encode($storeName ?? '', 15, 512) ?>,
            renderRoute: "<?php echo e(route('admin.labels.render-image')); ?>",
            searchRoute: "<?php echo e(route('admin.labels.fetch-products')); ?>"
        };
    </script>

    
        <script>
            function labelGenerator() {
                return {
                    labelType: 'barcode',
                    search: '',
                    categoryId: '',
                    products: [],
                    isLoading: false,
                    storeName: window.LABEL_APP_GLOBALS.storeName,
                    pagination: {
                        current_page: 1,
                        total_pages: 1,
                        total: 0,
                        per_page: 30
                    },

                    cfg: {
                        pageSize: 'thermal_2x1',
                        showStore: true,
                        showName: true,
                        showPrice: true,
                        showBorder: false,
                        fontSize: 12
                    },
                    globalCopy: 1,

                    get selectedCount() {
                        return this.products.filter(p => p._selected).length;
                    },

                    pageSizes: {
                        thermal_2x1: {
                            bodyW: '2in',
                            bodyH: '1in',
                            labelW: '1.8in',
                            padding: '2mm',
                            imgPx: 60
                        },
                        thermal_3x2: {
                            bodyW: '3in',
                            bodyH: '2in',
                            labelW: '2.8in',
                            padding: '3mm',
                            imgPx: 80
                        },
                        thermal_4x3: {
                            bodyW: '4in',
                            bodyH: '3in',
                            labelW: '3.8in',
                            padding: '4mm',
                            imgPx: 100
                        },
                        a5: {
                            bodyW: '148mm',
                            bodyH: '210mm',
                            labelW: '200px',
                            padding: '8mm',
                            imgPx: 70
                        },
                        a4: {
                            bodyW: '210mm',
                            bodyH: '297mm',
                            labelW: '240px',
                            padding: '10mm',
                            imgPx: 70
                        },
                    },

                    init() {
                        this.fetchProducts(1);
                    },

                    setLabelType(type) {
                        this.labelType = type;
                    },

                    selectAll(status) {
                        this.products.forEach(p => p._selected = status);
                    },

                    applyGlobalCopy() {
                        let n = Math.max(1, Math.min(99, parseInt(this.globalCopy) || 1));
                        this.globalCopy = n;
                        this.products.forEach(p => p._copies = n);
                    },

                    resetFilters() {
                        this.search = '';
                        this.categoryId = '';
                        this.fetchProducts(1);
                    },

                    getLabelUrl(value, size) {
                        const baseUrl = window.LABEL_APP_GLOBALS.renderRoute;
                        return `${baseUrl}?type=${this.labelType}&value=${encodeURIComponent(value)}&size=${size}`;
                    },

                    /**
                     * Format structured attributes for display.
                     * 1 attr  → "Color: Red"
                     * N attrs → "Size: L, Color: Red"
                     * none    → "" (falsy — callers use this to skip rendering)
                     */
                    formatAttrs(attrs) {
                        if (!attrs || attrs.length === 0) return '';
                        return attrs.map(a => `${a.name}: ${a.value}`).join(', ');
                    },

                    async fetchProducts(page) {
                        this.isLoading = true;

                        const stateMemory = {};
                        this.products.forEach(p => {
                            stateMemory[p.unique_id] = {
                                sel: p._selected,
                                cop: p._copies
                            };
                        });

                        try {
                            const url = new URL(window.LABEL_APP_GLOBALS.searchRoute, window.location.origin);
                            url.searchParams.append('page', page);
                            url.searchParams.append('per_page', this.pagination.per_page);
                            url.searchParams.append('search', this.search);
                            url.searchParams.append('category_id', this.categoryId);

                            const res = await fetch(url);
                            const json = await res.json();

                            if (json.status === 'success') {
                                this.products = json.data.map(p => ({
                                    ...p,
                                    _selected: stateMemory[p.unique_id] ? stateMemory[p.unique_id].sel : false,
                                    _copies: stateMemory[p.unique_id] ? stateMemory[p.unique_id].cop : 1
                                }));
                                this.pagination = json.meta;
                            }
                        } catch (err) {
                            console.error(err);
                            if (typeof BizAlert !== 'undefined') BizAlert.toast('Failed to fetch labels', 'error');
                        } finally {
                            this.isLoading = false;
                            setTimeout(() => {
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            }, 50);
                        }
                    },

                    triggerPrint(isPreview = false, singleProduct = null) {
                        // If a single product is passed, put it in an array. Otherwise, get checked items.
                        const selected = singleProduct ? [singleProduct] : this.products.filter(p => p._selected);

                        if (selected.length === 0) {
                            if (typeof BizAlert !== 'undefined') BizAlert.toast('Please select at least one product.',
                                'warning');
                            else alert('Please select at least one product.');
                            return;
                        }

                        const ps = this.pageSizes[this.cfg.pageSize];
                        let labelsHtml = '';

                        selected.forEach(p => {
                            const imgUrl = this.getLabelUrl(p.label_value, ps.imgPx);
                            const price = p.display_price ? `₹${parseFloat(p.display_price).toFixed(2)}` : '';
                            let varText = this.formatAttrs(p.attributes);

                           for (let i = 0; i < p._copies; i++) {
                                // We use p.label_value here. If barcode is empty, the backend already set this to the SKU!
                                labelsHtml += `
                                <div class="label">
                                    ${this.cfg.showStore ? `<div class="store-name">${this.storeName}</div>` : ''}
                                    
                                    <div>
                                        ${this.cfg.showName ? `<div class="product-name">${p.name}</div>` : ''}
                                        ${varText ? `<div class="variant-name">${varText}</div>` : ''}
                                    </div>

                                    <div class="barcode-wrapper">
                                        <img class="barcode-img" src="${imgUrl}" />
                                        <div class="barcode-number">${p.label_value}</div>
                                    </div>

                                    ${this.cfg.showPrice && price ? `
                                    <div class="price-wrapper">
                                        <span class="price" style="font-size:${this.cfg.fontSize}px">${price}</span>
                                    </div>` : ''}
                                </div>`;
                            }
                        });

                        // 🌟 NEW: Fetch the hidden HTML template and inject our data
                        let popupHtml = document.getElementById('print-template').innerHTML;

                        popupHtml = popupHtml.replace(/__PADDING__/g, ps.padding);
                        popupHtml = popupHtml.replace(/__LABEL_WIDTH__/g, ps.labelW);
                        popupHtml = popupHtml.replace(/__IMAGE_PX__/g, ps.imgPx);
                        popupHtml = popupHtml.replace(/__BORDER__/g, this.cfg.showBorder ? '1px solid #ccc' : 'none');
                        popupHtml = popupHtml.replace('__BODY__', labelsHtml);

                        const pw = window.open('', '_blank', 'width=900,height=700');
                        if (!pw) {
                            if (typeof BizAlert !== 'undefined') BizAlert.toast('Popup blocked! Please allow popups.', 'error');
                            else alert('Popup blocked! Please allow popups.');
                            return;
                        }

                        pw.document.write(popupHtml);
                        pw.document.close();

                        if (!isPreview) {
                            pw.onload = () => {
                                setTimeout(() => {
                                    pw.focus();
                                    pw.print();
                                    pw.onafterprint = () => pw.close();
                                }, 500);
                            };
                        }
                    }
                }
            }
        </script>
    
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/products/labels.blade.php ENDPATH**/ ?>