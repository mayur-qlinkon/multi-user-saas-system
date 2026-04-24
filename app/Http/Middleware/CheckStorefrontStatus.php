<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckStorefrontStatus
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Identify company — try custom domain first, then slug
        $company = $request->attributes->get('custom_domain_company');

        if (! $company) {
            $companySlug = $request->route('slug');
            $company = Company::where('slug', $companySlug)->first();
        }

        if (! $company) {
            abort(404);
        }

        // 2. Bypass: is the logged-in user the owner/staff of this company?
        $isCompanyStaff = Auth::check() && Auth::user()->company_id === $company->id;

        // 3. Check storefront online/offline status
        $isOnline = get_setting('storefront_online', 1, $company->id);

        if ((int) $isOnline === 0 && ! $isCompanyStaff) {
            return response()->view('storefront.maintenance', [
                'company' => $company,
            ], 503);
        }

        return $next($request);
    }
}