<?php

namespace App\Services\Platform;

use App\Models\Module;
use Illuminate\Support\Str;

class ModuleService
{
    public function getAllModules()
    {
        return Module::latest()->get();
    }

    public function storeModule(array $data): Module
    {
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? false;

        return Module::create($data);
    }

    public function updateModule(Module $module, array $data): Module
    {
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? false;

        $module->update($data);

        return $module;
    }

    public function deleteModule(Module $module): bool
    {
        return $module->delete();
    }
}
