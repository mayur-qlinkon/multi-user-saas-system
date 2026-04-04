<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserService
{
    protected UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function getFilteredUsers(bool $isOwner, ?string $requestedStoreId, ?string $activeSessionStoreId)
    {
        return $this->userRepo->getFilteredUsers($isOwner, $requestedStoreId, $activeSessionStoreId);
    }
    public function getTenantStores()
    {
        return $this->userRepo->getTenantStores();
    }

    public function getAvailableRoles()
    {
        return $this->userRepo->getAvailableRoles();
    }
   

    public function storeUser(array $data): User
    {
        // 1. Hash the password
        $data['password'] = Hash::make($data['password']);

        // 2. Create the user (Tenantable trait injects the company_id automatically!)
        $user = $this->userRepo->create($data);

        // 3. Attach the user to the selected physical store
        $user->stores()->attach($data['store_id']);

        // 4. Attach the selected role
        $user->roles()->attach($data['role_id']);

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        // 1. Handle optional password update
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // 2. Update core user data
        $this->userRepo->updateUser($user, $data);

        // 3. Sync pivot tables (Sync removes old relations and adds the new one)
        $user->stores()->sync([$data['store_id']]);
        $user->roles()->sync([$data['role_id']]);

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        // 🌟 GUARDRAIL: Never allow deletion of an Owner account
        if ($user->roles->contains('slug', 'owner')) {
            throw new \Exception('Security Violation: Owner accounts cannot be deleted.');
        }
        
        $user->stores()->detach();
        $user->roles()->detach();

        return $this->userRepo->deleteUser($user);
    }
}