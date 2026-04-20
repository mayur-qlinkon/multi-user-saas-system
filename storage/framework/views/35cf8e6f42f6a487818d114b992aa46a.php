

<?php $__env->startSection('title', 'Create Page'); ?>

<?php $__env->startSection('header-title'); ?>
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('admin.pages.index')); ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Create Page</h1>
            <p class="text-xs text-gray-400 font-medium mt-1">Add new content to your public storefront</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .form-label { display: block; font-size: 12px; font-weight: 700; color: #4b5563; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
    
    /* Standard inputs */
    .form-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 10px 14px; font-size: 14px; color: #1f2937; background: #fff; transition: all 150ms; outline: none; }
    .form-input:focus { border-color: var(--brand-500); box-shadow: 0 0 0 4px color-mix(in srgb, var(--brand-500) 10%, transparent); }
    
    /* Input Groups (Prefixes) */
    .input-group { display: flex; align-items: stretch; border: 1.5px solid #e5e7eb; border-radius: 10px; overflow: hidden; transition: all 150ms; background: #fff; }
    .input-group:focus-within { border-color: var(--brand-500); box-shadow: 0 0 0 4px color-mix(in srgb, var(--brand-500) 10%, transparent); }
    .input-group-prefix { display: flex; align-items: center; padding: 0 12px; background: #f9fafb; border-right: 1.5px solid #e5e7eb; color: #9ca3af; font-size: 13px; font-weight: 500; user-select: none; }
    .input-group-field { flex: 1; width: 100%; padding: 10px 12px; font-size: 13px; color: #1f2937; background: transparent; outline: none; border: none; }
    
    .card-box { background: #fff; border: 1px solid #f1f5f9; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="pb-10 w-full max-w-[1600px] mx-auto">
        
        <div class="mb-6 flex flex-col sm:flex-row flex-wrap sm:items-center justify-between gap-4">
            <div>
                
                <p class="text-sm text-gray-500 mt-1">Add a new legal, informational, or custom page to your storefront.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('admin.pages.index')); ?>"
                    class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-5 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Pages
                </a>
            </div>
        </div>
    <form method="POST" action="<?php echo e(route('admin.pages.store')); ?>" class="w-full">    
        <?php echo csrf_field(); ?>


        <div class="flex flex-col lg:flex-row gap-6 w-full">
            
            
            
            <div class="flex-1 min-w-0 space-y-6">
                
                <div class="card-box">
                    <div class="mb-5">
                        <label class="form-label">Page Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="<?php echo e(old('title')); ?>" required
                               class="form-input text-lg font-bold" placeholder="e.g., Privacy Policy">
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="form-label flex justify-between">
                            <span>Page Content</span>
                            <span class="text-gray-400 font-normal normal-case tracking-normal">Supports HTML</span>
                        </label>
                        
                        
                        <textarea name="content" rows="18" 
                                  class="form-input font-mono text-[13px] leading-relaxed" 
                                  placeholder="<h2>Write your content here...</h2>"><?php echo e(old('content')); ?></textarea>
                        
                        <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

            </div>

            
            
            <div class="w-full lg:w-[320px] xl:w-[360px] flex-shrink-0 space-y-6">
                
                
                <div class="card-box bg-gray-50/50">
                    <div class="flex items-center justify-between mb-6">
                        <label class="form-label mb-0 text-gray-700">Visibility</label>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="is_published" value="0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_published" value="1" class="sr-only peer" <?php echo e(old('is_published') ? 'checked' : ''); ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-[13px] font-bold text-white transition-all hover:opacity-95" style="background: var(--brand-600);">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Create Page
                    </button>
                </div>

                
                <div class="card-box">
                    <h3 class="text-[12px] font-black text-gray-800 uppercase tracking-wider mb-5 pb-3 border-b border-gray-100">Page Attributes</h3>
                    
                    <div class="mb-5">
                        <label class="form-label">Page Type <span class="text-red-500">*</span></label>
                        <select name="type" class="form-input bg-gray-50 cursor-pointer" required>
                            <option value="">Select a type...</option>
                            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e(old('type') == $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1.5 leading-snug">This helps categorize links in your public storefront footer.</p>
                        <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="form-label">URL Slug</label>
                        
                        
                        <div class="input-group">
                            <span class="input-group-prefix">/page/</span>
                            <input type="text" name="slug" value="<?php echo e(old('slug')); ?>" 
                                   class="input-group-field font-mono" placeholder="auto-generated">
                        </div>
                        
                        <p class="text-[11px] text-gray-400 mt-1.5 leading-snug">Leave blank to auto-generate from the title.</p>
                        <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                
                <div class="card-box">
                    <h3 class="text-[12px] font-black text-gray-800 uppercase tracking-wider mb-5 pb-3 border-b border-gray-100">Search Engine (SEO)</h3>
                    
                    <div class="mb-5">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="seo_title" value="<?php echo e(old('seo_title')); ?>" 
                               class="form-input" placeholder="Title for Google search">
                        <?php $__errorArgs = ['seo_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="form-label">Meta Description</label>
                        <textarea name="seo_description" rows="4" class="form-input text-[13px]" 
                                  placeholder="A brief summary of this page..."><?php echo e(old('seo_description')); ?></textarea>
                        <?php $__errorArgs = ['seo_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/storefront-sections/pages/create.blade.php ENDPATH**/ ?>