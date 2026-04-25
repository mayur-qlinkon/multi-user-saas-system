<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'payment_method_id',
    'selected' => null,
    'label' => 'Payment Method',
    'required' => true,
    'showIcons' => true,
    'xModel' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'name' => 'payment_method_id',
    'selected' => null,
    'label' => 'Payment Method',
    'required' => true,
    'showIcons' => true,
    'xModel' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $methods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();
?>

<div class="w-full">
    <?php if($label): ?>
        <label class="block text-[12px] font-bold text-gray-600 uppercase tracking-wider mb-2">
            <?php echo e($label); ?> <?php echo $required ? '<span class="text-red-500">*</span>' : ''; ?>

        </label>
    <?php endif; ?>

    <div class="relative group">
        <select
            name="<?php echo e($name); ?>"
            id="<?php echo e($name); ?>"
            <?php echo e($required ? 'required' : ''); ?>

            <?php if($xModel): ?> x-model="<?php echo e($xModel); ?>" <?php endif; ?>
            <?php if($showIcons): ?> data-payment-selector <?php endif; ?>
            <?php echo e($attributes->merge(['class' => 'w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 text-sm text-gray-800 font-medium focus:border-[#108c2a] focus:ring-1 focus:ring-[#108c2a] outline-none transition-all appearance-none bg-white shadow-sm cursor-pointer'])); ?>

        >
            <option value="">-- Select Method --</option>

            <?php $__currentLoopData = $methods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option
                    value="<?php echo e($method->id); ?>"
                    data-slug="<?php echo e($method->slug); ?>"
                    data-online="<?php echo e($method->is_online); ?>"
                    <?php echo e(old($name, $selected) == $method->id ? 'selected' : ''); ?>

                >
                    <?php echo e($method->name ?? $method->label); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <?php if($showIcons): ?>
            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                id="icon-<?php echo e($name); ?>">
                <i data-lucide="wallet" class="w-4 h-4"></i>
            </div>
        <?php endif; ?>

        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <i data-lucide="chevron-down" class="w-4 h-4"></i>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('7279e71c-b01d-4d68-8624-6e41a10af503')): $__env->markAsRenderedOnce('7279e71c-b01d-4d68-8624-6e41a10af503'); ?>
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
                    lucide.createIcons();
                }
            }
        });
    </script>
<?php endif; ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas\resources\views/components/payment-method-select.blade.php ENDPATH**/ ?>