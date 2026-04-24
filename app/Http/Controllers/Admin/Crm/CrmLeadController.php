<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreActivityRequest;
use App\Http\Requests\Crm\StoreCrmLeadRequest;
use App\Http\Requests\Crm\StoreTaskRequest;
use App\Models\CrmActivity;
use App\Models\CrmLead;
use App\Models\CrmLeadSource;
use App\Models\CrmPipeline;
use App\Models\CrmStage;
use App\Models\CrmTag;
use App\Models\CrmTask;
use App\Models\User;
use App\Services\CrmLeadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class CrmLeadController extends Controller
{
    public function __construct(protected CrmLeadService $service) {}

    // ════════════════════════════════════════════════════
    //  INDEX
    //  GET /admin/crm/leads
    // ════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $filters = $request->only([
            'q', 'pipeline_id', 'stage_id', 'priority',
            'source_id', 'tag_id', 'mine', 'assigned_to',
            'unassigned', 'overdue', 'converted',
            'from', 'to', 'sort', 'dir',
        ]);

        $leads = $this->service->getLeads($filters, perPage: 25);
        $stats = $this->service->getStats($companyId);
        $pipelines = CrmPipeline::where('company_id', $companyId)->active()->ordered()->get();
        $stages = CrmStage::where('company_id', $companyId)
            ->when($request->pipeline_id, fn ($q) => $q->where('crm_pipeline_id', $request->pipeline_id))
            ->active()->ordered()->get();
        $sources = CrmLeadSource::where('company_id', $companyId)->active()->ordered()->get();
        $tags = CrmTag::where('company_id', $companyId)->ordered()->get();
        $users = $this->getAssignableUsers($companyId);

        return view('admin.crm.leads.index', compact(
            'leads', 'stats', 'pipelines', 'stages',
            'sources', 'tags', 'users', 'filters'
        ));
    }

    // ── Public wrapper for blade use ──
    public function formatTaskPublic(CrmTask $task): array
    {
        return $this->formatTask($task);
    }

    // ════════════════════════════════════════════════════
    //  CREATE
    //  GET /admin/crm/leads/create
    // ════════════════════════════════════════════════════

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $pipelines = CrmPipeline::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->with(['stages' => fn ($q) => $q->active()->ordered()])
            ->get();
        $sources = CrmLeadSource::where('company_id', $companyId)->active()->ordered()->get();
        $tags = CrmTag::where('company_id', $companyId)->ordered()->get();
        $users = $this->getAssignableUsers($companyId);

        return view('admin.crm.leads.create', compact('pipelines', 'sources', 'tags', 'users'));
    }

    // ════════════════════════════════════════════════════
    //  STORE
    //  POST /admin/crm/leads
    // ════════════════════════════════════════════════════

    public function store(StoreCrmLeadRequest $request): JsonResponse
    {
        $companyId = Auth::user()->company_id;

        try {
            $lead = $this->service->createLead($request->validated(), $companyId);

            Log::info('[AdminCrmLead] Lead created', [
                'lead_id' => $lead->id,
                'by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Lead \"{$lead->name}\" created.",
                'redirect' => route('admin.crm.leads.show', $lead->id),
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (Throwable $e) {
            Log::error('[AdminCrmLead] Store failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to create lead.'], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  SHOW
    //  GET /admin/crm/leads/{lead}
    // ════════════════════════════════════════════════════

    public function show(CrmLead $lead)
    {
        $this->authorizeLead($lead);

        $lead->load([
            'stage',
            'pipeline.stages' => fn ($q) => $q->active()->ordered(),
            'source',
            'tags',
            'assignees',
            'activities' => fn ($q) => $q->with('user:id,name')->latest()->limit(50),
            'tasks' => fn ($q) => $q->with('assignedUser:id,name')->orderBy('due_at'),
            'client',
            'order',
        ]);

        $companyId = Auth::user()->company_id;

        $activityTypes = collect(CrmActivity::TYPES)
            ->filter(fn ($v, $k) => ! in_array($k, ['stage_change', 'lead_created', 'converted', 'score_changed']))
            ->map(fn ($v, $k) => ['key' => $k, 'label' => $v['label'], 'icon' => $v['icon']])
            ->values();

        $taskTypes = collect(CrmTask::TYPES)
            ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])
            ->values();

        $stages = $lead->pipeline?->stages ?? collect();
        $tags = CrmTag::where('company_id', $companyId)->ordered()->get();
        $sources = CrmLeadSource::where('company_id', $companyId)->active()->ordered()->get();
        $users = $this->getAssignableUsers($companyId);

        return view('admin.crm.leads.show', compact(
            'lead', 'stages', 'activityTypes', 'taskTypes',
            'tags', 'sources', 'users'
        ));
    }

    // ════════════════════════════════════════════════════
    //  EDIT
    //  GET /admin/crm/leads/{lead}/edit
    // ════════════════════════════════════════════════════

    public function edit(CrmLead $lead)
    {
        $this->authorizeLead($lead);
        $lead->load(['tags', 'assignees', 'stage.pipeline', 'source']);

        $companyId = Auth::user()->company_id;
        $currentStageId = $lead->crm_stage_id;

        $pipelines = CrmPipeline::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->with(['stages' => function ($query) use ($currentStageId) {
                $query->where(function ($stageQuery) use ($currentStageId) {
                    $stageQuery->where('is_active', true);

                    if ($currentStageId) {
                        $stageQuery->orWhere('id', $currentStageId);
                    }
                })->ordered();
            }])
            ->get();
        $sources = CrmLeadSource::where('company_id', $companyId)->active()->ordered()->get();
        $tags = CrmTag::where('company_id', $companyId)->ordered()->get();
        $users = $this->getAssignableUsers($companyId);

        // dd($pipelines);
        return view('admin.crm.leads.edit', compact('lead', 'pipelines', 'sources', 'tags', 'users'));
    }

    // ════════════════════════════════════════════════════
    //  UPDATE
    //  PUT /admin/crm/leads/{lead}
    // ════════════════════════════════════════════════════

    public function update(StoreCrmLeadRequest $request, CrmLead $lead): JsonResponse
    {
        $this->authorizeLead($lead);

        try {
            $updated = $this->service->updateLead($lead, $request->validated());

            return response()->json([
                'success' => true,
                'message' => "Lead \"{$updated->name}\" updated.",
                'lead' => $updated->only([
                    'id', 'name', 'phone', 'email', 'priority', 'score',
                    'is_converted', 'next_followup_at', 'last_contacted_at',
                ]),
            ]);

        } catch (Throwable $e) {
            Log::error('[AdminCrmLead] Update failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to update lead.'], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  DESTROY
    //  DELETE /admin/crm/leads/{lead}
    // ════════════════════════════════════════════════════

    public function destroy(CrmLead $lead): JsonResponse
    {
        $this->authorizeLead($lead);

        try {
            $name = $lead->name;
            $this->service->deleteLead($lead);

            return response()->json([
                'success' => true,
                'message' => "Lead \"{$name}\" deleted.",
            ]);

        } catch (Throwable $e) {
            Log::error('[AdminCrmLead] Destroy failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to delete lead.'], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  MOVE STAGE — AJAX
    //  POST /admin/crm/leads/{lead}/stage
    // ════════════════════════════════════════════════════

    public function moveStage(Request $request, CrmLead $lead): JsonResponse
    {
        $this->authorizeLead($lead);

        $request->validate([
            'stage_id' => ['required', 'integer', 'exists:crm_stages,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $updated = $this->service->moveToStage(
                $lead,
                $request->stage_id,
                $request->note
            );

            return response()->json([
                'success' => true,
                'message' => "Moved to \"{$updated->stage->name}\".",
                'stage' => [
                    'id' => $updated->stage->id,
                    'name' => $updated->stage->name,
                    'color' => $updated->stage->color,
                    'is_won' => $updated->stage->is_won,
                    'is_lost' => $updated->stage->is_lost,
                ],
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('[AdminCrmLead] MoveStage failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to move stage.'], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  LOG ACTIVITY — AJAX
    //  POST /admin/crm/leads/{lead}/activity
    // ════════════════════════════════════════════════════

    public function logActivity(StoreActivityRequest $request, CrmLead $lead): JsonResponse
    {
        $this->authorizeLead($lead);

        try {
            $activity = $this->service->logActivity(
                $lead,
                $request->type,
                $request->description,
                $request->meta
            );

            $activity->load('user:id,name');

            return response()->json([
                'success' => true,
                'message' => 'Activity logged.',
                'activity' => [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'type_label' => $activity->type_label,
                    'type_icon' => $activity->type_icon,
                    'description' => $activity->description,
                    'is_auto' => $activity->is_auto,
                    'user_name' => $activity->user?->name ?? 'System',
                    'created_at' => $activity->created_at->diffForHumans(),
                    'created_at_full' => $activity->created_at->format('d M Y, h:i A'),
                ],
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('[AdminCrmLead] LogActivity failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to log activity.'], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  STORE TASK — AJAX
    //  POST /admin/crm/leads/{lead}/tasks
    // ════════════════════════════════════════════════════

    public function storeTask(StoreTaskRequest $request, CrmLead $lead): JsonResponse
    {
        $this->authorizeLead($lead);

        try {
            $task = $this->service->createTask($lead, $request->validated());
            $task->load('assignedUser:id,name');

            return response()->json([
                'success' => true,
                'message' => "Task \"{$task->title}\" created.",
                'task' => $this->formatTask($task),
            ]);

        } catch (Throwable $e) {
            Log::error('[AdminCrmLead] StoreTask failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to create task.'], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  UPDATE TASK — AJAX
    //  PUT /admin/crm/leads/{lead}/tasks/{task}
    // ════════════════════════════════════════════════════

    public function updateTask(StoreTaskRequest $request, CrmLead $lead, CrmTask $task): JsonResponse
    {
        $this->authorizeLead($lead);
        $this->authorizeTask($task, $lead);

        try {
            $task->update($request->validated());
            $task->load('assignedUser:id,name');

            return response()->json([
                'success' => true,
                'message' => 'Task updated.',
                'task' => $this->formatTask($task->fresh()),
            ]);

        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update task.'], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  COMPLETE TASK — AJAX
    //  POST /admin/crm/leads/{lead}/tasks/{task}/complete
    // ════════════════════════════════════════════════════

    public function completeTask(Request $request, CrmLead $lead, CrmTask $task): JsonResponse
    {
        $this->authorizeLead($lead);
        $this->authorizeTask($task, $lead);

        $request->validate([
            'completion_note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $completed = $this->service->completeTask($task, $request->completion_note ?? '');

            return response()->json([
                'success' => true,
                'message' => 'Task marked as completed.',
                'task' => $this->formatTask($completed),
            ]);

        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to complete task.'], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  DESTROY TASK — AJAX
    //  DELETE /admin/crm/leads/{lead}/tasks/{task}
    // ════════════════════════════════════════════════════

    public function destroyTask(CrmLead $lead, CrmTask $task): JsonResponse
    {
        $this->authorizeLead($lead);
        $this->authorizeTask($task, $lead);

        try {
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted.',
                'task_id' => $task->id,
            ]);

        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete task.'], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  CONVERT LEAD → CLIENT — AJAX
    //  POST /admin/crm/leads/{lead}/convert
    // ════════════════════════════════════════════════════

    public function convert(CrmLead $lead): JsonResponse
    {
        $this->authorizeLead($lead);

        try {
            $converted = $this->service->convertLead($lead);

            return response()->json([
                'success' => true,
                'message' => 'Lead converted to client successfully.',
                'client_id' => $converted->client_id,
                // 🌟 FIX: Pass the name as a query string parameter
                'client_url' => $converted->client_id
                    ? route('admin.clients.index', ['search' => $lead->name])
                    : null,
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('[AdminCrmLead] Convert failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Conversion failed.'], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  UPDATE SCORE — AJAX
    //  POST /admin/crm/leads/{lead}/score
    // ════════════════════════════════════════════════════

    public function updateScore(Request $request, CrmLead $lead): JsonResponse
    {
        $this->authorizeLead($lead);

        $request->validate([
            'points' => ['required', 'integer'],
            'operation' => ['required', Rule::in(['add', 'subtract'])],
        ]);

        try {
            $request->operation === 'add'
                ? $lead->addScore($request->points)
                : $lead->subtractScore($request->points);

            return response()->json([
                'success' => true,
                'score' => $lead->fresh()->score,
                'score_label' => $lead->fresh()->score_label,
            ]);

        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update score.'], 500);
        }
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ════════════════════════════════════════════════════

    private function authorizeLead(CrmLead $lead): void
    {
        if ($lead->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }
    }

    private function authorizeTask(CrmTask $task, CrmLead $lead): void
    {
        if ($task->crm_lead_id !== $lead->id || $task->company_id !== Auth::user()->company_id) {
            abort(403, 'Task does not belong to this lead.');
        }
    }

    private function getAssignableUsers(int $companyId): Collection
    {
        return User::query()
            ->internal()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function formatTask(CrmTask $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'type' => $task->type,
            'type_label' => $task->type_label,
            'status' => $task->status,
            'status_label' => $task->status_label,
            'status_color' => $task->status_color,
            'priority' => $task->priority,
            'due_at' => $task->due_at?->format('d M Y, h:i A'),
            'due_at_iso' => $task->due_at?->toISOString(),
            'is_overdue' => $task->is_overdue,
            'completed_at' => $task->completed_at?->format('d M Y, h:i A'),
            'completion_note' => $task->completion_note,
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $task->assignedUser?->name,
        ];
    }
}
