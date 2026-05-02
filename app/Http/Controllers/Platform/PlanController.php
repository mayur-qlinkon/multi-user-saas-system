<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Module;
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
     * Display the plans list.
     */
    public function index()
    {
        // Assuming your service returns ['plans' => $plans, 'modules' => $modules]
        $data = $this->planService->getPlansForIndex();

        // We will move this view to a 'plans' subdirectory
        return view('platform.plans.index', $data);
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        // Fetch active modules for the checkboxes
        $modules = Module::where('is_active', true)->get(); 
        
        return view('platform.plans.create', compact('modules'));
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
            'ocr_scan_limit' => ['required', 'integer', 'min:0'],

            'is_active' => ['nullable', 'boolean'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['exists:modules,id'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->planService->storePlan($validated);

        return redirect()->route('platform.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit(Plan $plan)
    {
        $plan->load('modules');
        $modules = Module::where('is_active', true)->get();

        return view('platform.plans.edit', compact('plan', 'modules'));
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
            'ocr_scan_limit' => ['required', 'integer', 'min:0'],

            'is_active' => ['nullable', 'boolean'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['exists:modules,id'],
        ]);

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