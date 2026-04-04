@extends('layouts.admin')

@section('title', 'Work Logs')

@section('header-title')
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Work Logs</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">Review and approve employee work logs</p>
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .field-label {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 6px;
    }

    .field-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 30px;
        font-size: 13.5px;
        outline: none;
        transition: border-color 150ms ease, box-shadow 150ms ease;
        font-family: inherit;
        background: #fff;
    }

    .field-input:focus {
        border-color: var(--brand-600);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-600) 10%, transparent);
    }

    select.field-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }

    .field-error {
        font-size: 11px;
        color: #dc2626;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .table-row { border-bottom: 1px solid #f8fafc; transition: background 100ms; }
    .table-row:hover { background: #fafbfc; }
    .table-row:last-child { border-bottom: none; }

    .stat-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 14px;
        padding: 14px 16px;
        transition: box-shadow 150ms, border-color 150ms;
    }
    .stat-card:hover { border-color: #e2e8f0; box-shadow: 0 3px 12px rgba(0,0,0,0.06); }
</style>
@endpush

@section('content')

<div class="pb-10" x-data="workLogPage()" x-init="init()">

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Hours</p>
            <p class="text-2xl font-black text-gray-900">{{ number_format($logs->getCollection()->sum('hours_worked'), 1) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Approved</p>
            <p class="text-2xl font-black text-green-600">{{ $logs->getCollection()->where('status', 'approved')->count() }}</p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Pending</p>
            <p class="text-2xl font-black text-blue-500">{{ $logs->getCollection()->where('status', 'submitted')->count() }}</p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Rejected</p>
            <p class="text-2xl font-black text-red-500">{{ $logs->getCollection()->where('status', 'rejected')->count() }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white border border-gray-100 rounded-2xl px-4 py-3 mb-4">
        <form method="GET" action="{{ route('admin.hrm.work-logs.index') }}">
            <div class="flex items-center gap-3 flex-wrap">
                <div class="min-w-[160px]">
                    <select name="employee_id" class="field-input !py-2 !text-[13px]">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->user?->name }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[130px]">
                    <select name="status" class="field-input !py-2 !text-[13px]">
                        <option value="">All Status</option>
                        @foreach(['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $label)
                            <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="field-input !py-2 !text-[13px]" placeholder="From">
                </div>
                <div>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="field-input !py-2 !text-[13px]" placeholder="To">
                </div>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 text-[12px] font-bold px-4 py-2 rounded-lg text-white hover:opacity-90 transition-opacity"
                    style="background: var(--brand-600)">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    Filter
                </button>
                @if(request()->hasAny(['employee_id', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.hrm.work-logs.index') }}"
                        class="inline-flex items-center gap-1.5 text-[12px] font-bold px-4 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider w-[50px]">#</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Employee</th>
                        <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Task</th>
                        <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Description</th>
                        <th class="px-3 py-3 text-center text-[10px] font-black text-gray-400 uppercase tracking-wider">Hours</th>
                        <th class="px-3 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Category</th>
                        <th class="px-3 py-3 text-center text-[10px] font-black text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $statusBadge = match($log->status) {
                                'approved'  => 'text-green-700 bg-green-50 border-green-200',
                                'submitted' => 'text-blue-700 bg-blue-50 border-blue-200',
                                'rejected'  => 'text-red-700 bg-red-50 border-red-200',
                                default     => 'text-gray-500 bg-gray-50 border-gray-200',
                            };
                            $statusLabels = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected'];
                        @endphp
                        <tr class="table-row">
                            <td class="px-5 py-3 text-[12px] font-bold text-gray-400">{{ $logs->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-3">
                                <div>
                                    <p class="text-[13px] font-bold text-gray-800">{{ $log->employee?->user?->name ?? '—' }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $log->employee?->employee_code }}</p>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-[12px] text-gray-600">{{ $log->log_date ? \Carbon\Carbon::parse($log->log_date)->format('d M Y') : '—' }}</td>
                            <td class="px-3 py-3 text-[12px] text-gray-600">{{ $log->task?->title ?? '—' }}</td>
                            <td class="px-3 py-3">
                                <p class="text-[12px] text-gray-600 truncate max-w-[180px]" title="{{ $log->description }}">
                                    {{ $log->description ?? '—' }}
                                </p>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="text-[13px] font-black text-gray-800">{{ number_format($log->hours_worked, 1) }}</span>
                                <span class="text-[10px] text-gray-400 ml-0.5">h</span>
                            </td>
                            <td class="px-3 py-3 text-[12px] text-gray-600 capitalize">{{ $log->category ?? '—' }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center text-[10px] font-extrabold uppercase tracking-wider border px-2.5 py-1 rounded-md {{ $statusBadge }}">
                                    {{ $statusLabels[$log->status] ?? $log->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($log->status === 'submitted')
                                        <button @click="approveLog({{ $log->id }})"
                                            class="w-[30px] h-[30px] rounded-lg flex items-center justify-center bg-green-50 text-green-500 hover:bg-green-100 hover:text-green-700 transition-colors"
                                            title="Approve">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button @click="rejectLog({{ $log->id }})"
                                            class="w-[30px] h-[30px] rounded-lg flex items-center justify-center bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 transition-colors"
                                            title="Reject">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @else
                                        <span class="text-[11px] text-gray-300 italic pr-1">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="flex flex-col items-center justify-center py-20 text-center">
                                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                        <i data-lucide="clock" class="w-7 h-7 text-gray-300"></i>
                                    </div>
                                    <p class="font-semibold text-gray-500 mb-1">No work logs found</p>
                                    <p class="text-sm text-gray-400">Employees submit their daily work logs for your review.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Reject Remarks Modal --}}
    <div x-show="rejectModalOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden mx-4" @click.away="rejectModalOpen = false"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-black text-gray-800 uppercase tracking-widest text-sm">Reject Work Log</h3>
                <button @click="rejectModalOpen = false" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="field-label">Rejection Remarks</label>
                    <textarea x-model="rejectRemarks" class="field-input" rows="3"
                        placeholder="Reason for rejection..."></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                <button type="button" @click="rejectModalOpen = false"
                    class="px-4 py-2 text-[13px] font-bold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="button" @click="submitReject()" :disabled="saving"
                    class="px-5 py-2 text-[13px] font-bold text-white rounded-lg hover:opacity-90 transition-opacity disabled:opacity-50 bg-red-600">
                    <span x-show="!saving">Confirm Reject</span>
                    <span x-show="saving" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
window.workLogPage = function() {
    return {
        rejectModalOpen: false,
        rejectId: null,
        rejectRemarks: '',
        saving: false,

        init() {
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async approveLog(id) {
            try {
                const response = await fetch(`{{ url('admin/hrm/work-logs') }}/${id}/approve`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ action: 'approve' }),
                });

                const data = await response.json();

                if (!response.ok) {
                    BizAlert.toast(data.message || 'Something went wrong', 'error');
                    return;
                }

                BizAlert.toast(data.message || 'Work log approved', 'success');
                setTimeout(() => window.location.reload(), 600);
            } catch (e) {
                BizAlert.toast('Network error. Please try again.', 'error');
            }
        },

        rejectLog(id) {
            this.rejectId = id;
            this.rejectRemarks = '';
            this.rejectModalOpen = true;
        },

        async submitReject() {
            this.saving = true;
            try {
                const response = await fetch(`{{ url('admin/hrm/work-logs') }}/${this.rejectId}/approve`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ action: 'reject', remarks: this.rejectRemarks }),
                });

                const data = await response.json();

                if (!response.ok) {
                    BizAlert.toast(data.message || 'Something went wrong', 'error');
                    return;
                }

                BizAlert.toast(data.message || 'Work log rejected', 'success');
                this.rejectModalOpen = false;
                setTimeout(() => window.location.reload(), 600);
            } catch (e) {
                BizAlert.toast('Network error. Please try again.', 'error');
            } finally {
                this.saving = false;
            }
        },

        confirmDelete(id) {
            BizAlert.confirm('Delete Work Log', 'Are you sure you want to delete this work log?', 'Delete').then(async (result) => {
                if (!result.isConfirmed) return;

                try {
                    const response = await fetch(`{{ url('admin/hrm/work-logs') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        BizAlert.toast(data.message || 'Cannot delete', 'error');
                        return;
                    }

                    BizAlert.toast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 600);
                } catch (e) {
                    BizAlert.toast('Network error. Please try again.', 'error');
                }
            });
        },
    };
};
</script>
@endpush
