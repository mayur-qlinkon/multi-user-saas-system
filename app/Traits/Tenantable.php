<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait Tenantable
{
    /**
     * The "booting" method of the trait.
     * This runs automatically when a model using this trait is initialized.
     */
    protected static function bootTenantable()
    {
        // 1. Only apply this if a user is logged in AND they belong to a company
        if (Auth::check() && Auth::user()->company_id) {

            // 2. THE IRON WALL (Read Protection)
            // Automatically append "where company_id = X" to EVERY select/update/delete query
            static::addGlobalScope('tenant', function (Builder $builder) {
                // We use getTable() to prevent ambiguous column errors in JOINs
                $builder->where($builder->getModel()->getTable().'.company_id', Auth::user()->company_id);
            });

            // 3. AUTO-ASSIGN (Write Convenience)
            // Automatically insert the company_id when creating new records
            static::creating(function ($model) {
                if (empty($model->company_id)) {
                    $model->company_id = Auth::user()->company_id;
                }
            });
        }
    }
}
