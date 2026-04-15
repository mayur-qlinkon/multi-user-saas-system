<?php

namespace App\Http\Controllers\Auth\Customer;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class CustomerAuthController extends Controller
{
    // ════════════════════════════════════════════════════
    //  SHOW FORMS
    // ════════════════════════════════════════════════════

    public function showLoginForm(string $slug)
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        return view('storefront.auth.login', compact('company'));
    }

    public function showRegisterForm(string $slug)
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        return view('storefront.auth.register', compact('company'));
    }

    // ════════════════════════════════════════════════════
    //  LOGIN LOGIC
    // ════════════════════════════════════════════════════

    public function login(Request $request, string $slug)
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 🌟 THE ISOLATION LOCK 🌟
        // By injecting company_id and status here, Auth::attempt will ONLY
        // match users who belong to this specific storefront and are active.
        $credentials['company_id'] = $company->id;
        $credentials['status'] = 'active';

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            Log::info('[Storefront] Customer Logged In', [
                'user_id' => Auth::id(),
                'company' => $slug,
            ]);

            return redirect()->intended(route('storefront.portal.dashboard', ['slug' => $slug]));
        }

        Log::warning('[Storefront] Failed Login Attempt', [
            'email' => $request->email,
            'company' => $slug,
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records for this store.',
        ])->onlyInput('email');
    }

    // ════════════════════════════════════════════════════
    //  REGISTER & BRIDGE LOGIC
    // ════════════════════════════════════════════════════

    public function register(Request $request, string $slug)
    {
        $company = Company::where('slug', $slug)->firstOrFail();

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // Validate email uniqueness strictly within THIS company
            'email' => [
                'required', 'string', 'email', 'max:150',
                Rule::unique('users')->where(function ($query) use ($company) {
                    return $query->where('company_id', $company->id);
                }),
            ],
        ]);

        DB::beginTransaction();

        try {
            // 1. Create the Auth User
            $user = clone User::create([
                'company_id' => $company->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            // 2. THE CRM BRIDGE
            // We pass the data to our DRY helper method to keep this controller clean
            $this->linkOrCreateClientProfile($company->id, $user);

            DB::commit();

            // Auto-login the newly registered user
            Auth::login($user);

            Log::info('[Storefront] New Customer Registered', [
                'user_id' => $user->id,
                'company' => $slug,
            ]);

            return redirect()->route('storefront.portal.dashboard', ['slug' => $slug]);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('[Storefront] Customer Registration Failed', [
                'company' => $slug,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Something went wrong during registration. Please try again.')->withInput();
        }
    }

    // ════════════════════════════════════════════════════
    //  LOGOUT LOGIC
    // ════════════════════════════════════════════════════

    public function logout(Request $request, string $slug)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('storefront.login', ['slug' => $slug]);
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ════════════════════════════════════════════════════

    /**
     * Checks if a CRM Client already exists for this email/phone.
     * If yes, links the user ID. If no, creates a new CRM profile.
     */
    private function linkOrCreateClientProfile(int $companyId, User $user): void
    {
        // Try to find an existing client by email, or by phone (if phone was provided)
        $client = Client::where('company_id', $companyId)
            ->where(function ($query) use ($user) {
                $query->where('email', $user->email);
                if ($user->phone) {
                    $query->orWhere('phone', $user->phone);
                }
            })->first();

        if ($client) {
            // CRM profile exists! Link the Auth account to it.
            $client->update(['user_id' => $user->id]);

            Log::info('[Storefront] Auth Account linked to existing Client', [
                'user_id' => $user->id,
                'client_id' => $client->id,
            ]);
        } else {
            // Brand new customer. Build their CRM profile.
            Client::create([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'registration_type' => 'unregistered',
                'is_active' => true,
            ]);
        }
    }
}
