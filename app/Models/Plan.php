<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'user_limit',
        'store_limit',
        'is_active'
    ];

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'plan_modules');
    }

    public function subscriptions()
    {
        return $this->hasMany(CompanySubscription::class);
    }
}