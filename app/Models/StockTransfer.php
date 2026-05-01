<?php

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use Tenantable;

    protected $fillable = [
        'company_id', 'transfer_number', 'from_store_id', 'to_store_id',
        'from_warehouse_id', 'to_warehouse_id', 'status', 'notes',
        'dispatched_at', 'received_at', 'created_by', 'received_by',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'received_at'   => 'datetime',
    ];

    public function fromStore()  { return $this->belongsTo(Store::class, 'from_store_id'); }
    public function toStore()    { return $this->belongsTo(Store::class, 'to_store_id'); }
    public function items()      { return $this->hasMany(StockTransferItem::class); }
    public function creator()    { return $this->belongsTo(User::class, 'created_by'); }
    public function receiver()   { return $this->belongsTo(User::class, 'received_by'); }
}