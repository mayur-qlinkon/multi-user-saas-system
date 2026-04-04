<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Tenantable;

class ProductBatch extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'product_sku_id',
        'warehouse_id',
        'supplier_id',
        'batch_number',
        'purchase_price',
        'manufacturing_date',
        'expiry_date',
        'qty',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date'        => 'date',
    ];

    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}