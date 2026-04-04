<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permissionSlug)
    {
        // Use our new global helper!
        if (!has_permission($permissionSlug)) {
            // For AJAX/API requests, return JSON
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }
            
            // For standard web requests, show a 403 page
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}