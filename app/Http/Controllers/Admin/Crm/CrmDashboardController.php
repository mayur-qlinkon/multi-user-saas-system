<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmActivity;
use App\Models\CrmLead;
use App\Models\CrmPipeline;
use App\Models\CrmTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrmDashboardController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $userId = Auth::id();

        // ════════════════════════════════════════════════════
        //  1. TOP STATS
        // ════════════════════════════════════════════════════

        $base = CrmLead::where('company_id', $companyId);

        $stats = [
            'total' => (clone $base)->count(),
            'converted' => (clone $base)->where('is_converted', true)->count(),
            'hot' => (clone $base)->where('priority', 'hot')->count(),
            'overdue' => (clone $base)->whereNotNull('next_followup_at')
                ->where('next_followup_at', '<', now())
                ->where('is_converted', false)
                ->count(),
            'total_value' => (clone $base)->where('is_converted', false)->sum('lead_value'),
        ];

        // ════════════════════════════════════════════════════
        //  2. PIPELINE STAGE STATS (funnel data)
        //  Keyed by pipeline_id for JS switching
        // ════════════════════════════════════════════════════

        $pipelines = CrmPipeline::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->with(['stages' => fn ($q) => $q->active()->ordered()])
            ->get();

        $stageStats = [];

        foreach ($pipelines as $pipeline) {
            $stageStats[$pipeline->id] = $pipeline->stages->map(function ($stage) use ($companyId) {
                $leads = CrmLead::where('company_id', $companyId)
                    ->where('crm_stage_id', $stage->id)
                    ->where('is_converted', false);

                return [
                    'stage' => $stage->name,
                    'color' => $stage->color,
                    'count' => $leads->count(),
                    'value' => (float) $leads->sum('lead_value'),
                ];
            })->values()->toArray();
        }

        // ════════════════════════════════════════════════════
        //  3. SOURCE BREAKDOWN
        // ════════════════════════════════════════════════════

        $sourceStats = CrmLead::where('company_id', $companyId)
            ->select('crm_lead_source_id', DB::raw('count(*) as count'))
            ->groupBy('crm_lead_source_id')
            ->with('source:id,name')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'source' => $row->source?->name ?? 'Unknown',
                'count' => $row->count,
            ]);

        // ════════════════════════════════════════════════════
        //  4. PRIORITY DISTRIBUTION
        // ════════════════════════════════════════════════════

        $priorityStats = CrmLead::where('company_id', $companyId)
            ->where('is_converted', false)
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // ════════════════════════════════════════════════════
        //  5. HOT LEADS (top 6)
        // ════════════════════════════════════════════════════

        $hotLeads = CrmLead::where('company_id', $companyId)
            ->where('priority', 'hot')
            ->where('is_converted', false)
            ->with(['stage:id,name,color'])
            ->orderByDesc('score')
            ->limit(6)
            ->get();

        // ════════════════════════════════════════════════════
        //  6. OVERDUE FOLLOW-UPS (top 5)
        // ════════════════════════════════════════════════════

        $overdueLeads = CrmLead::where('company_id', $companyId)
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<', now())
            ->where('is_converted', false)
            ->orderBy('next_followup_at')
            ->limit(5)
            ->get();

        // ════════════════════════════════════════════════════
        //  7. MY TASKS — today + overdue for logged in user
        // ════════════════════════════════════════════════════

        $myTasks = CrmTask::where('company_id', $companyId)
            ->where('assigned_to', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where(fn ($q) => $q->whereDate('due_at', today())         // due today
                ->orWhere('due_at', '<', now())        // overdue
            )
            ->with(['lead:id,name,phone'])
            ->orderBy('due_at')
            ->limit(8)
            ->get();

        // ════════════════════════════════════════════════════
        //  8. RECENT ACTIVITY (last 15 entries)
        // ════════════════════════════════════════════════════

        $recentActivities = CrmActivity::where('company_id', $companyId)
            ->with(['lead:id,name'])
            ->latest()
            ->limit(15)
            ->get();

        // ════════════════════════════════════════════════════
        //  9. ASSIGNEE PERFORMANCE
        //  Only for owner/admin — skip if not owner
        // ════════════════════════════════════════════════════

        $assigneeStats = collect();

        /** @var User $user */
        $user = Auth::user();
        if (is_owner() || $user->hasRole('admin')) {
            $assigneeStats = User::query()
                ->internal()
                ->where('company_id', $companyId)
                ->whereHas('crmLeads') // only users with leads
                ->get(['id', 'name'])
                ->map(function ($user) use ($companyId) {
                    $leads = CrmLead::where('company_id', $companyId)
                        ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id)
                        );

                    return [
                        'name' => $user->name,
                        'assigned' => (clone $leads)->count(),
                        'converted' => (clone $leads)->where('is_converted', true)->count(),
                        'hot' => (clone $leads)->where('priority', 'hot')->count(),
                        'overdue' => (clone $leads)
                            ->whereNotNull('next_followup_at')
                            ->where('next_followup_at', '<', now())
                            ->where('is_converted', false)
                            ->count(),
                        'value' => (float) (clone $leads)
                            ->where('is_converted', false)
                            ->sum('lead_value'),
                    ];
                })
                ->filter(fn ($s) => $s['assigned'] > 0)
                ->sortByDesc('assigned')
                ->values();
        }

        return view('admin.crm.dashboard', compact(
            'stats',
            'pipelines',
            'stageStats',
            'sourceStats',
            'priorityStats',
            'hotLeads',
            'overdueLeads',
            'myTasks',
            'recentActivities',
            'assigneeStats'
        ));
    }
}
