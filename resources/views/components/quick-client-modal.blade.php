@props(['states' => []])

<div x-show="isClientModalOpen" x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
    <div class="bg-white w-full max-w-lg rounded shadow-2xl flex flex-col" x-show="isClientModalOpen" x-transition
        @click.away="isClientModalOpen = false">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-[15px] font-bold text-gray-800">Quick Add Client</h3>
            <button type="button" @click="isClientModalOpen = false" class="text-gray-400 hover:text-red-500"><i
                    data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="p-6 grid grid-cols-2 gap-4">
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Name <span
                        class="text-red-500">*</span></label>
                <input type="text" x-model="newClient.name"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm outline-none focus:border-[#108c2a]">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Phone <span
                        class="text-red-500">*</span></label>
                {{-- 🌟 Strips non-numbers and forces max 10 chars --}}
                <input type="text" x-model="newClient.phone"
                    @input="newClient.phone = newClient.phone.replace(/[^0-9]/g, '').slice(0, 10)"
                    placeholder="10-digit number"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm outline-none focus:border-[#108c2a]">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">City <span
                        class="text-red-500">*</span></label>
                {{-- 🌟 Strips everything except Letters and Spaces --}}
                <input type="text" x-model="newClient.city"
                    @input="newClient.city = newClient.city.replace(/[^a-zA-Z\s]/g, '')" placeholder="City name"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm outline-none focus:border-[#108c2a]">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">State <span
                        class="text-red-500">*</span></label>
                <select x-model="newClient.state_id"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm outline-none focus:border-[#108c2a] bg-white">
                    <option value="">Select State</option>
                    @foreach ($states as $state)
                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">GST Treatment
                    <span class="text-red-500">*</span></label>
                <select x-model="newClient.registration_type"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm outline-none focus:border-[#108c2a] bg-white">
                    <option value="unregistered">Unregistered</option>
                    <option value="regular">Regular</option>
                    <option value="composition">Composition</option>
                </select>
            </div>
        </div>
        <div class="p-5 border-t border-gray-100 bg-white grid grid-cols-2 gap-3">
            <button type="button" @click="isClientModalOpen = false"
                class="bg-gray-100 text-gray-700 font-bold text-sm py-2.5 rounded">Cancel</button>
            <button type="button" @click="saveQuickClient()"
                class="bg-[#108c2a] text-white font-bold text-sm py-2.5 rounded">Save Client</button>
        </div>
    </div>
</div>
