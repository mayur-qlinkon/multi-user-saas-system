<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait Branchable
{
    /**
     * The "booting" method of the trait.
     */
    protected static function bootBranchable()
    {
        // 1. Only apply if a user is logged in AND they have selected a store in their session
        if (Auth::check() && session()->has('store_id')) {

            // 2. THE BRANCH WALL (Read Protection)
            // Auto-append "where store_id = X" to queries.
            static::addGlobalScope('branch', function (Builder $builder) {
                $builder->where($builder->getModel()->getTable().'.store_id', session('store_id'));
            });

            // 3. AUTO-ASSIGN (Write Convenience)
            // Automatically insert the store_id when creating new Invoices, Quotes, etc.
            static::creating(function ($model) {
                if (empty($model->store_id)) {
                    $model->store_id = session('store_id');
                }
            });
        }
    }
}
