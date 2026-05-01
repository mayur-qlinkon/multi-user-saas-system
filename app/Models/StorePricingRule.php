<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class StorePricingRule extends Model
{
    use Tenantable;

    protected $fillable = [
        'company_id', 'store_id', 'product_sku_id',
        'override_price', 'override_mrp', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function store()      { return $this->belongsTo(Store::class); }
    public function productSku() { return $this->belongsTo(ProductSku::class); }
}