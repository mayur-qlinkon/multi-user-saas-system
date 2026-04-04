<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        // 🌟 1. Prevent Staff/Admins from breaking the Customer Portal
        // If the route strictly requires a 'customer', but the user isn't one.
        if (in_array('customer', $roles) && !$user->hasRole('customer')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            abort(403, 'Admins cannot access the customer portal. Please log in with a customer account.');
        }

        // 🌟 2. Prevent Customers from accessing the Admin Backend
        // If the user is a customer, but the route doesn't explicitly allow 'customer'.
        if ($user->hasRole('customer') && !in_array('customer', $roles)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            abort(403, 'Customers cannot access the admin dashboard.');
        }

        // 3. Super Admin Backend Bypass — check BOTH the role AND the is_super_admin flag.
        // The flag acts as a fallback if the role record was accidentally deleted.
        if ($user->is_super_admin || $user->hasRole('super_admin')) {
            return $next($request);
        }

        // 4. Standard Role Check
        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}