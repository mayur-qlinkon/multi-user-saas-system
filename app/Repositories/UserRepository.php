<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Store;
use App\Models\Role;

class UserRepository
{
    /*
    |--------------------------------------------------------------------------
    | EXISTING AUTHENTICATION METHODS (Safe & Untouched)
    |--------------------------------------------------------------------------
    */
    
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::with([
            'company',
            'roles.permissions',
            'stores'
        ])->where('email', $email)->first();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function updatePassword(User $user, string $hashedPassword): void
    {
        $user->update(['password' => $hashedPassword]);
    }

    public function updateStatus(User $user, string $status): void
    {
        $user->update(['status' => $status]);
    }

    protected function internalUsersQuery()
    {
        return User::query()
            ->internal()
            ->with(['roles', 'stores'])
            ->latest();
    }

    public function getFilteredUsers(bool $isOwner, ?string $requestedStoreId, ?string $activeSessionStoreId)
    {
        $query = $this->internalUsersQuery();

        if ($isOwner) {
            if ($requestedStoreId) {
                $query->whereHas('stores', function ($q) use ($requestedStoreId) {
                    $q->where('stores.id', $requestedStoreId);
                });
            }
        } else {
            if ($activeSessionStoreId) {
                $query->whereHas('stores', function ($q) use ($activeSessionStoreId) {
                    $q->where('stores.id', $activeSessionStoreId);
                });
            }
        }

        return $query->get();
    }
    /*
    |--------------------------------------------------------------------------
    | NEW TENANT / STAFF MANAGEMENT METHODS
    |--------------------------------------------------------------------------
    */

    public function getAllTenantUsers()
    {
        return $this->internalUsersQuery()->get();
    }

    public function getTenantStores()
    {
        // The Tenantable trait automatically scopes this as well
        return Store::where('is_active', true)->get();
    }

    public function getAvailableRoles()
    {
        return Role::whereNotIn('slug', ['super_admin', 'owner', 'customer'])->get();
    }

    public function updateUser(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }
}
