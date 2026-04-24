<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;

class RoleService
{
    public function getRoles()
    {
        return Role::with('permissions')
            ->whereNotIn('slug', ['owner', 'super_admin'])
            ->latest()
            ->get();
    }

    public function getGroupedPermissions()
    {
        return Permission::all()->groupBy('module_group');
    }

    public function storeRole(array $data): Role
    {
        $data['slug'] = Str::slug($data['name']);

        $role = Role::create($data);

        if (! empty($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        return $role;
    }

    public function updateRole(Role $role, array $data): Role
    {
        // Never mutate the slug — system logic depends on it staying stable.
        unset($data['slug']);

        $role->update($data);

        $role->permissions()->sync($data['permissions'] ?? []);

        return $role;
    }

    public function deleteRole(Role $role): bool
    {
        $role->permissions()->detach();

        return $role->delete();
    }
}
