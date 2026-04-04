<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreModuleRequest;
use App\Http\Requests\Platform\UpdateModuleRequest;
use App\Models\Module;
use App\Services\Platform\ModuleService;

class ModuleController extends Controller
{
    protected ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Display the single-page CRUD view.
     */
    public function index()
    {
        $modules = $this->moduleService->getAllModules();

        return view('platform.modules', compact('modules'));
    }

    /**
     * Store a newly created module.
     */
    public function store(StoreModuleRequest $request)
    {
        $this->moduleService->storeModule($request->validated());

        return redirect()->route('platform.modules.index')
                         ->with('success', 'Module created successfully.');
    }

    /**
     * Update the specified module.
     */
    public function update(UpdateModuleRequest $request, Module $module)
    {
        $this->moduleService->updateModule($module, $request->validated());

        return redirect()->route('platform.modules.index')
                         ->with('success', 'Module updated successfully.');
    }

    /**
     * Remove the specified module.
     */
    public function destroy(Module $module)
    {
        $this->moduleService->deleteModule($module);

        return back()->with('success', 'Module deleted successfully.');
    }
}