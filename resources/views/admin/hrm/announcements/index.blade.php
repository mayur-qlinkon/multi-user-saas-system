@extends('layouts.admin')

@section('title', 'Announcements')

@section('header-title')
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Announcements</h1>
        {{-- <p class="text-xs text-gray-400 font-medium mt-0.5">Manage and broadcast company-wide updates.</p> --}}
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .table-header th { font-size: 10px; font-weight: 900; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; padding: 12px 24px; border-bottom: 1px solid #f1f5f9; background: #fdfdfd; }
    .table-cell { padding: 16px 24px; vertical-align: top; border-bottom: 1px solid #f1f5f9; }
</style>
@endpush

@section('content')
<div class="pb-12" x-data="announcementIndex()">

    {{-- SYSTEM ALERTS --}}
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => BizAlert.toast("{{ session('success') }}", 'success'));
        </script>
    @endif

    {{-- TOOLBAR: Search, Filters & Actions --}}
    <div class="bg-white rounded-t-xl shadow-sm border border-gray-100 p-4 border-b-0 mb-0">
        <form id="filterForm" method="GET" action="{{ route('admin.hrm.announcements.index') }}" class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            
            {{-- Left Side: Search & Filters --}}
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 w-full md:w-auto flex-1">
                
                {{-- Search --}}
                <div class="relative w-full sm:flex-1 sm:min-w-[200px] sm:max-w-sm">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search title or content..."
                        class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder-gray-400">
                </div>

                {{-- Status Filter --}}
                <select name="status" onchange="document.getElementById('filterForm').submit()" 
                    class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none bg-white cursor-pointer sm:min-w-[130px]">
                    <option value="">All Statuses</option>
                    @foreach($statusOptions as $val => $label)
                        <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                {{-- Type Filter --}}
                <select name="type" onchange="document.getElementById('filterForm').submit()" 
                    class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none bg-white cursor-pointer sm:min-w-[130px]">
                    <option value="">All Types</option>
                    @foreach($typeOptions as $val => $label)
                        <option value="{{ $val }}" {{ ($filters['type'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                {{-- Filter/Clear Buttons --}}
                <div class="flex gap-2">
                    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors">
                        Filter
                    </button>
                    @if(array_filter($filters))
                        <a href="{{ route('admin.hrm.announcements.index') }}" title="Clear Filters"
                            class="bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-500 w-10 flex items-center justify-center rounded-lg transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Right Side: Add Button --}}
            <div class="flex items-center gap-2 w-full md:w-auto justify-start md:justify-end shrink-0">
                @if(has_permission('announcements.create'))
                    <a href="{{ route('admin.hrm.announcements.create') }}"
                        class="w-full justify-center md:w-auto md:justify-start bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-all flex items-center gap-2 whitespace-nowrap active:scale-95">
                        <i data-lucide="plus" class="w-4 h-4"></i> Create Announcement
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- DATA TABLE --}}
    <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap min-w-[800px]">
                <thead class="table-header">
                    <tr>
                        <th class="w-[40%]">Announcement Info</th>
                        <th class="hidden md:table-cell w-[15%]">Type & Priority</th>
                        <th class="hidden md:table-cell w-[15%]">Target</th>
                        <th class="hidden md:table-cell w-[15%]">Publish / Expire</th>
                        <th class="w-[10%] text-center">Status</th>
                        <th class="w-[5%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($announcements as $announcement)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            
                            {{-- Info --}}
                            <td class="table-cell">
                                <div class="flex items-start gap-3">
                                    @if($announcement->is_pinned)
                                        <i data-lucide="pin" class="w-4 h-4 text-yellow-500 shrink-0 mt-0.5"></i>
                                    @else
                                        <i data-lucide="megaphone" class="w-4 h-4 text-gray-300 shrink-0 mt-0.5"></i>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.hrm.announcements.show', $announcement) }}" class="font-bold text-[14px] text-brand-600 hover:text-brand-700 hover:underline truncate block max-w-md">
                                            {{ $announcement->title }}
                                        </a>
                                        <p class="text-[12px] text-gray-500 truncate max-w-md mt-0.5">
                                            {{ Str::limit(strip_tags($announcement->content), 60) }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Type & Priority --}}
                            <td class="hidden md:table-cell">
                                @php
                                    $typeBadge = match($announcement->type) {
                                        'policy' => 'bg-blue-50 text-blue-700',
                                        'event' => 'bg-purple-50 text-purple-700',
                                        'urgent' => 'bg-orange-50 text-orange-700',
                                        'celebration' => 'bg-green-50 text-green-700',
                                        default => 'bg-gray-50 text-gray-600',
                                    };
                                    $priorityColor = match($announcement->priority) {
                                        'critical' => 'text-red-500',
                                        'high' => 'text-amber-500',
                                        default => 'text-gray-400',
                                    };
                                @endphp
                                <div class="flex flex-col items-start gap-1.5">
                                    <span class="inline-flex text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded {{ $typeBadge }}">
                                        {{ $announcement->type_label }}
                                    </span>
                                    <span class="text-[11px] font-bold {{ $priorityColor }} flex items-center gap-1">
                                        <i data-lucide="flag" class="w-3 h-3"></i> {{ $announcement->priority_label }}
                                    </span>
                                </div>
                            </td>

                            {{-- Target --}}
                            <td class="hidden md:table-cell">
                                <span class="text-[12px] font-bold text-gray-700">
                                    {{ \App\Models\Hrm\Announcement::TARGET_LABELS[$announcement->target_audience] ?? ucfirst($announcement->target_audience) }}
                                </span>
                                @if($announcement->requires_acknowledgement)
                                    <div class="text-[10px] text-gray-400 font-medium mt-1 flex items-center gap-1">
                                        <i data-lucide="check-square" class="w-3 h-3"></i> Req. Ack
                                    </div>
                                @endif
                            </td>

                            {{-- Dates --}}
                            <td class="hidden md:table-cell">
                                <div class="text-[12px] font-bold text-gray-800">
                                    {{ $announcement->publish_at ? $announcement->publish_at->format('d M Y') : 'Draft' }}
                                </div>
                                <div class="text-[11px] text-gray-400 mt-0.5">
                                    Exp: {{ $announcement->expire_at ? $announcement->expire_at->format('d M Y') : 'Never' }}
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="table-cell text-center">
                                @php
                                    $statusBadge = match($announcement->status) {
                                        'published' => 'bg-green-50 text-green-700 border-green-200',
                                        'scheduled' => 'bg-sky-50 text-sky-700 border-sky-200',
                                        'expired'   => 'bg-red-50 text-red-600 border-red-200',
                                        default     => 'bg-gray-50 text-gray-500 border-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex text-[10px] font-extrabold uppercase tracking-wider border px-2 py-0.5 rounded {{ $statusBadge }}">
                                    {{ $announcement->status_label }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="table-cell text-right">
                                <div class="flex items-center justify-end gap-2 transition-opacity">
                                    {{-- Quick Publish Button --}}
                                    @if(!$announcement->is_published)
                                        @if(has_permission('announcements.publish'))
                                            <button @click="publishAnnouncement({{ $announcement->id }}, '{{ addslashes($announcement->title) }}')" title="Publish Now"
                                                class="w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 flex items-center justify-center transition-colors">
                                                <i data-lucide="send" class="w-4 h-4 text-green-600"></i>
                                            </button>
                                        @endif
                                    @endif

                                    @if(has_permission('announcements.view'))
                                        <a href="{{ route('admin.hrm.announcements.show', $announcement) }}" title="View"
                                            class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                    
                                    @if(has_permission('announcements.update'))
                                        <a href="{{ route('admin.hrm.announcements.edit', $announcement) }}" title="Edit"
                                            class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                    @endif

                                    @if(has_permission('announcements.create'))
                                        <button @click="duplicateAnnouncement({{ $announcement->id }}, '{{ addslashes($announcement->title) }}')" title="Duplicate as Draft"
                                            class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 flex items-center justify-center transition-colors">
                                            <i data-lucide="copy-plus" class="w-4 h-4"></i>
                                        </button>
                                    @endif

                                    @if(has_permission('announcements.delete'))
                                        <button @click="confirmDelete({{ $announcement->id }}, '{{ addslashes($announcement->title) }}')" title="Delete"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 font-medium">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                        <i data-lucide="megaphone" class="w-8 h-8 text-gray-300"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500">No Announcements Found</p>
                                    <p class="text-xs mt-1">Adjust your filters or create a new announcement to get started.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($announcements->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
window.announcementIndex = function() {
    return {
        init() {
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        // --- NEW PUBLISH FUNCTION ---
        publishAnnouncement(id, title) {
            BizAlert.confirm('Publish Announcement', `Are you sure you want to publish "${title}" immediately?`, 'Yes, Publish').then(async r => {
                if (!r.isConfirmed) return;
                
                try {
                    const response = await fetch(`{{ url('admin/hrm/announcements') }}/${id}/publish`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    });
                    const res = await response.json();
                    
                    if (res.success) { // Assuming your controller returns a boolean 'success'
                        BizAlert.toast(res.message || 'Announcement published successfully!', 'success');
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        BizAlert.toast(res.message || 'Failed to publish announcement.', 'error');
                    }
                } catch {
                    BizAlert.toast('Network error while publishing.', 'error');
                }
            });
        },

        // --- DUPLICATE FUNCTION ---
        duplicateAnnouncement(id, title) {
            BizAlert.confirm('Duplicate Announcement', `Duplicate "${title}" as a new draft?`, 'Yes, Duplicate').then(async r => {
                if (!r.isConfirmed) return;

                try {
                    const response = await fetch(`{{ url('admin/hrm/announcements') }}/${id}/duplicate`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const res = await response.json();

                    if (res.success) {
                        BizAlert.toast(res.message || 'Duplicated successfully!', 'success');
                        setTimeout(() => { window.location.href = res.redirect; }, 600);
                    } else {
                        BizAlert.toast(res.message || 'Failed to duplicate.', 'error');
                    }
                } catch {
                    BizAlert.toast('Network error while duplicating.', 'error');
                }
            });
        },

        // --- EXISTING DELETE FUNCTION ---
        confirmDelete(id, title) {
            BizAlert.confirm('Delete Announcement', `Are you sure you want to delete "${title}"?`, 'Delete').then(async r => {
                if (!r.isConfirmed) return;
                
                try {
                    const response = await fetch(`{{ url('admin/hrm/announcements') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const res = await response.json();
                    
                    if (res.success) {
                        BizAlert.toast(res.message, 'success');
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        BizAlert.toast(res.message || 'Cannot delete.', 'error');
                    }
                } catch {
                    BizAlert.toast('Network error.', 'error');
                }
            });
        }
    };
};
</script>
@endpush