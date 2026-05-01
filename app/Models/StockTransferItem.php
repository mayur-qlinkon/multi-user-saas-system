<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id', 'product_id', 'product_sku_id', 'batch_id',
        'qty_sent', 'qty_received', 'cost_price', 'notes',
    ];

    public function transfer() { return $this->belongsTo(StockTransfer::class, 'stock_transfer_id'); }
    public function product()  { return $this->belongsTo(Product::class); }
    public function sku()      { return $this->belongsTo(ProductSku::class, 'product_sku_id'); }
}