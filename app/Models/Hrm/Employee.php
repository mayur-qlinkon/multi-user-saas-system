<?php

namespace App\Models\Hrm;

use App\Models\Store;
use App\Models\User;
use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Spatie\Activitylog\LogOptions;
// use Spatie\Activitylog\Traits\LogsActivity;

class Employee extends Model
{
    use  SoftDeletes, Tenantable;

    // ── Constants ──

    const STATUS_ACTIVE = 'active';

    const STATUS_INACTIVE = 'inactive';

    const STATUS_TERMINATED = 'terminated';

    const STATUS_ON_NOTICE = 'on_notice';

    const STATUS_ABSCONDING = 'absconding';

    const TYPE_FULL_TIME = 'full_time';

    const TYPE_PART_TIME = 'part_time';

    const TYPE_CONTRACT = 'contract';

    const TYPE_INTERN = 'intern';

    const TYPE_FREELANCER = 'freelancer';

    const SALARY_MONTHLY = 'monthly';

    const SALARY_DAILY = 'daily';

    const SALARY_HOURLY = 'hourly';

    const STATUS_LABELS = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_TERMINATED => 'Terminated',
        self::STATUS_ON_NOTICE => 'On Notice',
        self::STATUS_ABSCONDING => 'Absconding',
    ];

    const STATUS_COLORS = [
        self::STATUS_ACTIVE => ['bg' => '#ecfdf5', 'text' => '#065f46', 'dot' => '#10b981'],
        self::STATUS_INACTIVE => ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#9ca3af'],
        self::STATUS_TERMINATED => ['bg' => '#fef2f2', 'text' => '#991b1b', 'dot' => '#ef4444'],
        self::STATUS_ON_NOTICE => ['bg' => '#fffbeb', 'text' => '#92400e', 'dot' => '#f59e0b'],
        self::STATUS_ABSCONDING => ['bg' => '#fef2f2', 'text' => '#991b1b', 'dot' => '#dc2626'],
    ];

    const TYPE_LABELS = [
        self::TYPE_FULL_TIME => 'Full Time',
        self::TYPE_PART_TIME => 'Part Time',
        self::TYPE_CONTRACT => 'Contract',
        self::TYPE_INTERN => 'Intern',
        self::TYPE_FREELANCER => 'Freelancer',
    ];

    // ── Fillable ──

    protected $fillable = [
        'company_id', 'user_id', 'store_id', 'department_id', 'designation_id', 'shift_id', 'reporting_to',
        'employee_code', 'date_of_birth', 'gender', 'marital_status', 'blood_group',
        'date_of_joining', 'date_of_leaving', 'probation_end_date',
        'employment_type', 'status', 'exit_reason',
        'basic_salary', 'salary_type',
        'bank_name', 'bank_account_number', 'bank_ifsc', 'bank_branch',
        'pan_number', 'aadhaar_number', 'uan_number', 'esi_number', 'pf_number',
        'current_address', 'permanent_address',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
        'photo', 'id_proof', 'address_proof', 'notes', 'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'date_of_leaving' => 'date',
        'probation_end_date' => 'date',
        'basic_salary' => 'decimal:2',
        'pan_number' => 'encrypted',
        'aadhaar_number' => 'encrypted',
    ];

    // ── Boot ──

    protected static function booted(): void
    {
        static::creating(function (Employee $employee) {
            if (empty($employee->employee_code)) {
                $employee->employee_code = static::generateCode($employee->company_id);
            }
        });
    }

    public static function generateCode(int $companyId, string $prefix = 'EMP'): string
    {
        $latest = static::withTrashed()
            ->where('company_id', $companyId)
            ->where('employee_code', 'like', "{$prefix}-%")
            ->orderBy('id', 'desc')
            ->value('employee_code');

        $sequence = $latest ? ((int) last(explode('-', $latest))) + 1 : 1;

        return "{$prefix}-".str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // ── Activity Log ──

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logAll()
    //         ->logOnlyDirty()
    //         ->dontSubmitEmptyLogs()
    //         ->setDescriptionForEvent(fn (string $event) => "Employee {$this->employee_code} was {$event}");
    // }

    // ── Relationships ──

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reporting_to');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'reporting_to');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function salaryStructures(): HasMany
    {
        return $this->hasMany(EmployeeSalaryStructure::class);
    }

    public function salarySlips(): HasMany
    {
        return $this->hasMany(SalarySlip::class);
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ──

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeOfDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    // ── Accessors ──

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): array
    {
        return self::STATUS_COLORS[$this->status] ?? ['bg' => '#f3f4f6', 'text' => '#374151', 'dot' => '#9ca3af'];
    }

    public function getFullNameAttribute(): string
    {
        return $this->user?->name ?? '';
    }
}
