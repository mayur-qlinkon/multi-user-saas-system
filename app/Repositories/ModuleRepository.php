<?php

namespace App\Repositories;

use App\Models\Module;

class ModuleRepository
{
    public function getAllModules()
    {
        return Module::latest()->get();
    }

    public function createModule(array $data): Module
    {
        return Module::create($data);
    }

    public function updateModule(Module $module, array $data): bool
    {
        return $module->update($data);
    }

    public function deleteModule(Module $module): bool
    {
        return $module->delete();
    }
}