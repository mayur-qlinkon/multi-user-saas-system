@extends('layouts.admin')

@section('title', 'My Salary Slips')

@section('header-title')
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">My Salary Slips</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">View and download your salary slips</p>
    </div>
@endsection

@push('styles')
<style>
    .field-input {
        border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 8px 14px;
        font-size: 13px; outline: none; background: #fff; font-family: inherit;
        transition: border-color 150ms ease;
    }
    .field-input:focus { border-color: var(--brand-600); }
</style>
@endpush

@section('content')

<div class="space-y-5 pb-10">

    {{-- ── Filter Bar ── --}}
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
        <form method="GET" class="flex items-center gap-3 px-5 py-4 border-b border-gray-50 flex-wrap">
            <select name="year" class="field-input w-full sm:w-auto">
                <option value="">All Years</option>
                @foreach($years as $yr)
                <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>
            <select name="status" class="field-input">
                <option value="">All Status</option>
                @foreach(\App\Models\Hrm\SalarySlip::STATUS_LABELS as $val => $label)
                <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit"
                class="px-4 py-2 text-[12px] font-bold text-white rounded-lg border-none cursor-pointer"
                style="background: var(--brand-600)">Filter</button>
            <a href="{{ route('admin.hrm.my-salary-slips.index') }}"
                class="px-4 py-2 text-[12px] font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Reset</a>
        </form>

        {{-- Slip Cards --}}
        @forelse($slips as $slip)
        @php $sc = \App\Models\Hrm\SalarySlip::STATUS_COLORS[$slip->status] ?? ['bg'=>'#f3f4f6','text'=>'#374151','dot'=>'#9ca3af']; @endphp
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">

            {{-- Left Side: Icon & Info (Stays horizontal on mobile) --}}
            <div class="flex items-center gap-4 flex-1 min-w-0">
                {{-- Month Icon --}}
                <div class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center flex-shrink-0"
                    style="background: var(--brand-50)">
                    <p class="text-[9px] font-black uppercase tracking-wider" style="color: var(--brand-600)">
                        {{ date('M', mktime(0,0,0,$slip->month,1)) }}
                    </p>
                    <p class="text-[15px] font-black" style="color: var(--brand-600)">{{ $slip->year }}</p>
                </div>

                {{-- Slip Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-[14px] font-black text-gray-800">
                        {{ $slip->month_name }} {{ $slip->year }}
                    </p>
                    <p class="text-[12px] text-gray-400 mt-0.5">{{ $slip->slip_number }}</p>
                </div>
            </div> {{-- End Left Side Wrapper --}}

            {{-- Days Summary --}}
            <div class="hidden sm:flex items-center gap-6 text-center">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Present</p>
                    <p class="text-[14px] font-black text-gray-700">{{ $slip->present_days }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Absent</p>
                    <p class="text-[14px] font-black text-gray-700">{{ $slip->absent_days }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Working</p>
                    <p class="text-[14px] font-black text-gray-700">{{ $slip->working_days }}</p>
                </div>
            </div>

            {{-- Right Side: Salary & Actions (Spreads out on mobile, groups on right for iPad+) --}}
            <div class="flex items-end sm:items-center justify-between sm:justify-end gap-4 w-full sm:w-auto border-t border-gray-100 sm:border-0 pt-3 sm:pt-0">
                
                {{-- Net Salary --}}
                <div class="text-left sm:text-right flex-shrink-0">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Net Salary</p>
                    <p class="text-[18px] font-black text-gray-900">₹{{ number_format($slip->net_salary, 0) }}</p>
                </div>

                {{-- Status + Download --}}
                <div class="flex flex-col items-end gap-2 flex-shrink-0">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-lg"
                        style="background: {{ $sc['bg'] }}; color: {{ $sc['text'] }}">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $sc['dot'] }}"></span>
                        {{ $slip->status_label }}
                    </span>

                    @if(in_array($slip->status, ['approved', 'paid']))
                        <a href="{{ route('admin.hrm.my-salary-slips.pdf', $slip) }}" target="_blank"
                            class="flex items-center justify-center gap-1.5 text-[11px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors mt-1 sm:mt-0">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i> Download
                        </a>
                    @endif
                </div>
            </div> {{-- End Right Side Wrapper --}}
        </div>
        @empty
        <div class="px-5 py-16 text-center">
            <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="banknote" class="w-6 h-6 text-gray-300"></i>
            </div>
            <p class="text-[14px] font-bold text-gray-400">No salary slips yet</p>
            <p class="text-[12px] text-gray-300 mt-1">Your payslips will appear here once generated by HR.</p>
        </div>
        @endforelse

        @if($slips->hasPages())
        <div class="px-5 py-3 border-t border-gray-50">{{ $slips->links() }}</div>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();
});
</script>
@endpush
