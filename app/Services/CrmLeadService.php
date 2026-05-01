<?php

namespace App\Services;

use App\Events\Crm\LeadConverted;
use App\Events\Crm\LeadLost;
use App\Events\Crm\LeadStageChanged;
use App\Models\CrmActivity;
use App\Models\CrmLead;
use App\Models\CrmLeadSource;
use App\Models\CrmPipeline;
use App\Models\CrmStage;
use App\Models\CrmTag;
use App\Models\CrmTask;
use App\Models\Order;

use App\Notifications\Crm\CrmTaskAssignedNotification;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CrmLeadService
{
    // ════════════════════════════════════════════════════
    //  GET LEADS — paginated with filters
    // ════════════════════════════════════════════════════

    public function getLeads(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {

        $query = CrmLead::query()
            ->with([
                'stage:id,name,color,is_won,is_lost',
                'pipeline:id,name',
                'source:id,name,icon',
                'tags:id,name,color',
                'assignees:id,name',
            ])
            ->withCount(['tasks as pending_tasks_count' => fn ($q) => $q->whereIn('status', ['pending', 'in_progress']),
            ]);

        if (! empty($filters['q'])) {
            $query->search($filters['q']);
        }
        if (! empty($filters['pipeline_id'])) {
            $query->byPipeline($filters['pipeline_id']);
        }
        if (! empty($filters['stage_id'])) {
            $query->byStage($filters['stage_id']);
        }
        if (! empty($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }
        if (! empty($filters['source_id'])) {
            $query->where('crm_lead_source_id', $filters['source_id']);
        }
        if (! empty($filters['mine'])) {
            $query->assigned(Auth::id());
        }
        if (! empty($filters['assigned_to'])) {
            $query->assigned($filters['assigned_to']);
        }
        if (! empty($filters['unassigned'])) {
            $query->unassigned();
        }
        if (! empty($filters['overdue'])) {
            $query->overdue();
        }
        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        if (isset($filters['converted'])) {
            $filters['converted'] ? $query->converted() : $query->notConverted();
        }

        if (! empty($filters['tag_id'])) {
            $query->whereHas('tags', fn ($q) => $q->where('crm_tags.id', $filters['tag_id'])
            );
        }

        $sortField = $filters['sort'] ?? 'created_at';
        $sortDir = $filters['dir'] ?? 'desc';

        $allowedSorts = ['created_at', 'name', 'score', 'lead_value', 'next_followup_at', 'last_contacted_at'];
        in_array($sortField, $allowedSorts)
            ? $query->orderBy($sortField, $sortDir)
            : $query->recent();

        return $query->paginate($perPage)->withQueryString();
    }

    // ════════════════════════════════════════════════════
    //  CREATE LEAD
    // ════════════════════════════════════════════════════

    public function createLead(array $data): CrmLead
    {
        return DB::transaction(function () use ($data) {

            $pipelineId = $data['crm_pipeline_id'] ?? $this->getDefaultPipelineId();
            $stageId = $data['crm_stage_id'] ?? $this->getFirstStageId($pipelineId);

            if (! $pipelineId || ! $stageId) {
                throw new \RuntimeException(
                    'No pipeline or stage found. Please set up your CRM pipeline first.'
                );
            }

            $lead = CrmLead::create(array_merge($data, [
                'crm_pipeline_id' => $pipelineId,
                'crm_stage_id' => $stageId,
                'created_by' => Auth::id(),
            ]));

            if (! empty($data['tags'])) {
                $this->syncTags($lead, $data['tags']);
            }

            $assignTo = $data['assigned_to'] ?? Auth::id();
            if ($assignTo) {
                $lead->assignTo($assignTo);
            }

            // ── Load relations needed for activity log ──
            $lead->loadMissing(['source', 'pipeline']);

            // ── Activity log ──
            CrmActivity::logAuto(
                leadId: $lead->id,
                type: 'lead_created',
                description: 'Lead created'.($lead->source ? " via {$lead->source->name}" : ''),
                meta: [
                    'source' => $lead->source?->name,
                    'pipeline' => $lead->pipeline?->name,
                ],
            );

            // ── Score based on data completeness ──
            $this->applyInitialScore($lead);

            Log::info('[CrmLeadService] Lead created', [
                'lead_id' => $lead->id,
                'name' => $lead->name,
            ]);

            return $lead->load(['stage', 'pipeline', 'source', 'tags', 'assignees']);
        });
    }

    // ════════════════════════════════════════════════════
    //  UPDATE LEAD
    // ════════════════════════════════════════════════════

    public function updateLead(CrmLead $lead, array $data): CrmLead
    {
        return DB::transaction(function () use ($lead, $data) {

            $tags = $data['tags'] ?? null;
            $assignedTo = $data['assigned_to'] ?? null;
            unset($data['tags'], $data['assigned_to']);

            $lead->update($data);

            if ($tags !== null) {
                $this->syncTags($lead, $tags);
            }
            if ($assignedTo !== null) {
                $lead->assignTo($assignedTo);
            }

            Log::info('[CrmLeadService] Lead updated', [
                'lead_id' => $lead->id,
                'fields' => array_keys($data),
                'by' => Auth::id(),
            ]);

            return $lead->fresh(['stage', 'pipeline', 'source', 'tags', 'assignees']);
        });
    }

    // ════════════════════════════════════════════════════
    //  MOVE TO STAGE — all side-effects handled here
    //  No Observer needed — fully explicit
    // ════════════════════════════════════════════════════

    public function moveToStage(CrmLead $lead, int $stageId, ?string $note = null): CrmLead
    {
        $stage = CrmStage::where('id', $stageId)
            ->where('crm_pipeline_id', $lead->crm_pipeline_id)
            ->first();

        if (! $stage) {
            throw new \InvalidArgumentException(
                "Stage #{$stageId} does not belong to this pipeline."
            );
        }

        // Same stage — no-op
        if ($lead->crm_stage_id === $stageId) {
            return $lead;
        }

        $oldStageId = $lead->crm_stage_id;

        // ── Update stage ──
        $lead->update(['crm_stage_id' => $stageId]);

        // ── Log stage change activity ──
        CrmActivity::logAuto(
            leadId: $lead->id,
            type: 'stage_change',
            description: "Stage moved to \"{$stage->name}\"",
            meta: [
                'from_stage_id' => $oldStageId,
                'to_stage_id' => $stageId,
                'to_stage_name' => $stage->name,
            ],
        );

        // ── Manual note if provided ──
        if ($note) {
            CrmActivity::log(
                leadId: $lead->id,
                type: 'note',
                description: $note,
            );
        }

        // ── Score boost for forward movement ──
        $lead->addScore(5);

        // ── Notification event (queued listener handles it) ──
        event(new LeadStageChanged($lead, $oldStageId, $stageId));

        // ── Won stage: mark converted + fire event for client creation ──
        if ($stage->is_won) {
            $lead->update([
                'is_converted' => true,
                'converted_at' => now(),
            ]);

            CrmActivity::logAuto(
                leadId: $lead->id,
                type: 'converted',
                description: 'Lead marked as Won — conversion initiated.',
            );

            event(new LeadConverted($lead->fresh()));
        }

        // ── Lost stage: cancel pending tasks, clear followup ──
        if ($stage->is_lost) {
            $cancelled = CrmTask::where('crm_lead_id', $lead->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->update(['status' => 'cancelled']);

            $lead->updateQuietly(['next_followup_at' => null]);

            if ($cancelled > 0) {
                CrmActivity::logAuto(
                    leadId: $lead->id,
                    type: 'stage_change',
                    description: "Lead marked Lost — {$cancelled} pending task(s) cancelled.",
                );
            }

            event(new LeadLost($lead->fresh()));
        }

        Log::info('[CrmLeadService] Lead moved to stage', [
            'lead_id' => $lead->id,
            'from_stage_id' => $oldStageId,
            'to_stage_id' => $stageId,
            'stage_name' => $stage->name,
            'by' => Auth::id(),
        ]);

        return $lead->fresh(['stage']);
    }

    // ════════════════════════════════════════════════════
    //  CONVERT LEAD → CLIENT
    // ════════════════════════════════════════════════════

    public function convertLead(CrmLead $lead): CrmLead
    {
        if ($lead->is_converted) {
            throw new \InvalidArgumentException(
                "Lead #{$lead->id} is already converted."
            );
        }

        return DB::transaction(function () use ($lead) {

            $wonStage = CrmStage::where('crm_pipeline_id', $lead->crm_pipeline_id)
                ->where('is_won', true)
                ->first();

            if ($wonStage) {
                // moveToStage handles activity log + LeadConverted event
                return $this->moveToStage($lead, $wonStage->id);
            }

            // No Won stage — mark converted directly
            $lead->update([
                'is_converted' => true,
                'converted_at' => now(),
            ]);

            CrmActivity::logAuto(
                leadId: $lead->id,
                type: 'converted',
                description: 'Lead converted to client.',
            );

            // Fire event — CreateClientFromLead listener runs
            event(new LeadConverted($lead->fresh()));

            Log::info('[CrmLeadService] Lead converted (no Won stage)', [
                'lead_id' => $lead->id,
                'name' => $lead->name,
                'by' => Auth::id(),
            ]);

            return $lead->fresh(['stage', 'client']);
        });
    }

    // ════════════════════════════════════════════════════
    //  LOG ACTIVITY
    // ════════════════════════════════════════════════════

    public function logActivity(
        CrmLead $lead,
        string $type,
        string $description,
        ?array $meta = null
    ): CrmActivity {

        if (! array_key_exists($type, CrmActivity::TYPES)) {
            throw new \InvalidArgumentException("Invalid activity type: {$type}");
        }

        $activity = CrmActivity::log(
            leadId: $lead->id,
            type: $type,
            description: $description,
            meta: $meta,
            userId: Auth::id(),
        );

        if (in_array($type, ['call', 'whatsapp', 'email', 'meeting'])) {
            $lead->updateQuietly(['last_contacted_at' => now()]);
            $lead->addScore(CrmLead::SCORE_WHATSAPP_REPLIED);
        }

        Log::info('[CrmLeadService] Activity logged', [
            'lead_id' => $lead->id,
            'type' => $type,
            'by' => Auth::id(),
        ]);

        return $activity;
    }

    // ════════════════════════════════════════════════════
    //  CREATE TASK
    // ════════════════════════════════════════════════════

    public function createTask(CrmLead $lead, array $data): CrmTask
    {
        $task = CrmTask::create(array_merge($data, [
            'crm_lead_id' => $lead->id,
            'created_by' => Auth::id(),
            'assigned_to' => $data['assigned_to'] ?? Auth::id(),
            'status' => 'pending',
        ]));

        if (empty($data['remind_at']) && ! empty($data['due_at'])) {
            $task->updateQuietly([
                'remind_at' => Carbon::parse($data['due_at'])->subHour(),
            ]);
        }

        $this->refreshNextFollowup($lead);

        // 🌟 Send Notification to assignee (if it's not the user currently creating it)
        if ($task->assigned_to && $task->assigned_to != Auth::id()) {
            $assignee = \App\Models\User::find($task->assigned_to);
            if ($assignee) {
                $assignee->notify(new CrmTaskAssignedNotification($task));
            }
        }

        Log::info('[CrmLeadService] Task created', [
            'lead_id' => $lead->id,
            'task_id' => $task->id,
            'by' => Auth::id(),
        ]);

        return $task;
    }

    // ════════════════════════════════════════════════════
    //  COMPLETE TASK
    // ════════════════════════════════════════════════════

    public function completeTask(CrmTask $task, string $note = ''): CrmTask
    {
        $task->complete($note);

        $lead = $task->lead;

        if ($lead) {
            $lead->addScore(CrmLead::SCORE_TASK_COMPLETED);

            CrmActivity::logAuto(
                leadId: $lead->id,
                type: 'task_completed',
                description: "Task completed: {$task->title}".($note ? " — {$note}" : ''),
                meta: ['task_id' => $task->id, 'task_type' => $task->type],
            );

            $this->refreshNextFollowup($lead);
        }

        Log::info('[CrmLeadService] Task completed', [
            'task_id' => $task->id,
            'lead_id' => $task->crm_lead_id,
            'by' => Auth::id(),
        ]);

        return $task->fresh();
    }

    // ════════════════════════════════════════════════════
    //  DELETE LEAD (soft)
    // ════════════════════════════════════════════════════

    public function deleteLead(CrmLead $lead): bool
    {
        // ── Cancel pending tasks before soft delete ──
        CrmTask::where('crm_lead_id', $lead->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->update(['status' => 'cancelled']);

        $deleted = $lead->delete();

        Log::info('[CrmLeadService] Lead soft deleted', [
            'lead_id' => $lead->id,
            'name' => $lead->name,
            'by' => Auth::id(),
        ]);

        return $deleted;
    }

    // ════════════════════════════════════════════════════
    //  CREATE LEAD FROM ORDER (Auto-CRM)
    // ════════════════════════════════════════════════════

    public function createOrUpdateFromOrder(Order $order): ?CrmLead
    {
        try {

            $existingLead = CrmLead::where('phone', $order->customer_phone)->first();

            if ($existingLead) {
                $existingLead->updateQuietly([
                    'last_contacted_at' => now(),
                    'order_id' => $order->id,
                ]);

                $existingLead->addScore(CrmLead::SCORE_ORDER_PLACED);

                CrmActivity::logAuto(
                    leadId: $existingLead->id,
                    type: 'note',
                    description: "New order: #{$order->order_number} — ₹".number_format($order->total_amount, 2),
                    meta: ['order_id' => $order->id, 'order_number' => $order->order_number],
                );

                return $existingLead;
            }

            $pipelineId = $this->getDefaultPipelineId();
            $stageId = $this->getFirstStageId($pipelineId);

            if (! $pipelineId || ! $stageId) {
                Log::warning('[CrmLeadService] Cannot auto-create lead — no pipeline', [
                    'order_id' => $order->id,
                ]);

                return null;
            }

            $sourceId = CrmLeadSource::query()
                ->where(fn ($q) => $q->where('name', 'like', '%storefront%')
                    ->orWhere('name', 'like', '%online%')
                    ->orWhere('name', 'like', '%website%')
                )
                ->value('id');

            $lead = CrmLead::create([
                'crm_pipeline_id' => $pipelineId,
                'crm_stage_id' => $stageId,
                'crm_lead_source_id' => $sourceId,
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'email' => $order->customer_email,
                'address' => $order->delivery_address,
                'city' => $order->delivery_city,
                'state' => $order->delivery_state,
                'country' => $order->delivery_country ?? 'India',
                'zip_code' => $order->delivery_pincode,
                'order_id' => $order->id,
                'lead_value' => $order->total_amount,
                'last_contacted_at' => now(),
                'description' => "Auto-created from storefront order #{$order->order_number}",
            ]);

            CrmActivity::logAuto(
                leadId: $lead->id,
                type: 'lead_created',
                description: "Lead auto-created from storefront order #{$order->order_number}",
                meta: ['order_id' => $order->id],
            );

            $this->applyInitialScore($lead);

            Log::info('[CrmLeadService] Lead auto-created from order', [
                'lead_id' => $lead->id,
                'order_number' => $order->order_number,
            ]);

            return $lead;

        } catch (Throwable $e) {
            Log::error('[CrmLeadService] createOrUpdateFromOrder failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    // ════════════════════════════════════════════════════
    //  STATS
    // ════════════════════════════════════════════════════

    public function getStats(): array
    {
        $base = CrmLead::query();

        return [
            'total' => (clone $base)->count(),
            'new_today' => (clone $base)->whereDate('created_at', today())->count(),
            'converted' => (clone $base)->where('is_converted', true)->count(),
            'overdue' => (clone $base)->overdue()->count(),
            'hot' => (clone $base)->where('priority', 'hot')->count(),
            'unassigned' => (clone $base)->unassigned()->count(),
            'total_value' => (clone $base)->notConverted()->sum('lead_value'),
            'won_value' => (clone $base)->converted()->sum('lead_value'),
            'my_tasks' => CrmTask::getStatsForUser(Auth::id()),
        ];
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — Apply initial score on creation
    // ════════════════════════════════════════════════════

    private function applyInitialScore(CrmLead $lead): void
    {
        $score = 0;

        if ($lead->phone) {
            $score += 5;
        }
        if ($lead->email) {
            $score += 5;
        }
        if ($lead->company_name) {
            $score += 5;
        }
        if ($lead->lead_value) {
            $score += 10;
        }
        if ($lead->order_id) {
            $score += CrmLead::SCORE_ORDER_PLACED;
        }

        if ($score > 0) {
            $lead->updateQuietly(['score' => $score]);
        }
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — Get default pipeline ID with fallback
    // ════════════════════════════════════════════════════

    private function getDefaultPipelineId(): ?int
    {
        return CrmPipeline::where('is_active', true)
            ->where('is_default', true)
            ->value('id')
            ?? CrmPipeline::where('is_active', true)
                ->orderBy('sort_order')
                ->value('id');
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — Get first active non-terminal stage
    // ════════════════════════════════════════════════════

    private function getFirstStageId(int $pipelineId): ?int
    {
        return CrmStage::where('crm_pipeline_id', $pipelineId)
            ->where('is_active', true)
            ->where('is_won', false)
            ->where('is_lost', false)
            ->orderBy('sort_order')
            ->value('id');
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — Sync tags (IDs or names)
    // ════════════════════════════════════════════════════

    private function syncTags(CrmLead $lead, array $tags): void
    {
        if (empty($tags)) {
            $lead->tags()->detach();

            return;
        }

        $tagIds = [];
        foreach ($tags as $tag) {
            if (is_int($tag) || ctype_digit((string) $tag)) {
                $tagIds[] = (int) $tag;
            } else {
                $tagIds[] = CrmTag::firstOrCreate(
                    ['color' => '#6b7280']
                )->id;
            }
        }

        $lead->tags()->sync($tagIds);
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE — Refresh lead's next_followup_at
    //  Always equals earliest pending task due_at
    // ════════════════════════════════════════════════════

    private function refreshNextFollowup(CrmLead $lead): void
    {
        $nextDue = CrmTask::where('crm_lead_id', $lead->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->min('due_at');

        $lead->updateQuietly(['next_followup_at' => $nextDue]);
    }
}
