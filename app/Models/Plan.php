<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes; // 🌟 Added SoftDeletes trait

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'trial_days',
        'user_limit',
        'store_limit',
        'product_limit',
        'employee_limit',
        'is_recommended',
        'button_text',
        'button_link',
        'sort_order',
        'is_active',
    ];

    // 🌟 Casts ensure Laravel treats these fields correctly (e.g., boolean instead of 1/0)
    protected $casts = [
        'price' => 'decimal:2',
        'is_recommended' => 'boolean',
        'is_active' => 'boolean',
        'user_limit' => 'integer',
        'store_limit' => 'integer',
        'product_limit' => 'integer',
        'employee_limit' => 'integer',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
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
