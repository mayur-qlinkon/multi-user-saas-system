@props([
    'name' => 'payment_method_id',
    'selected' => null,
    'label' => 'Payment Method',
    'required' => true,
    'showIcons' => true,
])

@php
    // Self-hydrating: Always gets the latest active methods for this company
    $methods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();
@endphp

<div class="w-full">
    @if ($label)
        <label class="block text-[12px] font-bold text-gray-600 uppercase tracking-wider mb-2">
            {{ $label }} {!! $required ? '<span class="text-red-500">*</span>' : '' !!}
        </label>
    @endif

    <div class="relative group">
        <select name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-800 font-medium focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all appearance-none bg-white shadow-sm cursor-pointer']) }}>
            <option value="">-- Select Method --</option>
            @foreach ($methods as $method)
                <option :value="{{ $method->id }}" data-slug="{{ $method->slug }}"
                    data-online="{{ $method->is_online }}" {{ old($name, $selected) == $method->id ? 'selected' : '' }}>
                    {{-- 🌟 Fallback to name if label doesn't exist in your DB --}}
                    {{ $method->name ?? $method->label }}
                </option>
            @endforeach
        </select>

        {{-- Dynamic Icon Container (Updates via JS) --}}
        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
            id="icon-{{ $name }}">
            <i data-lucide="wallet" class="w-4 h-4"></i>
        </div>

        {{-- Standard Down Arrow --}}
        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <i data-lucide="chevron-down" class="w-4 h-4"></i>
        </div>
    </div>
</div>

{{-- Small JS snippet to handle Icon Switching dynamically --}}
@once
    <script>
        document.addEventListener('change', function(e) {
            if (e.target.matches('select[data-payment-selector]')) {
                const select = e.target;
                const container = document.getElementById('icon-' + select.name);
                const slug = select.options[select.selectedIndex].getAttribute('data-slug');

                let iconName = 'wallet';
                if (slug === 'cash') iconName = 'banknote';
                if (slug === 'upi') iconName = 'qr-code';
                if (slug === 'card') iconName = 'credit-card';
                if (slug === 'bank_transfer') iconName = 'landmark';

                if (container) {
                    container.innerHTML = `<i data-lucide="${iconName}" class="w-4 h-4"></i>`;
                    lucide.createIcons(); // Re-render icons
                }
            }
        });
    </script>
@endonce
