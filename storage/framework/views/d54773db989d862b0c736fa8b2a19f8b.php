<?php $__env->startSection('title', 'Create Task'); ?>

<?php $__env->startSection('header-title'); ?>
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('admin.hrm.tasks.index')); ?>"
            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        </a>
        <div>
            <h1 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Create Task</h1>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Assign a new task to your team</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .form-section {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .section-head {
        padding: 13px 18px;
        border-bottom: 1px solid #f8fafc;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-icon {
        width: 28px; height: 28px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .section-title {
        font-size: 12px;
        font-weight: 800;
        color: #374151;
        letter-spacing: 0.03em;
    }

    .section-body { padding: 18px; }

    .field-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 5px;
    }

    .field-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 9px 13px;
        font-size: 13px;
        color: #1f2937;
        outline: none;
        font-family: inherit;
        background: #fff;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }

    .field-input:focus {
        border-color: var(--brand-600);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-600) 10%, transparent);
    }

    select.field-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }

    .field-input.has-error { border-color: #f43f5e; }

    .field-error {
        font-size: 11px;
        font-weight: 600;
        color: #f43f5e;
        margin-top: 4px;
    }

    .sticky-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 1.5px solid #f1f5f9;
        padding: 14px 24px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        z-index: 20;
        border-radius: 0 0 16px 16px;
    }

    /* Priority pill selector */
    .priority-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px;
        border-radius: 9px;
        border: 2px solid transparent;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 120ms ease;
        user-select: none;
    }
    .priority-pill:hover { opacity: 0.85; }
    .priority-pill .dot {
        width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
    }

    /* Assignee checkbox list */
    .assignee-list {
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        max-height: 200px;
        overflow-y: auto;
        background: #fff;
    }
    .assignee-list::-webkit-scrollbar { width: 4px; }
    .assignee-list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

    .assignee-item {
        display: flex; align-items: center; gap-10px;
        padding: 9px 13px;
        border-bottom: 1px solid #f8fafc;
        cursor: pointer;
        transition: background 100ms;
        gap: 10px;
    }
    .assignee-item:last-child { border-bottom: none; }
    .assignee-item:hover { background: #f9fafb; }
    .assignee-item input[type="checkbox"] { accent-color: var(--brand-600); width: 14px; height: 14px; cursor: pointer; flex-shrink: 0; }
    .assignee-item label { font-size: 12.5px; color: #374151; cursor: pointer; }
    .assignee-item .emp-code { font-size: 10px; color: #9ca3af; font-weight: 600; }
    [x-cloak] { display: none !important; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="pb-10" x-data="taskCreate()">

    <form method="POST" action="<?php echo e(route('admin.hrm.tasks.store')); ?>">
        <?php echo csrf_field(); ?>

        
        <?php if($errors->any()): ?>
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-start gap-3">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <p class="text-sm font-semibold text-red-700">Please fix the errors below.</p>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #eff6ff">
                    <i data-lucide="clipboard" style="width:14px;height:14px;color:#3b82f6"></i>
                </div>
                <span class="section-title">Task Details</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">

                    
                    <div class="sm:col-span-2">
                        <label class="field-label">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="<?php echo e(old('title')); ?>"
                            placeholder="e.g. Prepare Q1 payroll report"
                            class="field-input <?php echo e($errors->has('title') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Project</label>
                        <input type="text" name="project" value="<?php echo e(old('project')); ?>"
                            placeholder="e.g. Payroll Management"
                            class="field-input <?php echo e($errors->has('project') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['project'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Category</label>
                        <input type="text" name="category" value="<?php echo e(old('category')); ?>"
                            placeholder="e.g. Finance, HR, IT"
                            class="field-input <?php echo e($errors->has('category') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div class="sm:col-span-2">
                        <label class="field-label">Priority <span class="text-red-500">*</span></label>
                        <input type="hidden" name="priority" x-model="priority">
                        <div class="flex flex-wrap gap-2 mt-1">
                            <?php $__currentLoopData = \App\Models\Hrm\HrmTask::PRIORITY_LABELS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $pc = \App\Models\Hrm\HrmTask::PRIORITY_COLORS[$val]; ?>
                                <button type="button"
                                    @click="priority = '<?php echo e($val); ?>'"
                                    :class="priority === '<?php echo e($val); ?>' ? 'ring-2 ring-offset-1' : 'opacity-60'"
                                    class="priority-pill"
                                    style="background: <?php echo e($pc['bg']); ?>; color: <?php echo e($pc['text']); ?>; --ring-color: <?php echo e($pc['dot'] ?? $pc['text']); ?>"
                                    :style="priority === '<?php echo e($val); ?>' ? 'border-color: <?php echo e($pc['dot'] ?? $pc['text']); ?>; opacity: 1;' : 'border-color: transparent;'">
                                    <span class="dot" style="background: <?php echo e($pc['dot'] ?? $pc['text']); ?>"></span>
                                    <?php echo e($label); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Start Date</label>
                        <input type="date" name="start_date" value="<?php echo e(old('start_date')); ?>"
                            class="field-input <?php echo e($errors->has('start_date') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Due Date</label>
                        <input type="date" name="due_date" value="<?php echo e(old('due_date')); ?>"
                            class="field-input <?php echo e($errors->has('due_date') ? 'has-error' : ''); ?>">
                        <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="form-section" x-data="mdEditor(<?php echo \Illuminate\Support\Js::from(old('description', ''))->toHtml() ?>)">
            <div class="section-head">
                <div class="section-icon" style="background: #f9fafb">
                    <i data-lucide="file-text" style="width:14px;height:14px;color:#6b7280"></i>
                </div>
                <span class="section-title">Description</span>

                
                <div class="ml-auto flex items-center gap-3">
                    
                    <div x-show="tab === 'write'" class="flex items-center gap-0.5">
                        <button type="button" @click="wrap('**','**','bold text')"
                            title="Bold (Ctrl+B)"
                            class="w-7 h-7 flex items-center justify-center rounded-md text-[13px] font-black text-gray-600 hover:bg-gray-100 transition-colors">B</button>
                        <button type="button" @click="wrap('*','*','italic text')"
                            title="Italic (Ctrl+I)"
                            class="w-7 h-7 flex items-center justify-center rounded-md text-[13px] font-bold italic text-gray-600 hover:bg-gray-100 transition-colors">I</button>
                        <div class="w-px h-4 bg-gray-200 mx-1"></div>
                        <button type="button" @click="insertBullet()"
                            title="Bullet list"
                            class="px-2 h-7 flex items-center justify-center rounded-md text-[11px] font-bold text-gray-600 hover:bg-gray-100 transition-colors gap-1">
                            <span class="text-[15px] leading-none">•</span> List
                        </button>
                    </div>

                    
                    <div class="flex bg-gray-100 rounded-lg p-0.5 gap-0.5">
                        <button type="button" @click="tab='write'"
                            :class="tab==='write' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 text-[11px] font-bold rounded-md transition-all">Write</button>
                        <button type="button" @click="tab='preview'"
                            :class="tab==='preview' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 text-[11px] font-bold rounded-md transition-all">Preview</button>
                    </div>
                </div>
            </div>

            <div class="section-body">
                
                <div x-show="tab === 'write'">
                    <textarea name="description" x-ref="ta" x-model="content" rows="7"
                        @keydown.ctrl.b.prevent="wrap('**','**','bold text')"
                        @keydown.ctrl.i.prevent="wrap('*','*','italic text')"
                        placeholder="Describe the task..."
                        class="field-input resize-y font-mono text-[13px] <?php echo e($errors->has('description') ? 'has-error' : ''); ?>"></textarea>
                </div>

                
                <div x-show="tab === 'preview'" x-cloak>
                    <div class="border border-gray-200 rounded-xl px-4 py-3 min-h-[120px] text-[13px] text-gray-700 leading-relaxed">
                        <template x-if="!content.trim()">
                            <p class="text-gray-400 italic text-[12px]">Nothing to preview yet. Switch to Write and type something.</p>
                        </template>
                        <div x-show="content.trim()" x-html="renderMd(content)"></div>
                    </div>
                </div>

                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="field-error mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <p class="text-[10px] text-gray-400 mt-1.5">
                    <span class="font-mono">**text**</span> → <strong>bold</strong> &nbsp;·&nbsp;
                    <span class="font-mono">*text*</span> → <em>italic</em> &nbsp;·&nbsp;
                    <span class="font-mono">- item</span> → bullet point
                </p>
            </div>
        </div>

        
        <div class="form-section">
            <div class="section-head">
                <div class="section-icon" style="background: #faf5ff">
                    <i data-lucide="users" style="width:14px;height:14px;color:#a855f7"></i>
                </div>
                <span class="section-title">Assignees</span>
            </div>
            <div class="section-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">

                    
                    <div>
                        <label class="field-label">Primary Assignee</label>
                        <select name="primary_assignee" class="field-input <?php echo e($errors->has('primary_assignee') ? 'has-error' : ''); ?>">
                            <option value="">Select primary assignee</option>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>" <?php echo e(old('primary_assignee') == $emp->id ? 'selected' : ''); ?>>
                                    <?php echo e($emp->employee_code); ?> – <?php echo e($emp->user->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['primary_assignee'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="field-label">Additional Assignees</label>
                        <div class="mb-2">
                            <div class="relative">
                                
                                <input type="text" x-model="assigneeSearch"
                                    placeholder="Filter employees..."
                                    class="field-input pl-9 !py-2 !text-[12px]">
                            </div>
                        </div>
                        <div class="assignee-list">
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="assignee-item"
                                    x-show="!assigneeSearch || '<?php echo e(strtolower($emp->employee_code . ' ' . $emp->user->name)); ?>'.includes(assigneeSearch.toLowerCase())">
                                    <input type="checkbox" name="assignees[]" value="<?php echo e($emp->id); ?>"
                                        <?php echo e(in_array($emp->id, old('assignees', [])) ? 'checked' : ''); ?>>
                                    <div>
                                        <div class="text-[12.5px] font-semibold text-gray-700"><?php echo e($emp->user->name); ?></div>
                                        <div class="emp-code"><?php echo e($emp->employee_code); ?></div>
                                    </div>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php $__errorArgs = ['assignees'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field-error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="sticky-footer">
            <a href="<?php echo e(route('admin.hrm.tasks.index')); ?>"
                class="flex items-center justify-center px-5 py-2.5 rounded-xl text-[13px] font-bold text-gray-600 border border-gray-200 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-[14px] font-bold text-white transition-opacity hover:opacity-90"
                style="background: var(--brand-600)">
                <i data-lucide="check" style="width:16px;height:16px"></i>
                Create Task
            </button>
        </div>

    </form>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
window.taskCreate = function() {
    return {
        priority: '<?php echo e(old('priority', 'medium')); ?>',
        assigneeSearch: '',

        init() {
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },
    };
};

/* ── Markdown Editor Component ───────────────────────────── */
window.mdEditor = function(initial) {
    return {
        tab: 'write',
        content: initial || '',

        /* Inline markdown → HTML (safe — HTML is escaped first) */
        inlineToHtml(t) {
            t = t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            /* Bold must come before italic to avoid mis-matching inner * */
            t = t.replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>');
            t = t.replace(/\*([^*\n]+)\*/g, '<em>$1</em>');
            return t;
        },

        renderMd(text) {
            if (!text || !text.trim()) return '';
            const lines = text.split('\n');
            let html = '';
            let inList = false;
            lines.forEach(line => {
                if (/^- /.test(line)) {
                    if (!inList) { html += '<ul style="list-style:disc;padding-left:1.1rem;margin:4px 0">'; inList = true; }
                    html += `<li style="margin:2px 0">${this.inlineToHtml(line.slice(2))}</li>`;
                } else {
                    if (inList) { html += '</ul>'; inList = false; }
                    if (line.trim() === '') {
                        html += '<div style="height:6px"></div>';
                    } else {
                        html += `<p style="margin:2px 0">${this.inlineToHtml(line)}</p>`;
                    }
                }
            });
            if (inList) html += '</ul>';
            return html;
        },

        /* Wrap selected text (or placeholder) with before/after markers */
        wrap(before, after, placeholder) {
            const ta = this.$refs.ta;
            const s = ta.selectionStart;
            const e = ta.selectionEnd;
            const sel = this.content.slice(s, e) || placeholder;
            this.content = this.content.slice(0, s) + before + sel + after + this.content.slice(e);
            this.$nextTick(() => {
                ta.focus();
                ta.setSelectionRange(s + before.length, s + before.length + sel.length);
            });
        },

        /* Prepend `- ` to the current line (or remove it if already there) */
        insertBullet() {
            const ta = this.$refs.ta;
            const pos = ta.selectionStart;
            const lineStart = this.content.lastIndexOf('\n', pos - 1) + 1;
            const lineHasBullet = this.content.slice(lineStart, lineStart + 2) === '- ';
            if (lineHasBullet) {
                this.content = this.content.slice(0, lineStart) + this.content.slice(lineStart + 2);
                this.$nextTick(() => { ta.focus(); ta.setSelectionRange(Math.max(lineStart, pos - 2), Math.max(lineStart, pos - 2)); });
            } else {
                this.content = this.content.slice(0, lineStart) + '- ' + this.content.slice(lineStart);
                this.$nextTick(() => { ta.focus(); ta.setSelectionRange(pos + 2, pos + 2); });
            }
        },
    };
};
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlink-saas - Slug Based\resources\views/admin/hrm/tasks/create.blade.php ENDPATH**/ ?>