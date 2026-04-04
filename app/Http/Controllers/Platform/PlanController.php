<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use App\Services\Platform\PlanService;

class PlanController extends Controller
{
    protected PlanService $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Display the single-page CRUD view.
     */
    public function index()
    {
        $data = $this->planService->getPlansForIndex();

        // Passes both $plans and $modules so the modal dropdowns can be populated
        return view('platform.plans', $data);
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'user_limit'  => ['required', 'integer', 'min:1'],
            'store_limit' => ['required', 'integer', 'min:1'],
            'modules'     => ['nullable', 'array'],
            'modules.*'   => ['exists:modules,id']
        ]);

        $this->planService->storePlan($validated);

        return redirect()->route('platform.plans.index')
                         ->with('success', 'Plan created successfully.');
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'user_limit'  => ['required', 'integer', 'min:1'],
            'store_limit' => ['required', 'integer', 'min:1'],
            'modules'     => ['nullable', 'array'],
            'modules.*'   => ['exists:modules,id']
        ]);

        $this->planService->updatePlan($plan, $validated);

        return redirect()->route('platform.plans.index')
                         ->with('success', 'Plan updated successfully.');
    }

    /**
     * Remove the specified plan.
     */
    public function destroy(Plan $plan)
    {
        $this->planService->deletePlan($plan);

        return back()->with('success', 'Plan deleted successfully.');
    }
}