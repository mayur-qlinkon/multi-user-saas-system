<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['selected' => null, 'name' => 'state_id', 'label' => 'State']));

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

foreach (array_filter((['selected' => null, 'name' => 'state_id', 'label' => 'State']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="space-y-1.5">
    <label for="<?php echo e($name); ?>"
        class="text-[11px] font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> <?php echo e($label); ?>

    </label>
    <div class="relative">
        <select name="<?php echo e($name); ?>" id="<?php echo e($name); ?>"
            <?php echo e($attributes->merge(['class' => 'w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none appearance-none cursor-pointer font-medium bg-gray-50/50'])); ?>>
            <option value="">Select State</option>
            <?php $__currentLoopData = \App\Models\State::where('is_active', true)->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <option value="<?php echo e($state->name); ?>" <?php echo e($selected === $state->name ? 'selected' : ''); ?>

                    data-code="<?php echo e($state->code); ?>">
                    <?php echo e($state->name); ?> (<?php echo e($state->code); ?>)
                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <i data-lucide="chevron-down"
            class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
    </div>
</div>
<?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas\resources\views/components/state-select.blade.php ENDPATH**/ ?>