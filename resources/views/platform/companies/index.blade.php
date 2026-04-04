@extends('layouts.app')

@section('title', 'Companies')
@section('header', 'Companies')

@section('content')
<div class="space-y-6">

    {{-- Flash --}}
    @if (session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm font-medium px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm font-medium px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Companies</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ $companies->count() }} total registered companies</p>
        </div>
        <a href="{{ route('platform.companies.create') }}"
            class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Onboard Company
        </a>
    </div>

    {{-- Filter / Search --}}
    <div x-data="{ search: '', status: 'all' }" class="space-y-4">

        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input x-model="search" type="text" placeholder="Search by name, email or slug…"
                    class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-500 bg-white">
            </div>
            <div class="flex gap-2">
                <button @click="status = 'all'"    :class="status === 'all'    ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'" class="px-4 py-2 text-sm font-semibold rounded-xl transition-colors">All</button>
                <button @click="status = 'active'" :class="status === 'active' ? 'bg-green-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'" class="px-4 py-2 text-sm font-semibold rounded-xl transition-colors">Active</button>
                <button @click="status = 'inactive'" :class="status === 'inactive' ? 'bg-red-500 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'" class="px-4 py-2 text-sm font-semibold rounded-xl transition-colors">Inactive</button>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="text-left px-5 py-3.5">Company</th>
                        <th class="text-left px-5 py-3.5 hidden md:table-cell">Slug</th>
                        <th class="text-left px-5 py-3.5 hidden lg:table-cell">Owner</th>
                        <th class="text-center px-5 py-3.5 hidden sm:table-cell">Users</th>
                        <th class="text-center px-5 py-3.5 hidden sm:table-cell">Stores</th>
                        <th class="text-center px-5 py-3.5">Status</th>
                        <th class="text-right px-5 py-3.5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($companies as $company)
                        @php $owner = $company->users->first(); @endphp
                        <tr x-show="
                                (status === 'all' || (status === 'active' && {{ $company->is_active ? 'true' : 'false' }}) || (status === 'inactive' && {{ !$company->is_active ? 'true' : 'false' }}))
                                &&
                                (search === '' || '{{ strtolower($company->name . ' ' . $company->email . ' ' . $company->slug) }}'.includes(search.toLowerCase()))
                            "
                            class="hover:bg-gray-50/60 transition-colors">

                            {{-- Company --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-brand-500/10 text-brand-600 font-bold text-sm flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($company->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $company->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $company->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Slug --}}
                            <td class="px-5 py-4 hidden md:table-cell">
                                <code class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-lg">{{ $company->slug }}</code>
                            </td>

                            {{-- Owner --}}
                            <td class="px-5 py-4 hidden lg:table-cell">
                                @if ($owner)
                                    <p class="font-medium text-gray-700">{{ $owner->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $owner->email }}</p>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Counts --}}
                            <td class="px-5 py-4 text-center hidden sm:table-cell">
                                <span class="font-semibold text-gray-700">{{ $company->users_count }}</span>
                            </td>
                            <td class="px-5 py-4 text-center hidden sm:table-cell">
                                <span class="font-semibold text-gray-700">{{ $company->stores_count }}</span>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4 text-center">
                                @if ($company->is_active)
                                    <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-[11px] font-bold px-2.5 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-red-50 text-red-600 text-[11px] font-bold px-2.5 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Inactive
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('platform.companies.show', $company) }}"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-brand-600 hover:bg-brand-50 transition-colors" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="{{ route('platform.companies.edit', $company) }}"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    {{-- Hidden DELETE form, submitted by JS after SweetAlert confirm --}}
                                    <form id="del-form-{{ $company->id }}" method="POST"
                                        action="{{ route('platform.companies.destroy', $company) }}" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button type="button"
                                        onclick="confirmDelete({{ $company->id }}, '{{ addslashes($company->name) }}')"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Terminate">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline stroke-linecap="round" stroke-linejoin="round" points="3 6 5 6 21 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-400 text-sm">
                                No companies yet. <a href="{{ route('platform.companies.create') }}" class="text-brand-600 font-semibold hover:underline">Onboard the first one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Terminate Company?',
        html: `<p style="color:#4b5563;font-size:14px;margin-top:4px;">
                    You are about to permanently terminate:<br>
                    <strong style="color:#111827">${name}</strong>
               </p>
               <p style="color:#ef4444;font-size:13px;margin-top:10px;">
                   This will delete all stores, data, and users belonging to this company.<br>
                   <strong>This action cannot be undone.</strong>
               </p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Terminate',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        focusCancel: true,
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('del-form-' + id).submit();
        }
    });
}
</script>
@endsection
