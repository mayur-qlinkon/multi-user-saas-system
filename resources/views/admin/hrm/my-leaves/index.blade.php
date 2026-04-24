@extends('layouts.admin')

@section('title', 'My Leaves')

@section('header-title')
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">My Leaves</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">Apply for leave and track your requests</p>
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .field-label {
        font-size: 11px; font-weight: 700; color: #6b7280;
        text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px;
    }
    .field-input {
        width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px;
        padding: 10px 30px; font-size: 13.5px; outline: none;
        transition: border-color 150ms ease, box-shadow 150ms ease;
        font-family: inherit; background: #fff;
    }
    .field-input:focus {
        border-color: var(--brand-600);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-600) 10%, transparent);
    }
    .field-error { font-size: 11px; color: #dc2626; margin-top: 4px; display: flex; align-items: center; gap: 4px; }

    .modal-backdrop {
        position: fixed; inset: 0; background: rgba(15,23,42,0.45);
        backdrop-filter: blur(3px); z-index: 50;
        display: flex; align-items: center; justify-content: center; padding: 16px;
    }
    .modal-box {
        background: #fff; border-radius: 18px; width: 100%; max-width: 520px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.18); overflow: hidden;
    }
    .modal-header {
        padding: 18px 22px 14px; border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
    }
    .btn-primary {
        background: var(--brand-600); color: #fff; font-weight: 700;
        font-size: 13px; padding: 9px 20px; border-radius: 10px; border: none;
        cursor: pointer; transition: opacity 150ms; font-family: inherit;
    }
    .btn-primary:hover { opacity: 0.88; }
    .btn-primary:disabled { opacity: 0.55; cursor: not-allowed; }
    .btn-ghost {
        background: transparent; color: #6b7280; font-weight: 600;
        font-size: 13px; padding: 9px 16px; border-radius: 10px;
        border: 1.5px solid #e5e7eb; cursor: pointer; transition: background 150ms; font-family: inherit;
    }
    .btn-ghost:hover { background: #f9fafb; }
</style>
@endpush

@section('content')

<div x-data="myLeaves()" x-init="init()" class="space-y-5 pb-10">

    {{-- ── Leave Balance Stat Cards ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @forelse($leaveBalances as $lb)
        <div class="bg-white border border-gray-100 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider truncate pr-2">{{ $lb['name'] }}</p>
                <div class="w-2 h-2 rounded-full flex-shrink-0" style="background: {{ $lb['color'] }}"></div>
            </div>
            <p class="text-[26px] font-black text-gray-900 leading-none">{{ $lb['available'] }}</p>
            <p class="text-[11px] text-gray-400 mt-1">available of {{ $lb['allocated'] }}</p>
            <div class="mt-2.5 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                @php $pct = $lb['allocated'] > 0 ? min(100, ($lb['used'] / $lb['allocated']) * 100) : 0; @endphp
                <div class="h-full rounded-full transition-all" style="width: {{ $pct }}%; background: {{ $lb['color'] }}"></div>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">{{ $lb['used'] }} used</p>
        </div>
        @empty
        <div class="col-span-full bg-white border border-gray-100 rounded-2xl p-5 text-center text-sm text-gray-400">
            No leave types configured yet. Contact HR.
        </div>
        @endforelse
    </div>

    {{-- ── Leave Requests Table ── --}}
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-50 flex-wrap">
            <div class="flex items-center gap-2 flex-wrap">
                <select x-model="filterStatus" @change="filterLeaves()"
                    class="text-[12px] font-semibold text-gray-600 border border-gray-200 rounded-lg px-3 py-2 outline-none bg-white">
                    <option value="">All Status</option>
                    @foreach(\App\Models\Hrm\Leave::STATUS_LABELS as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select x-model="filterType" @change="filterLeaves()"
                    class="text-[12px] font-semibold text-gray-600 border border-gray-200 rounded-lg px-3 py-2 outline-none bg-white">
                    <option value="">All Types</option>
                    @foreach($leaveTypes as $lt)
                    <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                    @endforeach
                </select>
            </div>
            <button @click="openApply()" class="btn-primary flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Apply Leave
            </button>
        </div>

        {{-- 🖥️ DESKTOP VIEW (TABLE) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="border-b border-gray-50">
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Leave Type</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Dates</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Days</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Applied On</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider w-20">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($leaves as $leave)
                    @php $sc = \App\Models\Hrm\Leave::STATUS_COLORS[$leave->status]; @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="text-[13px] font-bold text-gray-800">{{ $leave->leaveType?->name }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">{{ str_replace('_', ' ', $leave->day_type) }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="inline-flex items-center gap-2 text-[13px] font-medium text-gray-700 bg-gray-50/80 px-2.5 py-1.5 rounded-lg border border-gray-100">
                                <span>{{ $leave->from_date->format('d M Y') }}</span>
                                @if($leave->from_date != $leave->to_date)
                                    <i data-lucide="arrow-right" class="w-3 h-3 text-gray-400"></i>
                                    <span>{{ $leave->to_date->format('d M Y') }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-[13px] font-bold text-gray-800">{{ $leave->total_days }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-lg"
                                style="background: {{ $sc['bg'] }}; color: {{ $sc['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: {{ $sc['dot'] }}"></span>
                                {{ \App\Models\Hrm\Leave::STATUS_LABELS[$leave->status] }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-[12px] text-gray-500">{{ $leave->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                @if($leave->status === 'pending')
                                <button @click="openEdit({{ $leave->id }}, {{ $leave->leave_type_id }}, '{{ $leave->from_date->format('Y-m-d') }}', '{{ $leave->to_date->format('Y-m-d') }}', {{ $leave->total_days }}, '{{ $leave->day_type }}', '{{ addslashes($leave->reason) }}')"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                <button @click="confirmDelete({{ $leave->id }})"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                                @else
                                <span class="text-[11px] text-gray-300">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">No leave requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 📱 MOBILE VIEW (CARDS) --}}
        <div class="md:hidden divide-y divide-gray-50 border-t border-gray-50">
            @forelse($leaves as $leave)
                @php $sc = \App\Models\Hrm\Leave::STATUS_COLORS[$leave->status]; @endphp
                <div class="p-4 hover:bg-gray-50/50 transition-colors">
                    
                    {{-- Header: Type, Days, Status --}}
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-[13px] font-bold text-gray-800">{{ $leave->leaveType?->name }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5 capitalize">{{ str_replace('_', ' ', $leave->day_type) }} • <span class="font-bold text-gray-600">{{ $leave->total_days }} Day(s)</span></p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md"
                            style="background: {{ $sc['bg'] }}; color: {{ $sc['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: {{ $sc['dot'] }}"></span>
                            {{ \App\Models\Hrm\Leave::STATUS_LABELS[$leave->status] }}
                        </span>
                    </div>

                    {{-- Context: Dates --}}
                    <div class="flex items-center justify-between">
                        <div class="inline-flex items-center gap-2 text-[12px] font-medium text-gray-700 bg-gray-50/80 px-2.5 py-1.5 rounded-lg border border-gray-100">
                            <span>{{ $leave->from_date->format('d M Y') }}</span>
                            @if($leave->from_date != $leave->to_date)
                                <i data-lucide="arrow-right" class="w-3 h-3 text-gray-400"></i>
                                <span>{{ $leave->to_date->format('d M Y') }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Footer: Applied Date & Actions --}}
                    <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-50">
                        <span class="text-[11px] text-gray-400 font-medium">Applied: {{ $leave->created_at->format('d M Y') }}</span>
                        
                        <div class="flex items-center gap-1">
                            @if($leave->status === 'pending')
                                <button @click="openEdit({{ $leave->id }}, {{ $leave->leave_type_id }}, '{{ $leave->from_date->format('Y-m-d') }}', '{{ $leave->to_date->format('Y-m-d') }}', {{ $leave->total_days }}, '{{ $leave->day_type }}', '{{ addslashes($leave->reason) }}')"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <button @click="confirmDelete({{ $leave->id }})"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            @else
                                <span class="text-[11px] text-gray-300 px-2 italic">Locked</span>
                            @endif
                        </div>
                    </div>
                    
                </div>
            @empty
                <div class="p-8 text-center text-sm text-gray-400 bg-white">
                    <p class="font-semibold text-gray-500 mb-1">No leave requests found</p>
                    <p class="text-xs text-gray-400">Use the button above to apply for leave.</p>
                </div>
            @endforelse
        </div>

        @if($leaves->hasPages())
        <div class="px-5 py-3 border-t border-gray-50">{{ $leaves->links() }}</div>
        @endif
    </div>

    {{-- ── Apply / Edit Modal ── --}}
    <template x-teleport="body">
        <div x-show="showModal" x-cloak class="modal-backdrop" @click.self="showModal = false">
            <div class="modal-box" @click.stop>
                <div class="modal-header">
                    <div>
                        <p class="text-[15px] font-black text-gray-900" x-text="editId ? 'Edit Leave Request' : 'Apply for Leave'"></p>
                        <p class="text-[12px] text-gray-400 mt-0.5">Submit your leave application</p>
                    </div>
                    <button @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <div>
                        <p class="field-label">Leave Type</p>
                        <select x-model="form.leave_type_id" class="field-input">
                            <option value="">Select leave type</option>
                            @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                            @endforeach
                        </select>
                        <p class="field-error" x-show="errors.leave_type_id" x-text="errors.leave_type_id"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="field-label">From Date</p>
                            <input type="date" x-model="form.from_date" @change="calcDays()" class="field-input">
                            <p class="field-error" x-show="errors.from_date" x-text="errors.from_date"></p>
                        </div>
                        <div>
                            <p class="field-label">To Date</p>
                            <input type="date" x-model="form.to_date" @change="calcDays()" class="field-input">
                            <p class="field-error" x-show="errors.to_date" x-text="errors.to_date"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="field-label">Day Type</p>
                            <select x-model="form.day_type" @change="calcDays()" class="field-input">
                                <option value="full_day">Full Day</option>
                                <option value="first_half">First Half</option>
                                <option value="second_half">Second Half</option>
                            </select>
                        </div>
                        <div>
                            <p class="field-label">Total Days</p>
                            <input type="number" x-model="form.total_days" step="0.5" min="0.5" class="field-input" readonly>
                        </div>
                    </div>

                    <div>
                        <p class="field-label">Reason</p>
                        <textarea x-model="form.reason" rows="3" class="field-input" style="resize:none" placeholder="Briefly describe the reason..."></textarea>
                        <p class="field-error" x-show="errors.reason" x-text="errors.reason"></p>
                    </div>
                </div>

                <div class="px-5 pb-5 flex justify-end gap-2">
                    <button @click="showModal = false" class="btn-ghost">Cancel</button>
                    <button @click="submitLeave()" :disabled="saving" class="btn-primary">
                        <span x-text="saving ? 'Saving...' : (editId ? 'Update Request' : 'Submit Request')"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

@endsection

@push('scripts')
<script>
function myLeaves() {
    return {
        showModal: false,
        editId: null,
        saving: false,
        filterStatus: '{{ request('status', '') }}',
        filterType: '{{ request('leave_type_id', '') }}',
        form: { leave_type_id: '', from_date: '', to_date: '', day_type: 'full_day', total_days: 1, reason: '' },
        errors: {},

        init() {
            if (window.lucide) lucide.createIcons();
        },

        openApply() {
            this.editId = null;
            this.form = { leave_type_id: '', from_date: '', to_date: '', day_type: 'full_day', total_days: 1, reason: '' };
            this.errors = {};
            this.showModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openEdit(id, typeId, from, to, days, dayType, reason) {
            this.editId = id;
            this.form = { leave_type_id: String(typeId), from_date: from, to_date: to, total_days: days, day_type: dayType, reason: reason };
            this.errors = {};
            this.showModal = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        calcDays() {
            if (!this.form.from_date || !this.form.to_date) return;
            const from = new Date(this.form.from_date);
            const to = new Date(this.form.to_date);
            if (to < from) { this.form.to_date = this.form.from_date; return; }
            const diff = Math.ceil((to - from) / (1000 * 60 * 60 * 24)) + 1;
            this.form.total_days = (this.form.day_type === 'full_day') ? diff : 0.5;
        },

        filterLeaves() {
            const params = new URLSearchParams(window.location.search);
            if (this.filterStatus) params.set('status', this.filterStatus); else params.delete('status');
            if (this.filterType) params.set('leave_type_id', this.filterType); else params.delete('leave_type_id');
            window.location.search = params.toString();
        },

        async submitLeave() {
            this.saving = true;
            this.errors = {};
            const url = this.editId
                ? `/admin/hrm/my-leaves/${this.editId}`
                : '/admin/hrm/my-leaves';
            const method = this.editId ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) {
                    BizAlert.toast(data.message, 'success');
                    this.showModal = false;
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    if (data.errors) this.errors = data.errors;
                    else BizAlert.toast(data.message || 'Something went wrong.', 'error');
                }
            } catch(e) {
                BizAlert.toast('Network error. Please try again.', 'error');
            } finally {
                this.saving = false;
            }
        },

        confirmDelete(id) {
            // Pass 'Delete' as the button text, then handle the Promise
            BizAlert.confirm('Delete this leave request?', 'This cannot be undone.', 'Delete').then(async (result) => {
                
                // If they click cancel, stop here.
                if (!result.isConfirmed) return;

                try {
                    const res = await fetch(`/admin/hrm/my-leaves/${id}`, {
                        method: 'DELETE',
                        headers: { 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
                            'Accept': 'application/json' 
                        }
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        BizAlert.toast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        BizAlert.toast(data.message, 'error');
                    }
                } catch (error) {
                    BizAlert.toast('Network error. Please try again.', 'error');
                }
            });
        }
    };
}
</script>
@endpush
