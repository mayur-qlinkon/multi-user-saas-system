<?php

namespace App\Repositories;

use App\Models\Plan;
use App\Models\Module;

class PlanRepository
{
    public function getAllPlans()
    {
        return Plan::with('modules')->latest()->get();
    }

    public function getActiveModules()
    {
        return Module::where('is_active', true)->get();
    }

    public function createPlan(array $data): Plan
    {
        return Plan::create($data);
    }

    public function updatePlan(Plan $plan, array $data): bool
    {
        return $plan->update($data);
    }

    public function deletePlan(Plan $plan): bool
    {
        return $plan->delete();
    }
}