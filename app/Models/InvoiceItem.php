<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'product_id', 'product_sku_id', 'unit_id',
        'product_name', 'hsn_code', 'quantity', 'unit_price',
        'tax_type', 'discount_type', 'discount_amount', 'taxable_value',
        'tax_percent', 'cgst_amount', 'sgst_amount', 'igst_amount',
        'tax_amount', 'total_amount', 'return_quantity'
    ];

    protected $casts = [
        'quantity'      => 'decimal:4',
        'unit_price'    => 'decimal:4',
        'taxable_value' => 'decimal:4',
        'total_amount'  => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}