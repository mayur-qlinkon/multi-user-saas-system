@extends('layouts.platform')

@section('title', 'Onboard Company')
@section('header', 'Onboard New Company')

@section('content')
<div class="w-full">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('platform.companies.index') }}" class="hover:text-brand-600 font-medium">Companies</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        <span class="text-gray-800 font-semibold">Onboard New</span>
    </div>

    @if (session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm font-medium px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('platform.companies.store') }}"
        x-data="slugChecker('{{ route('platform.companies.slug-check') }}')"
        class="space-y-6">
        @csrf

        {{-- Company Details --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline stroke-linecap="round" stroke-linejoin="round" points="9 22 9 12 15 12 15 22"/></svg>
                    Company Details
                </h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Company Name --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Company Name <span class="text-red-500">*</span></label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}"
                        @input="autoSlug($event.target.value)"
                        class="w-full border border-gray-200 px-3 py-2.5 rounded-xl text-sm focus:outline-none focus:border-brand-500 @error('company_name') border-red-400 @enderror"
                        placeholder="Acme Corporation" required>
                    @error('company_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Company Email --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Company Email <span class="text-red-500">*</span></label>
                    <input type="email" name="company_email" value="{{ old('company_email') }}"
                        class="w-full border border-gray-200 px-3 py-2.5 rounded-xl text-sm focus:outline-none focus:border-brand-500 @error('company_email') border-red-400 @enderror"
                        placeholder="contact@acme.com" required>
                    @error('company_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Slug --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Company Slug</label>
                    <div class="relative">
                        <input type="text" name="slug" id="slug-input"
                            :value="slug"
                            @input="slug = $event.target.value; checkSlug()"
                            class="w-full border border-gray-200 px-3 py-2.5 pr-36 rounded-xl text-sm font-mono focus:outline-none focus:border-brand-500 @error('slug') border-red-400 @enderror"
                            placeholder="acme-corporation">
                        {{-- Status indicator --}}
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1.5 text-xs font-semibold">
                            <template x-if="slugStatus === 'checking'">
                                <span class="text-gray-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                    Checking…
                                </span>
                            </template>
                            <template x-if="slugStatus === 'available'">
                                <span class="text-green-600 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Available
                                </span>
                            </template>
                            <template x-if="slugStatus === 'taken'">
                                <span class="text-red-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    Taken
                                </span>
                            </template>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">Lowercase letters, numbers and hyphens only. Leave blank to auto-generate.</p>
                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        maxlength="10"
                        minlength="10"
                        pattern="[0-9]{10}"
                        inputmode="numeric"
                        class="w-full border border-gray-200 px-3 py-2.5 rounded-xl text-sm focus:outline-none focus:border-brand-500"
                        placeholder="+91 98765 43210">
                </div>

                {{-- GST --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">GST Number</label>
                    <input type="text" name="gst_number" value="{{ old('gst_number') }}" maxlength="15"
                        class="w-full border border-gray-200 px-3 py-2.5 rounded-xl text-sm font-mono uppercase focus:outline-none focus:border-brand-500"
                        placeholder="22AAAAA0000A1Z5">
                </div>

                {{-- City --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">City</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                        class="w-full border border-gray-200 px-3 py-2.5 rounded-xl text-sm focus:outline-none focus:border-brand-500"
                        placeholder="Mumbai">
                </div>

                {{-- State --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">State <span class="text-red-500">*</span></label>
                    <select name="state_id" required
                        class="w-full border border-gray-200 px-3 py-2.5 rounded-xl text-sm focus:outline-none focus:border-brand-500 bg-white @error('state_id') border-red-400 @enderror">
                        <option value="">Select state…</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->id }}" {{ old('state_id') == $state->id ? 'selected' : '' }}>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('state_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Status --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }} class="accent-brand-600">
                            <span class="text-sm font-medium text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_active" value="0" {{ old('is_active') === '0' ? 'checked' : '' }} class="accent-red-500">
                            <span class="text-sm font-medium text-gray-700">Inactive</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        {{-- Owner Account --}}
        <div class="bg-white border border-orange-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-orange-100 bg-orange-50/40">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Primary Owner Account
                    <span class="ml-auto text-xs font-normal text-orange-500">Created once — cannot be changed here later</span>
                </h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}"
                        class="w-full border border-gray-200 px-3 py-2.5 rounded-xl text-sm focus:outline-none focus:border-orange-400 @error('owner_name') border-red-400 @enderror"
                        placeholder="John Doe" required>
                    @error('owner_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Login Email <span class="text-red-500">*</span></label>
                    <input type="email" name="owner_email" value="{{ old('owner_email') }}"
                        class="w-full border border-gray-200 px-3 py-2.5 rounded-xl text-sm focus:outline-none focus:border-orange-400 @error('owner_email') border-red-400 @enderror"
                        placeholder="john@acme.com" required>
                    @error('owner_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password <span class="text-red-500">*</span></label>
                    <div class="flex gap-2" x-data="{ pwd: '{{ old('owner_password') }}' }">
                        <input type="text" name="owner_password" x-model="pwd"
                            class="flex-1 border border-gray-200 px-3 py-2.5 rounded-xl text-sm font-mono focus:outline-none focus:border-orange-400 @error('owner_password') border-red-400 @enderror"
                            placeholder="Min. 8 characters" required>
                        <button type="button"
                            @click="pwd = Math.random().toString(36).slice(-8) + Math.random().toString(36).slice(-4).toUpperCase() + '1!'"
                            class="px-4 py-2.5 bg-orange-50 hover:bg-orange-100 border border-orange-200 text-orange-700 text-xs font-bold rounded-xl transition-colors shrink-0">
                            Generate
                        </button>
                    </div>
                    @error('owner_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('platform.companies.index') }}"
                class="text-sm font-semibold text-gray-500 hover:text-gray-800 transition-colors">
                ← Cancel
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Onboard Company
            </button>
        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
function slugChecker(baseUrl) {
    return {
        slug: '{{ old('slug') }}',
        slugStatus: '{{ old('slug') ? 'available' : '' }}',
        _timer: null,
        _checkUrl: baseUrl,

        autoSlug(name) {
            if (this.slug && this._userEdited) return;
            this.slug = name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            this.checkSlug();
        },

        checkSlug() {
                if (!this.slug) { this.slugStatus = ''; return; }
                
                this.slugStatus = 'checking';
                clearTimeout(this._timer);
                
                this._timer = setTimeout(async () => {
                    const url = this._checkUrl + '?slug=' + encodeURIComponent(this.slug);
                    
                    try {
                        const res = await fetch(url, { 
                            headers: { 
                                'Accept': 'application/json', 
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content 
                            } 
                        });
                        
                        // 🌟 THE FIX: Stop if the server returns a 404/500 error!
                        if (!res.ok) {
                            console.error('Server error during slug check:', res.status);
                            this.slugStatus = ''; // Reset status instead of saying 'taken'
                            return;
                        }
                        
                        const data = await res.json();
                        this.slugStatus = data.available ? 'available' : 'taken';
                        
                        if (data.available) {
                            this.slug = data.slug;
                        }
                        
                    } catch (error) { 
                        console.error('Network error:', error);
                        this.slugStatus = ''; 
                    }
                }, 450);
            }
    };
}
</script>
@endsection
