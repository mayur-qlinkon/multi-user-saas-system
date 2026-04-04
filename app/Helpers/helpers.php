<?php
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

use App\Models\CompanySubscription;
use App\Models\Setting;
use App\Models\SystemSetting;
use App\Models\Company;
use App\Models\User;
use App\Models\Store;


if (!function_exists('get_setting')) {
    function get_setting($key = null, $default = null, ?int $forCompanyId = null) {
        static $settings = [];

        // 1. Identify the Company ID (The "Tenant")
        $companyId = null;

        // Context 0: Explicit company ID passed directly (e.g. from OrderService, jobs, commands)
        // This bypasses request/auth context entirely — useful for background processes
        if ($forCompanyId) {
            $companyId = $forCompanyId;
        }

        // Context A: Public Storefront (Identify by Route Slug)
        elseif (request()->route('slug')) {
            // First try the pre-set attribute (fastest — set by StorefrontController)
            $companyId = request()->attributes->get('current_company_id');

            // Fallback: resolve from slug directly if attribute not yet set
            // This handles view composers that fire before the controller sets it
            if (!$companyId) {
                $slug      = request()->route('slug');
                $companyId = Cache::remember(
                    "company_slug_to_id_{$slug}",
                    3600,
                    fn() => Company::where('slug', $slug)->value('id')
                );

                // Cache it on the request for subsequent calls this request
                if ($companyId) {
                    request()->attributes->set('current_company_id', $companyId);
                }
            }
        }
        
        // Context B: Admin Panel (Identify by Auth)
        if (!$companyId && Auth::check()) {
            $companyId = Auth::user()->company_id;
        }

        // If no company context is found (e.g., standard login page), return default
        if (!$companyId) {
            return is_null($key) ? (object) [] : $default;
        }

        // 2. Fetch and Cache (Memory + File Cache)
        if (!isset($settings[$companyId])) {
            $settings[$companyId] = Cache::remember("company_settings_{$companyId}", 86400, function () use ($companyId) {
                return Setting::where('company_id', $companyId)
                    ->pluck('value', 'key')
                    ->toArray();
            });
        }

        // 3. Return Logic
        if (is_null($key)) {
            return (object) $settings[$companyId];
        }

        return $settings[$companyId][$key] ?? $default;
    }
}
function batch_enabled()
{    
    return (bool) Setting::get('enable_batch_tracking', 0);
}



if (!function_exists('tenant_subscription')) {
    /**
     * Get the current active subscription for the logged-in user's company.
     */
    function tenant_subscription()
    {
        if (!Auth::check() || !Auth::user()->company_id) return null;

        // We cache this in the request so we don't hit the database 50 times per page load
        return cache()->remember('tenant_sub_' . Auth::user()->company_id, 60, function () {
            return CompanySubscription::with('plan.modules')
                ->where('company_id', Auth::user()->company_id)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->first();
        });
    }
}

if (!function_exists('has_module')) {
    /**
     * Check if the current company's plan includes a specific module.
     */
    function has_module($moduleSlug)
    {
        if (is_super_admin()) {
            return true;
        }
        $subscription = tenant_subscription();
        if (!$subscription || !$subscription->plan) return false;

        return $subscription->plan->modules->contains('slug', $moduleSlug);
    }

    // usage
    /*
            @if(has_module('invoice'))
                <a href="{{ route('invoices.index') }}" class="nav-item {{ $navCls(['invoices.*']) }}">
                    <span class="flex items-center gap-3">
                        <i data-lucide="file-text" class="nav-icon w-[18px] h-[18px]"></i> Invoices
                    </span>
                </a>
            @endif
    */
}

if (!function_exists('check_plan_limit')) {
    /**
     * Check if the tenant has hit their resource limit ('users' or 'stores').
     */
    function check_plan_limit($resourceType)
    {
        $subscription = tenant_subscription();
        if (!$subscription || !$subscription->plan) return false;

        if ($resourceType === 'users') {
            // Because of the Tenantable trait, this ONLY counts their company's users!
            return User::count() < $subscription->plan->user_limit;
        }

        if ($resourceType === 'stores') {
            return Store::count() < $subscription->plan->store_limit;
        }

        return false;
    }
}


