<?php $__env->startSection('title', 'Bulk Import'); ?>

<?php $__env->startSection('header-title'); ?>
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Bulk Import</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] { display: none !important; }
    .filter-input { border: 1.5px solid #e5e7eb; border-radius: 9px; padding: 7px 10px; font-size: 12px; color: #374151; outline: none; background: #fff; font-family: inherit; transition: border-color 150ms; }
    .filter-input:focus { border-color: var(--brand-600); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="w-full" x-data="bulkImport({ productLimit: <?php echo e($productLimit ?? 'null'); ?>, productCount: <?php echo e($productCount); ?>, limitExceeded: <?php echo e($productLimitReached ? 'true' : 'false'); ?> })" x-cloak>

    
    <div class="mb-6">
        
        <p class="text-sm text-gray-500 mt-1">Upload CSV files to import data in bulk. Each entity has its own format.</p>
    </div>

    
    <div class="mb-6 border-b border-gray-200 overflow-x-auto no-scrollbar pb-1">
        <template x-for="section in tabSections" :key="section.key">
            <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-3 mb-4 sm:mb-1 last:mb-0 min-w-max">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider shrink-0 sm:w-[130px] pl-1 sm:pl-0" x-text="section.label"></span>
                <div class="flex gap-1 flex-nowrap sm:flex-wrap">
                    <template x-for="tab in tabsInSection(section.key)" :key="tab.key">
                        <button @click="activeTab = tab.key"
                            :class="activeTab === tab.key
                                ? 'border-b-2 text-gray-800 font-black'
                                : 'text-gray-500 hover:text-gray-700 font-bold'"
                            :style="activeTab === tab.key ? 'border-color: var(--brand-600)' : ''"
                            class="px-4 py-2.5 text-[13px] transition-colors"
                            x-text="tab.label">
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    
    <div x-show="activeTab === 'categories'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            
            <div class="p-6" x-show="!importId">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-[15px] font-black text-gray-800">Import Categories</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Upload a CSV with columns: <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">name, slug, parent_slug</code></p>
                    </div>
                    <a href="<?php echo e(route('admin.bulk-import.sample', 'categories')); ?>"
                        target="_blank"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3 h-3"></i> Sample CSV
                    </a>
                </div>

                <?php echo $__env->make('admin.bulk-import._import-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._duplicate-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._dry-run-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-gray-300 transition-colors"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="dragOver = false; handleFileDrop($event)"
                    :class="dragOver ? 'border-blue-400 bg-blue-50' : ''">

                    <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-sm font-bold text-gray-600 mb-1">Drop your CSV file here</p>
                    <p class="text-xs text-gray-400 mb-3">or click to browse</p>

                    <label class="inline-flex items-center gap-2 px-4 py-2 text-[12px] font-bold text-white rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="file-up" class="w-3.5 h-3.5"></i>
                        Choose File
                        <input type="file" accept=".csv" class="hidden" @change="handleFileSelect($event)">
                    </label>
                </div>

                
                <div x-show="selectedFile" x-transition class="mt-4 flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
                    <div class="flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                        <span class="text-sm font-bold text-gray-700" x-text="selectedFile?.name"></span>
                        <span class="text-xs text-gray-400" x-text="formatFileSize(selectedFile?.size)"></span>
                    </div>
                    <button @click="selectedFile = null" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                
                <div x-show="uploadError" x-transition class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-red-600 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="uploadError"></span>
                </div>

                
                <div class="mt-4 flex justify-end" x-show="selectedFile && !uploading">
                    <button @click="startUpload('categories')"
                        class="flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        Start Import
                    </button>
                </div>

                
                <div x-show="uploading" class="mt-4 flex items-center justify-center gap-2 text-gray-500">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="text-sm font-bold">Uploading...</span>
                </div>
            </div>

            
            <div class="p-6" x-show="importId" x-transition>

                
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-[15px] font-black text-gray-800">Importing Categories...</h2>
                        <span class="text-xs font-bold text-gray-500" x-text="progressText"></span>
                    </div>
                    <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300"
                            :style="'width: ' + progressPercent + '%; background: var(--brand-600)'">
                        </div>
                    </div>
                </div>

                
                
                <div x-show="importRunIsDryRun" x-transition class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-indigo-700 flex items-center gap-2">
                    <i data-lucide="flask-conical" class="w-4 h-4 shrink-0"></i>
                    <span>Dry run — validating only. No data will be saved to the database.</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3 mb-5">
                    <div class="bg-gray-50 rounded-lg px-4 py-3 text-center border border-gray-100">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total</p>
                        <p class="text-lg font-black text-gray-800" x-text="totalRows"></p>
                    </div>
                    <div class="bg-sky-50 rounded-lg px-4 py-3 text-center border border-sky-100">
                        <p class="text-[11px] font-bold text-sky-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Create' : 'Created'"></p>
                        <p class="text-lg font-black text-sky-700" x-text="createdRows"></p>
                    </div>
                    <div class="bg-emerald-50 rounded-lg px-4 py-3 text-center border border-emerald-100">
                        <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Update' : 'Updated'"></p>
                        <p class="text-lg font-black text-emerald-700" x-text="updatedRows"></p>
                    </div>
                    <div class="bg-amber-50 rounded-lg px-4 py-3 text-center border border-amber-100">
                        <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Skipped</p>
                        <p class="text-lg font-black text-amber-700" x-text="skippedRows"></p>
                    </div>
                    <div class="bg-red-50 rounded-lg px-4 py-3 text-center border border-red-100">
                        <p class="text-[11px] font-bold text-red-500 uppercase tracking-wider">Failed</p>
                        <p class="text-lg font-black text-red-600" x-text="failedRows"></p>
                    </div>
                </div>

                
                
                <div x-show="chunkError && !done" x-transition class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-[12px] font-bold text-red-600">
                        <i data-lucide="wifi-off" class="w-4 h-4 shrink-0"></i>
                        <span>Processing interrupted. Progress is saved — click Resume to continue from where it stopped.</span>
                    </div>
                    <button @click="processNextChunk(currentType, currentOffset)"
                        class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">
                        <i data-lucide="rotate-cw" class="w-3 h-3"></i>
                        Resume
                    </button>
                </div>

                <div x-show="done" x-transition class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-[14px] font-black text-gray-800">Import Complete</span>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="failedRows > 0">
                            <a :href="'<?php echo e(route('admin.bulk-import.errors', ':id')); ?>'.replace(':id', importId)"
                                target="_blank"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                Download Error Report
                            </a>
                        </template>
                        <button @click="resetState()"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                            New Import
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    
    <div x-show="activeTab === 'units'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            
            <div class="p-6" x-show="!importId">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-[15px] font-black text-gray-800">Import Units</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Upload a CSV with columns: <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">name, short_name</code></p>
                    </div>
                    <a href="<?php echo e(route('admin.bulk-import.sample', 'units')); ?>"
                        target="_blank"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3 h-3"></i> Sample CSV
                    </a>
                </div>

                <?php echo $__env->make('admin.bulk-import._import-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._duplicate-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._dry-run-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-gray-300 transition-colors"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="dragOver = false; handleFileDrop($event)"
                    :class="dragOver ? 'border-blue-400 bg-blue-50' : ''">
                    <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-sm font-bold text-gray-600 mb-1">Drop your CSV file here</p>
                    <p class="text-xs text-gray-400 mb-3">or click to browse</p>
                    <label class="inline-flex items-center gap-2 px-4 py-2 text-[12px] font-bold text-white rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="file-up" class="w-3.5 h-3.5"></i>
                        Choose File
                        <input type="file" accept=".csv" class="hidden" @change="handleFileSelect($event)">
                    </label>
                </div>

                
                <div x-show="selectedFile" x-transition class="mt-4 flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
                    <div class="flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                        <span class="text-sm font-bold text-gray-700" x-text="selectedFile?.name"></span>
                        <span class="text-xs text-gray-400" x-text="formatFileSize(selectedFile?.size)"></span>
                    </div>
                    <button @click="selectedFile = null" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                
                <div x-show="uploadError" x-transition class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-red-600 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="uploadError"></span>
                </div>

                
                <div class="mt-4 flex justify-end" x-show="selectedFile && !uploading">
                    <button @click="startUpload('units')"
                        class="flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        Start Import
                    </button>
                </div>

                
                <div x-show="uploading" class="mt-4 flex items-center justify-center gap-2 text-gray-500">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="text-sm font-bold">Uploading...</span>
                </div>
            </div>

            
            <div class="p-6" x-show="importId" x-transition>
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-[15px] font-black text-gray-800">Importing Units...</h2>
                        <span class="text-xs font-bold text-gray-500" x-text="progressText"></span>
                    </div>
                    <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300"
                            :style="'width: ' + progressPercent + '%; background: var(--brand-600)'"></div>
                    </div>
                </div>
                
                <div x-show="importRunIsDryRun" x-transition class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-indigo-700 flex items-center gap-2">
                    <i data-lucide="flask-conical" class="w-4 h-4 shrink-0"></i>
                    <span>Dry run — validating only. No data will be saved to the database.</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3 mb-5">
                    <div class="bg-gray-50 rounded-lg px-4 py-3 text-center border border-gray-100">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total</p>
                        <p class="text-lg font-black text-gray-800" x-text="totalRows"></p>
                    </div>
                    <div class="bg-sky-50 rounded-lg px-4 py-3 text-center border border-sky-100">
                        <p class="text-[11px] font-bold text-sky-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Create' : 'Created'"></p>
                        <p class="text-lg font-black text-sky-700" x-text="createdRows"></p>
                    </div>
                    <div class="bg-emerald-50 rounded-lg px-4 py-3 text-center border border-emerald-100">
                        <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Update' : 'Updated'"></p>
                        <p class="text-lg font-black text-emerald-700" x-text="updatedRows"></p>
                    </div>
                    <div class="bg-amber-50 rounded-lg px-4 py-3 text-center border border-amber-100">
                        <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Skipped</p>
                        <p class="text-lg font-black text-amber-700" x-text="skippedRows"></p>
                    </div>
                    <div class="bg-red-50 rounded-lg px-4 py-3 text-center border border-red-100">
                        <p class="text-[11px] font-bold text-red-500 uppercase tracking-wider">Failed</p>
                        <p class="text-lg font-black text-red-600" x-text="failedRows"></p>
                    </div>
                </div>
                
                <div x-show="chunkError && !done" x-transition class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-[12px] font-bold text-red-600">
                        <i data-lucide="wifi-off" class="w-4 h-4 shrink-0"></i>
                        <span>Processing interrupted. Progress is saved — click Resume to continue from where it stopped.</span>
                    </div>
                    <button @click="processNextChunk(currentType, currentOffset)"
                        class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">
                        <i data-lucide="rotate-cw" class="w-3 h-3"></i>
                        Resume
                    </button>
                </div>

                <div x-show="done" x-transition class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-[14px] font-black text-gray-800">Import Complete</span>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="failedRows > 0">
                            <a :href="'<?php echo e(route('admin.bulk-import.errors', ':id')); ?>'.replace(':id', importId)"
                                target="_blank"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                Download Error Report
                            </a>
                        </template>
                        <button @click="resetState()"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                            New Import
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    
    <div x-show="activeTab === 'products'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="p-6" x-show="!importId">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                        <div>
                            <h2 class="text-[15px] font-black text-gray-800">Import Products</h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Upload a CSV with columns:
                                <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">name, slug, category_slug, unit, product_type, description</code>
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="<?php echo e(route('admin.bulk-import.sample', 'products')); ?>"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                Sample CSV
                            </a>

                            <a href="<?php echo e(route('admin.bulk-import.export-all')); ?>"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                <i data-lucide="database" class="w-3 h-3"></i>
                                Existing Data
                            </a>
                        </div>
                    </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5 text-[12px] text-amber-800 mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                    <span><strong>Important:</strong> Import categories and units first. Products reference them by <code class="font-mono">category_slug</code> and unit <code class="font-mono">short_name</code>.</span>
                </div>

                <?php echo $__env->make('admin.bulk-import._import-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._duplicate-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._dry-run-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                
                <div x-show="limitExceeded && limitExceededDetails" x-transition
                    class="mb-4 bg-orange-50 border border-orange-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="shield-alert" class="w-5 h-5 shrink-0 text-orange-500 mt-0.5"></i>
                        <div class="flex-1">
                            <p class="text-[13px] font-black text-orange-800 mb-1">Product Limit Exceeded</p>
                            <p class="text-[12px] text-orange-700 leading-snug" x-text="limitExceededDetails?.error"></p>
                            <div class="flex flex-wrap gap-x-5 gap-y-1 mt-2.5">
                                <span class="text-[11px] text-orange-700">Plan limit: <strong x-text="limitExceededDetails?.product_limit"></strong></span>
                                <span class="text-[11px] text-orange-700">Existing products: <strong x-text="limitExceededDetails?.existing_count"></strong></span>
                                <span class="text-[11px] text-orange-700">File would add: <strong x-text="limitExceededDetails?.incoming_new_count"></strong></span>
                                <span class="text-[11px] text-orange-700">Available slots: <strong x-text="limitExceededDetails?.available_slots"></strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div x-show="limitExceeded && !limitExceededDetails" x-transition
                    class="mb-4 bg-orange-50 border border-orange-200 rounded-xl p-4 flex items-center gap-3">
                    <i data-lucide="shield-alert" class="w-5 h-5 shrink-0 text-orange-500"></i>
                    <div>
                        <p class="text-[13px] font-black text-orange-800">Product limit reached</p>
                        <p class="text-[12px] text-orange-700 leading-snug mt-0.5">
                            Your plan allows <strong x-text="productLimit"></strong> products and you currently have <strong x-text="productCount"></strong>. No new products can be imported. Upgrade your plan to continue.
                        </p>
                    </div>
                </div>

                
                <div class="border-2 border-dashed rounded-xl p-8 text-center transition-colors"
                    :class="limitExceeded
                        ? 'border-gray-100 bg-gray-50 opacity-50 pointer-events-none cursor-not-allowed'
                        : (dragOver ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:border-gray-300')"
                    @dragover.prevent="if (!limitExceeded) dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="dragOver = false; if (!limitExceeded) handleFileDrop($event)">
                    <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-sm font-bold text-gray-600 mb-1">Drop your CSV file here</p>
                    <p class="text-xs text-gray-400 mb-3">or click to browse</p>
                    <label class="inline-flex items-center gap-2 px-4 py-2 text-[12px] font-bold text-white rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="file-up" class="w-3.5 h-3.5"></i>
                        Choose File
                        <input type="file" accept=".csv" class="hidden" @change="handleFileSelect($event)">
                    </label>
                </div>

                <div x-show="selectedFile" x-transition class="mt-4 flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
                    <div class="flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                        <span class="text-sm font-bold text-gray-700" x-text="selectedFile?.name"></span>
                        <span class="text-xs text-gray-400" x-text="formatFileSize(selectedFile?.size)"></span>
                    </div>
                    <button @click="selectedFile = null" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div x-show="uploadError" x-transition class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-red-600 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="uploadError"></span>
                </div>

                
                <div class="mt-4 flex justify-end" x-show="selectedFile && !uploading">
                    <template x-if="limitExceeded">
                        <button disabled
                            class="flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-lg opacity-70 cursor-not-allowed bg-orange-400">
                            <i data-lucide="shield-alert" class="w-4 h-4"></i>
                            Product Limit Reached
                        </button>
                    </template>
                    <template x-if="!limitExceeded">
                        <button @click="startUpload('products')"
                            class="flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity"
                            style="background: var(--brand-600)">
                            <i data-lucide="play" class="w-4 h-4"></i>
                            Start Import
                        </button>
                    </template>
                </div>

                <div x-show="uploading" class="mt-4 flex items-center justify-center gap-2 text-gray-500">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="text-sm font-bold">Uploading...</span>
                </div>
            </div>

            <div class="p-6" x-show="importId" x-transition>
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-[15px] font-black text-gray-800">Importing Products...</h2>
                        <span class="text-xs font-bold text-gray-500" x-text="progressText"></span>
                    </div>
                    <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300"
                            :style="'width: ' + progressPercent + '%; background: var(--brand-600)'"></div>
                    </div>
                </div>
                
                <div x-show="importRunIsDryRun" x-transition class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-indigo-700 flex items-center gap-2">
                    <i data-lucide="flask-conical" class="w-4 h-4 shrink-0"></i>
                    <span>Dry run — validating only. No data will be saved to the database.</span>
                </div>

                
                <div x-show="limitSkippedRows > 0" x-transition class="mb-4 bg-orange-50 border border-orange-200 rounded-lg px-4 py-2.5 flex items-center gap-2">
                    <i data-lucide="shield-alert" class="w-4 h-4 shrink-0 text-orange-600"></i>
                    <p class="text-[12px] font-bold text-orange-700">
                        Product limit reached — <span class="font-black" x-text="limitSkippedRows"></span> row(s) skipped. Upgrade your plan to import more products.
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3 mb-5">
                    <div class="bg-gray-50 rounded-lg px-4 py-3 text-center border border-gray-100">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total</p>
                        <p class="text-lg font-black text-gray-800" x-text="totalRows"></p>
                    </div>
                    <div class="bg-sky-50 rounded-lg px-4 py-3 text-center border border-sky-100">
                        <p class="text-[11px] font-bold text-sky-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Create' : 'Created'"></p>
                        <p class="text-lg font-black text-sky-700" x-text="createdRows"></p>
                    </div>
                    <div class="bg-emerald-50 rounded-lg px-4 py-3 text-center border border-emerald-100">
                        <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Update' : 'Updated'"></p>
                        <p class="text-lg font-black text-emerald-700" x-text="updatedRows"></p>
                    </div>
                    <div class="bg-amber-50 rounded-lg px-4 py-3 text-center border border-amber-100">
                        <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Skipped</p>
                        <p class="text-lg font-black text-amber-700" x-text="skippedRows"></p>
                    </div>
                    <div class="bg-red-50 rounded-lg px-4 py-3 text-center border border-red-100">
                        <p class="text-[11px] font-bold text-red-500 uppercase tracking-wider">Failed</p>
                        <p class="text-lg font-black text-red-600" x-text="failedRows"></p>
                    </div>
                </div>
                
                <div x-show="chunkError && !done" x-transition class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-[12px] font-bold text-red-600">
                        <i data-lucide="wifi-off" class="w-4 h-4 shrink-0"></i>
                        <span>Processing interrupted. Progress is saved — click Resume to continue from where it stopped.</span>
                    </div>
                    <button @click="processNextChunk(currentType, currentOffset)"
                        class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">
                        <i data-lucide="rotate-cw" class="w-3 h-3"></i>
                        Resume
                    </button>
                </div>

                <div x-show="done" x-transition class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-[14px] font-black text-gray-800">Import Complete</span>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="failedRows > 0">
                            <a :href="'<?php echo e(route('admin.bulk-import.errors', ':id')); ?>'.replace(':id', importId)"
                                target="_blank"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                Download Error Report
                            </a>
                        </template>
                        <button @click="resetState()"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                            New Import
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    
    <div x-show="activeTab === 'skus'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="p-6" x-show="!importId">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-[15px] font-black text-gray-800">Import Product Variants (SKUs)</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Required: <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">product_slug, price, cost</code> + at least one <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">attribute_1_name, attribute_1_value</code> pair. Optional: <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">sku, barcode, mrp, stock_alert</code> and up to 5 attribute pairs.</p>
                    </div>
                    <a href="<?php echo e(route('admin.bulk-import.sample', 'skus')); ?>"
                        target="_blank"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3 h-3"></i> Sample CSV
                    </a>
                     <!-- Existing Products (IMPORTANT) -->
                    <a href="<?php echo e(route('admin.bulk-import.export', 'products')); ?>"
                        target="_blank"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg border border-green-200 text-green-600 bg-green-50 hover:bg-green-100 transition-colors flex items-center gap-1.5">
                        <i data-lucide="package" class="w-3 h-3"></i>
                        Product Slugs
                    </a>

                    <!-- Existing Warehouses -->
                    <a href="<?php echo e(route('admin.bulk-import.export', 'warehouses')); ?>"
                        target="_blank"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg border border-purple-200 text-purple-600 bg-purple-50 hover:bg-purple-100 transition-colors flex items-center gap-1.5">
                        <i data-lucide="warehouse" class="w-3 h-3"></i>
                        Warehouses
                    </a>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5 text-[12px] text-amber-800 mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                    <span><strong>Important:</strong> The product (linked by <code class="font-mono">product_slug</code>) must already exist and be of type <strong>Variable</strong>. Attributes + values will be auto-created if missing. Duplicate variant combinations are rejected. Max 100 variants per product.</span>
                </div>

                <?php echo $__env->make('admin.bulk-import._import-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._duplicate-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._dry-run-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-gray-300 transition-colors"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="dragOver = false; handleFileDrop($event)"
                    :class="dragOver ? 'border-blue-400 bg-blue-50' : ''">
                    <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-sm font-bold text-gray-600 mb-1">Drop your CSV file here</p>
                    <p class="text-xs text-gray-400 mb-3">or click to browse</p>
                    <label class="inline-flex items-center gap-2 px-4 py-2 text-[12px] font-bold text-white rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="file-up" class="w-3.5 h-3.5"></i>
                        Choose File
                        <input type="file" accept=".csv" class="hidden" @change="handleFileSelect($event)">
                    </label>
                </div>

                <div x-show="selectedFile" x-transition class="mt-4 flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
                    <div class="flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                        <span class="text-sm font-bold text-gray-700" x-text="selectedFile?.name"></span>
                        <span class="text-xs text-gray-400" x-text="formatFileSize(selectedFile?.size)"></span>
                    </div>
                    <button @click="selectedFile = null" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div x-show="uploadError" x-transition class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-red-600 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="uploadError"></span>
                </div>

                <div class="mt-4 flex justify-end" x-show="selectedFile && !uploading">
                    <button @click="startUpload('skus')"
                        class="flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        Start Import
                    </button>
                </div>

                <div x-show="uploading" class="mt-4 flex items-center justify-center gap-2 text-gray-500">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="text-sm font-bold">Uploading...</span>
                </div>
            </div>

            <div class="p-6" x-show="importId" x-transition>
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-[15px] font-black text-gray-800">Importing SKUs...</h2>
                        <span class="text-xs font-bold text-gray-500" x-text="progressText"></span>
                    </div>
                    <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300"
                            :style="'width: ' + progressPercent + '%; background: var(--brand-600)'"></div>
                    </div>
                </div>
                
                <div x-show="importRunIsDryRun" x-transition class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-indigo-700 flex items-center gap-2">
                    <i data-lucide="flask-conical" class="w-4 h-4 shrink-0"></i>
                    <span>Dry run — validating only. No data will be saved to the database.</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3 mb-5">
                    <div class="bg-gray-50 rounded-lg px-4 py-3 text-center border border-gray-100">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total</p>
                        <p class="text-lg font-black text-gray-800" x-text="totalRows"></p>
                    </div>
                    <div class="bg-sky-50 rounded-lg px-4 py-3 text-center border border-sky-100">
                        <p class="text-[11px] font-bold text-sky-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Create' : 'Created'"></p>
                        <p class="text-lg font-black text-sky-700" x-text="createdRows"></p>
                    </div>
                    <div class="bg-emerald-50 rounded-lg px-4 py-3 text-center border border-emerald-100">
                        <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Update' : 'Updated'"></p>
                        <p class="text-lg font-black text-emerald-700" x-text="updatedRows"></p>
                    </div>
                    <div class="bg-amber-50 rounded-lg px-4 py-3 text-center border border-amber-100">
                        <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Skipped</p>
                        <p class="text-lg font-black text-amber-700" x-text="skippedRows"></p>
                    </div>
                    <div class="bg-red-50 rounded-lg px-4 py-3 text-center border border-red-100">
                        <p class="text-[11px] font-bold text-red-500 uppercase tracking-wider">Failed</p>
                        <p class="text-lg font-black text-red-600" x-text="failedRows"></p>
                    </div>
                </div>
                
                <div x-show="chunkError && !done" x-transition class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-[12px] font-bold text-red-600">
                        <i data-lucide="wifi-off" class="w-4 h-4 shrink-0"></i>
                        <span>Processing interrupted. Progress is saved — click Resume to continue from where it stopped.</span>
                    </div>
                    <button @click="processNextChunk(currentType, currentOffset)"
                        class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">
                        <i data-lucide="rotate-cw" class="w-3 h-3"></i>
                        Resume
                    </button>
                </div>

                <div x-show="done" x-transition class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-[14px] font-black text-gray-800">Import Complete</span>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="failedRows > 0">
                            <a :href="'<?php echo e(route('admin.bulk-import.errors', ':id')); ?>'.replace(':id', importId)"
                                target="_blank"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                Download Error Report
                            </a>
                        </template>
                        <button @click="resetState()"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                            New Import
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

        
    <div x-show="activeTab === 'product_images'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            
            <div class="p-6" x-show="!importId">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-[15px] font-black text-gray-800">Import Product Images</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Upload a ZIP (max 20 MB) containing images named <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">slug-1.jpg</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">slug-2.png</code>. Supported: jpg, jpeg, png, webp.
                        </p>
                    </div>
                    <a href="<?php echo e(route('admin.bulk-import.product-images.guide')); ?>" target="_blank"
                        class="shrink-0 inline-flex items-center gap-2 px-4 py-2 text-[12px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors whitespace-nowrap">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        Download Naming Guide (.xlsx)
                    </a>
                </div>

                
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-4">
                    <p class="text-[12px] font-bold text-blue-800 mb-1.5 flex items-center gap-1.5">
                        <i data-lucide="lightbulb" class="w-3.5 h-3.5"></i>
                        Not sure how to name your images?
                    </p>
                    <p class="text-[11px] text-blue-700 leading-relaxed">
                        Click <strong>"Download Naming Guide"</strong> above to get an Excel file with all your product slugs pre-filled and example filenames ready to copy.
                        Simply rename your image files to match, zip them, and upload.
                    </p>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5 text-[12px] text-amber-800 mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                    <span><strong>Important:</strong> Import products first. Each filename must start with the product <code class="font-mono">slug</code> and end with a sequence number (e.g. <code class="font-mono">aloe-vera-1.jpg</code>). <code class="font-mono">slug-1</code> becomes the primary image.</span>
                </div>

                <?php echo $__env->make('admin.bulk-import._import-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-gray-300 transition-colors"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="dragOver = false; handleZipDrop($event)"
                    :class="dragOver ? 'border-blue-400 bg-blue-50' : ''">
                    <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-sm font-bold text-gray-600 mb-1">Drop your ZIP file here</p>
                    <p class="text-xs text-gray-400 mb-3">or click to browse (max 20 MB)</p>
                    <label class="inline-flex items-center gap-2 px-4 py-2 text-[12px] font-bold text-white rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="file-archive" class="w-3.5 h-3.5"></i>
                        Choose ZIP
                        <input type="file" accept=".zip,application/zip,application/x-zip-compressed" class="hidden" @change="handleZipSelect($event)">
                    </label>
                </div>

                
                <div x-show="selectedFile" x-transition class="mt-4 flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
                    <div class="flex items-center gap-2">
                        <i data-lucide="file-archive" class="w-4 h-4 text-emerald-600"></i>
                        <span class="text-sm font-bold text-gray-700" x-text="selectedFile?.name"></span>
                        <span class="text-xs text-gray-400" x-text="formatFileSize(selectedFile?.size)"></span>
                    </div>
                    <button @click="selectedFile = null" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                
                <div x-show="uploadError" x-transition class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-red-600 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="uploadError"></span>
                </div>

                
                <div class="mt-4 flex justify-end" x-show="selectedFile && !uploading">
                    <button @click="startUpload('product-images')"
                        class="flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        Start Import
                    </button>
                </div>

                <div x-show="uploading" class="mt-4 flex items-center justify-center gap-2 text-gray-500">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="text-sm font-bold">Extracting ZIP...</span>
                </div>
            </div>

            
            <div class="p-6" x-show="importId" x-transition>
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-[15px] font-black text-gray-800">Importing Product Images...</h2>
                        <span class="text-xs font-bold text-gray-500" x-text="progressText"></span>
                    </div>
                    <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300"
                            :style="'width: ' + progressPercent + '%; background: var(--brand-600)'"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3 mb-5">
                    <div class="bg-gray-50 rounded-lg px-4 py-3 text-center border border-gray-100">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total</p>
                        <p class="text-lg font-black text-gray-800" x-text="totalRows"></p>
                    </div>
                    <div class="bg-sky-50 rounded-lg px-4 py-3 text-center border border-sky-100">
                        <p class="text-[11px] font-bold text-sky-600 uppercase tracking-wider">Created</p>
                        <p class="text-lg font-black text-sky-700" x-text="createdRows"></p>
                    </div>
                    <div class="bg-emerald-50 rounded-lg px-4 py-3 text-center border border-emerald-100">
                        <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Updated</p>
                        <p class="text-lg font-black text-emerald-700" x-text="updatedRows"></p>
                    </div>
                    <div class="bg-amber-50 rounded-lg px-4 py-3 text-center border border-amber-100">
                        <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Skipped</p>
                        <p class="text-lg font-black text-amber-700" x-text="skippedRows"></p>
                    </div>
                    <div class="bg-red-50 rounded-lg px-4 py-3 text-center border border-red-100">
                        <p class="text-[11px] font-bold text-red-500 uppercase tracking-wider">Failed</p>
                        <p class="text-lg font-black text-red-600" x-text="failedRows"></p>
                    </div>
                </div>

                <div x-show="chunkError && !done" x-transition class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-[12px] font-bold text-red-600">
                        <i data-lucide="wifi-off" class="w-4 h-4 shrink-0"></i>
                        <span>Processing interrupted. Progress is saved — click Resume to continue from where it stopped.</span>
                    </div>
                    <button @click="processNextChunk(currentType, currentOffset)"
                        class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">
                        <i data-lucide="rotate-cw" class="w-3 h-3"></i>
                        Resume
                    </button>
                </div>

                <div x-show="done" x-transition class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-[14px] font-black text-gray-800">Import Complete</span>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="failedRows > 0 || skippedRows > 0">
                            <a :href="'<?php echo e(route('admin.bulk-import.errors', ':id')); ?>'.replace(':id', importId)"
                                target="_blank"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                Download Error Report
                            </a>
                        </template>
                        <button @click="resetState()"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                            New Import
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    
    <div x-show="activeTab === 'clients'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            
            <div class="p-6" x-show="!importId">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-[15px] font-black text-gray-800">Import Clients</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Columns: <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">name, company_name, phone, email, gst_number, registration_type, address, city, state, zip_code, country, notes</code></p>
                    </div>
                    <a href="<?php echo e(route('admin.bulk-import.sample', 'clients')); ?>"
                        target="_blank"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3 h-3"></i> Sample CSV
                    </a>
                </div>

                
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-2.5 text-[12px] text-emerald-800 mb-4 flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                    <span><strong>Only Name is required.</strong> Other fields are optional. Rows with bad data will be skipped — you'll see a clear reason in the error report.</span>
                </div>

                <?php echo $__env->make('admin.bulk-import._import-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._duplicate-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._dry-run-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-gray-300 transition-colors"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="dragOver = false; handleFileDrop($event)"
                    :class="dragOver ? 'border-blue-400 bg-blue-50' : ''">
                    <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-sm font-bold text-gray-600 mb-1">Drop your CSV file here</p>
                    <p class="text-xs text-gray-400 mb-3">or click to browse</p>
                    <label class="inline-flex items-center gap-2 px-4 py-2 text-[12px] font-bold text-white rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="file-up" class="w-3.5 h-3.5"></i>
                        Choose File
                        <input type="file" accept=".csv" class="hidden" @change="handleFileSelect($event)">
                    </label>
                </div>

                <div x-show="selectedFile" x-transition class="mt-4 flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
                    <div class="flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                        <span class="text-sm font-bold text-gray-700" x-text="selectedFile?.name"></span>
                        <span class="text-xs text-gray-400" x-text="formatFileSize(selectedFile?.size)"></span>
                    </div>
                    <button @click="selectedFile = null" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div x-show="uploadError" x-transition class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-red-600 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="uploadError"></span>
                </div>

                <div class="mt-4 flex justify-end" x-show="selectedFile && !uploading">
                    <button @click="startUpload('clients')"
                        class="flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        Start Import
                    </button>
                </div>

                <div x-show="uploading" class="mt-4 flex items-center justify-center gap-2 text-gray-500">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="text-sm font-bold">Uploading...</span>
                </div>
            </div>

            <div class="p-6" x-show="importId" x-transition>
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-[15px] font-black text-gray-800">Importing Clients...</h2>
                        <span class="text-xs font-bold text-gray-500" x-text="progressText"></span>
                    </div>
                    <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300"
                            :style="'width: ' + progressPercent + '%; background: var(--brand-600)'"></div>
                    </div>
                </div>

                <div x-show="importRunIsDryRun" x-transition class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-indigo-700 flex items-center gap-2">
                    <i data-lucide="flask-conical" class="w-4 h-4 shrink-0"></i>
                    <span>Dry run — validating only. No data will be saved to the database.</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3 mb-5">
                    <div class="bg-gray-50 rounded-lg px-4 py-3 text-center border border-gray-100">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total</p>
                        <p class="text-lg font-black text-gray-800" x-text="totalRows"></p>
                    </div>
                    <div class="bg-sky-50 rounded-lg px-4 py-3 text-center border border-sky-100">
                        <p class="text-[11px] font-bold text-sky-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Create' : 'Created'"></p>
                        <p class="text-lg font-black text-sky-700" x-text="createdRows"></p>
                    </div>
                    <div class="bg-emerald-50 rounded-lg px-4 py-3 text-center border border-emerald-100">
                        <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Update' : 'Updated'"></p>
                        <p class="text-lg font-black text-emerald-700" x-text="updatedRows"></p>
                    </div>
                    <div class="bg-amber-50 rounded-lg px-4 py-3 text-center border border-amber-100">
                        <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Skipped</p>
                        <p class="text-lg font-black text-amber-700" x-text="skippedRows"></p>
                    </div>
                    <div class="bg-red-50 rounded-lg px-4 py-3 text-center border border-red-100">
                        <p class="text-[11px] font-bold text-red-500 uppercase tracking-wider">Failed</p>
                        <p class="text-lg font-black text-red-600" x-text="failedRows"></p>
                    </div>
                </div>

                <div x-show="chunkError && !done" x-transition class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-[12px] font-bold text-red-600">
                        <i data-lucide="wifi-off" class="w-4 h-4 shrink-0"></i>
                        <span>Processing interrupted. Progress is saved — click Resume to continue from where it stopped.</span>
                    </div>
                    <button @click="processNextChunk(currentType, currentOffset)"
                        class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">
                        <i data-lucide="rotate-cw" class="w-3 h-3"></i>
                        Resume
                    </button>
                </div>

                <div x-show="done" x-transition class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-[14px] font-black text-gray-800">Import Complete</span>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="failedRows > 0">
                            <a :href="'<?php echo e(route('admin.bulk-import.errors', ':id')); ?>'.replace(':id', importId)"
                                target="_blank"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                Download Error Report
                            </a>
                        </template>
                        <button @click="resetState()"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                            New Import
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    
    <div x-show="activeTab === 'suppliers'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="p-6" x-show="!importId">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-[15px] font-black text-gray-800">Import Suppliers</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Columns: <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">name, phone, email, gstin, pan, registration_type, address, city, state, pincode, credit_days, credit_limit, notes</code></p>
                    </div>
                    <a href="<?php echo e(route('admin.bulk-import.sample', 'suppliers')); ?>"
                        target="_blank"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3 h-3"></i> Sample CSV
                    </a>
                </div>

                
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-2.5 text-[12px] text-emerald-800 mb-4 flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                    <span><strong>Only Name is required.</strong> Other fields are optional. Duplicates are matched by GSTIN first, then phone.</span>
                </div>

                <?php echo $__env->make('admin.bulk-import._import-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._duplicate-mode-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.bulk-import._dry-run-toggle', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-gray-300 transition-colors"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="dragOver = false; handleFileDrop($event)"
                    :class="dragOver ? 'border-blue-400 bg-blue-50' : ''">
                    <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-sm font-bold text-gray-600 mb-1">Drop your CSV file here</p>
                    <p class="text-xs text-gray-400 mb-3">or click to browse</p>
                    <label class="inline-flex items-center gap-2 px-4 py-2 text-[12px] font-bold text-white rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="file-up" class="w-3.5 h-3.5"></i>
                        Choose File
                        <input type="file" accept=".csv" class="hidden" @change="handleFileSelect($event)">
                    </label>
                </div>

                <div x-show="selectedFile" x-transition class="mt-4 flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 border border-gray-200">
                    <div class="flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                        <span class="text-sm font-bold text-gray-700" x-text="selectedFile?.name"></span>
                        <span class="text-xs text-gray-400" x-text="formatFileSize(selectedFile?.size)"></span>
                    </div>
                    <button @click="selectedFile = null" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div x-show="uploadError" x-transition class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-red-600 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="uploadError"></span>
                </div>

                <div class="mt-4 flex justify-end" x-show="selectedFile && !uploading">
                    <button @click="startUpload('suppliers')"
                        class="flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        Start Import
                    </button>
                </div>

                <div x-show="uploading" class="mt-4 flex items-center justify-center gap-2 text-gray-500">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="text-sm font-bold">Uploading...</span>
                </div>
            </div>

            <div class="p-6" x-show="importId" x-transition>
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-[15px] font-black text-gray-800">Importing Suppliers...</h2>
                        <span class="text-xs font-bold text-gray-500" x-text="progressText"></span>
                    </div>
                    <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300"
                            :style="'width: ' + progressPercent + '%; background: var(--brand-600)'"></div>
                    </div>
                </div>

                <div x-show="importRunIsDryRun" x-transition class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-indigo-700 flex items-center gap-2">
                    <i data-lucide="flask-conical" class="w-4 h-4 shrink-0"></i>
                    <span>Dry run — validating only. No data will be saved to the database.</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3 mb-5">
                    <div class="bg-gray-50 rounded-lg px-4 py-3 text-center border border-gray-100">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total</p>
                        <p class="text-lg font-black text-gray-800" x-text="totalRows"></p>
                    </div>
                    <div class="bg-sky-50 rounded-lg px-4 py-3 text-center border border-sky-100">
                        <p class="text-[11px] font-bold text-sky-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Create' : 'Created'"></p>
                        <p class="text-lg font-black text-sky-700" x-text="createdRows"></p>
                    </div>
                    <div class="bg-emerald-50 rounded-lg px-4 py-3 text-center border border-emerald-100">
                        <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider" x-text="importRunIsDryRun ? 'Would Update' : 'Updated'"></p>
                        <p class="text-lg font-black text-emerald-700" x-text="updatedRows"></p>
                    </div>
                    <div class="bg-amber-50 rounded-lg px-4 py-3 text-center border border-amber-100">
                        <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Skipped</p>
                        <p class="text-lg font-black text-amber-700" x-text="skippedRows"></p>
                    </div>
                    <div class="bg-red-50 rounded-lg px-4 py-3 text-center border border-red-100">
                        <p class="text-[11px] font-bold text-red-500 uppercase tracking-wider">Failed</p>
                        <p class="text-lg font-black text-red-600" x-text="failedRows"></p>
                    </div>
                </div>

                <div x-show="chunkError && !done" x-transition class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-[12px] font-bold text-red-600">
                        <i data-lucide="wifi-off" class="w-4 h-4 shrink-0"></i>
                        <span>Processing interrupted. Progress is saved — click Resume to continue from where it stopped.</span>
                    </div>
                    <button @click="processNextChunk(currentType, currentOffset)"
                        class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">
                        <i data-lucide="rotate-cw" class="w-3 h-3"></i>
                        Resume
                    </button>
                </div>

                <div x-show="done" x-transition class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-[14px] font-black text-gray-800">Import Complete</span>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="failedRows > 0">
                            <a :href="'<?php echo e(route('admin.bulk-import.errors', ':id')); ?>'.replace(':id', importId)"
                                target="_blank"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                Download Error Report
                            </a>
                        </template>
                        <button @click="resetState()"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                            New Import
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    
    <?php if($imports->isNotEmpty()): ?>
    <div class="mt-8">
        <h3 class="text-[13px] font-black text-gray-500 uppercase tracking-wider mb-3">Recent Imports</h3>
        
        
        <div class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-[11px] font-black text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-2.5">Type</th>
                        <th class="px-4 py-2.5">Date</th>
                        <th class="px-4 py-2.5">Rows</th>
                        <th class="px-4 py-2.5">Success</th>
                        <th class="px-4 py-2.5">Skipped</th>
                        <th class="px-4 py-2.5">Failed</th>
                        <th class="px-4 py-2.5">Import Mode</th>
                        <th class="px-4 py-2.5">Dup Mode</th>
                        <th class="px-4 py-2.5">Status</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__currentLoopData = $imports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $imp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="text-[12px]">
                        <td class="px-4 py-2.5 font-bold text-gray-800 capitalize"><?php echo e($imp->type); ?></td>
                        <td class="px-4 py-2.5 text-gray-500"><?php echo e($imp->created_at->format('d M Y, h:i A')); ?></td>
                        <td class="px-4 py-2.5 text-gray-600 font-bold"><?php echo e($imp->total_rows); ?></td>
                        <td class="px-4 py-2.5 text-emerald-600 font-bold"><?php echo e($imp->success_rows); ?></td>
                        <td class="px-4 py-2.5 text-amber-600 font-bold"><?php echo e($imp->skipped_rows); ?></td>
                        <td class="px-4 py-2.5 text-red-500 font-bold"><?php echo e($imp->failed_rows); ?></td>
                        <td class="px-4 py-2.5 text-gray-600 font-bold"><?php echo e(str_replace('_', ' ', $imp->import_mode ?? '—')); ?></td>
                        <td class="px-4 py-2.5 text-gray-600 font-bold capitalize"><?php echo e($imp->duplicate_mode ?? '—'); ?></td>
                        <td class="px-4 py-2.5">
                            <?php
                                $statusColors = [
                                    'pending'    => 'bg-gray-100 text-gray-600',
                                    'processing' => 'bg-blue-50 text-blue-600',
                                    'completed'  => 'bg-emerald-50 text-emerald-600',
                                    'failed'     => 'bg-red-50 text-red-600',
                                ];
                            ?>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider <?php echo e($statusColors[$imp->status] ?? ''); ?>">
                                <?php echo e($imp->status); ?>

                            </span>
                        </td>
                        <td class="px-4 py-2.5">
                            <?php if($imp->failed_rows > 0): ?>
                                <a href="<?php echo e(route('admin.bulk-import.errors', $imp)); ?>" target="_blank" class="text-red-500 hover:text-red-700 text-[11px] font-bold">
                                    Errors
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        
        <div class="md:hidden divide-y divide-gray-50 border border-gray-100 rounded-xl bg-white shadow-sm">
            <?php $__currentLoopData = $imports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $imp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $statusColors = [
                        'pending'    => 'bg-gray-100 text-gray-600 border-gray-200',
                        'processing' => 'bg-blue-50 text-blue-600 border-blue-200',
                        'completed'  => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                        'failed'     => 'bg-red-50 text-red-600 border-red-200',
                    ];
                ?>
                <div class="p-4 flex flex-col gap-3">
                    
                    
                    <div class="flex justify-between items-start gap-2">
                        <div class="min-w-0">
                            <p class="font-bold text-[14px] text-gray-900 capitalize"><?php echo e($imp->type); ?> Import</p>
                            <p class="text-[11px] text-gray-500 font-medium mt-0.5"><?php echo e($imp->created_at->format('d M Y, h:i A')); ?></p>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-1.5">
                            <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider border <?php echo e($statusColors[$imp->status] ?? ''); ?>">
                                <?php echo e($imp->status); ?>

                            </span>
                            <?php if($imp->failed_rows > 0): ?>
                                <a href="<?php echo e(route('admin.bulk-import.errors', $imp)); ?>" target="_blank" class="text-red-500 hover:text-red-700 text-[10px] font-bold flex items-center gap-1">
                                    <i data-lucide="download" class="w-3 h-3"></i> Errors
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="bg-gray-50/80 rounded-lg p-3 border border-gray-100">
                        <div class="grid grid-cols-4 gap-2 text-center mb-2 pb-2 border-b border-gray-100/60">
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Total</p>
                                <p class="text-[12px] font-bold text-gray-600"><?php echo e($imp->total_rows); ?></p>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Success</p>
                                <p class="text-[12px] font-bold text-emerald-600"><?php echo e($imp->success_rows); ?></p>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Skip</p>
                                <p class="text-[12px] font-bold text-amber-600"><?php echo e($imp->skipped_rows); ?></p>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Fail</p>
                                <p class="text-[12px] font-bold text-red-500"><?php echo e($imp->failed_rows); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-[10px]">
                            <span class="text-gray-500 font-medium">Mode: <strong class="text-gray-700"><?php echo e(str_replace('_', ' ', $imp->import_mode ?? '—')); ?></strong></span>
                            <span class="text-gray-500 font-medium">Dupes: <strong class="text-gray-700 capitalize"><?php echo e($imp->duplicate_mode ?? '—'); ?></strong></span>
                        </div>
                    </div>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

    </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function bulkImport(config = {}) {
    return {
        activeTab: 'categories',
        tabSections: [
            { key: 'inventory', label: 'Inventory Import' },
            { key: 'contacts',  label: 'Contacts Import' },
        ],
        tabs: [
            { key: 'categories', label: 'Categories',    section: 'inventory' },
            { key: 'units',      label: 'Units',         section: 'inventory' },
            { key: 'products',   label: 'Products',      section: 'inventory' },
            { key: 'skus',       label: 'Product SKUs',  section: 'inventory' },
            <?php if(has_permission('images_import')): ?>
                { key: 'product_images', label: 'Product Images', section: 'inventory' },
            <?php endif; ?>
            { key: 'clients',    label: 'Clients',       section: 'contacts' },
            { key: 'suppliers',  label: 'Suppliers',     section: 'contacts' },
        ],

        tabsInSection(sectionKey) {
            return this.tabs.filter(t => t.section === sectionKey);
        },

        // Upload state
        selectedFile: null,
        uploading: false,
        uploadError: '',
        dragOver: false,
        duplicateMode: 'skip', // skip | update | error
        importMode: 'create_or_update', // create_only | update_only | create_or_update
        isDryRun: false,

        // Plan limit state (initialized from server-rendered values)
        productLimit: config.productLimit ?? null,
        productCount: config.productCount ?? 0,
        limitExceeded: config.limitExceeded ?? false,
        limitExceededDetails: null,

        // Processing state
        importId: null,
        currentType: null,
        currentOffset: 0,
        totalRows: 0,
        processedRows: 0,
        successRows: 0,
        failedRows: 0,
        skippedRows: 0,
        createdRows: 0,
        updatedRows: 0,
        limitSkippedRows: 0,
        duplicateCount: 0,
        importRunIsDryRun: false,
        chunkError: false,
        done: false,
        processing: false,

        get progressPercent() {
            if (this.totalRows === 0) return 0;
            return Math.round((this.processedRows / this.totalRows) * 100);
        },

        get progressText() {
            return `${this.processedRows} / ${this.totalRows} (${this.progressPercent}%)`;
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.selectedFile = file;
                this.uploadError = '';
            }
        },

        handleFileDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file && (file.name.endsWith('.csv') || file.type === 'text/csv')) {
                this.selectedFile = file;
                this.uploadError = '';
            } else {
                this.uploadError = 'Please drop a valid CSV file.';
            }
        },

        handleZipSelect(event) {
            const file = event.target.files[0];
            if (file) {
                if (!file.name.toLowerCase().endsWith('.zip')) {
                    this.uploadError = 'Please select a valid ZIP file.';
                    return;
                }
                if (file.size > 20 * 1024 * 1024) {
                    this.uploadError = 'ZIP exceeds 20 MB limit.';
                    return;
                }
                this.selectedFile = file;
                this.uploadError = '';
            }
        },

        handleZipDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file && file.name.toLowerCase().endsWith('.zip')) {
                if (file.size > 20 * 1024 * 1024) {
                    this.uploadError = 'ZIP exceeds 20 MB limit.';
                    return;
                }
                this.selectedFile = file;
                this.uploadError = '';
            } else {
                this.uploadError = 'Please drop a valid ZIP file.';
            }
        },

        formatFileSize(bytes) {
            if (!bytes) return '';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        },

        async startUpload(type) {
            if (!this.selectedFile) return;
            this.uploading = true;
            this.uploadError = '';
            this.limitExceeded = false;
            this.limitExceededDetails = null;

            const formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('import_mode', this.importMode);
            // CSV-only flags — product-images (ZIP) doesn't use them
            if (type !== 'product-images') {
                formData.append('duplicate_mode', this.duplicateMode);
                formData.append('is_dry_run', this.isDryRun ? '1' : '0');
            }

            try {
                const res = await fetch(`/admin/bulk-import/${type}/upload`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData,
                });

                const data = await res.json();

                if (!res.ok) {
                    if (data.limit_exceeded) {
                        this.limitExceeded = true;
                        this.limitExceededDetails = data;
                    } else {
                        this.uploadError = data.error || data.message || 'Upload failed.';
                    }
                    this.uploading = false;
                    return;
                }

                this.importId = data.import_id;
                this.currentType = type;
                this.currentOffset = 0;
                this.totalRows = data.total_rows;
                this.duplicateCount = data.duplicate_count || 0;
                this.importRunIsDryRun = !!data.is_dry_run;
                this.processedRows = 0;
                this.successRows = 0;
                this.failedRows = 0;
                this.skippedRows = 0;
                this.createdRows = 0;
                this.updatedRows = 0;
                this.limitSkippedRows = 0;
                this.chunkError = false;
                this.done = false;
                this.uploading = false;

                // Start chunk processing
                this.processNextChunk(type, 0);

            } catch (e) {
                this.uploadError = 'Network error. Please try again.';
                this.uploading = false;
            }
        },

        async processNextChunk(type, offset) {
            if (this.done) return;
            this.processing = true;
            this.chunkError = false;
            this.currentType = type;
            this.currentOffset = offset;

            try {
                const res = await fetch(`/admin/bulk-import/${type}/process`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ import_id: this.importId, offset }),
                });

                const data = await res.json();

                if (!res.ok) {
                    this.chunkError = true;
                    this.processing = false;
                    return;
                }

                this.processedRows = data.processed;
                this.successRows = data.success;
                this.failedRows = data.failed;
                this.skippedRows = data.skipped || 0;
                this.createdRows = data.created || 0;
                this.updatedRows = data.updated || 0;
                this.limitSkippedRows = data.limit_skipped || 0;
                if (typeof data.is_dry_run !== 'undefined') {
                    this.importRunIsDryRun = !!data.is_dry_run;
                }

                if (data.done) {
                    this.done = true;
                    this.processing = false;
                    this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                } else {
                    // Process next chunk
                    this.processNextChunk(type, data.next_offset);
                }

            } catch (e) {
                this.chunkError = true;
                this.processing = false;
            }
        },

        resetState() {
            this.selectedFile = null;
            this.uploading = false;
            this.uploadError = '';
            this.importId = null;
            this.currentType = null;
            this.currentOffset = 0;
            this.totalRows = 0;
            this.processedRows = 0;
            this.successRows = 0;
            this.failedRows = 0;
            this.skippedRows = 0;
            this.createdRows = 0;
            this.updatedRows = 0;
            this.limitSkippedRows = 0;
            this.duplicateCount = 0;
            this.importRunIsDryRun = false;
            this.chunkError = false;
            this.done = false;
            this.processing = false;
            this.limitExceeded = false;
            this.limitExceededDetails = null;
            // Keep duplicateMode, importMode, and isDryRun so the user's preferences persist across imports
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/bulk-import/index.blade.php ENDPATH**/ ?>