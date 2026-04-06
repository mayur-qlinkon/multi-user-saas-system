<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Tenantable; // 🛡️ Added the Iron Wall

class ProductSku extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'product_id',
        'unit_id',
        'sku',
        'hsn_code',
        'barcode',
        'cost',
        'price',
        'mrp',
        'order_tax',
        'tax_type',
        'stock_alert',
        'is_active',
        // Removed expiry_date (it belongs in product_batches now)
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault([
            'name' => '⚠️ Deleted/Unknown Product',
            'category_id' => null,
        ]);
    }
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function skuValues(): HasMany
    {
        return $this->hasMany(ProductSkuValue::class);
    }

    // 🌟 UPDATED: Now points to the correct ProductStock model
    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class, 'product_sku_id');
    }

    // 🌟 UPDATED: Uses the correct 'product_stocks' table and new pivot fields
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_stocks')
                    ->withPivot('qty', 'rack_number')
                    ->withTimestamps();
    }

    // Helper to get total live stock for this specific SKU across all warehouses
    public function getTotalStockAttribute()
    {
        // Sums up the 'qty' column directly from the product_stocks table
        return $this->stocks()->sum('qty');
    }
    /**
     * CORE RULE: If barcode exists -> use barcode. If empty -> use SKU.
     */
    public function getDisplayBarcodeAttribute(): string
    {
        return !empty($this->barcode) ? $this->barcode : $this->sku;
    }
    /**
     * Relationship: A SKU can have many batches.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class, 'product_sku_id');
    }
}