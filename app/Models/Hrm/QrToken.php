<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Tenantable;
use App\Models\User;
use App\Models\Store;

class QrToken extends Model
{
    use Tenantable;

    protected $fillable = [
        'company_id',
        'store_id',
        'token',
        'generated_by',
        'expires_at',
        'is_used',
        'used_by',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    // ── Relationships ──

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function generatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function usedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    // ── Scopes ──

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('is_used', false)->where('expires_at', '>', now());
    }

    // ── Methods ──

    public function isValid(): bool
    {
        return !$this->is_used && $this->expires_at->isFuture();
    }

    public function markUsed(int $userId): void
    {
        $this->update([
            'is_used' => true,
            'used_by' => $userId,
            'used_at' => now(),
        ]);
    }
}
