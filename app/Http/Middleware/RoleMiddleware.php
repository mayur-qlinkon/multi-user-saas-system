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

        if (! $user) {
            abort(403, 'Unauthorized access.');
        }

        // Prevent customers (users with a linked client profile) from accessing
        // admin/staff routes. Customer portal access is handled by IsCustomer middleware.
        if ($user->client) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Customers cannot access the admin dashboard.');
        }

        // Super Admin Backend Bypass — check BOTH the role AND the is_super_admin flag.
        // The flag acts as a fallback if the role record was accidentally deleted.
        if ($user->is_super_admin || $user->hasRole('super_admin')) {
            return $next($request);
        }

        // 4. Standard Role Check
        if (! $user->hasAnyRole($roles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
