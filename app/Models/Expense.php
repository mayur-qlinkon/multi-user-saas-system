<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
// use Spatie\Activitylog\LogOptions;
// use Spatie\Activitylog\Traits\LogsActivity;

class Expense extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, Tenantable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'store_id',
        'expense_category_id',
        'expense_number',
        // Merchant & Tax
        'currency_code',
        'exchange_rate',
        'tax_type',
        'tax_percent',
        'merchant_name',
        'merchant_gstin',
        'reference_number',
        'expense_date',
        // Financials
        'payment_status', // 'unpaid', 'partial', 'paid'
        'round_off',
        'base_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_amount',
        // Attributes
        'is_reimbursable',
        'is_billable',
        'status', // 'draft', 'pending_approval', 'approved', 'rejected', 'reimbursed'
        'attachment',
        'source',
        'notes',

        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expense_date' => 'date',
        'approved_at' => 'datetime',

        'exchange_rate' => 'decimal:4',
        'tax_percent' => 'decimal:2',
        'round_off' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',

        'is_reimbursable' => 'boolean',
        'is_billable' => 'boolean',
    ];

    // ════════════════════════════════════════════════════
    //  ACTIVITY LOGGING (Audit Trail)
    // ════════════════════════════════════════════════════

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logFillable()
    //         ->logOnlyDirty()
    //         ->dontSubmitEmptyLogs()
    //         ->setDescriptionForEvent(fn (string $eventName) => "Expense has been {$eventName}");
    // }

    // ════════════════════════════════════════════════════
    //  MEDIA LIBRARY (Spatie)
    // ════════════════════════════════════════════════════

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('receipts')
            ->useDisk('public') // Or 's3' if you use AWS
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
    }

    // ════════════════════════════════════════════════════
    //  RELATIONSHIPS
    // ════════════════════════════════════════════════════

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * The employee/user who incurred or submitted the expense.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * The manager/admin who approved the expense.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 🌟 THE UNIFIED PAYMENTS LINK
     * Link this expense to your unified payments ledger.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }
}
