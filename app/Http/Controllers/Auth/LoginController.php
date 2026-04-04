<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct(private AuthService $authService) {}

    // -------------------------------------------------------
    // GET /login — Show login form
    // -------------------------------------------------------
    public function create(): View
    {
        return view('auth.login');
    }

    // -------------------------------------------------------
    // POST /login — Handle login form submission
    // -------------------------------------------------------    
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        try {

            $user = $this->authService->login(
                credentials: $request->only('email','password'),
                remember: $request->boolean('remember')
            );

            $request->clearAttempts();

            $request->session()->regenerate();

            /*
            |--------------------------------------------------------------------------
            | Store Session Context
            |--------------------------------------------------------------------------
            | Resolves the active store from pivot stores or employee fallback.
            | Heals the session automatically if stale.
            */
            active_store($user);
            
            return redirect()->intended(
                $this->redirectByRole($user)
            );
            // return redirect()
            //     ->intended(route('dashboard'))
            //     ->with('success','Welcome back!');

        } catch (\Illuminate\Validation\ValidationException $e) {

            $request->incrementAttempts();

            throw $e;

        }
    }
    private function redirectByRole($user)
    {
        // 🌟 THE GUARDRAIL: Kick out storefront customers who found the backend door
        if ($user->hasRole('customer')) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            
            // Redirect them back to the login page with an error
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Customers must log in through their specific store URL.',
            ]);
        }

        if ($user->hasRole('super_admin')) {
            return route('platform.dashboard');
        }

        if ($user->hasRole('owner')) {
            return route('admin.dashboard');
        }

        if ($user->hasRole('employee')) {
            return route('admin.hrm.employee.dashboard');
        }

        if ($user->hasRole('hr-manager')) {
            return route('admin.crm.dashboard');
        }

        return route('admin.dashboard');
    }

}