if (!function_exists('has_permission')) {
    /**
     * Check if the authenticated user has a specific permission slug.
     * Automatically grants true if the user is the 'owner'.
     */
    function has_permission($permissionSlug)
    {
        $user = Auth::user();
        if (!$user) return false;

        if (is_super_admin()) {
            return true;
        }

        // 1. Check if they are the owner (owners can do everything)
        if ($user->roles->contains('slug', 'owner')) {
            return true;
        }

         // If array passed, loop through
        if (is_array($permissionSlug)) {
            foreach ($permissionSlug as $slug) {
                foreach ($user->roles as $role) {
                    if ($role->permissions->contains('slug', $slug)) {
                        return true;
                    }
                }
            }
            return false;
        }

        // 2. Check if their assigned role has the specific permission
        foreach ($user->roles as $role) {
            if ($role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        return false;
    }
}
 
// ── Check if a platform feature is enabled ──
// Reads from system_settings table — super admin controls this
// NOT the same as plan modules (CheckModuleAccess handles that)
// Usage: feature_enabled('crm')   → checks system_settings key 'feature_crm'
//        feature_enabled('pos')   → checks system_settings key 'feature_pos'
//
// In blade:   @if(feature_enabled('crm'))
// In routes:  ->middleware('feature:crm')
if (!function_exists('feature_enabled')) {
    function feature_enabled(string $feature): bool
    {
        // Default to true (enabled) if key not set — safe fallback
        // Prevents features from being accidentally blocked if key missing
        return SystemSetting::isEnabled("feature_{$feature}", true);
    }
}
 
// ── Check if platform is in maintenance mode ──
// Usage: is_maintenance_mode() → true/false
// Used by: MaintenanceMiddleware (Phase 2)
if (!function_exists('is_maintenance_mode')) {
    function is_maintenance_mode(): bool
    {
        return SystemSetting::isEnabled('maintenance_mode');
    }
}
 
// ── Get active platform announcement (if any) ──
// Returns null if no announcement or announcement is disabled
// Usage: platform_announcement() → string or null
if (!function_exists('platform_announcement')) {
    function platform_announcement(): ?string
    {
        $isActive = \App\Models\SystemSetting::isEnabled('platform_announcement_active');
        if (!$isActive) return null;
 
        $text = SystemSetting::getSetting('platform_announcement_text');
        return $text ?: null;
    }
}

// ── Get a system-level setting (platform / super admin) ──
// Wrapper around SystemSetting::getSetting() for convenience
// Usage: get_system_setting('maintenance_mode')
//        get_system_setting('maintenance_message', 'We are back soon!')
if (!function_exists('get_system_setting')) {
    function get_system_setting(string $key, mixed $default = null): mixed
    {
        return SystemSetting::getSetting($key, $default);
    }
}
 
// ── Check if current user is super admin ──
// Single place — if you ever change detection logic, change here only
// Usage: is_super_admin() → true/false
if (!function_exists('is_super_admin')) {
    /**
     * Check if the current authenticated user is a super admin.
     * Checks BOTH the is_super_admin flag (primary) and the super_admin role (fallback).
     * The flag is the source of truth — it is never removed by company/role deletion.
     */
    function is_super_admin(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();

        return (bool) ($user->is_super_admin || $user->hasRole('super_admin'));
    }
}

if (!function_exists('is_owner')) {
    /**
     * Check if the authenticated user is the owner.
     */
    function is_owner()
    {
        $user = Auth::user();
        return $user && $user->roles->contains('slug', 'owner');
    }
}

if (!function_exists('active_store')) {
    /**
     * Resolve the active store for a user with automatic session healing.
     *
     * Priority:
     * 1. Session store_id if it exists in user's assigned stores (store_user pivot)
     * 2. First assigned store from store_user pivot
     * 3. Employee's store (employees.store_id) as fallback
     *
     * @return \App\Models\Store|null
     */
    function active_store(?User $user = null): ?Store
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return null;
        }

        $stores = $user->stores;
        $sessionStoreId = session('store_id');

        // 1. Session store matches an assigned store — valid
        if ($sessionStoreId && $stores->isNotEmpty()) {
            $match = $stores->firstWhere('id', $sessionStoreId);
            if ($match) {
                return $match;
            }
        }

        // 2. Session is stale or missing — use first assigned store
        if ($stores->isNotEmpty()) {
            $fallback = $stores->first();
            session(['store_id' => $fallback->id]);

            return $fallback;
        }

        // 3. No pivot stores — fallback to employee's store
        $employee = $user->employee;
        if ($employee && $employee->store_id) {
            $employeeStore = $employee->store;
            if ($employeeStore) {
                session(['store_id' => $employeeStore->id]);

                return $employeeStore;
            }
        }

        return null;
    }
}