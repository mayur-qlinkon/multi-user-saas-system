<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;

class ResolveCustomDomain
{
    public function handle(Request $request, Closure $next)
    {
        $host      = $request->getHost();
        $appHost   = parse_url(config('app.url'), PHP_URL_HOST);

        // If this IS the main domain, these routes shouldn't run.
        // (The Route::domain() wrapper on web.php handles the separation,
        // but this is a safety net for any edge cases.)
        if ($host === $appHost) {
            abort(404);
        }

        // Look up the company by custom domain
        $company = Company::where('domain', $host)
                          ->where('is_active', true)
                          ->first();

        if (! $company) {
            abort(404, 'This domain is not configured in our system.');
        }

        // Make the company available globally (same pattern used in StorefrontController)
        $request->attributes->set('current_company_id', $company->id);
        $request->attributes->set('custom_domain_company', $company);

        return $next($request);
    }
}