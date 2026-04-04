

<?php $__env->startSection('title', 'Edit Announcement'); ?>

<?php $__env->startSection('header-title'); ?>
    <div>
        <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">HRM / Announcements</h1>
        <p class="text-xs text-gray-400 font-medium mt-0.5">Update an existing company announcement.</p>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] { display: none !important; }
    .section-card { background: #fff; border: 1.5px solid #f1f5f9; border-radius: 16px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.01); }
    .field-label { display: block; font-size: 11px; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
    .field-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: #1f2937; outline: none; transition: all 150ms ease; background: #fff; }
    .field-input:focus { border-color: var(--brand-500); box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-500) 15%, transparent); }
    .field-input.has-error { border-color: #ef4444; }
    .field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="pb-12" x-data="announcementForm()">

    <form action="<?php echo e(route('admin.hrm.announcements.update', $announcement->id)); ?>" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('admin.hrm.announcements.index')); ?>"
                    class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors shadow-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <h2 class="text-lg font-black text-gray-800 tracking-tight">Edit Announcement</h2>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('admin.hrm.announcements.index')); ?>" 
                    class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-50 transition-colors bg-white shadow-sm">
                    Cancel
                </a>
                <button type="submit" :disabled="isSubmitting"
                    class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md transition-all active:scale-95 flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="!isSubmitting"><i data-lucide="save" class="w-4 h-4 inline-block mr-1 pb-0.5"></i> Update Announcement</span>
                    <span x-show="isSubmitting" x-cloak class="flex items-center gap-2">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Saving...
                    </span>
                </button>
            </div>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

            
            <div class="lg:col-span-2 space-y-6">
                
                
                <div class="section-card">
                    <div class="mb-5 pb-3 border-b border-gray-100 flex items-center gap-2 text-gray-800">
                        <i data-lucide="file-text" class="w-4 h-4 text-brand-500"></i>
                        <h3 class="text-[13px] font-black uppercase tracking-wider">Message Content</h3>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label for="title" class="field-label">Announcement Title <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" value="<?php echo e(old('title', $announcement->title)); ?>" required
                                placeholder="E.g., Q3 Company Townhall Meeting"
                                class="field-input <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> has-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="field-error"><i data-lucide="alert-circle" class="w-3 h-3"></i> <?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label for="content" class="field-label">Detailed Message <span class="text-red-500">*</span></label>
                            <textarea id="content" name="content" rows="10" required
                                placeholder="Write the full announcement details here..."
                                class="field-input resize-y min-h-[200px] <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> has-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('content', $announcement->content)); ?></textarea>
                            <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="field-error"><i data-lucide="alert-circle" class="w-3 h-3"></i> <?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                
                <div class="section-card">
                    <div class="mb-5 pb-3 border-b border-gray-100 flex items-center gap-2 text-gray-800">
                        <i data-lucide="paperclip" class="w-4 h-4 text-blue-500"></i>
                        <h3 class="text-[13px] font-black uppercase tracking-wider">Media & Attachments</h3>
                    </div>

                    <div>
                        <label class="field-label">Update Attached File (Optional)</label>
                        
                        <?php if($announcement->attachment): ?>
                            <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                        <i data-lucide="file" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-800">Current File</p>
                                        <p class="text-[11px] text-gray-500"><?php echo e($announcement->attachment_name ?? 'document.pdf'); ?></p>
                                    </div>
                                </div>
                                <a href="<?php echo e($announcement->attachment_url); ?>" target="_blank" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-white px-3 py-1.5 rounded-md border border-blue-200">View File</a>
                            </div>
                        <?php endif; ?>

                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-brand-500 hover:bg-brand-50/50 transition-colors relative group">
                            <div class="space-y-1 text-center">
                                <i data-lucide="upload-cloud" class="mx-auto h-10 w-10 text-gray-400 group-hover:text-brand-500 transition-colors"></i>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="attachment" class="relative cursor-pointer rounded-md font-bold text-brand-600 hover:text-brand-500 focus-within:outline-none">
                                        <span><?php echo e($announcement->attachment ? 'Replace file' : 'Upload a file'); ?></span>
                                        <input id="attachment" name="attachment" type="file" class="sr-only" @change="fileName = $event.target.files[0]?.name">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-[11px] text-gray-500 font-medium">PDF, DOCX, PNG, JPG up to 10MB</p>
                            </div>
                        </div>
                        <p x-show="fileName" x-cloak class="mt-2 text-sm font-bold text-green-600 flex items-center gap-1.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i> <span x-text="fileName"></span> selected.
                        </p>
                        <?php $__errorArgs = ['attachment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><i data-lucide="alert-circle" class="w-3 h-3"></i> <?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

            </div>

            
            <div class="space-y-6">
                
                
                <div class="section-card">
                    <div class="mb-5 pb-3 border-b border-gray-100 flex items-center gap-2 text-gray-800">
                        <i data-lucide="settings-2" class="w-4 h-4 text-gray-500"></i>
                        <h3 class="text-[13px] font-black uppercase tracking-wider">Classification</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="type" class="field-label">Type</label>
                            <select id="type" name="type" class="field-input cursor-pointer bg-gray-50">
                                <?php $__currentLoopData = $typeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e(old('type', $announcement->type) == $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="field-error"><i data-lucide="alert-circle" class="w-3 h-3"></i> <?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label for="priority" class="field-label">Priority Level</label>
                            <select id="priority" name="priority" class="field-input cursor-pointer bg-gray-50">
                                <?php $__currentLoopData = $priorityOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e(old('priority', $announcement->priority) == $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="field-error"><i data-lucide="alert-circle" class="w-3 h-3"></i> <?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label for="target_audience" class="field-label">Target Audience</label>
                            <select id="target_audience" name="target_audience" x-model="targetAudience" class="field-input cursor-pointer bg-gray-50">
                                <option value="all">All Employees</option>
                                <option value="department">Specific Department</option>
                                <option value="store">Specific Store/Branch</option>
                                <option value="designation">Specific Designation</option>
                            </select>
                            <?php $__errorArgs = ['target_audience'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="field-error"><i data-lucide="alert-circle" class="w-3 h-3"></i> <?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                                                
                        <div x-show="targetAudience !== 'all'" x-cloak x-transition.opacity class="p-3 bg-blue-50 border border-blue-100 rounded-lg">
                            <p class="text-xs font-bold text-blue-800 mb-1 flex items-center gap-1.5">
                                <i data-lucide="info" class="w-3.5 h-3.5"></i> Action Required
                            </p>
                            <p class="text-[11px] text-blue-600 leading-tight">
                                You have selected a specific <span x-text="targetAudience" class="font-bold"></span>. Ensure you select the target groups before saving.
                            </p>
                            
                            <input type="text" name="target_ids[]" :disabled="targetAudience === 'all'" 
                                   value="<?php echo e(is_array(old('target_ids', $announcement->target_ids)) ? implode(',', old('target_ids', $announcement->target_ids)) : old('target_ids', $announcement->target_ids)); ?>"
                                   placeholder="Select targets..." class="field-input mt-2 !py-1.5 !text-xs">
                            
                            <?php $__errorArgs = ['target_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="field-error"><i data-lucide="alert-circle" class="w-3 h-3"></i> <?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php $__errorArgs = ['target_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="field-error"><i data-lucide="alert-circle" class="w-3 h-3"></i> Invalid target selection.</p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                
                <div class="section-card">
                    <div class="mb-5 pb-3 border-b border-gray-100 flex items-center gap-2 text-gray-800">
                        <i data-lucide="calendar-clock" class="w-4 h-4 text-purple-500"></i>
                        <h3 class="text-[13px] font-black uppercase tracking-wider">Scheduling</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="publish_at" class="field-label">Publish Date & Time (Optional)</label>
                            <input type="datetime-local" id="publish_at" name="publish_at" 
                                value="<?php echo e(old('publish_at', $announcement->publish_at ? $announcement->publish_at->format('Y-m-d\TH:i') : '')); ?>"
                                class="field-input <?php $__errorArgs = ['publish_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> has-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <p class="text-[10px] text-gray-400 mt-1 font-medium">Leave blank to keep as Draft.</p>
                            <?php $__errorArgs = ['publish_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="field-error"><i data-lucide="alert-circle" class="w-3 h-3"></i> <?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label for="expire_at" class="field-label">Expiry Date & Time (Optional)</label>
                            <input type="datetime-local" id="expire_at" name="expire_at" 
                                value="<?php echo e(old('expire_at', $announcement->expire_at ? $announcement->expire_at->format('Y-m-d\TH:i') : '')); ?>"
                                class="field-input <?php $__errorArgs = ['expire_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> has-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <p class="text-[10px] text-gray-400 mt-1 font-medium">When should this stop showing?</p>
                            <?php $__errorArgs = ['expire_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="field-error"><i data-lucide="alert-circle" class="w-3 h-3"></i> <?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                
                <div class="section-card">
                    <div class="mb-5 pb-3 border-b border-gray-100 flex items-center gap-2 text-gray-800">
                        <i data-lucide="sliders" class="w-4 h-4 text-orange-500"></i>
                        <h3 class="text-[13px] font-black uppercase tracking-wider">Preferences</h3>
                    </div>

                    <div class="space-y-5">
                        
                        <label class="relative flex items-start cursor-pointer group">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="is_pinned" value="1" <?php echo e(old('is_pinned', $announcement->is_pinned) ? 'checked' : ''); ?> class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-yellow-400 group-hover:bg-gray-300 peer-checked:group-hover:bg-yellow-500 transition-colors"></div>
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-bold text-gray-800">Pin to Dashboard</span>
                                <p class="text-[11px] text-gray-500 font-medium leading-tight mt-0.5">Keeps this announcement at the top of employee feeds.</p>
                            </div>
                        </label>

                        
                        <label class="relative flex items-start cursor-pointer group">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="requires_acknowledgement" value="1" <?php echo e(old('requires_acknowledgement', $announcement->requires_acknowledgement) ? 'checked' : ''); ?> class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500 group-hover:bg-gray-300 peer-checked:group-hover:bg-green-600 transition-colors"></div>
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-bold text-gray-800">Require Acknowledgement</span>
                                <p class="text-[11px] text-gray-500 font-medium leading-tight mt-0.5">Forces employees to click "I Accept" after reading.</p>
                            </div>
                        </label>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
window.announcementForm = function() {
    return {
        isSubmitting: false,
        fileName: '',
        // Initialize target audience from old input or the database model
        targetAudience: '<?php echo e(old('target_audience', $announcement->target_audience ?? 'all')); ?>',
        
        init() {
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        }
    };
};
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/hrm/announcements/edit.blade.php ENDPATH**/ ?>