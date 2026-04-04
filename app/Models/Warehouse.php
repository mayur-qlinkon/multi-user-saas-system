<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Tenantable;
class Warehouse extends Model
{
    use HasFactory, SoftDeletes,Tenantable;

    protected $fillable = [
        'store_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'zip_code',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    /**
     * Relationship to the Store
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
    public function state() { return $this->belongsTo(State::class); }
}