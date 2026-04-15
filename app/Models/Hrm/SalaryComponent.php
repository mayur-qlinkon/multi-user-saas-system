<?php

namespace App\Models\Hrm;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryComponent extends Model
{
    use SoftDeletes, Tenantable;

    const TYPE_EARNING = 'earning';

    const TYPE_DEDUCTION = 'deduction';

    const CALC_FIXED = 'fixed';

    const CALC_PERCENTAGE = 'percentage';

    protected $fillable = [
        'company_id', 'name', 'code', 'type', 'description',
        'calculation_type', 'percentage_of', 'default_amount',
        'is_taxable', 'is_statutory', 'appears_on_payslip',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_statutory' => 'boolean',
        'appears_on_payslip' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ── Relationships ──

    public function employeeStructures(): HasMany
    {
        return $this->hasMany(EmployeeSalaryStructure::class);
    }

    // ── Scopes ──

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeEarnings(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EARNING);
    }

    public function scopeDeductions(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_DEDUCTION);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
