<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CheckStorefrontStatus
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Identify the company from the route
        $companySlug = $request->route('slug');
        $company = Company::where('slug', $companySlug)->first();

        if (!$company) {
            abort(404); 
        }

        // 2. The Bypass: Is the logged-in user the owner/staff of THIS specific company?
        // (We check if they are logged in, and if their company_id matches the storefront's company_id)
        $isCompanyStaff = Auth::check() && Auth::user()->company_id === $company->id;

        // Optional: If you want Super Admins to also bypass maintenance mode, you could do:
        // $isCompanyStaff = (Auth::check() && Auth::user()->company_id === $company->id) || (Auth::check() && Auth::user()->hasRole('super_admin'));

        // 3. Use your optimized helper function! 
        // We pass the $company->id explicitly as the 3rd parameter to be 100% safe and fast.
        $isOnline = get_setting('storefront_online', 1, $company->id);

        // 4. If the storefront is offline AND the user is NOT the owner/staff, block them.
        if ((int) $isOnline === 0 && !$isCompanyStaff) {
            return response()->view('storefront.maintenance', [
                'company' => $company
            ], 503);
        }

        // 5. If online (or if the user is the owner), let them in!
        return $next($request);
    }
}