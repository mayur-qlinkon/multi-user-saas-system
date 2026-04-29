<?php

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Hrm\Employee;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Store;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

if (! function_exists('get_setting')) {
    function get_setting($key = null, $default = null, ?int $forCompanyId = null)
    {
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
            if (! $companyId) {
                $slug = request()->route('slug');
                $companyId = Cache::remember(
                    "company_slug_to_id_{$slug}",
                    3600,
                    fn () => Company::where('slug', $slug)->value('id')
                );

                // Cache it on the request for subsequent calls this request
                if ($companyId) {
                    request()->attributes->set('current_company_id', $companyId);
                }
            }
        }

        // Context B: Admin Panel (Identify by Auth)
        if (! $companyId && Auth::check()) {
            $companyId = Auth::user()->company_id;
        }

        // If no company context is found (e.g., standard login page), return default
        if (! $companyId) {
            return is_null($key) ? (object) [] : $default;
        }

        // 2. Fetch and Cache (Memory + File Cache)
        if (! isset($settings[$companyId])) {
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
if (! function_exists('batch_enabled')) {
    function batch_enabled()
    {
        return (bool) Setting::get('enable_batch_tracking', 0);
    }
}

if (! function_exists('tenant_subscription')) {
    /**
     * Get the current active subscription for the logged-in user's company.
     *
     * Two-layer cache:
     *   1. Static PHP array — zero overhead after first call within a request (eliminates
     *      the cache driver round-trip on the 20+ has_module() calls per page).
     *   2. Laravel cache driver — persists across requests so the DB is rarely hit.
     */
    function tenant_subscription()
    {
        if (! Auth::check() || ! Auth::user()->company_id) {
            return null;
        }

        static $memo = [];

        $companyId = Auth::user()->company_id;

        if (! array_key_exists($companyId, $memo)) {
            $memo[$companyId] = cache()->remember('tenant_sub_'.$companyId, 60, function () use ($companyId) {
                return CompanySubscription::with('plan.modules')
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })->first();
            });
        }

        return $memo[$companyId];
    }
}

