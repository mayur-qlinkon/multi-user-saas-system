<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_modules');
    }
}
