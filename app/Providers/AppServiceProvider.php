<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

use App\Models\Hrm\Announcement;
use App\Policies\Hrm\AnnouncementPolicy;

use App\Models\Category;
use App\Models\Company;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
       
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Announcement::class, AnnouncementPolicy::class);

        View::composer('layouts.storefront', function ($view) {

            // ── Resolve company from URL slug — no auth needed ──
            // Works for public storefront where owner is not logged in
            $slug    = request()->route('slug');
            $company = null;

            if ($slug) {
                $company = Company::where('slug', $slug)
                    ->first(); // soft fail — no 404 here, blade handles null
            }

            // ── Fallback: if somehow still null (edge case) ──
            if (!$company && Auth::check()) {
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
        
        // ── 2. ADMIN VIEW LOGIC (New & IDE Friendly) ──
        View::composer('layouts.admin', function ($view) {
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user(); // This line tells VS Code to look at your User Model
                
                $view->with([
                    'unreadNotifications' => $user->unreadNotificationsLimit()->get(),
                    'unreadCount'         => $user->unreadNotifications()->count()
                ]);
            }
        });
    }
}
