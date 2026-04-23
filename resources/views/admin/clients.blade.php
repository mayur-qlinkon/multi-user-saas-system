@extends('layouts.admin')

@section('title', 'Clients Management - Qlinkon BIZNESS')

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }

        body.modal-open {
            overflow: hidden;
        }
    </style>
@endpush

@section('header-title')
    <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Clients</h1>
@endsection

@section('content')
    <div class="pb-10" x-data="clientManager(@js($clients->items()))">

        @if (session('success'))
            <div
                class="bg-green-50 text-green-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-green-100 mb-6 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div
                class="bg-[#fee2e2] text-[#ef4444] px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix
                    the following errors:</div>
                <ul class="list-disc list-inside pl-7 text-xs font-medium space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── TOOLBAR (Search, Filters & Actions) ── --}}
        <div class="bg-white rounded-t-xl shadow-sm border border-gray-100 p-4 border-b-0 mb-0">
            <form method="GET" action="{{ route('admin.clients.index') }}"
                class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4">

                {{-- Left Side: Search + Filters --}}
                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 w-full xl:flex-1 xl:max-w-3xl">
                    <div class="relative w-full sm:flex-1">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[#108c2a]"></i>
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                            placeholder="Search Name, Phone, Email, City or GSTIN..."
                            class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-700 focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all placeholder-gray-400">
                    </div>

                    <select name="status"
                        class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none bg-white">
                        <option value="">All Status</option>
                        <option value="active" @selected(($status ?? '') === 'active')>Active</option>
                        <option value="inactive" @selected(($status ?? '') === 'inactive')>Inactive</option>
                    </select>

                    <select name="registration_type"
                        class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none bg-white">
                        <option value="">All GST Types</option>
                        <option value="registered" @selected(($registrationType ?? '') === 'registered')>Regular</option>
                        <option value="composition" @selected(($registrationType ?? '') === 'composition')>Composition</option>
                        <option value="unregistered" @selected(($registrationType ?? '') === 'unregistered')>Unregistered</option>
                        <option value="sez" @selected(($registrationType ?? '') === 'sez')>SEZ</option>
                        <option value="overseas" @selected(($registrationType ?? '') === 'overseas')>Overseas</option>
                    </select>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="bg-[#108c2a] hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                            <i data-lucide="filter" class="w-4 h-4"></i> Apply
                        </button>
                        @if (!empty($search) || !empty($status) || !empty($registrationType))
                            <a href="{{ route('admin.clients.index') }}"
                                class="bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 px-3 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-1.5 whitespace-nowrap"
                                title="Clear filters">
                                <i data-lucide="x" class="w-4 h-4"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Right Side: Actions --}}
                <div class="flex flex-row flex-wrap items-center gap-2 w-full xl:w-auto justify-start xl:justify-end mt-2 xl:mt-0">

                    @if (has_permission('clients.export'))
                        <button type="button" @click="exportCSV()"
                            class="bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 px-3 md:px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#108c2a]"></i> CSV
                        </button>
                        <button type="button" @click="exportPDF()"
                            class="bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 px-3 md:px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                            <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i> PDF
                        </button>
                    @endif

                    @if (has_module('bulk_import') && has_permission('clients.create'))
                        <a href="{{ route('admin.bulk-import.index') }}"
                            class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-3 md:px-4 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                            <i data-lucide="upload-cloud" class="w-4 h-4 text-[#108c2a]"></i> Bulk Import
                        </a>
                    @endif

                    @if (has_permission('clients.create'))
                        <button type="button" @click="openCreate()"
                            class="bg-[#108c2a] hover:bg-green-700 text-white px-4 md:px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add Client
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Client
                                Details</th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Contact
                            </th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">City
                            </th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider text-center">Status</th>
                            <th
                                class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider text-right">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($clients as $client)
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-full bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center text-sm font-bold shrink-0">
                                            {{ strtoupper(substr($client->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800">{{ $client->name }}</div>
                                            <div class="text-[12px] text-gray-400 mt-0.5">
                                                {{ $client->email ?? 'No email added' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400"></i>
                                        {{ $client->phone ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($client->city)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                            {{ $client->city }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic font-medium">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($client->is_active)
                                        <span class="bg-[#dcfce7] text-[#16a34a] px-3 py-1 rounded-md font-bold text-[10px] uppercase tracking-wider">Active</span>
                                    @else
                                        <span class="bg-gray-200 text-gray-500 px-3 py-1 rounded-md font-bold text-[10px] uppercase tracking-wider">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 transition-opacity">
                                        @if(has_permission('clients.update'))
                                        <button type="button" @click="openEdit({{ $client->id }})"
                                            class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 flex items-center justify-center transition-colors"
                                            title="Edit Client">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        @endif

                                        @if(has_permission('clients.delete'))
                                        <button type="button"
                                            @click="openDelete({{ $client->id }}, '{{ addslashes($client->name) }}')"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors"
                                            title="Delete Client">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="users" class="w-12 h-12 mb-3 text-gray-300"></i>
                                        <p class="text-sm font-medium">No clients found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($clients->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>

        <div x-cloak x-show="showCreateModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeAll()" x-show="showCreateModal"
                x-transition.opacity></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden" x-show="showCreateModal"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">

                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-[16px] font-bold text-gray-800 tracking-tight">Add New Client</h3>
                    <button type="button" @click="closeAll()"
                        class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('admin.clients.store') }}" method="POST"
                    @submit="BizAlert.loading('Saving...')">
                    @csrf
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto">

                        <div class="sm:col-span-2">
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Full Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="e.g. Rahul Sharma"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Phone Number <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="phone" required minlength="10" maxlength="10"
                                placeholder="e.g. 9876543210"
                                @input="$event.target.value = $event.target.value.replace(/[^0-9]/g, '')"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                            <p class="text-[10px] font-medium text-gray-400 mt-1">10 digits only. Must be unique.</p>
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Email Address</label>
                            <input type="email" name="email" placeholder="e.g. client@example.com"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Company Name</label>
                            <input type="text" name="company_name" placeholder="e.g. Tech Corp"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">GST Number</label>
                            <input type="text" name="gst_number" placeholder="e.g. 22AAAAA0000A1Z5"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Registration Type <span
                                    class="text-red-500">*</span></label>
                            <select name="registration_type"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all bg-white">
                                <option value="registered">Regular</option>
                                <option value="composition">Composition</option>
                                <option value="unregistered">Unregistered</option>
                                <option value="sez">SEZ</option>
                                <option value="overseas">Overseas</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-2">
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Address Details</label>
                            <input type="text" name="address" placeholder="Street Address"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all mb-3">
                            <div class="grid grid-cols-2 gap-3">
                                <input type="text" name="city" required placeholder="City *"
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                                <select name="state_id" required
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all bg-white">
                                    <option value="">Select State *</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="zip_code" placeholder="Zip Code"
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                                <input type="text" name="country" value="India" placeholder="Country"
                                    value="India"
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                            </div>
                        </div>



                        <div class="sm:col-span-2">
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Notes</label>
                            <textarea name="notes" rows="2" placeholder="Any client specifics..."
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all resize-none"></textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="relative inline-flex items-center cursor-pointer bg-gray-50 p-3 rounded-xl border border-gray-100 pr-5 w-fit">
                                <input type="checkbox" name="is_active" value="1" x-model="clientForm.is_active" class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#108c2a]"></div>
                                <span class="ms-3 text-sm font-bold text-gray-700">Active Client Account</span>
                            </label>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
                        <button type="button" @click="closeAll()"
                            class="px-5 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-md text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-brand-500 text-white rounded-md text-sm font-bold hover:bg-brand-600 transition-colors shadow-sm">Save
                            Client</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="showEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeAll()" x-show="showEditModal"
                x-transition.opacity></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden" x-show="showEditModal"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">

                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-[16px] font-bold text-gray-800 tracking-tight">Edit Client</h3>
                    <button type="button" @click="closeAll()"
                        class="text-gray-400 hover:text-red-500 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="`/admin/clients/${clientForm.id}`" method="POST"
                    @submit="BizAlert.loading('Updating...')">
                    @csrf
                    @method('PUT')
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto">

                        <div class="sm:col-span-2">
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Full Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="clientForm.name" required
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Phone Number <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="phone" x-model="clientForm.phone" required minlength="10"
                                maxlength="10"
                                @input="$event.target.value = $event.target.value.replace(/[^0-9]/g, ''); clientForm.phone = $event.target.value"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Email Address</label>
                            <input type="email" name="email" x-model="clientForm.email"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Company Name</label>
                            <input type="text" name="company_name" x-model="clientForm.company_name"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">GST Number</label>
                            <input type="text" name="gst_number" x-model="clientForm.gst_number"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Registration Type <span
                                    class="text-red-500">*</span></label>
                            <select name="registration_type" x-model="clientForm.registration_type"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all bg-white">
                                <option value="registered">Regular</option>
                                <option value="composition">Composition</option>
                                <option value="unregistered">Unregistered</option>
                                <option value="sez">SEZ</option>
                                <option value="overseas">Overseas</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-2">
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Address Details</label>
                            <input type="text" name="address" x-model="clientForm.address"
                                placeholder="Street Address"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all mb-3">
                            <div class="grid grid-cols-2 gap-3">
                                <input type="text" name="city" x-model="clientForm.city" required
                                    placeholder="City *"
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                                <select name="state_id" x-model="clientForm.state_id" required
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all bg-white">
                                    <option value="">Select State *</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="zip_code" x-model="clientForm.zip_code"
                                    placeholder="Zip Code"
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                                <input type="text" name="country" x-model="clientForm.country" placeholder="Country"
                                    class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Notes</label>
                            <textarea name="notes" x-model="clientForm.notes" rows="2"
                                class="w-full border border-gray-300 rounded-md px-3.5 py-2.5 text-sm focus:border-brand-500 outline-none transition-all resize-none"></textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="relative inline-flex items-center cursor-pointer bg-gray-50 p-3 rounded-xl border border-gray-100 pr-5 w-fit">
                                <input type="checkbox" name="is_active" value="1" x-model="clientForm.is_active" class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#108c2a]"></div>
                                <span class="ms-3 text-sm font-bold text-gray-700">Active Client Account</span>
                            </label>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
                        <button type="button" @click="closeAll()"
                            class="px-5 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-md text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-brand-500 text-white rounded-md text-sm font-bold hover:bg-brand-600 transition-colors shadow-sm">Update
                            Client</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeAll()" x-show="showDeleteModal"
                x-transition.opacity></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden text-center"
                x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                <div class="p-6 pt-8">
                    <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-triangle" class="w-8 h-8 text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Delete Client?</h3>
                    <p class="text-sm text-gray-500">Are you sure you want to delete <strong class="text-gray-800"
                            x-text="deleteForm.name"></strong>? This action cannot be undone.</p>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-center gap-3 bg-gray-50/50">
                    <button type="button" @click="closeAll()"
                        class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-md text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                    <form :action="`/admin/clients/${deleteForm.id}`" method="POST"
                        @submit="BizAlert.loading('Deleting...')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-6 py-2.5 bg-red-500 text-white rounded-md text-sm font-bold hover:bg-red-600 transition-colors shadow-sm">Yes,
                            Delete</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script>
        function clientManager(allClientsData) {
            return {
                allClients: allClientsData,
                showCreateModal: false,
                showEditModal: false,
                showDeleteModal: false,

                /// Fully expanded client state mapped to DB schema
                clientForm: {
                    id: '',
                    name: '',
                    phone: '',
                    email: '',
                    company_name: '',
                    gst_number: '',
                    registration_type: 'registered',
                    address: '',
                    city: '',
                    state_id: '',
                    zip_code: '',
                    country: '',
                    notes: '',
                    is_active: true
                },
                deleteForm: {
                    id: '',
                    name: ''
                },

                openCreate() {
                    document.body.classList.add('modal-open')
                    this.closeAll();
                    // Clear out data just in case an edit was cancelled previously
                    this.clientForm = {
                        id: '',
                        name: '',
                        phone: '',
                        email: '',
                        company_name: '',
                        gst_number: '',
                        registration_type: 'registered',
                        address: '',
                        city: '',
                        state_id: '',
                        zip_code: '',
                        country: 'India',
                        notes: '',
                        is_active: true
                    };
                    this.showCreateModal = true;
                },

                openEdit(id) {
                    this.closeAll();
                    let client = this.allClients.find(c => c.id === id);
                    if (!client) return;
                    this.clientForm = {
                        id: client.id,
                        name: client.name,
                        phone: client.phone || '',
                        email: client.email || '',
                        company_name: client.company_name || '',
                        gst_number: client.gst_number || '',
                        registration_type: client.registration_type || 'registered',
                        address: client.address || '',
                        city: client.city || '',
                        state_id: client.state_id || '',
                        zip_code: client.zip_code || '',
                        country: client.country || 'India',
                        notes: client.notes || '',
                        is_active: client.is_active === true || client.is_active === 1
                    };
                    this.showEditModal = true;
                },

                openDelete(id, name) {
                    this.closeAll();
                    this.deleteForm = {
                        id: id,
                        name: name
                    };
                    this.showDeleteModal = true;
                },

                closeAll() {
                    document.body.classList.remove('modal-open')
                    this.showCreateModal = false;
                    this.showEditModal = false;
                    this.showDeleteModal = false;
                },
                // --- EXPORT ALL DATA TO CSV ---
                exportCSV() {
                    // Define all headers for the CSV
                    const headers = ["Name", "Email", "Phone", "Company", "GST", "Address", "City", "State", "Zip",
                        "Country", "Notes"
                    ];

                    // Map the data rows
                    const rows = this.allClients.map(client => [
                        `"${client.name || ''}"`,
                        `"${client.email || ''}"`,
                        `"${client.phone || ''}"`,
                        `"${client.company_name || ''}"`,
                        `"${client.gst_number || ''}"`,
                        `"${client.address || ''}"`,
                        `"${client.city || ''}"`,
                        `"${client.state || ''}"`,
                        `"${client.zip_code || ''}"`,
                        `"${client.country || ''}"`,
                        `"${client.notes || ''}"`
                    ]);

                    let csvContent = headers.join(",") + "\n" + rows.map(e => e.join(",")).join("\n");

                    // Trigger Download
                    const blob = new Blob([csvContent], {
                        type: 'text/csv;charset=utf-8;'
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.setAttribute("download", "All_Clients_Full_Report.csv");
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    BizAlert.toast('Full CSV Exported!', 'success');
                },

                // --- EXPORT SPECIFIC DATA TO PDF ---
                exportPDF() {
                    const {
                        jsPDF
                    } = window.jspdf;
                    const doc = new jsPDF('l', 'mm', 'a4'); // Landscape orientation for better spacing

                    doc.setFontSize(16);
                    doc.setTextColor(16, 140, 42); // Brand Green
                    doc.text("Qlinkon BIZNESS - Client Contact Directory", 14, 15);

                    // Define headers as requested: Name, Phone, Email, GST, City
                    const head = [
                        ["Client Name", "Phone Number", "Email Address", "GST Number", "City"]
                    ];

                    // Extract only the 5 specific fields requested
                    const body = this.allClients.map(client => [
                        client.name || '-',
                        client.phone || '-',
                        client.email || '-',
                        client.gst_number || '-',
                        client.city || '-'
                    ]);

                    doc.autoTable({
                        head: head,
                        body: body,
                        startY: 22,
                        theme: 'striped',
                        headStyles: {
                            fillColor: [16, 140, 42],
                            fontStyle: 'bold'
                        },
                        styles: {
                            fontSize: 10,
                            cellPadding: 4
                        },
                        columnStyles: {
                            0: {
                                cellWidth: 50
                            }, // Name
                            1: {
                                cellWidth: 40
                            }, // Phone
                            3: {
                                cellWidth: 50
                            }, // GST
                        }
                    });

                    doc.save("Clients_Contact_Report.pdf");
                    BizAlert.toast('PDF Report Exported!', 'success');
                }



            }
        }
    </script>
@endpush
