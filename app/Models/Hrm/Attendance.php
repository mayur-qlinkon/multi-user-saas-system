<?php

namespace App\Models\Hrm;

use App\Models\Store;
use App\Models\User;
use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Attendance extends Model
{
    use HasFactory,LogsActivity, SoftDeletes, Tenantable;

    // ── Constants ──

    const STATUS_PRESENT = 'present';

    const STATUS_ABSENT = 'absent';

    const STATUS_LATE = 'late';

    const STATUS_HALF_DAY = 'half_day';

    const STATUS_ON_LEAVE = 'on_leave';

    const STATUS_HOLIDAY = 'holiday';

    const STATUS_WEEK_OFF = 'week_off';

    const STATUS_PENDING = 'pending';

    const METHOD_QR = 'qr';

    const METHOD_MANUAL = 'manual';

    const METHOD_AUTO = 'auto';

    const METHOD_BIOMETRIC = 'biometric';

    const STATUS_LABELS = [
        self::STATUS_PRESENT => 'Present',
        self::STATUS_ABSENT => 'Absent',
        self::STATUS_LATE => 'Late',
        self::STATUS_HALF_DAY => 'Half Day',
        self::STATUS_ON_LEAVE => 'On Leave',
        self::STATUS_HOLIDAY => 'Holiday',
        self::STATUS_WEEK_OFF => 'Week Off',
        self::STATUS_PENDING => 'Pending Approval',
    ];

    const STATUS_COLORS = [
        self::STATUS_PRESENT => ['bg' => '#ecfdf5', 'text' => '#065f46', 'dot' => '#10b981'],
        self::STATUS_ABSENT => ['bg' => '#fef2f2', 'text' => '#991b1b', 'dot' => '#ef4444'],
        self::STATUS_LATE => ['bg' => '#fffbeb', 'text' => '#92400e', 'dot' => '#f59e0b'],
        self::STATUS_HALF_DAY => ['bg' => '#eff6ff', 'text' => '#1e40af', 'dot' => '#3b82f6'],
        self::STATUS_ON_LEAVE => ['bg' => '#f5f3ff', 'text' => '#5b21b6', 'dot' => '#8b5cf6'],
        self::STATUS_HOLIDAY => ['bg' => '#fdf4ff', 'text' => '#86198f', 'dot' => '#d946ef'],
        self::STATUS_WEEK_OFF => ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#9ca3af'],
        self::STATUS_PENDING => ['bg' => '#fff7ed', 'text' => '#9a3412', 'dot' => '#f97316'],
    ];

    // ── Fillable ──

    protected $fillable = [
        'company_id', 'employee_id', 'store_id', 'date',
        'check_in_time', 'check_in_lat', 'check_in_lng', 'check_in_method',
        'check_out_time', 'check_out_lat', 'check_out_lng', 'check_out_method',
        'worked_hours', 'overtime_hours', 'status',
        'is_holiday', 'working_on_holiday',
        'is_overridden', 'overridden_by', 'override_reason', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'check_in_lat' => 'decimal:7',
        'check_in_lng' => 'decimal:7',
        'check_out_lat' => 'decimal:7',
        'check_out_lng' => 'decimal:7',
        'worked_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'is_holiday' => 'boolean',
        'working_on_holiday' => 'boolean',
        'is_overridden' => 'boolean',
    ];

    // ── Activity Log ──

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'check_in_time', 'check_out_time', 'is_overridden', 'override_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "Attendance for {$this->date->format('d M Y')} was {$event}");
    }

    // ── Relationships ──

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function overriddenByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    // ── Scopes ──

    public function scopeForDate(Builder $query, $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('date', today());
    }

    public function scopeForDateRange(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeCheckedIn(Builder $query): Builder
    {
        return $query->whereNotNull('check_in_time');
    }

    public function scopePendingCheckout(Builder $query): Builder
    {
        return $query->whereNotNull('check_in_time')->whereNull('check_out_time');
    }

    // ── Accessors ──

    public function getIsCompleteAttribute(): bool
    {
        return $this->check_in_time && $this->check_out_time;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): array
    {
        return self::STATUS_COLORS[$this->status] ?? ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#9ca3af'];
    }
}
