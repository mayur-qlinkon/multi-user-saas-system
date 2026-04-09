<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\Platform\PlanService;
use Illuminate\Http\Request;

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
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'string', 'in:monthly,yearly,lifetime'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'user_limit' => ['required', 'integer', 'min:1'],
            'store_limit' => ['required', 'integer', 'min:1'],
            'product_limit' => ['required', 'integer', 'min:1'],
            'employee_limit' => ['required', 'integer', 'min:1'],

            // UI Customization Fields
            'is_recommended' => ['nullable', 'boolean'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],

            // Relationships
            'modules' => ['nullable', 'array'],
            'modules.*' => ['exists:modules,id'],
        ]);

        // Convert HTML checkbox values ('on' / null) to true/false booleans for the service
        $validated['is_recommended'] = $request->has('is_recommended');
        $validated['is_active'] = $request->has('is_active');

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
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'string', 'in:monthly,yearly,lifetime'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'user_limit' => ['required', 'integer', 'min:1'],
            'store_limit' => ['required', 'integer', 'min:1'],
            'product_limit' => ['required', 'integer', 'min:1'],
            'employee_limit' => ['required', 'integer', 'min:1'],

            // UI Customization Fields
            'is_recommended' => ['nullable', 'boolean'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],

            // Relationships
            'modules' => ['nullable', 'array'],
            'modules.*' => ['exists:modules,id'],
        ]);

        // Convert HTML checkbox values ('on' / null) to true/false booleans for the service
        $validated['is_recommended'] = $request->has('is_recommended');
        $validated['is_active'] = $request->has('is_active');

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
