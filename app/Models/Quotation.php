<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Quotation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'company_id',
        'store_id',
        
        // Customer Snapshots & Links
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_gstin',
        'billing_address',
        'shipping_address',

        // Tracking & Auditing
        'created_by',
        'sent_by',
        
        // References & Dates
        'quotation_number',
        'reference_number',
        'quotation_date',
        'valid_until',

        // Conversion Tracking
        'converted_to_invoice_id',
        'converted_at',

        // Status & Config
        'status',
        'currency_code',
        'exchange_rate',

        // Indian GST Context
        'supply_state',
        'gst_treatment',

        // Financials
        'subtotal',
        'discount_type',
        'discount_amount',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'tax_amount',
        'shipping_charge',
        'other_charges',
        'round_off',
        'grand_total',

        // Notes & Terms
        'notes',
        'terms_conditions',
        
        // Sending Status
        'is_sent',
        'sent_at',
    ];

    /**
     * The attributes that should be cast.
     * * @var array<string, string>
     */
    protected $casts = [
        'quotation_date' => 'date',
        'valid_until'    => 'date',
        'converted_at'   => 'datetime',
        'sent_at'        => 'datetime',
        'is_sent'        => 'boolean',
        // 🌟 CRITICAL: Cast JSON strings back to arrays automatically
        'billing_address'  => 'array',
        'shipping_address' => 'array',
        
        // Ensure financials are cast as floats for accurate math/display
        'subtotal'        => 'float',
        'discount_amount' => 'float',
        'taxable_amount'  => 'float',
        'cgst_amount'     => 'float',
        'sgst_amount'     => 'float',
        'igst_amount'     => 'float',
        'tax_amount'      => 'float',
        'shipping_charge' => 'float',
        'other_charges'   => 'float',
        'round_off'       => 'float',
        'grand_total'     => 'float',
        'exchange_rate'   => 'float',
    ];

    // ─────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_to_invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS & ACCESSORS
    // ─────────────────────────────────────────────────────────

    /**
     * Check if the quotation has expired based on valid_until date
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->valid_until) {
            return false;
        }
        return now()->startOfDay()->greaterThan($this->valid_until);
    }

    /**
     * Get the appropriate customer name (either the linked client or the snapshotted name)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->customer ? $this->customer->name : ($this->customer_name ?? 'Walk-in / Unknown');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Logs every fillable attribute
            ->logOnlyDirty() // ONLY logs attributes that actually changed
            ->dontSubmitEmptyLogs() // Prevents logging if nothing was actually modified
            ->setDescriptionForEvent(fn(string $eventName) => "Quotation has been {$eventName}");
    }
}