<?php

namespace App\Models\Hrm;

use App\Traits\Tenantable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Spatie\Activitylog\LogOptions;
// use Spatie\Activitylog\Traits\LogsActivity;

class Shift extends Model
{
    use  SoftDeletes, Tenantable;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
        'start_time',
        'end_time',
        'late_mark_after',
        'early_leave_before',
        'half_day_after',
        'break_duration_minutes',
        'min_working_hours_minutes',
        'overtime_after_minutes',
        'is_night_shift',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_night_shift' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'break_duration_minutes' => 'integer',
        'min_working_hours_minutes' => 'integer',
        'overtime_after_minutes' => 'integer',
    ];

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logAll()
    //         ->logOnlyDirty()
    //         ->dontSubmitEmptyLogs()
    //         ->setDescriptionForEvent(fn (string $event) => "Shift {$this->name} was {$event}");
    // }

    // ── Relationships ──

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // ── Scopes ──

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ── Accessors ──

    public function getFormattedTimingAttribute(): string
    {
        $start = Carbon::parse($this->start_time)->format('h:i A');
        $end = Carbon::parse($this->end_time)->format('h:i A');

        return "{$start} - {$end}";
    }

    public function getMinWorkingHoursAttribute(): float
    {
        return round($this->min_working_hours_minutes / 60, 1);
    }
}
