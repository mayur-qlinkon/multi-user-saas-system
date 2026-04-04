<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Store;

class EnsureStoreExists
{
    public function handle(Request $request, Closure $next)
    {
        // Remember Phase 1? Because of the Tenantable trait, Store::count() 
        // will ONLY count the stores belonging to the logged-in user's company!
        if (Store::count() === 0) {
            
            // If they are already on the onboarding page, let them through to prevent an infinite redirect loop
            if ($request->routeIs('admin.onboarding.*')) {
                return $next($request);
            }

            // Otherwise, redirect them to the onboarding setup
            return redirect()->route('admin.onboarding.index')
                             ->with('warning', 'Welcome! Please set up your first store to get started.');
        }

        // If they have a store but are trying to access onboarding, send them to the dashboard
        if ($request->routeIs('admin.onboarding.*')) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}