if (! function_exists('has_module')) {
    /**
     * Check if the current company's plan includes a specific module.
     */
    function has_module($moduleSlug)
    {
        if (is_super_admin()) {
            return true;
        }
        $subscription = tenant_subscription();
        if (! $subscription || ! $subscription->plan) {
            return false;
        }

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

if (! function_exists('check_plan_limit')) {
    /**
     * Check if the tenant has hit their resource limit ('users', 'stores', 'products', or 'employees').
     */
    function check_plan_limit($resourceType)
    {
        $subscription = tenant_subscription();
        if (! $subscription || ! $subscription->plan) {
            return false;
        }

        if ($resourceType === 'users') {
            // internal() excludes customer-role and client-linked users — staff only.
            return User::internal()->count() < $subscription->plan->user_limit;
        }

        if ($resourceType === 'stores') {
            return Store::count() < $subscription->plan->store_limit;
        }

        if ($resourceType === 'products') {
            return Product::count() < $subscription->plan->product_limit;
        }

        if ($resourceType === 'employees') {
            return Employee::count() < $subscription->plan->employee_limit;
        }

        return false;
    }
}

if (! function_exists('has_permission')) {
    /**
     * Check if the authenticated user has a specific permission slug.
     * Automatically grants true if the user is the 'owner'.
     *
     * Static cache eliminates redundant in-memory lookups across the 54+ sidebar calls
     * on every page load. The cache key combines user ID + permission(s) so impersonation
     * or multi-user CLI contexts remain correct.
     */
    function has_permission($permissionSlug)
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        static $memo = [];

        $cacheKey = $user->id.'|'.(is_array($permissionSlug) ? implode(',', $permissionSlug) : $permissionSlug);

        if (array_key_exists($cacheKey, $memo)) {
            return $memo[$cacheKey];
        }

        if (is_super_admin()) {
            return $memo[$cacheKey] = true;
        }

        // 1. Check if they are the owner (owners can do everything)
        if ($user->roles->contains('slug', 'owner')) {
            return $memo[$cacheKey] = true;
        }

        // If array passed, loop through
        if (is_array($permissionSlug)) {
            foreach ($permissionSlug as $slug) {
                foreach ($user->roles as $role) {
                    if ($role->permissions->contains('slug', $slug)) {
                        return $memo[$cacheKey] = true;
                    }
                }
            }

            return $memo[$cacheKey] = false;
        }

        // 2. Check if their assigned role has the specific permission
        foreach ($user->roles as $role) {
            if ($role->permissions->contains('slug', $permissionSlug)) {
                return $memo[$cacheKey] = true;
            }
        }

        return $memo[$cacheKey] = false;
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
if (! function_exists('feature_enabled')) {
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
if (! function_exists('is_maintenance_mode')) {
    function is_maintenance_mode(): bool
    {
        return SystemSetting::isEnabled('maintenance_mode');
    }
}

// ── Get active platform announcement (if any) ──
// Returns null if no announcement or announcement is disabled
// Usage: platform_announcement() → string or null
if (! function_exists('platform_announcement')) {
    function platform_announcement(): ?string
    {
        $isActive = SystemSetting::isEnabled('platform_announcement_active');
        if (! $isActive) {
            return null;
        }

        $text = SystemSetting::getSetting('platform_announcement_text');

        return $text ?: null;
    }
}

// ── Get a system-level setting (platform / super admin) ──
// Wrapper around SystemSetting::getSetting() for convenience
// Usage: get_system_setting('maintenance_mode')
//        get_system_setting('maintenance_message', 'We are back soon!')
if (! function_exists('get_system_setting')) {
    function get_system_setting(string $key, mixed $default = null): mixed
    {
        return SystemSetting::getSetting($key, $default);
    }
}

// ── Check if current user is super admin ──
// Single place — if you ever change detection logic, change here only
// Usage: is_super_admin() → true/false
if (! function_exists('is_super_admin')) {
    /**
     * Check if the current authenticated user is a super admin.
     * Checks BOTH the is_super_admin flag (primary) and the super_admin role (fallback).
     * The flag is the source of truth — it is never removed by company/role deletion.
     *
     * Result is cached in a static array for the lifetime of the request so that the
     * 70+ blade calls to has_module() / has_permission() — each of which invokes this
     * function — do not each trigger a separate DB query via hasRole()->exists().
     */
    function is_super_admin(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        static $cache = [];
        $userId = Auth::id();

        if (! array_key_exists($userId, $cache)) {
            $user = Auth::user();

            // The is_super_admin column is loaded with the user model (no extra query).
            // Only fall back to the role query when the flag is false — covers legacy
            // accounts that were promoted via role before the flag column existed.
            if ($user->is_super_admin) {
                $cache[$userId] = true;
            } elseif ($user->relationLoaded('roles')) {
                // Roles already eager-loaded (by the admin view composer) — no DB hit.
                $cache[$userId] = $user->roles->contains('slug', 'super_admin');
            } else {
                // One-time DB query per request; result is memoised above for all future calls.
                $cache[$userId] = (bool) $user->hasRole('super_admin');
            }
        }

        return $cache[$userId];
    }
}

if (! function_exists('is_owner')) {
    /**
     * Check if the authenticated user is the owner.
     */
    function is_owner()
    {
        $user = Auth::user();

        return $user && $user->roles->contains('slug', 'owner');
    }
}

if (! function_exists('active_store')) {
    /**
     * Resolve the active store for a user with automatic session healing.
     *
     * Priority:
     * 1. Session store_id if it exists in user's assigned stores (store_user pivot)
     * 2. First assigned store from store_user pivot
     * 3. Employee's store (employees.store_id) as fallback
     */
    function active_store(?User $user = null): ?Store
    {
        $user = $user ?? Auth::user();

        if (! $user) {
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

// ─────────────────────────────────────────────────────────────────────────
//  STORE SCOPE HELPERS  (added for multi-store access control)
// ─────────────────────────────────────────────────────────────────────────

if (! function_exists('auth_store_ids')) {
    /**
     * Returns the store IDs the current user may access.
     * null  → no restriction (owner / super admin sees all company stores)
     * array → only these store IDs (regular users see their assigned stores)
     *
     * Usage in controller index():
     *   $storeIds = auth_store_ids();
     *   $query->when($storeIds, fn($q) => $q->whereIn('store_id', $storeIds));
     */
    function auth_store_ids(): ?array
    {
        if (is_super_admin()) {
            return null;
        }

        $user = Auth::user();
        if (! $user) {
            return [];
        }

        // Owners see every store in their company (Tenantable already scopes company)
        if ($user->roles->contains('slug', 'owner')) {
            return null;
        }

        // Cache per-request to avoid repeated DB hits (helpers called many times per page)
        static $cache = [];
        $userId = $user->id;

        if (! array_key_exists($userId, $cache)) {
            $cache[$userId] = $user->stores()->pluck('stores.id')->toArray();
        }

        return $cache[$userId];
    }
}

if (! function_exists('auth_stores')) {
    /**
     * Returns an Eloquent query builder for the stores the current user
     * is allowed to see — use this for ALL dropdown/select queries.
     *
     * Usage:
     *   $stores = auth_stores()->get();
     *   $stores = auth_stores()->orderBy('name')->get();
     */
    function auth_stores(): \Illuminate\Database\Eloquent\Builder
    {
        $storeIds = auth_store_ids();

        $query = \App\Models\Store::where('is_active', true);

        if ($storeIds !== null) {
            // Non-owner: restrict to assigned stores only.
            // [0] guard prevents an accidental "no stores = see everything" leak.
            $query->whereIn('id', empty($storeIds) ? [0] : $storeIds);
        }

        return $query;
    }
}
