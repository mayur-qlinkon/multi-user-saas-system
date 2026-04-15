<?php

namespace App\Http\Middleware;

use App\Models\CompanySubscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // If it's a super admin (no company_id) or guest, let other middlewares handle it
        if (! $user || ! $user->company_id) {
            return $next($request);
        }

        // Check if the user's company has a valid, active subscription
        $hasActiveSubscription = CompanySubscription::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if (! $hasActiveSubscription) {
            // Redirect to a specific "billing required" page
            // Make sure to create this route later!
            return redirect()->route('subscriptions.index')
                ->with('error', 'Your subscription has expired. Please renew to continue.');
        }

        return $next($request);
    }
}
