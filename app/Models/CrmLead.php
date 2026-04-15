<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmLead extends Model
{
    use SoftDeletes, Tenantable;

    protected $fillable = [
        'company_id',
        'crm_pipeline_id',
        'crm_stage_id',
        'crm_lead_source_id',

        // Person
        'name',
        'phone',
        'email',
        'company_name',

        // Address
        'address',
        'city',
        'state',
        'country',
        'zip_code',

        // Social
        'instagram_id',
        'facebook_id',
        'google_profile',
        'website',

        // Scoring
        'score',
        'priority',
        'lead_value',

        // Conversion
        'client_id',
        'is_converted',
        'converted_at',

        // Links
        'order_id',

        // Contact tracking
        'last_contacted_at',
        'next_followup_at',

        // Notes
        'description',

        // Audit
        'created_by',
    ];

    protected $casts = [
        'is_converted' => 'boolean',
        'converted_at' => 'datetime',
        'last_contacted_at' => 'datetime',
        'next_followup_at' => 'datetime',
        'lead_value' => 'decimal:2',
        'score' => 'integer',
    ];

    // ════════════════════════════════════════════════════
    //  SCORING CONSTANTS
    // ════════════════════════════════════════════════════

    public const SCORE_WHATSAPP_REPLIED = 10;

    public const SCORE_ORDER_PLACED = 20;

    public const SCORE_MEETING_DONE = 15;

    public const SCORE_NO_RESPONSE_3DAYS = -5;

    public const SCORE_TASK_COMPLETED = 5;

    // ════════════════════════════════════════════════════
    //  RELATIONSHIPS
    // ════════════════════════════════════════════════════

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(CrmPipeline::class, 'crm_pipeline_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(CrmStage::class, 'crm_stage_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(CrmLeadSource::class, 'crm_lead_source_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Tags ──
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            CrmTag::class,
            'crm_lead_tags',
            'crm_lead_id',
            'crm_tag_id'
        );
    }

    // ── Assignees (flexible 1→N) ──
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'crm_lead_assignees',
            'crm_lead_id',
            'user_id'
        )->withPivot('is_primary')->withTimestamps();
    }

    public function primaryAssignee(): BelongsTo
    {
        // Convenience: get the primary assigned user directly
        return $this->belongsTo(User::class, 'id', 'id')
            ->join('crm_lead_assignees', function ($join) {
                $join->on('users.id', '=', 'crm_lead_assignees.user_id')
                    ->where('crm_lead_assignees.crm_lead_id', '=', $this->id)
                    ->where('crm_lead_assignees.is_primary', '=', true);
            });
    }

    // ── Activities ──
    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class)->latest();
    }

    // ── Tasks ──
    public function tasks(): HasMany
    {
        return $this->hasMany(CrmTask::class)->orderBy('due_at');
    }

    public function pendingTasks(): HasMany
    {
        return $this->hasMany(CrmTask::class)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('due_at');
    }

    // ════════════════════════════════════════════════════
    //  SCOPES
    // ════════════════════════════════════════════════════

    public function scopeConverted(Builder $q): Builder
    {
        return $q->where('is_converted', true);
    }

    public function scopeNotConverted(Builder $q): Builder
    {
        return $q->where('is_converted', false);
    }

    public function scopeByStage(Builder $q, int $stageId): Builder
    {
        return $q->where('crm_stage_id', $stageId);
    }

    public function scopeByPipeline(Builder $q, int $pipelineId): Builder
    {
        return $q->where('crm_pipeline_id', $pipelineId);
    }

    public function scopeByPriority(Builder $q, string $priority): Builder
    {
        return $q->where('priority', $priority);
    }

    public function scopeHot(Builder $q): Builder
    {
        return $q->where('priority', 'hot')
            ->orWhere('score', '>=', 50);
    }

    public function scopeOverdue(Builder $q): Builder
    {
        return $q->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<', now())
            ->where('is_converted', false);
    }

    public function scopeAssigned(Builder $q, int $userId): Builder
    {
        return $q->whereHas('assignees', fn ($inner) => $inner->where('user_id', $userId)
        );
    }

    public function scopeUnassigned(Builder $q): Builder
    {
        return $q->whereDoesntHave('assignees');
    }

    public function scopeRecent(Builder $q): Builder
    {
        return $q->orderBy('created_at', 'desc');
    }

    public function scopeSearch(Builder $q, string $search): Builder
    {
        return $q->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('company_name', 'like', "%{$search}%")
        );
    }

    // ════════════════════════════════════════════════════
    //  ACCESSORS
    // ════════════════════════════════════════════════════

    public function getScoreLabelAttribute(): string
    {
        return match (true) {
            $this->score >= 50 => 'Hot',
            $this->score >= 20 => 'Warm',
            default => 'Cold',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->next_followup_at
            && $this->next_followup_at->isPast()
            && ! $this->is_converted;
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'hot' => '#ef4444',
            'high' => '#f97316',
            'medium' => '#eab308',
            'low' => '#6b7280',
            default => '#6b7280',
        };
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        if (! $this->phone) {
            return null;
        }
        $number = preg_replace('/[^0-9]/', '', $this->phone);

        return "https://wa.me/91{$number}";
    }

    // ════════════════════════════════════════════════════
    //  SCORE HELPERS
    // ════════════════════════════════════════════════════

    public function addScore(int $points): void
    {
        $this->increment('score', $points);
    }

    public function subtractScore(int $points): void
    {
        $newScore = max(0, $this->score - $points);
        $this->update(['score' => $newScore]);
    }

    // ════════════════════════════════════════════════════
    //  ASSIGNMENT HELPERS
    // ════════════════════════════════════════════════════

    /**
     * Assign a user as primary assignee.
     * For future multi-assign: remove syncWithoutDetaching → attachIfNotExists
     */
    public function assignTo(int $userId): void
    {
        // For now: one primary — detach others, attach new
        $this->assignees()->sync([
            $userId => ['is_primary' => true],
        ]);
    }

    public function getAssignedUserIdAttribute(): ?int
    {
        return $this->assignees()
            ->wherePivot('is_primary', true)
            ->value('users.id');
    }

    // ════════════════════════════════════════════════════
    //  CONVERSION HELPER
    // ════════════════════════════════════════════════════

    public function markConverted(?int $clientId = null): void
    {
        $this->update([
            'is_converted' => true,
            'converted_at' => now(),
            'client_id' => $clientId,
        ]);
    }
}
