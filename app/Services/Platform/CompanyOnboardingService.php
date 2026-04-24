<?php

namespace App\Services\Platform;

use App\Models\Company;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyOnboardingService
{
    /**
     * Handle the complex onboarding of a new Company + Owner + Default Store.
     */
    public function onboard(array $data): Company
    {
        return DB::transaction(function () use ($data) {

            // 1. Create the Company
            $slug = ! empty($data['slug'])
                ? $data['slug']
                : Str::slug($data['company_name']).'-'.Str::lower(Str::random(5));

            $company = Company::create([
                'name' => $data['company_name'],
                'slug' => $slug,
                'email' => $data['company_email'],
                'phone' => $data['phone'] ?? null,
                'city' => $data['city'] ?? null,
                'state_id' => $data['state_id'],
                'gst_number' => $data['gst_number'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // 2. Create the Owner (User)
            $owner = User::create([
                'company_id' => $company->id,
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'password' => Hash::make($data['owner_password']),
                'state_id' => $data['state_id'],
                'status' => 'active',
            ]);

            // 3. Assign the 'Owner' Role (scoped to this company)
            $ownerRole = Role::firstOrCreate(
                ['company_id' => $company->id, 'slug' => 'owner'],
                ['company_id' => $company->id, 'name' => 'Owner']
            );
            $owner->roles()->syncWithoutDetaching([$ownerRole->id]);

            // 4. Create the Default Store (Crucial for the ERP to function)
            $store = Store::create([
                'company_id' => $company->id,
                'name' => $data['company_name'].' - Main Branch',
                'slug' => Str::slug($data['company_name'].'-main'),
                'state_id' => $data['state_id'],
                'is_active' => true,
            ]);

            // 5. Attach Store to User so they can log in without layout crashing
            $owner->stores()->attach($store->id);

            return $company;
        });
    }

    /**
     * Update an existing company.
     */
    public function update(Company $company, array $data): Company
    {
        $company->update([
            'name' => $data['company_name'],
            'slug' => $data['slug'],
            'email' => $data['company_email'],
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
            'state_id' => $data['state_id'],
            'gst_number' => $data['gst_number'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $company;
    }

    /**
     * Soft delete a company and its non-super-admin users and stores.
     * Super admin accounts are NEVER touched regardless of which company they belong to.
     */
    public function delete(Company $company): void
    {
        // Final safety net — refuse if any super admin belongs to this company.
        $hasSuperAdmin = $company->users()
            ->where(function ($q) {
                $q->where('is_super_admin', true)
                    ->orWhereHas('roles', fn ($r) => $r->where('slug', 'super_admin'));
            })
            ->exists();

        if ($hasSuperAdmin) {
            throw new \RuntimeException(
                "Cannot delete \"{$company->name}\": it contains a super admin account."
            );
        }

        DB::transaction(function () use ($company) {
            // Soft-delete only regular (non-super-admin) users.
            $company->users()
                ->where('is_super_admin', false)
                ->whereDoesntHave('roles', fn ($r) => $r->where('slug', 'super_admin'))
                ->delete();

            $company->stores()->delete();
            $company->delete();
        });
    }
}
