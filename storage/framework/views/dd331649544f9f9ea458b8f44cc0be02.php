

<?php $__env->startSection('title', $lead->name . ' — CRM Lead'); ?>

<?php $__env->startSection('header-title'); ?>
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('admin.crm.leads.index')); ?>"
            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        </a>
        <div>
            <h1 class="text-[17px] font-bold text-gray-800 leading-none"><?php echo e($lead->name); ?></h1>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Lead Detail</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    [x-cloak] { display: none !important; }

    .detail-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        overflow: hidden;
    }

    .card-title {
        font-size: 10px;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        padding: 14px 18px;
        border-bottom: 1px solid #f8fafc;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 8px 18px;
        border-bottom: 1px solid #f8fafc;
        font-size: 13px;
    }

    .info-row:last-child { border-bottom: none; }
    .info-key { color: #94a3b8; font-weight: 500; flex-shrink: 0; font-size: 12px; }
    .info-val { color: #1e293b; font-weight: 600; text-align: right; word-break: break-word; max-width: 65%; }

    /* Stage pipeline ── */
    .stage-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        flex: 1;
        min-width: 0;
    }

    .stage-circle {
        width: 32px; height: 32px;
        border-radius: 50%;
        border: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        transition: all 150ms ease;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .stage-circle.current {
        border-color: var(--stage-color);
        background: var(--stage-color);
        box-shadow: 0 0 0 4px var(--stage-color-ring);
    }

    .stage-circle.past {
        border-color: var(--stage-color);
        background: var(--stage-color);
        opacity: 0.5;
    }

    .stage-circle.future {
        border-color: #e2e8f0;
        background: #f8fafc;
    }

    .stage-circle:hover { transform: scale(1.1); }
    .stage-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-align: center; line-height: 1.3; }
    .stage-label.current { color: #1e293b; }

    .stage-connector {
        flex: 1;
        height: 2px;
        background: #e2e8f0;
        margin-top: -18px;
        position: relative;
        z-index: 0;
    }

    .stage-connector.done { background: var(--connector-color); opacity: 0.5; }

    /* Timeline ── */
    .timeline-item {
        display: flex;
        gap: 14px;
        padding: 10px 18px;
        border-bottom: 1px solid #f8fafc;
        transition: background 100ms;
    }

    .timeline-item:hover { background: #fafbfc; }
    .timeline-item:last-child { border-bottom: none; }

    .timeline-dot {
        width: 28px; height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }

    /* Tasks ── */
    .task-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 18px;
        border-bottom: 1px solid #f8fafc;
        transition: background 100ms;
    }

    .task-item:hover { background: #fafbfc; }
    .task-item:last-child { border-bottom: none; }

    /* Field input ── */
    .field-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 13px;
        color: #1f2937;
        outline: none;
        font-family: inherit;
        background: #fff;
        transition: border-color 150ms;
    }

    .field-input:focus { border-color: var(--brand-600); }

    .field-select {
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 13px;
        color: #1f2937;
        outline: none;
        font-family: inherit;
        background: #fff;
        transition: border-color 150ms;
    }

    .field-select:focus { border-color: var(--brand-600); }

    /* Priority ── */
    .prio-btn {
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        border: 1.5px solid transparent;
        cursor: pointer;
        transition: all 120ms;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        border: 1.5px solid #e5e7eb;
        color: #374151;
        background: #fff;
        cursor: pointer;
        transition: all 120ms;
        text-decoration: none;
    }

    .action-btn:hover { background: #f9fafb; border-color: #d1d5db; }
    .action-btn.primary { background: var(--brand-600); border-color: var(--brand-600); color: #fff; }
    .action-btn.primary:hover { opacity: 0.9; }
    .action-btn.danger { color: #dc2626; border-color: #fecaca; }
    .action-btn.danger:hover { background: #fef2f2; }
    .action-btn.success { color: #15803d; border-color: #bbf7d0; }
    .action-btn.success:hover { background: #f0fdf4; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php
    $priorityColors = [
        'hot'    => ['bg' => '#fef2f2', 'text' => '#dc2626', 'border' => '#fecaca'],
        'high'   => ['bg' => '#fff7ed', 'text' => '#c2410c', 'border' => '#fed7aa'],
        'medium' => ['bg' => '#fefce8', 'text' => '#a16207', 'border' => '#fef08a'],
        'low'    => ['bg' => '#f9fafb', 'text' => '#6b7280', 'border' => '#e5e7eb'],
    ];
    $pColor = $priorityColors[$lead->priority] ?? $priorityColors['medium'];

    // Avatar
    $avatarColors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4'];
    $avatarBg = $avatarColors[crc32($lead->name) % count($avatarColors)];
?>

<div class="pb-10"
    x-data="leadDetail()"
    x-init="init()">

    
    <div class="flex items-center justify-between flex-wrap gap-3 mb-5">

        <div class="flex items-center gap-2 flex-wrap">

            
            <?php if($lead->phone): ?>
                <a href="<?php echo e($lead->whatsapp_url); ?>" target="_blank" class="action-btn success">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                    WhatsApp
                </a>
            <?php endif; ?>

            
            <a href="<?php echo e(route('admin.crm.leads.edit', $lead->id)); ?>" class="action-btn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Lead
            </a>

            
            <?php if(!$lead->is_converted): ?>
                <button @click="convertLead()" class="action-btn success" :disabled="converting">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span x-text="converting ? 'Converting...' : 'Convert to Client'"></span>
                </button>
            <?php else: ?>
                <a href="<?php echo e(route('admin.clients.index', ['search' => $lead->name])); ?>" class="action-btn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    View Client
                </a>
            <?php endif; ?>

            
            <button @click="deleteLead()" class="action-btn danger">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                Delete
            </button>
        </div>

        
        <?php if($lead->is_converted): ?>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-green-50 text-green-700 text-[12px] font-bold border border-green-100">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                Converted <?php echo e($lead->converted_at?->format('d M Y')); ?>

            </span>
        <?php endif; ?>
    </div>

    
    <template x-if="pageSuccess">
        <div class="mb-4 bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex items-center gap-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
            <p class="text-sm font-semibold text-green-800" x-text="pageSuccess"></p>
        </div>
    </template>

    <template x-if="pageError">
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <p class="text-sm font-semibold text-red-700" x-text="pageError"></p>
        </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        
        <div class="lg:col-span-1 space-y-4">

            
            <div class="detail-card">
                <div class="px-5 py-5 flex flex-col items-center text-center border-b border-gray-50">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white mb-3 shadow-sm"
                        style="background: <?php echo e($avatarBg); ?>">
                        <?php echo e(strtoupper(substr($lead->name, 0, 1))); ?>

                    </div>
                    <h2 class="text-[16px] font-bold text-gray-900 mb-1"><?php echo e($lead->name); ?></h2>
                    <?php if($lead->company_name): ?>
                        <p class="text-[12px] text-gray-400 font-medium mb-1"><?php echo e($lead->company_name); ?></p>
                    <?php endif; ?>

                    
                    <div class="flex items-center gap-1.5 mt-2 flex-wrap justify-center">
                        <?php $__currentLoopData = ['low','medium','high','hot']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $pc = $priorityColors[$p]; ?>
                            <button
                                @click="setPriority('<?php echo e($p); ?>')"
                                class="prio-btn"
                                style="background: <?php echo e($lead->priority === $p ? $pc['bg'] : '#fff'); ?>;
                                       color: <?php echo e($lead->priority === $p ? $pc['text'] : '#9ca3af'); ?>;
                                       border-color: <?php echo e($lead->priority === $p ? $pc['border'] : '#f1f5f9'); ?>"
                                :style="currentPriority === '<?php echo e($p); ?>'
                                    ? 'background:<?php echo e($pc['bg']); ?>;color:<?php echo e($pc['text']); ?>;border-color:<?php echo e($pc['border']); ?>'
                                    : 'background:#fff;color:#9ca3af;border-color:#f1f5f9'">
                                <?php echo e($p === 'hot' ? '🔥' : ''); ?> <?php echo e(ucfirst($p)); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    
                    <div class="mt-3 flex items-center gap-2">
                        <span class="text-[11px] text-gray-400 font-medium">Score:</span>
                        <span class="text-[13px] font-black" x-text="score"
                            :style="score >= 50 ? 'color:#ef4444' : (score >= 20 ? 'color:#f59e0b' : 'color:#6b7280')">
                            <?php echo e($lead->score); ?>

                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                            :style="score >= 50 ? 'background:#fef2f2;color:#dc2626' : (score >= 20 ? 'background:#fefce8;color:#a16207' : 'background:#f9fafb;color:#6b7280')"
                            x-text="score >= 50 ? 'Hot' : (score >= 20 ? 'Warm' : 'Cold')">
                        </span>
                    </div>
                </div>

                
                <?php if($lead->phone): ?>
                    <div class="info-row">
                        <span class="info-key">Phone</span>
                        <a href="tel:<?php echo e($lead->phone); ?>" class="info-val" style="color: var(--brand-600)">
                            <?php echo e($lead->phone); ?>

                        </a>
                    </div>
                <?php endif; ?>
                <?php if($lead->email): ?>
                    <div class="info-row">
                        <span class="info-key">Email</span>
                        <a href="mailto:<?php echo e($lead->email); ?>" class="info-val" style="color: var(--brand-600); font-size: 11px">
                            <?php echo e($lead->email); ?>

                        </a>
                    </div>
                <?php endif; ?>
                <?php if($lead->source): ?>
                    <div class="info-row">
                        <span class="info-key">Source</span>
                        <span class="info-val"><?php echo e($lead->source->name); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($lead->lead_value): ?>
                    <div class="info-row">
                        <span class="info-key">Value</span>
                        <span class="info-val">₹<?php echo e(number_format($lead->lead_value, 2)); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($lead->city || $lead->state): ?>
                    <div class="info-row">
                        <span class="info-key">Location</span>
                        <span class="info-val">
                            <?php echo e(implode(', ', array_filter([$lead->city, $lead->state]))); ?>

                        </span>
                    </div>
                <?php endif; ?>
                <?php if($lead->next_followup_at): ?>
                    <div class="info-row">
                        <span class="info-key">Follow-up</span>
                        <span class="info-val <?php echo e($lead->is_overdue ? 'text-red-600' : ''); ?>">
                            <?php echo e($lead->is_overdue ? '⚠ ' : ''); ?><?php echo e($lead->next_followup_at->format('d M Y')); ?>

                        </span>
                    </div>
                <?php endif; ?>
                <?php if($lead->last_contacted_at): ?>
                    <div class="info-row">
                        <span class="info-key">Last Contact</span>
                        <span class="info-val"><?php echo e($lead->last_contacted_at->diffForHumans()); ?></span>
                    </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-key">Added</span>
                    <span class="info-val"><?php echo e($lead->created_at->format('d M Y')); ?></span>
                </div>
                <?php if($lead->assignees->isNotEmpty()): ?>
                    <div class="info-row">
                        <span class="info-key">Assigned To</span>
                        <span class="info-val"><?php echo e($lead->assignees->first()->name); ?></span>
                    </div>
                <?php endif; ?>

                
                <?php if($lead->instagram_id || $lead->facebook_id || $lead->website): ?>
                    <div class="px-4 py-3 flex items-center gap-2 flex-wrap">
                        <?php if($lead->instagram_id): ?>
                            <a href="https://instagram.com/<?php echo e($lead->instagram_id); ?>" target="_blank"
                                class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-pink-50 text-pink-600">
                                Instagram
                            </a>
                        <?php endif; ?>
                        <?php if($lead->website): ?>
                            <a href="<?php echo e($lead->website); ?>" target="_blank"
                                class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600">
                                Website
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            
            <?php if($lead->tags->isNotEmpty()): ?>
                <div class="detail-card">
                    <div class="card-title">Tags</div>
                    <div class="px-4 py-3 flex flex-wrap gap-2">
                        <?php $__currentLoopData = $lead->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold"
                                style="background: <?php echo e($tag->color); ?>18; color: <?php echo e($tag->color); ?>">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: <?php echo e($tag->color); ?>"></span>
                                <?php echo e($tag->name); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if($lead->description): ?>
                <div class="detail-card">
                    <div class="card-title">Notes</div>
                    <div class="px-4 py-3 text-[13px] text-gray-600 leading-relaxed">
                        <?php echo e($lead->description); ?>

                    </div>
                </div>
            <?php endif; ?>

        </div>

        
        <div class="lg:col-span-2 space-y-4">

            
            <?php if($lead->pipeline): ?>
                <div class="detail-card">
                    <div class="card-title">Pipeline — <?php echo e($lead->pipeline->name); ?></div>
                    <div class="px-5 py-5">

                        
                        <div class="flex items-start">
                            <?php $stages = $lead->pipeline->stages; ?>
                            <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $stageIds = $stages->pluck('id')->toArray();
                                    $currentIdx = array_search($lead->crm_stage_id, $stageIds);
                                    $thisIdx    = $i;
                                    $isCurrent  = $stage->id === $lead->crm_stage_id;
                                    $isPast     = $thisIdx < $currentIdx;
                                ?>

                                <div class="stage-step" @click="moveToStage(<?php echo e($stage->id); ?>, '<?php echo e(addslashes($stage->name)); ?>')">
                                    <div class="stage-circle <?php echo e($isCurrent ? 'current' : ($isPast ? 'past' : 'future')); ?>"
                                        style="--stage-color: <?php echo e($stage->color); ?>;
                                               --stage-color-ring: <?php echo e($stage->color); ?>22">
                                        <?php if($isCurrent): ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="0"><circle cx="12" cy="12" r="5"/></svg>
                                        <?php elseif($isPast): ?>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        <?php else: ?>
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="#e2e8f0" stroke="#e2e8f0" stroke-width="0"><circle cx="12" cy="12" r="5"/></svg>
                                        <?php endif; ?>
                                    </div>
                                    <span class="stage-label <?php echo e($isCurrent ? 'current' : ''); ?>"
                                        style="<?php echo e($isCurrent ? 'color:' . $stage->color : ''); ?>">
                                        <?php echo e(Str::limit($stage->name, 10)); ?>

                                    </span>
                                </div>

                                <?php if(!$loop->last): ?>
                                    <div class="stage-connector <?php echo e($isPast ? 'done' : ''); ?> mt-4"
                                        style="--connector-color: <?php echo e($stage->color); ?>"></div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        
                        <div class="mt-4 flex items-center gap-2">
                            <span class="text-[11px] text-gray-400 font-medium">Current stage:</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold"
                                :style="`background: ${currentStageColor}18; color: ${currentStageColor}`">
                                <span class="w-1.5 h-1.5 rounded-full"
                                    :style="`background: ${currentStageColor}`"></span>
                                <span x-text="currentStageName"><?php echo e($lead->stage?->name); ?></span>
                            </span>
                            <span x-show="stageMoving"
                                class="text-[11px] text-gray-400 font-medium animate-pulse">Moving...</span>
                        </div>

                    </div>
                </div>
            <?php endif; ?>

            
            <div class="detail-card" x-data="tasksPanel()">
                <div class="card-title flex items-center justify-between">
                    <span>Tasks
                        <span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-600"
                            x-text="tasks.filter(t => ['pending','in_progress'].includes(t.status)).length">
                        </span>
                    </span>
                    <button @click="addOpen = !addOpen"
                        class="text-[11px] font-bold px-3 py-1 rounded-lg text-white hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)">
                        + Add Task
                    </button>
                </div>

                
                <div x-show="addOpen" x-cloak class="px-4 py-4 border-b border-gray-50 bg-gray-50/50 space-y-3">
                    <template x-if="taskAddError">
                        <div class="bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                            <p class="text-[12px] font-semibold text-red-600" x-text="taskAddError"></p>
                        </div>
                    </template>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input type="text" x-model="taskForm.title"
                            placeholder="Task title *"
                            class="field-input sm:col-span-2">
                        <select x-model="taskForm.type" class="field-select">
                            <?php $__currentLoopData = \App\Models\CrmTask::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <select x-model="taskForm.priority" class="field-select">
                            <option value="medium">Medium Priority</option>
                            <option value="high">High Priority</option>
                            <option value="low">Low Priority</option>
                        </select>
                        <input type="datetime-local" x-model="taskForm.due_at"
                            class="field-input" placeholder="Due date *">
                        <select x-model="taskForm.assigned_to" class="field-select">
                            <option value="">Assign to...</option>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <textarea x-model="taskForm.description" rows="2"
                            placeholder="Description (optional)"
                            class="field-input sm:col-span-2 resize-none"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="addTask()"
                            :disabled="taskAdding || !taskForm.title.trim() || !taskForm.due_at"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[12px] font-bold text-white hover:opacity-90 transition-opacity"
                            style="background: var(--brand-600)"
                            :class="taskAdding ? 'opacity-60 cursor-not-allowed' : ''">
                            <span x-text="taskAdding ? 'Adding...' : 'Add Task'"></span>
                        </button>
                        <button @click="addOpen = false; taskAddError = null"
                            class="px-4 py-2 rounded-xl text-[12px] font-bold text-gray-600 border border-gray-200 hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </div>

                
                <template x-if="tasks.length === 0">
                    <div class="px-5 py-8 text-center text-[13px] text-gray-400 font-medium">
                        No tasks yet. Add a follow-up task to stay on track.
                    </div>
                </template>

                <template x-for="task in tasks" :key="task.id">
                    <div class="task-item">

                        
                        <button @click="completeTask(task)"
                            :disabled="task.status === 'completed' || task.status === 'cancelled'"
                            class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-0.5 transition-colors"
                            :class="task.status === 'completed' ? 'border-green-400 bg-green-400' : (task.is_overdue ? 'border-red-400 hover:border-red-500' : 'border-gray-300 hover:border-green-400')"
                            :title="task.status === 'completed' ? 'Completed' : 'Mark complete'">
                            <svg x-show="task.status === 'completed'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </button>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[13px] font-bold text-gray-800 truncate"
                                        :class="task.status === 'completed' ? 'line-through text-gray-400' : ''"
                                        x-text="task.title"></p>
                                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded"
                                            :style="`background: ${task.status_color}15; color: ${task.status_color}`"
                                            x-text="task.status_label"></span>
                                        <span class="text-[10px] text-gray-400 font-medium"
                                            x-text="task.type_label"></span>
                                        <span class="text-[10px] font-semibold"
                                            :class="task.is_overdue ? 'text-red-500' : 'text-gray-400'"
                                            x-text="(task.is_overdue ? '⚠ ' : '') + task.due_at"></span>
                                    </div>
                                    <p x-show="task.assignee_name"
                                        class="text-[11px] text-gray-400 mt-0.5"
                                        x-text="'Assigned to ' + task.assignee_name"></p>
                                </div>
                                
                                <button @click="deleteTask(task.id)"
                                    x-show="task.status !== 'completed'"
                                    class="w-6 h-6 flex items-center justify-center rounded text-gray-300 hover:text-red-400 transition-colors flex-shrink-0">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            
            <div class="detail-card" x-data="activityPanel()">
                <div class="card-title">Activity Timeline</div>

                
                <div class="px-4 py-4 border-b border-gray-50 space-y-3">

                    
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <?php $__currentLoopData = $activityTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button @click="actForm.type = '<?php echo e($aType['key']); ?>'"
                                class="text-[11px] font-bold px-3 py-1.5 rounded-lg border transition-colors"
                                :class="actForm.type === '<?php echo e($aType['key']); ?>'
                                    ? 'text-white border-transparent'
                                    : 'text-gray-500 border-gray-200 hover:bg-gray-50'"
                                :style="actForm.type === '<?php echo e($aType['key']); ?>' ? 'background: var(--brand-600)' : ''">
                                <?php echo e($aType['label']); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    
                    <textarea x-model="actForm.description" rows="2"
                        placeholder="Add a note, log a call, record a WhatsApp conversation..."
                        class="field-input resize-none w-full"></textarea>

                    <template x-if="actError">
                        <p class="text-[12px] font-semibold text-red-500" x-text="actError"></p>
                    </template>

                    <button @click="logActivity()"
                        :disabled="actLogging || !actForm.description.trim()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[12px] font-bold text-white hover:opacity-90 transition-opacity"
                        style="background: var(--brand-600)"
                        :class="(actLogging || !actForm.description.trim()) ? 'opacity-50 cursor-not-allowed' : ''">
                        <span x-text="actLogging ? 'Logging...' : 'Log Activity'"></span>
                    </button>
                </div>

                
                <?php
                    $typeColors = [
                        'note'           => ['bg' => '#f8fafc', 'text' => '#64748b'],
                        'call'           => ['bg' => '#eff6ff', 'text' => '#2563eb'],
                        'whatsapp'       => ['bg' => '#f0fdf4', 'text' => '#16a34a'],
                        'email'          => ['bg' => '#faf5ff', 'text' => '#7c3aed'],
                        'meeting'        => ['bg' => '#fff7ed', 'text' => '#c2410c'],
                        'stage_change'   => ['bg' => '#fefce8', 'text' => '#a16207'],
                        'lead_created'   => ['bg' => '#f0fdf4', 'text' => '#15803d'],
                        'converted'      => ['bg' => '#f0fdf4', 'text' => '#15803d'],
                        'task_completed' => ['bg' => '#eff6ff', 'text' => '#2563eb'],
                        'score_changed'  => ['bg' => '#faf5ff', 'text' => '#7c3aed'],
                    ];
                ?>

                <div id="activity-timeline">
                    
                </div>

                <?php if($lead->activities->isEmpty()): ?>
                    <div class="px-5 py-8 text-center text-[13px] text-gray-400 font-medium">
                        No activity yet. Log a call, note, or WhatsApp message.
                    </div>
                <?php else: ?>
                    <?php $__currentLoopData = $lead->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $tc = $typeColors[$act->type] ?? ['bg' => '#f8fafc', 'text' => '#64748b']; ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"
                                style="background: <?php echo e($tc['bg']); ?>; color: <?php echo e($tc['text']); ?>">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <?php if($act->type === 'call'): ?>
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    <?php elseif($act->type === 'whatsapp'): ?>
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    <?php elseif($act->type === 'email'): ?>
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                                    <?php elseif($act->type === 'meeting'): ?>
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    <?php elseif($act->type === 'stage_change'): ?>
                                        <polyline points="9 18 15 12 9 6"/>
                                    <?php else: ?>
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                    <?php endif; ?>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-0.5">
                                    <span class="text-[11px] font-bold"
                                        style="color: <?php echo e($tc['text']); ?>"><?php echo e($act->type_label); ?></span>
                                    <span class="text-[10px] text-gray-400 font-medium flex-shrink-0"
                                        title="<?php echo e($act->created_at->format('d M Y, h:i A')); ?>">
                                        <?php echo e($act->created_at->diffForHumans()); ?>

                                    </span>
                                </div>
                                <p class="text-[13px] text-gray-700 leading-relaxed"><?php echo e($act->description); ?></p>
                                <p class="text-[11px] text-gray-400 mt-0.5">
                                    <?php echo e($act->is_auto ? 'System' : ($act->user?->name ?? 'Unknown')); ?>

                                </p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>

            </div>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// ── Activity type colors for JS-prepended items ──
const actTypeColors = {
    note:           { bg: '#f8fafc', text: '#64748b' },
    call:           { bg: '#eff6ff', text: '#2563eb' },
    whatsapp:       { bg: '#f0fdf4', text: '#16a34a' },
    email:          { bg: '#faf5ff', text: '#7c3aed' },
    meeting:        { bg: '#fff7ed', text: '#c2410c' },
    stage_change:   { bg: '#fefce8', text: '#a16207' },
    lead_created:   { bg: '#f0fdf4', text: '#15803d' },
    converted:      { bg: '#f0fdf4', text: '#15803d' },
    task_completed: { bg: '#eff6ff', text: '#2563eb' },
    score_changed:  { bg: '#faf5ff', text: '#7c3aed' },
};

// ── Main lead detail component ──
function leadDetail() {
    return {
        currentPriority:   '<?php echo e($lead->priority); ?>',
        currentStageName:  '<?php echo e($lead->stage?->name); ?>',
        currentStageColor: '<?php echo e($lead->stage?->color ?? "#6b7280"); ?>',
        stageMoving:       false,
        converting:        false,
        score:             <?php echo e($lead->score); ?>,
        pageSuccess:       null,
        pageError:         null,

        init() {},

        flash(msg, type = 'success') {
            if (type === 'success') { this.pageSuccess = msg; setTimeout(() => this.pageSuccess = null, 4000); }
            else                   { this.pageError   = msg; setTimeout(() => this.pageError   = null, 5000); }
        },

        async ajax(method, url, body = null) {
            const opts = {
                method,
                headers: {
                    'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            };
            if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
            const res  = await fetch(url, opts);
            return await res.json();
        },

        // ── Stage move ──
        async moveToStage(stageId, stageName) {
            if (this.stageMoving) return;
            const c = await Swal.fire({
                title: `Move to "${stageName}"?`,
                input: 'text',
                inputPlaceholder: 'Optional note (reason for moving)',
                showCancelButton: true,
                confirmButtonText: 'Move',
                confirmButtonColor: 'var(--brand-600)',
            });
            if (!c.isConfirmed) return;
            this.stageMoving = true;
            try {
                const data = await this.ajax('POST', `/admin/crm/leads/<?php echo e($lead->id); ?>/stage`, {
                    stage_id: stageId,
                    note:     c.value || null,
                });
                if (data.success) {
                    this.currentStageName  = data.stage.name;
                    this.currentStageColor = data.stage.color;
                    this.flash(data.message);
                    // Reload to update stage dots — simplest and most reliable
                    setTimeout(() => location.reload(), 800);
                } else {
                    this.flash(data.message, 'error');
                }
            } catch(e) { this.flash('Network error.', 'error'); }
            finally    { this.stageMoving = false; }
        },

        // ── Priority ──
        async setPriority(priority) {
            this.currentPriority = priority;
            try {
                const data = await this.ajax('PUT', `/admin/crm/leads/<?php echo e($lead->id); ?>`, {
                    name:     '<?php echo e(addslashes($lead->name)); ?>',
                    priority: priority,
                });
                if (data.success) this.flash('Priority updated.');
                else this.flash(data.message, 'error');
            } catch(e) { this.flash('Network error.', 'error'); }
        },

        // ── Convert ──
        async convertLead() {
            const c = await Swal.fire({
                title:             'Convert Lead to Client?',
                text:              'A client record will be created from this lead.',
                icon:              'question',
                showCancelButton:  true,
                confirmButtonText: 'Convert',
                confirmButtonColor:'#10b981',
            });
            if (!c.isConfirmed) return;
            this.converting = true;
            try {
                const data = await this.ajax('POST', `/admin/crm/leads/<?php echo e($lead->id); ?>/convert`);
                if (data.success) {
                    this.flash(data.message);
                    setTimeout(() => {
                        if (data.client_url) {
                            // Use your SPA engine to transition smoothly, fallback to hard redirect if missing
                            if (typeof navigate === 'function') {
                                navigate(data.client_url);
                            } else {
                                window.location.href = data.client_url;
                            }
                        } else {
                            location.reload();
                        }
                    }, 800); // Slightly faster redirect feels snappier
                } else {
                    this.flash(data.message, 'error');
                }
            } catch(e) { this.flash('Network error.', 'error'); }
            finally    { this.converting = false; }
        },

        // ── Delete ──
        async deleteLead() {
            const c = await Swal.fire({
                title:             'Delete Lead?',
                text:              'This lead will be soft deleted.',
                icon:              'warning',
                showCancelButton:  true,
                confirmButtonText: 'Delete',
                confirmButtonColor:'#ef4444',
            });
            if (!c.isConfirmed) return;
            try {
                const data = await this.ajax('DELETE', `/admin/crm/leads/<?php echo e($lead->id); ?>`);
                if (data.success) {
                    window.location.href = '<?php echo e(route("admin.crm.leads.index")); ?>';
                } else {
                    this.flash(data.message, 'error');
                }
            } catch(e) { this.flash('Network error.', 'error'); }
        },
    }
}

// ── Tasks panel ──
function tasksPanel() {
    return {
        tasks:        <?php echo json_encode($lead->tasks->map(fn($t) => app(\App\Http\Controllers\Admin\Crm\CrmLeadController::class)->formatTaskPublic($t)), 15, 512) ?>,
        addOpen:      false,
        taskAdding:   false,
        taskAddError: null,
        taskForm: { title: '', type: 'follow_up', priority: 'medium', due_at: '', assigned_to: '', description: '' },

        async addTask() {
            if (!this.taskForm.title.trim() || !this.taskForm.due_at || this.taskAdding) return;
            this.taskAdding   = true;
            this.taskAddError = null;
            try {
                const res  = await fetch(`/admin/crm/leads/<?php echo e($lead->id); ?>/tasks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.taskForm),
                });
                const data = await res.json();
                if (res.status === 422) { this.taskAddError = data.errors?.due_at?.[0] ?? data.message; return; }
                if (!data.success)      { this.taskAddError = data.message; return; }
                this.tasks.unshift(data.task);
                this.taskForm  = { title: '', type: 'follow_up', priority: 'medium', due_at: '', assigned_to: '', description: '' };
                this.addOpen   = false;
            } catch(e) { this.taskAddError = 'Network error.'; }
            finally    { this.taskAdding = false; }
        },

        async completeTask(task) {
            if (task.status === 'completed' || task.status === 'cancelled') return;
            const c = await Swal.fire({
                title: 'Complete Task?',
                input: 'text', inputPlaceholder: 'Completion note (optional)',
                showCancelButton: true,
                confirmButtonText: 'Mark Complete',
                confirmButtonColor: '#10b981',
            });
            if (!c.isConfirmed) return;
            try {
                const res  = await fetch(`/admin/crm/leads/<?php echo e($lead->id); ?>/tasks/${task.id}/complete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ completion_note: c.value || '' }),
                });
                const data = await res.json();
                if (data.success) {
                    const idx = this.tasks.findIndex(t => t.id === task.id);
                    if (idx !== -1) this.tasks[idx] = data.task;
                }
            } catch(e) {}
        },

        async deleteTask(taskId) {
            const c = await Swal.fire({
                title: 'Delete Task?', icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Delete', confirmButtonColor: '#ef4444',
            });
            if (!c.isConfirmed) return;
            try {
                const res  = await fetch(`/admin/crm/leads/<?php echo e($lead->id); ?>/tasks/${taskId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                if (data.success) this.tasks = this.tasks.filter(t => t.id !== taskId);
            } catch(e) {}
        },
    }
}

// ── Activity panel ──
function activityPanel() {
    return {
        actForm:   { type: 'note', description: '' },
        actLogging: false,
        actError:   null,

        async logActivity() {
            if (!this.actForm.description.trim() || this.actLogging) return;
            this.actLogging = true;
            this.actError   = null;
            try {
                const res  = await fetch(`/admin/crm/leads/<?php echo e($lead->id); ?>/activity`, {
                    method: 'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.actForm),
                });
                const data = await res.json();
                if (!data.success) { this.actError = data.message; return; }

                // Prepend to timeline
                const act = data.activity;
                const tc  = actTypeColors[act.type] || { bg: '#f8fafc', text: '#64748b' };
                const html = `
                    <div class="timeline-item" style="animation: fadeIn 200ms ease">
                        <div class="timeline-dot" style="background:${tc.bg};color:${tc.text}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-0.5">
                                <span class="text-[11px] font-bold" style="color:${tc.text}">${act.type_label}</span>
                                <span class="text-[10px] text-gray-400 font-medium">${act.created_at}</span>
                            </div>
                            <p class="text-[13px] text-gray-700 leading-relaxed">${act.description}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">${act.user_name}</p>
                        </div>
                    </div>`;

                const timeline = document.getElementById('activity-timeline');
                if (timeline) timeline.insertAdjacentHTML('afterbegin', html);

                this.actForm.description = '';
            } catch(e) { this.actError = 'Network error.'; }
            finally    { this.actLogging = false; }
        },
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\qlinkongraphics\Desktop\MyLab\qlinkonSoftware\resources\views/admin/crm/leads/show.blade.php ENDPATH**/ ?>