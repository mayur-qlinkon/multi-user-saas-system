<?php

namespace Database\Seeders\HRM;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 🌟 Ensure a default company exists (ID 1). If not, create one.
        $company = Company::first();
        if (! $company) {
            $company = Company::create([
                'name' => 'Default Company',
                'email' => 'company@example.com',
                'phone' => '1234567890',
                'address' => 'Default Address',
            ]);
            $this->command->info('Default company created with ID: '.$company->id);
        }
        $companyId = $company->id;

        // 🚨 Fetch all permissions to map slugs to IDs (eager load)
        $permissions = Permission::all()->pluck('id', 'slug');

        // ────────────────────────────────────────────────────────────────────
        // 1. DEFINE ROLES & PERMISSIONS (by slug)
        // ────────────────────────────────────────────────────────────────────
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'permissions' => $permissions->keys()->toArray(), // all permissions
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'permissions' => [
                    // Dashboard
                    'dashboard.view',
                    // Users
                    'users.view', 'users.create', 'users.edit',
                    // Expenses
                    'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.approve',
                    // Invoices
                    'invoices.view', 'invoices.create', 'invoices.edit',
                    // Purchases
                    'purchases.view', 'purchases.create', 'purchases.edit',
                    // Products
                    'products.view', 'products.create', 'products.edit',
                    // Categories
                    'categories.view', 'categories.create', 'categories.edit',
                    // Reports
                    'reports.view', 'reports.export',
                    // Stores
                    'stores.view',
                    // Others: limited
                    'pos.access',
                    'orders.view', 'orders.create', 'orders.update', 'orders.change_status',
                ],
            ],
            [
                'name' => 'Accountant',
                'slug' => 'accountant',
                'permissions' => [
                    // Finance & Billing
                    'dashboard.view',
                    'invoices.view', 'invoices.create', 'invoices.update', 'invoices.add_payment', 'invoices.download_pdf',
                    'invoice_returns.view', 'invoice_returns.create', 'invoice_returns.update',
                    'expenses.view', 'expenses.create', 'expenses.edit',
                    'purchases.view', 'purchases.create', 'purchases.update', 'purchases.add_payment',
                    'purchase_returns.view', 'purchase_returns.create', 'purchase_returns.update',
                    'reports.view', 'reports.export',
                    // Read-only on some
                    'products.view',
                    'categories.view',
                ],
            ],
            [
                'name' => 'Employee',
                'slug' => 'employee',
                'permissions' => [
                    'dashboard.view',
                    'expenses.view', 'expenses.create', 'expenses.edit', // own expenses (controlled by policy)
                    'pos.access', // if they need to use POS
                    'orders.view', 'orders.create', // maybe for sales staff
                    'products.view',
                ],
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'permissions' => [
                    'dashboard.view',
                    'reports.view',
                    'invoices.view',
                    'expenses.view',
                    'purchases.view',
                    'products.view',
                ],
            ],
        ];

        // ────────────────────────────────────────────────────────────────────
        // 2. CREATE ROLES (if not exist) & ATTACH PERMISSIONS
        // ────────────────────────────────────────────────────────────────────
        DB::transaction(function () use ($companyId, $permissions, $roles) {
            foreach ($roles as $roleData) {
                // Create or find role for this company
                $role = Role::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'slug' => $roleData['slug'],
                    ],
                    [
                        'name' => $roleData['name'],
                    ]
                );

                // Sync permissions (use IDs)
                $permissionIds = collect($roleData['permissions'])
                    ->map(fn ($slug) => $permissions[$slug] ?? null)
                    ->filter()
                    ->values()
                    ->toArray();

                $role->permissions()->sync($permissionIds);
                $this->command->info("Role '{$roleData['name']}' synced with ".count($permissionIds).' permissions.');
            }
        });

        // ────────────────────────────────────────────────────────────────────
        // 3. CREATE DEFAULT USERS AND ASSIGN ROLES
        // ────────────────────────────────────────────────────────────────────
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'phone' => '9999999991',
                'password' => 'password', // will be hashed
                'is_super_admin' => true,
                'role_slug' => null, // super admin doesn't need a role
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'phone' => '9999999992',
                'password' => 'password',
                'is_super_admin' => false,
                'role_slug' => 'admin',
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'phone' => '9999999993',
                'password' => 'password',
                'is_super_admin' => false,
                'role_slug' => 'manager',
            ],
            [
                'name' => 'Accountant User',
                'email' => 'accountant@example.com',
                'phone' => '9999999994',
                'password' => 'password',
                'is_super_admin' => false,
                'role_slug' => 'accountant',
            ],
            [
                'name' => 'Employee User',
                'email' => 'employee@example.com',
                'phone' => '9999999995',
                'password' => 'password',
                'is_super_admin' => false,
                'role_slug' => 'employee',
            ],
            [
                'name' => 'Viewer User',
                'email' => 'viewer@example.com',
                'phone' => '9999999996',
                'password' => 'password',
                'is_super_admin' => false,
                'role_slug' => 'viewer',
            ],
        ];

        DB::transaction(function () use ($companyId, $users) {
            foreach ($users as $userData) {
                // Check if user already exists by email or phone
                $user = User::where('email', $userData['email'])
                    ->orWhere('phone', $userData['phone'])
                    ->first();

                if (! $user) {
                    $user = User::create([
                        'company_id' => $companyId,
                        'is_super_admin' => $userData['is_super_admin'],
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'phone' => $userData['phone'],
                        'password' => Hash::make($userData['password']),
                        'status' => 'active',
                    ]);
                    $this->command->info("Created user: {$userData['name']} ({$userData['email']})");
                } else {
                    // Optionally update fields if needed
                    $user->update([
                        'is_super_admin' => $userData['is_super_admin'],
                        'name' => $userData['name'],
                        'company_id' => $companyId,
                        'status' => 'active',
                    ]);
                    $this->command->info("Updated existing user: {$userData['name']} ({$userData['email']})");
                }

                // Assign role if not super admin
                if (! $user->is_super_admin && $userData['role_slug']) {
                    $role = Role::where('company_id', $companyId)
                        ->where('slug', $userData['role_slug'])
                        ->first();

                    if ($role) {
                        $user->roles()->syncWithoutDetaching([$role->id]);
                        $this->command->info("Assigned role '{$role->name}' to {$user->name}");
                    } else {
                        $this->command->warn("Role '{$userData['role_slug']}' not found for user {$user->name}");
                    }
                }

                // Assign default store (store_id = 1) if available
                if (DB::table('stores')->where('id', 1)->exists()) {
                    // This assumes store_user pivot exists; we'll attach if not already attached
                    $user->stores()->syncWithoutDetaching([1]);
                }
            }
        });

        $this->command->info('✅ Role and user seeding completed.');
    }
}
