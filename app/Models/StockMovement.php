<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\Tenantable;

class StockMovement extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'store_id',
        'product_sku_id',
        'warehouse_id',
        'batch_id',
        'batch_number',
        'unit_id',
        'unit_cost',
        'direction',
        'user_id',
        'quantity',
        'balance_after',
        'movement_type',  // 'purchase', 'sale', 'purchase_return', 'sale_return',  'adjustment', 'transfer_in', 'transfer_out', 'opening_stock'                
        'reference_type',
        'reference_id',
        'note'
    ];

    protected $casts = [
        'unit_cost'     => 'decimal:4',
        'quantity'      => 'decimal:4',
        'balance_after' => 'decimal:2',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic relation to link back to the exact action that caused this movement.
     * e.g., $movement->reference might return an App\Models\Invoice instance.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}