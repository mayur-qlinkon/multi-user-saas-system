@extends('layouts.app')

@section('title', 'Manage Subscriptions - Qlinkon Super Admin')
@section('header', 'Client Subscriptions')

@section('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }

        body.modal-open {
            overflow: hidden;
        }
    </style>
@endsection

@section('content')
    <div class="pb-10" x-data="subscriptionManager(@js($subscriptions), @js($companies), @js($plans))">

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Client Subscriptions</h1>
                <p class="text-sm text-gray-500 mt-1">Assign and manage SaaS plans for your client companies.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="openModal()"
                    class="bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="link" class="w-4 h-4"></i> Assign Subscription
                </button>
            </div>
        </div>

        @if (session('success'))
            <div
                class="bg-green-50 text-green-700 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-green-100 mb-6 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 text-red-600 px-5 py-4 rounded-xl text-sm font-bold shadow-sm border border-red-100 mb-6">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> Please fix
                    the following errors:</div>
                <ul class="list-disc list-inside pl-7 text-xs font-medium space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Company
                            </th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Plan
                            </th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Duration
                            </th>
                            <th class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Status
                            </th>
                            <th
                                class="px-6 py-4 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider text-right">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($subscriptions as $sub)
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800 text-sm">
                                        {{ $sub->company->name ?? 'Unknown Company' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-brand-50 text-brand-700 border border-brand-100">
                                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                        {{ $sub->plan->name ?? 'Unknown Plan' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-600 font-medium">
                                        <div class="mb-0.5"><span class="text-gray-400">Start:</span>
                                            {{ $sub->starts_at ? \Carbon\Carbon::parse($sub->starts_at)->format('M d, Y') : 'Immediate' }}
                                        </div>
                                        <div><span class="text-gray-400">Exp:</span>
                                            {{ $sub->expires_at ? \Carbon\Carbon::parse($sub->expires_at)->format('M d, Y') : 'Lifetime / Null' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($sub->is_active)
                                        <span
                                            class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Active</span>
                                    @else
                                        <span
                                            class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button" @click="openModal({{ $sub->id }})"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-bold flex items-center gap-1.5 ml-auto">
                                        <i data-lucide="settings-2" class="w-3.5 h-3.5"></i> Update
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="link-2" class="w-12 h-12 mb-3 text-gray-300"></i>
                                        <p class="text-sm font-medium">No subscriptions active.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-cloak x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeAll()" x-show="showModal"
                x-transition.opacity></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col"
                x-show="showModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">

                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center">
                            <i data-lucide="link" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-[16px] font-bold text-gray-800 tracking-tight"
                            x-text="isEditing ? 'Update Subscription' : 'Assign Subscription'"></h3>
                    </div>
                    <button type="button" @click="closeAll()"
                        class="text-gray-400 hover:text-red-500 transition-colors p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('platform.subscriptions.assign') }}" method="POST">
                    @csrf

                    <div class="p-6 space-y-5">

                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Select Company <span
                                    class="text-red-500">*</span></label>
                            <select name="company_id" x-model="form.company_id" required :disabled="isEditing"
                                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all disabled:bg-gray-100 disabled:text-gray-500 cursor-pointer">
                                <option value="">-- Select a Client Company --</option>
                                <template x-for="company in companies" :key="company.id">
                                    <option :value="company.id" x-text="company.name"></option>
                                </template>
                            </select>
                            <template x-if="isEditing">
                                <div>
                                    <input type="hidden" name="company_id" :value="form.company_id">
                                    <p class="text-[10px] text-gray-500 mt-1">Company cannot be changed during an update.
                                    </p>
                                </div>
                            </template>
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Select Plan <span
                                    class="text-red-500">*</span></label>
                            <select name="plan_id" x-model="form.plan_id" required
                                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all cursor-pointer">
                                <option value="">-- Choose a Subscription Plan --</option>
                                <template x-for="plan in plans" :key="plan.id">
                                    <option :value="plan.id" x-text="`${plan.name} (₹${plan.price}/mo)`"></option>
                                </template>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Starts At</label>
                                <input type="date" name="starts_at" x-model="form.starts_at"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-gray-700 mb-1.5">Expires At</label>
                                <input type="date" name="expires_at" x-model="form.expires_at"
                                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all">
                            </div>
                        </div>

                        <div class="pt-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active"
                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600">
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-gray-700">Subscription is Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50">
                        <button type="button" @click="closeAll()"
                            class="px-5 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit"
                            class="px-6 py-2 bg-brand-600 text-white rounded-lg text-sm font-bold hover:bg-brand-700 transition-colors shadow-sm"
                            x-text="isEditing ? 'Update Details' : 'Assign Now'"></button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        function subscriptionManager(allSubscriptions, allCompanies, allPlans) {
            return {
                subscriptions: allSubscriptions,
                companies: allCompanies,
                plans: allPlans,

                showModal: false,
                isEditing: false,

                form: {
                    company_id: '',
                    plan_id: '',
                    starts_at: '',
                    expires_at: '',
                    is_active: true
                },

                // Helper to format DB datetime string to HTML date input format (YYYY-MM-DD)
                formatDateForInput(dateString) {
                    if (!dateString) return '';
                    return dateString.split('T')[0];
                },

                openModal(subId = null) {
                    document.body.classList.add('modal-open');

                    if (subId) {
                        // Editing existing
                        this.isEditing = true;
                        let sub = this.subscriptions.find(s => s.id === subId);

                        this.form = {
                            company_id: sub.company_id,
                            plan_id: sub.plan_id,
                            starts_at: this.formatDateForInput(sub.starts_at),
                            expires_at: this.formatDateForInput(sub.expires_at),
                            is_active: sub.is_active == 1 || sub.is_active == true
                        };
                    } else {
                        // Creating new
                        this.isEditing = false;

                        // Default start date to today
                        let today = new Date().toISOString().split('T')[0];

                        this.form = {
                            company_id: '',
                            plan_id: '',
                            starts_at: today,
                            expires_at: '',
                            is_active: true
                        };
                    }

                    this.showModal = true;
                },

                closeAll() {
                    document.body.classList.remove('modal-open');
                    this.showModal = false;
                }
            }
        }
    </script>
@endsection
