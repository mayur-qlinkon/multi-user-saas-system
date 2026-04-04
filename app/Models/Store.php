<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use App\Traits\Tenantable;

class Store extends Model
{
    use HasFactory, SoftDeletes, Tenantable;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'email',
        'phone',
        'upi_id',
        'logo',
        'signature',
        'gst_number',
        'currency',
        'address',
        'city',
        'state_id',
        'zip_code',
        'country',
        'invoice_prefix',
        'purchase_prefix',
        'next_invoice_number',
        'is_active',
        'office_lat',
        'office_lng',
        'gps_radius_meters',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'next_invoice_number' => 'integer',
        'office_lat' => 'float',
        'office_lng' => 'float',
        'gps_radius_meters' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($store) {
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name) . '-' . Str::random(5);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Store belongs to company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Store belongs to a State (GST Logic)
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Users assigned to this store
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_user');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getLogoUrlAttribute()
    {
        return $this->logo
            ? asset('storage/' . $this->logo)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }
    
    public function getSignatureUrlAttribute()
    {
        return $this->signature
            ? asset('storage/' . $this->signature)
            : null;
    }
}