@extends('layouts.admin')

@section('title', 'OCR Scan History')

@section('header-title')
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">OCR Scan History</h1>
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .filter-input {
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 13px;
        color: #1f2937;
        outline: none;
        background: #fff;
        transition: border-color 150ms;
        height: 38px;
    }
    .filter-input:focus { border-color: var(--brand-500); }

    .scan-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px 16px;
        transition: box-shadow 0.15s;
    }
    .scan-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.07); }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .badge-saved      { background: #d1fae5; color: #059669; }
    .badge-completed  { background: #dbeafe; color: #2563eb; }
    .badge-pending    { background: #fef3c7; color: #d97706; }
    .badge-failed     { background: #fee2e2; color: #dc2626; }
    .badge-card       { background: #ede9fe; color: #7c3aed; }
    .badge-invoice    { background: #fce7f3; color: #db2777; }
    .badge-receipt    { background: #ffedd5; color: #ea580c; }

    .detail-row {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 6px;
    }
    .detail-label {
        font-size: 11px;
        font-weight: 600;
        color: #9ca3af;
        width: 80px;
        flex-shrink: 0;
        text-transform: uppercase;
        padding-top: 2px;
    }
    .detail-value {
        font-size: 13px;
        color: #1f2937;
        font-weight: 500;
        word-break: break-word;
    }

    /* Modal overlay */
    .modal-backdrop {
        position: fixed; inset: 0;
        background: rgba(0,0,0,.45);
        z-index: 999;
        display: flex; align-items: center; justify-content: center;
        padding: 16px;
    }
    .modal-box {
        background: #fff;
        border-radius: 18px;
        width: 100%;
        max-width: 480px;
        max-height: 90vh;
        overflow-y: auto;
        padding: 20px;
    }
</style>
@endpush

@section('content')
<div class="p-4 md:p-6" x-data="ocrHistory()" x-init="init()">

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                 style="background: var(--brand-500)">
                <i data-lucide="clock" class="w-4 h-4 text-white"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-800">Scan History</h2>
                <p class="text-xs text-gray-400">{{ $scans->total() }} scan(s) found</p>
            </div>
        </div>
        <a href="{{ route('admin.ocr-scanner.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold"
           style="background: var(--brand-500)">
            <i data-lucide="scan-line" class="w-4 h-4"></i>
            New Scan
        </a>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('admin.ocr-scanner.history') }}"
          class="bg-white p-3 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row gap-3 mb-6">
        
        <div class="relative flex-1 min-w-[200px]">
            <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all outline-none"
                   placeholder="Search names, emails, or raw text...">
        </div>

        <div class="flex flex-wrap sm:flex-nowrap gap-3">
            <select name="scan_type" class="flex-1 sm:flex-none px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 outline-none focus:border-brand-500 focus:bg-white transition-all cursor-pointer">
                <option value="">All Document Types</option>
                <option value="business_card" @selected(request('scan_type') === 'business_card')>Business Card</option>
                <option value="invoice"       @selected(request('scan_type') === 'invoice')>Invoice</option>
                <option value="receipt"       @selected(request('scan_type') === 'receipt')>Receipt</option>
                <option value="general"       @selected(request('scan_type') === 'general')>General</option>
            </select>

            <select name="status" class="flex-1 sm:flex-none px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 outline-none focus:border-brand-500 focus:bg-white transition-all cursor-pointer">
                <option value="">All Statuses</option>
                <option value="saved"      @selected(request('status') === 'saved')>Saved</option>
                <option value="completed"  @selected(request('status') === 'completed')>Completed</option>
                <option value="failed"     @selected(request('status') === 'failed')>Failed</option>
            </select>

            <button type="submit"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2 rounded-xl text-white text-sm font-bold shadow-sm transition-transform active:scale-95"
                    style="background: var(--brand-600)">
                Filter
            </button>

            @if(request()->hasAny(['search', 'scan_type', 'status']))
                <a href="{{ route('admin.ocr-scanner.history') }}"
                   class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1 px-4 py-2 rounded-xl text-gray-500 text-sm font-bold border border-gray-200 bg-white hover:bg-gray-50 transition-colors">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Clear
                </a>
            @endif
        </div>
    </form>

    {{-- ── Scan Cards ──────────────────────────────────────────────── --}}
    @if ($scans->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3 bg-gray-100">
                <i data-lucide="scan-line" class="w-7 h-7"></i>
            </div>
            <p class="text-sm font-semibold text-gray-500 mb-1">No scans yet</p>
            <p class="text-xs">Start by scanning a business card or document.</p>
            <a href="{{ route('admin.ocr-scanner.index') }}"
               class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold"
               style="background: var(--brand-500)">
                <i data-lucide="scan-line" class="w-4 h-4"></i>
                Scan Now
            </a>
        </div>
    @else
        <div class="grid gap-3">
            @foreach ($scans as $scan)
                @php
                    $finalData = $scan->final_data;
                    $typeBadge = match($scan->scan_type) {
                        'business_card' => ['badge-card', '🪪 Business Card'],
                        'invoice'       => ['badge-invoice', '🧾 Invoice'],
                        'receipt'       => ['badge-receipt', '🏷️ Receipt'],
                        default         => ['badge-completed', '📄 General'],
                    };
                    $statusBadge = match($scan->status) {
                        'saved'      => 'badge-saved',
                        'completed'  => 'badge-completed',
                        'failed'     => 'badge-failed',
                        default      => 'badge-pending',
                    };
                @endphp
                <div class="scan-card">
                    <div class="flex items-start gap-3">
                        {{-- Image thumbnail --}}
                        <div class="w-12 h-12 rounded-xl bg-gray-100 flex-shrink-0 overflow-hidden">
                            @if ($scan->image_path)
                                <img src="{{ asset('storage/' . $scan->image_path) }}"
                                     class="w-full h-full object-cover" alt="scan">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <i data-lucide="image" class="w-5 h-5"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <p class="text-sm font-bold text-gray-800 truncate">
                                    {{ $scan->display_name }}
                                </p>
                                <span class="badge {{ $typeBadge[0] }}">{{ $typeBadge[1] }}</span>
                                <span class="badge {{ $statusBadge }}">{{ ucfirst($scan->status) }}</span>
                            </div>

                            @if (!empty($finalData['email']))
                                <p class="text-xs text-gray-500 truncate">
                                    <i data-lucide="mail" class="w-3 h-3 inline mr-1"></i>{{ $finalData['email'] }}
                                </p>
                            @endif
                            @if (!empty($finalData['phone']))
                                <p class="text-xs text-gray-500 truncate">
                                    <i data-lucide="phone" class="w-3 h-3 inline mr-1"></i>{{ $finalData['phone'] }}
                                </p>
                            @endif

                            <p class="text-xs text-gray-400 mt-1">
                                {{ $scan->created_at->diffForHumans() }}
                                · by {{ $scan->user->name ?? 'Unknown' }}
                            </p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-col gap-1 flex-shrink-0">
                            <button @click="openDetail({{ $scan->id }})"
                                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            <button @click="archiveScan({{ $scan->id }})"
                                    class="p-2 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Compact data preview --}}
                    @if (!empty($finalData))
                        <div class="mt-3 pt-3 border-t border-gray-50 flex flex-wrap gap-x-4 gap-y-1">
                            @foreach (array_slice($finalData, 0, 4) as $k => $v)
                                @if ($v)
                                    <span class="text-xs text-gray-500">
                                        <span class="text-gray-400 uppercase text-[10px]">{{ $k }}</span>
                                        {{ Str::limit($v, 25) }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $scans->links() }}
        </div>
    @endif

    {{-- ── Detail Modal ────────────────────────────────────────────── --}}
    <div x-show="showModal" x-cloak class="modal-backdrop" @click.self="showModal = false">
        <div class="modal-box">
            <div x-show="loadingDetail" class="text-center py-8 text-gray-400">
                <svg class="animate-spin w-6 h-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                Loading…
            </div>

            <div x-show="!loadingDetail && detail" x-cloak>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-800" x-text="detail?.scan_type?.replace('_', ' ').toUpperCase()"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-700">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Image --}}
                <template x-if="detail?.image_url">
                    <div class="mb-4 rounded-xl overflow-hidden bg-gray-50 border border-gray-100">
                        <img :src="detail.image_url" class="w-full object-contain max-h-48">
                    </div>
                </template>

                {{-- Final fields --}}
                <div class="mb-4">
                    <p class="text-xs font-bold text-gray-400 uppercase mb-2">Extracted Data</p>
                    <template x-for="(val, key) in detail?.final_data" :key="key">
                        <div x-show="val" class="detail-row">
                            <span class="detail-label" x-text="key.replace(/_/g,' ')"></span>
                            <span class="detail-value" x-text="val"></span>
                        </div>
                    </template>
                </div>

                {{-- Notes --}}
                <template x-if="detail?.notes">
                    <div class="mb-4 p-3 bg-amber-50 rounded-xl border border-amber-100">
                        <p class="text-xs font-semibold text-amber-700 mb-1">Notes</p>
                        <p class="text-sm text-amber-800" x-text="detail.notes"></p>
                    </div>
                </template>

                {{-- Raw text --}}
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                            class="text-xs text-gray-400 underline mb-2">
                        Toggle raw OCR text
                    </button>
                    <pre x-show="open"
                         class="text-xs text-gray-500 bg-gray-50 rounded-lg p-3 max-h-32 overflow-auto whitespace-pre-wrap"
                         x-text="detail?.raw_text"></pre>
                </div>

                <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
                    <span x-text="'Scanned ' + detail?.created_at"></span>
                    <span class="badge badge-saved" x-text="detail?.status"></span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function ocrHistory() {
    return {
        showModal    : false,
        loadingDetail: false,
        detail       : null,

        init() {
            lucide.createIcons();
        },

        async openDetail(id) {
            this.showModal     = true;
            this.loadingDetail = true;
            this.detail        = null;

            try {
                const res  = await fetch(`/admin/ocr-scanner/${id}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    this.detail = data.scan;
                    this.$nextTick(() => lucide.createIcons());
                }
            } catch (e) {
                this.showModal = false;
                if(typeof BizAlert !== 'undefined') BizAlert.toast('Could not load scan detail.', 'error');
                else alert('Could not load scan detail.');
            } finally {
                this.loadingDetail = false;
            }
        },

        async archiveScan(id) {
            if(typeof BizAlert === 'undefined') return;

            const result = await BizAlert.confirm(
                'Archive this scan?',
                'It will be removed from your history.',
                'Yes, Archive',
                'warning'
            );

            if (!result.isConfirmed) return;

            try {
                const res = await fetch(`/admin/ocr-scanner/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN'    : document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await res.json();

                if (data.success) {
                    BizAlert.toast('Scan archived successfully.', 'success');
                    // Add a slight delay before reload so the user sees the toast
                    setTimeout(() => window.location.reload(), 1000); 
                }
            } catch (e) {
                BizAlert.toast('Could not archive scan.', 'error');
            }
        },
    };
}
</script>
@endpush
