<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory, LogsActivity, SoftDeletes,Tenantable;

    protected $fillable = [
        'store_id', 'warehouse_id', 'customer_id', 'customer_name', 'created_by',
        'salesperson_id', 'pos_terminal_id', 'invoice_number', 'source',
        'invoice_date', 'due_date', 'supply_state', 'gst_treatment',
        'billing_address', 'shipping_address', 'currency_code', 'exchange_rate',
        'subtotal', 'discount_type', 'discount_amount', 'taxable_amount',
        'cgst_amount', 'sgst_amount', 'igst_amount', 'tax_amount',
        'shipping_charge', 'other_charges', 'round_off', 'grand_total',
        'status', 'payment_status', 'irn', 'ack_no', 'ack_date',
        'eway_bill_number', 'notes', 'terms_conditions',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'billing_address' => 'json',
        'shipping_address' => 'json',
        'subtotal' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Logs every fillable attribute
            ->logOnlyDirty() // ONLY logs attributes that actually changed
            ->dontSubmitEmptyLogs() // Prevents logging if nothing was actually modified
            ->setDescriptionForEvent(fn (string $eventName) => "Invoice has been {$eventName}");
    }

    /**
     * Relationships
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all returns (Credit Notes) linked to this invoice.
     */
    public function returns(): HasMany
    {
        return $this->hasMany(InvoiceReturn::class, 'invoice_id');
    }

    /**
     * Get the store/branch where the invoice was generated.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function customer()
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Unified Payments Relationship
     * This links to your single polymorphic payments table
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    /**
     * Stock Movements Relationship
     */
    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }
}
