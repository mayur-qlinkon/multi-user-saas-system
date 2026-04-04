<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'companies';

    /*
    |--------------------------------------------------------------------------
    | Fillable Fields
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'email',
        'phone',
        'logo',
        'gst_number',
        'currency',
        'address',
        'city',
        'state_id',
        'zip_code',
        'country',
        'is_active',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function state() {
        return $this->belongsTo(State::class);
    }
    /**
     * Company has many users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Company has many stores
     */
    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Boot Method
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {

            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name);
            }

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function subscription()
    {
        return $this->hasOne(CompanySubscription::class);
    }
}
