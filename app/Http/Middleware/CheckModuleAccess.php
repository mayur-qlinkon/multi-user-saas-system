<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\CompanySubscription;
use Illuminate\Support\Facades\Auth;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, $moduleSlug)
    {
        $user = Auth::user();

        // Let super admins pass through
        if (!$user || !$user->company_id) {
            return $next($request);
        }

        // Get the company's active subscription, including the plan and its attached modules
        $subscription = CompanySubscription::with('plan.modules')
            ->where('company_id', $user->company_id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();

        // If no active subscription or plan is found, block them
        if (!$subscription || !$subscription->plan) {
            abort(403, 'No active subscription found.');
        }

        // Check if the plan's modules contain the requested module slug (e.g., 'pos')
        $hasAccess = $subscription->plan->modules->contains('slug', $moduleSlug);

        if (!$hasAccess) {
            abort(403, 'Your current plan does not include access to the ' . strtoupper($moduleSlug) . ' module. Please upgrade your plan.');
        }

        return $next($request);
    }
}