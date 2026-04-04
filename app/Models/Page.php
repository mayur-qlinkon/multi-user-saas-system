<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    // ── Application-Level Enums ──
    // Keeps the DB flexible while keeping the code strict
    public const TYPE_LEGAL  = 'legal';
    public const TYPE_ABOUT  = 'about';
    public const TYPE_CUSTOM = 'custom';

    public const TYPES = [
        self::TYPE_LEGAL  => 'Legal & Compliance',
        self::TYPE_ABOUT  => 'Company Information',
        self::TYPE_CUSTOM => 'Custom Page',
    ];

    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'content',
        'type',
        'seo_title',
        'seo_description',
        'is_published',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // ════════════════════════════════════════════════════
    //  RELATIONSHIPS
    // ════════════════════════════════════════════════════

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ════════════════════════════════════════════════════
    //  QUERY SCOPES (For cleaner controllers)
    // ════════════════════════════════════════════════════

    /**
     * Only fetch pages that are live on the storefront.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Filter by specific page type (e.g., fetching only legal pages for the footer).
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // ════════════════════════════════════════════════════
    //  ACCESSORS / MUTATORS
    // ════════════════════════════════════════════════════

    /**
     * Fallback for SEO Title if it wasn't provided.
     */
    public function getMetaTitleAttribute(): string
    {
        return $this->seo_title ?: $this->title;
    }

    /**
     * Get the human-readable type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? 'Unknown Type';
    }
}