@extends('layouts.admin')

@section('title', 'Bulk Import')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .filter-input { border: 1.5px solid #e5e7eb; border-radius: 9px; padding: 7px 10px; font-size: 12px; color: #374151; outline: none; background: #fff; font-family: inherit; transition: border-color 150ms; }
    .filter-input:focus { border-color: var(--brand-600); }
</style>
@endpush

@section('content')

<div class="w-full" x-data="bulkImport()" x-cloak>

    {{-- Page Header ── --}}
    <div class="mb-6">
        <h1 class="text-xl font-black text-gray-800 tracking-tight">Bulk Import</h1>
        <p class="text-sm text-gray-500 mt-1">Upload CSV files to import data in bulk. Each entity has its own format.</p>
    </div>

    {{-- Tab Navigation ── --}}
    <div class="flex gap-1 mb-6 border-b border-gray-200">
        <template x-for="tab in tabs" :key="tab.key">
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

    {{-- Categories Tab ── --}}
    <div x-show="activeTab === 'categories'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Upload Section ── --}}
            <div class="p-6" x-show="!importId">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-[15px] font-black text-gray-800">Import Categories</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Upload a CSV with columns: <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">name, slug, parent_slug</code></p>
                    </div>
                    <a href="{{ route('admin.bulk-import.sample', 'categories') }}"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3 h-3"></i> Sample CSV
                    </a>
                </div>

                @include('admin.bulk-import._import-mode-selector')

                @include('admin.bulk-import._duplicate-mode-selector')

                @include('admin.bulk-import._dry-run-toggle')

                {{-- Drop Zone ── --}}
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

                {{-- Selected File Info ── --}}
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

                {{-- Upload Error ── --}}
                <div x-show="uploadError" x-transition class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-red-600 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="uploadError"></span>
                </div>

                {{-- Start Import Button ── --}}
                <div class="mt-4 flex justify-end" x-show="selectedFile && !uploading">
                    <button @click="startUpload('categories')"
                        class="flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        Start Import
                    </button>
                </div>

                {{-- Uploading spinner ── --}}
                <div x-show="uploading" class="mt-4 flex items-center justify-center gap-2 text-gray-500">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="text-sm font-bold">Uploading...</span>
                </div>
            </div>

            {{-- Processing Section ── --}}
            <div class="p-6" x-show="importId" x-transition>

                {{-- Progress Bar ── --}}
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

                {{-- Stats Cards ── --}}
                {{-- Dry Run Banner ── --}}
                <div x-show="importRunIsDryRun" x-transition class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-indigo-700 flex items-center gap-2">
                    <i data-lucide="flask-conical" class="w-4 h-4 shrink-0"></i>
                    <span>Dry run — validating only. No data will be saved to the database.</span>
                </div>

                <div class="grid grid-cols-5 gap-3 mb-5">
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

                {{-- Completed State ── --}}
                <div x-show="done" x-transition class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-[14px] font-black text-gray-800">Import Complete</span>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="failedRows > 0">
                            <a :href="'{{ route('admin.bulk-import.errors', ':id') }}'.replace(':id', importId)"
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

    {{-- Units Tab ── --}}
    <div x-show="activeTab === 'units'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Upload Section ── --}}
            <div class="p-6" x-show="!importId">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-[15px] font-black text-gray-800">Import Units</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Upload a CSV with columns: <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">name, short_name</code></p>
                    </div>
                    <a href="{{ route('admin.bulk-import.sample', 'units') }}"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3 h-3"></i> Sample CSV
                    </a>
                </div>

                @include('admin.bulk-import._import-mode-selector')

                @include('admin.bulk-import._duplicate-mode-selector')

                @include('admin.bulk-import._dry-run-toggle')

                {{-- Drop Zone ── --}}
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

                {{-- Selected File Info ── --}}
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

                {{-- Upload Error ── --}}
                <div x-show="uploadError" x-transition class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-red-600 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="uploadError"></span>
                </div>

                {{-- Start Import Button ── --}}
                <div class="mt-4 flex justify-end" x-show="selectedFile && !uploading">
                    <button @click="startUpload('units')"
                        class="flex items-center gap-2 px-5 py-2.5 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        Start Import
                    </button>
                </div>

                {{-- Uploading spinner ── --}}
                <div x-show="uploading" class="mt-4 flex items-center justify-center gap-2 text-gray-500">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="text-sm font-bold">Uploading...</span>
                </div>
            </div>

            {{-- Processing Section (shared) ── --}}
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
                {{-- Dry Run Banner ── --}}
                <div x-show="importRunIsDryRun" x-transition class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-indigo-700 flex items-center gap-2">
                    <i data-lucide="flask-conical" class="w-4 h-4 shrink-0"></i>
                    <span>Dry run — validating only. No data will be saved to the database.</span>
                </div>

                <div class="grid grid-cols-5 gap-3 mb-5">
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
                <div x-show="done" x-transition class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-[14px] font-black text-gray-800">Import Complete</span>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="failedRows > 0">
                            <a :href="'{{ route('admin.bulk-import.errors', ':id') }}'.replace(':id', importId)"
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

    {{-- Products Tab ── --}}
    <div x-show="activeTab === 'products'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="p-6" x-show="!importId">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-[15px] font-black text-gray-800">Import Products</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Upload a CSV with columns: <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">name, slug, category_slug, unit, product_type, description</code></p>
                    </div>
                    <a href="{{ route('admin.bulk-import.sample', 'products') }}"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3 h-3"></i> Sample CSV
                    </a>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5 text-[12px] text-amber-800 mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                    <span><strong>Important:</strong> Import categories and units first. Products reference them by <code class="font-mono">category_slug</code> and unit <code class="font-mono">short_name</code>.</span>
                </div>

                @include('admin.bulk-import._import-mode-selector')

                @include('admin.bulk-import._duplicate-mode-selector')

                @include('admin.bulk-import._dry-run-toggle')

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
                    <button @click="startUpload('products')"
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
                        <h2 class="text-[15px] font-black text-gray-800">Importing Products...</h2>
                        <span class="text-xs font-bold text-gray-500" x-text="progressText"></span>
                    </div>
                    <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300"
                            :style="'width: ' + progressPercent + '%; background: var(--brand-600)'"></div>
                    </div>
                </div>
                {{-- Dry Run Banner ── --}}
                <div x-show="importRunIsDryRun" x-transition class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-indigo-700 flex items-center gap-2">
                    <i data-lucide="flask-conical" class="w-4 h-4 shrink-0"></i>
                    <span>Dry run — validating only. No data will be saved to the database.</span>
                </div>

                <div class="grid grid-cols-5 gap-3 mb-5">
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
                <div x-show="done" x-transition class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-[14px] font-black text-gray-800">Import Complete</span>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="failedRows > 0">
                            <a :href="'{{ route('admin.bulk-import.errors', ':id') }}'.replace(':id', importId)"
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

    {{-- Product SKUs Tab ── --}}
    <div x-show="activeTab === 'skus'">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="p-6" x-show="!importId">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-[15px] font-black text-gray-800">Import Product SKUs</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Upload a CSV with columns: <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">product_slug, sku, price, cost, mrp, barcode, stock_alert</code></p>
                    </div>
                    <a href="{{ route('admin.bulk-import.sample', 'skus') }}"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3 h-3"></i> Sample CSV
                    </a>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5 text-[12px] text-amber-800 mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                    <span><strong>Important:</strong> Products must exist before importing SKUs. Each SKU is linked via <code class="font-mono">product_slug</code>.</span>
                </div>

                @include('admin.bulk-import._import-mode-selector')

                @include('admin.bulk-import._duplicate-mode-selector')

                @include('admin.bulk-import._dry-run-toggle')

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
                {{-- Dry Run Banner ── --}}
                <div x-show="importRunIsDryRun" x-transition class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5 text-[12px] font-bold text-indigo-700 flex items-center gap-2">
                    <i data-lucide="flask-conical" class="w-4 h-4 shrink-0"></i>
                    <span>Dry run — validating only. No data will be saved to the database.</span>
                </div>

                <div class="grid grid-cols-5 gap-3 mb-5">
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
                <div x-show="done" x-transition class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-[14px] font-black text-gray-800">Import Complete</span>
                    </div>
                    <div class="flex gap-2">
                        <template x-if="failedRows > 0">
                            <a :href="'{{ route('admin.bulk-import.errors', ':id') }}'.replace(':id', importId)"
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

    {{-- Recent Imports History ── --}}
    @if($imports->isNotEmpty())
    <div class="mt-8">
        <h3 class="text-[13px] font-black text-gray-500 uppercase tracking-wider mb-3">Recent Imports</h3>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
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
                    @foreach($imports as $imp)
                    <tr class="text-[12px]">
                        <td class="px-4 py-2.5 font-bold text-gray-800 capitalize">{{ $imp->type }}</td>
                        <td class="px-4 py-2.5 text-gray-500">{{ $imp->created_at->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-2.5 text-gray-600 font-bold">{{ $imp->total_rows }}</td>
                        <td class="px-4 py-2.5 text-emerald-600 font-bold">{{ $imp->success_rows }}</td>
                        <td class="px-4 py-2.5 text-amber-600 font-bold">{{ $imp->skipped_rows }}</td>
                        <td class="px-4 py-2.5 text-red-500 font-bold">{{ $imp->failed_rows }}</td>
                        <td class="px-4 py-2.5 text-gray-600 font-bold">{{ str_replace('_', ' ', $imp->import_mode ?? '—') }}</td>
                        <td class="px-4 py-2.5 text-gray-600 font-bold capitalize">{{ $imp->duplicate_mode ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            @php
                                $statusColors = [
                                    'pending'    => 'bg-gray-100 text-gray-600',
                                    'processing' => 'bg-blue-50 text-blue-600',
                                    'completed'  => 'bg-emerald-50 text-emerald-600',
                                    'failed'     => 'bg-red-50 text-red-600',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $statusColors[$imp->status] ?? '' }}">
                                {{ $imp->status }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5">
                            @if($imp->failed_rows > 0)
                                <a href="{{ route('admin.bulk-import.errors', $imp) }}" class="text-red-500 hover:text-red-700 text-[11px] font-bold">
                                    Errors
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function bulkImport() {
    return {
        activeTab: 'categories',
        tabs: [
            { key: 'categories', label: 'Categories' },
            { key: 'units',      label: 'Units' },
            { key: 'products',   label: 'Products' },
            { key: 'skus',       label: 'Product SKUs' },
        ],

        // Upload state
        selectedFile: null,
        uploading: false,
        uploadError: '',
        dragOver: false,
        duplicateMode: 'skip', // skip | update | error
        importMode: 'create_or_update', // create_only | update_only | create_or_update
        isDryRun: false,

        // Processing state
        importId: null,
        totalRows: 0,
        processedRows: 0,
        successRows: 0,
        failedRows: 0,
        skippedRows: 0,
        createdRows: 0,
        updatedRows: 0,
        duplicateCount: 0,
        importRunIsDryRun: false,
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

            const formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('duplicate_mode', this.duplicateMode);
            formData.append('import_mode', this.importMode);
            formData.append('is_dry_run', this.isDryRun ? '1' : '0');

            try {
                const res = await fetch(`/admin/bulk-import/${type}/upload`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData,
                });

                const data = await res.json();

                if (!res.ok) {
                    this.uploadError = data.error || data.message || 'Upload failed.';
                    this.uploading = false;
                    return;
                }

                this.importId = data.import_id;
                this.totalRows = data.total_rows;
                this.duplicateCount = data.duplicate_count || 0;
                this.importRunIsDryRun = !!data.is_dry_run;
                this.processedRows = 0;
                this.successRows = 0;
                this.failedRows = 0;
                this.skippedRows = 0;
                this.createdRows = 0;
                this.updatedRows = 0;
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
                    this.uploadError = data.error || 'Processing failed.';
                    this.processing = false;
                    return;
                }

                this.processedRows = data.processed;
                this.successRows = data.success;
                this.failedRows = data.failed;
                this.skippedRows = data.skipped || 0;
                this.createdRows = data.created || 0;
                this.updatedRows = data.updated || 0;
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
                this.uploadError = 'Network error during processing. You can retry.';
                this.processing = false;
            }
        },

        resetState() {
            this.selectedFile = null;
            this.uploading = false;
            this.uploadError = '';
            this.importId = null;
            this.totalRows = 0;
            this.processedRows = 0;
            this.successRows = 0;
            this.failedRows = 0;
            this.skippedRows = 0;
            this.createdRows = 0;
            this.updatedRows = 0;
            this.duplicateCount = 0;
            this.importRunIsDryRun = false;
            this.done = false;
            this.processing = false;
            // Keep duplicateMode, importMode, and isDryRun so the user's preferences persist across imports
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },
    }
}
</script>
@endpush
