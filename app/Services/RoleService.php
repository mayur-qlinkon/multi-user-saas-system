<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Support\Str;

class RoleService
{
    protected RoleRepository $roleRepo;

    public function __construct(RoleRepository $roleRepo)
    {
        $this->roleRepo = $roleRepo;
    }

    public function getRoles()
    {
        return $this->roleRepo->getTenantRoles();
    }

    public function getGroupedPermissions()
    {
        return $this->roleRepo->getAllPermissions();
    }

   public function storeRole(array $data): Role
    {
        // 1. Generate a clean, standard slug ONLY upon creation.
        // Because of your Tenantable trait, this is safely scoped to the company.
        $data['slug'] = Str::slug($data['name']);

        $role = $this->roleRepo->createRole($data);

        // Attach the checked permissions
        if (!empty($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        return $role;
    }

    public function updateRole(Role $role, array $data): Role
    {
        // 🛡️ CRITICAL FIX: We completely remove the slug from the update data.
        // Even if they change the display name (e.g. "Employee" -> "Staff"), 
        // the underlying system identifier (slug) stays 'employee' so code logic doesn't break!
        unset($data['slug']);

        $this->roleRepo->updateRole($role, $data);

        // Sync updates the pivot table (adds new checks, removes unchecked)
        $role->permissions()->sync($data['permissions'] ?? []);

        return $role;
    }

    public function deleteRole(Role $role): bool
    {
        // Detach all permissions before deleting
        $role->permissions()->detach();
        return $this->roleRepo->deleteRole($role);
    }
}