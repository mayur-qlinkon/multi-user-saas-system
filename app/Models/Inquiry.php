<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Tenantable;

class Inquiry extends Model
{
    use HasFactory, SoftDeletes,Tenantable;

    protected $guarded = ['id'];

    // Ensures dates are automatically cast to Carbon instances
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope: The store this inquiry belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope: The registered client (if they were logged in or matched by phone).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope: The requested items/products.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InquiryItem::class);
    }

    /**
     * Conversion Links
     */
    public function convertedQuotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'converted_to_quotation_id');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_to_invoice_id');
    }
}