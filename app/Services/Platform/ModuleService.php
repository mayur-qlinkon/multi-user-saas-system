<?php

namespace App\Services\Platform;

use App\Models\Module;
use App\Repositories\ModuleRepository;
use Illuminate\Support\Str;

class ModuleService
{
    protected ModuleRepository $moduleRepository;

    public function __construct(ModuleRepository $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    public function getAllModules()
    {
        return $this->moduleRepository->getAllModules();
    }

    public function storeModule(array $data): Module
    {
        // Auto-generate a clean slug from the module name
        $data['slug'] = Str::slug($data['name']);
        
        // Ensure boolean casting
        $data['is_active'] = $data['is_active'] ?? false;

        return $this->moduleRepository->createModule($data);
    }

    public function updateModule(Module $module, array $data): Module
    {
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? false;

        $this->moduleRepository->updateModule($module, $data);

        return $module;
    }

    public function deleteModule(Module $module): bool
    {
        // Because of your cascadeOnDelete in the migration, 
        // deleting this will automatically remove it from any plan_modules!
        return $this->moduleRepository->deleteModule($module);
    }
}