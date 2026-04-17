<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Hrm\Announcement;
use App\Models\User;
use App\Policies\Hrm\AnnouncementPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Announcement::class, AnnouncementPolicy::class);

        View::composer('layouts.storefront', function ($view) {

            // ── Resolve company from URL slug — no auth needed ──
            // Works for public storefront where owner is not logged in
            $slug = request()->route('slug');
            $company = null;

            if ($slug) {
                $company = Company::where('slug', $slug)
                    ->first(); // soft fail — no 404 here, blade handles null
            }

            // ── Fallback: if somehow still null (edge case) ──
            if (! $company && Auth::check()) {
                $company = Auth::user()->company;
            }

            // ── Nav categories — safe even if company is null ──
            $navCategories = $company
                ? Category::where('company_id', $company->id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->limit(10)
                    ->get()
                : collect();

            $view->with(compact('company', 'navCategories'));
        });

        // ── 2. ADMIN VIEW LOGIC ──
        View::composer('layouts.admin', function ($view) {
            if (Auth::check()) {
                /** @var User $user */
                $user = Auth::user();

                // Eager-load the relations that the admin layout accesses repeatedly.
                //
                // roles.permissions — consumed by every has_permission() and has_module()
                //   call in the sidebar (51 + 20 blade calls). Without eager-loading, the
                //   first has_permission() fires a roles query and N permission queries.
                //   More critically, is_super_admin() calls hasRole()->exists() on each
                //   invocation; with roles pre-loaded here, is_super_admin() detects
                //   relationLoaded('roles') and skips the DB entirely.
                //
                // stores — accessed by active_store() in the header and by the store
                //   switcher dropdown.
                //
                // employee — accessed in the HRM sidebar section and by active_store()
                //   fallback path.
                $user->loadMissing(['roles.permissions', 'stores', 'employee']);

                // Fetch notifications once and derive the count from the loaded
                // collection — avoids the second COUNT(*) query that firing
                // unreadNotifications()->count() would produce.
                $unreadNotifications = $user->unreadNotificationsLimit()->get();

                $view->with([
                    'unreadNotifications' => $unreadNotifications,
                    'unreadCount' => $unreadNotifications->count(),
                ]);
            }
        });
    }
}
