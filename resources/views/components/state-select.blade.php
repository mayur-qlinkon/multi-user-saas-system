@props(['selected' => null, 'name' => 'state_id', 'label' => 'State'])

<div class="space-y-1.5">
    <label for="{{ $name }}"
        class="text-[11px] font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> {{ $label }}
    </label>
    <div class="relative">
        <select name="{{ $name }}" id="{{ $name }}"
            {{ $attributes->merge(['class' => 'w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none appearance-none cursor-pointer font-medium bg-gray-50/50']) }}>
            <option value="">Select State</option>
            @foreach (\App\Models\State::where('is_active', true)->orderBy('name')->get() as $state)
                {{-- 🌟 Value is now the Name to match DB schema and GST logic --}}
                <option value="{{ $state->name }}" {{ $selected === $state->name ? 'selected' : '' }}
                    data-code="{{ $state->code }}">
                    {{ $state->name }} ({{ $state->code }})
                </option>
            @endforeach
        </select>
        <i data-lucide="chevron-down"
            class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
    </div>
</div>
