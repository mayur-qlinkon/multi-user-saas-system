<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Ensures the authenticated user has a linked Client (customer) profile.
 * Replaces the old role:customer check — no Role model dependency.
 */
class IsCustomer
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('storefront.login', [
                'slug' => $request->route('slug'),
            ]);
        }

        // Staff/admins will not have a client profile — block them.
        if (! $user->client) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'This portal is for customers only. Please log in with a customer account.');
        }

        return $next($request);
    }
}
