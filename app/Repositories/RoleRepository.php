<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\Permission;

class RoleRepository
{
    public function getTenantRoles()
    {
        // 1. Hide the system roles from the UI
        // 2. The Tenantable trait automatically restricts this to the current company
        return Role::with('permissions')
            ->whereNotIn('slug', ['owner', 'super_admin'])
            ->latest()
            ->get();
    }

    public function getAllPermissions()
    {
        // Group the permissions by their 'module' column so your Blade 
        // @foreach ($permissions as $module => $perms) loop works perfectly!
        return Permission::all()->groupBy('module_group');
    }

    public function createRole(array $data): Role
    {
        return Role::create($data);
    }

    public function updateRole(Role $role, array $data): bool
    {
        return $role->update($data);
    }

    public function deleteRole(Role $role): bool
    {
        return $role->delete();
    }
}