<?php

namespace App\Services\Platform;

use App\Models\Plan;
use App\Repositories\PlanRepository;
use Illuminate\Support\Str;

class PlanService
{
    protected PlanRepository $planRepository;

    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    public function getPlansForIndex(): array
    {
        return [
            'plans'   => $this->planRepository->getAllPlans(),
            'modules' => $this->planRepository->getActiveModules()
        ];
    }

    public function storePlan(array $data): Plan
    {
        // Handle business logic and defaults
        $data['slug']      = Str::slug($data['name']);
        $data['price']     = $data['price'] ?? 0;
        $data['is_active'] = true;

        $plan = $this->planRepository->createPlan($data);

        // Handle relationships
        if (!empty($data['modules'])) {
            $plan->modules()->sync($data['modules']);
        }

        return $plan;
    }

    public function updatePlan(Plan $plan, array $data): Plan
    {
        // Handle business logic and overrides
        $data['slug']  = Str::slug($data['name']);
        $data['price'] = $data['price'] ?? 0;

        $this->planRepository->updatePlan($plan, $data);

        // Sync relationships (empty array if null to detach removed modules)
        $plan->modules()->sync($data['modules'] ?? []);

        return $plan;
    }

    public function deletePlan(Plan $plan): bool
    {
        return $this->planRepository->deletePlan($plan);
    }
}