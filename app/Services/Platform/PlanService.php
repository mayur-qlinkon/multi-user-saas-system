<?php

namespace App\Services\Platform;

use App\Models\Plan;
use App\Models\Module;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PlanService
{
    public function getPlansForIndex(): array
    {
        return [
            // 🌟 Ordered by the new sort_order column so Super Admin controls the layout
            'plans'   => Plan::with('modules')->orderBy('sort_order', 'asc')->latest()->get(),
            'modules' => Module::where('is_active', true)->get()
        ];
    }

    public function storePlan(array $data): Plan
    {
        // 🌟 Wrapped in DB Transaction for data integrity
        return DB::transaction(function () use ($data) {
            
            // Handle business logic and defaults
            $data['slug'] = Str::slug($data['name']);
            $data['price'] = $data['price'] ?? 0;
            $data['button_text'] = $data['button_text'] ?? 'Get Started';
            $data['is_active'] = $data['is_active'] ?? true;
            $data['is_recommended'] = $data['is_recommended'] ?? false;

            // Direct Eloquent call (No Repository)
            $plan = Plan::create($data);

            // Handle relationships safely
            if (isset($data['modules']) && is_array($data['modules'])) {
                $plan->modules()->sync($data['modules']);
            }

            return $plan;
        });
    }

    public function updatePlan(Plan $plan, array $data): Plan
    {
        return DB::transaction(function () use ($plan, $data) {
            
            // Only update slug if the name was actually changed
            if (isset($data['name']) && $plan->name !== $data['name']) {
                $data['slug'] = Str::slug($data['name']);
            }
            
            // Fallback for unchecked booleans in HTML forms
            $data['is_recommended'] = $data['is_recommended'] ?? false;
            $data['is_active'] = $data['is_active'] ?? false;

            $plan->update($data);

            // Sync relationships (empty array if null to detach removed modules)
            $plan->modules()->sync($data['modules'] ?? []);

            return $plan;
        });
    }

    public function deletePlan(Plan $plan): bool
    {
        // Because we added SoftDeletes, this securely hides the plan 
        // without breaking existing company subscriptions!
        return $plan->delete(); 
    }
}