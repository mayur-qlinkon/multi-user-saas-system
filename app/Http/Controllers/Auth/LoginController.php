<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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
                credentials: $request->only('email', 'password'),
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

        } catch (ValidationException $e) {

            $request->incrementAttempts();

            throw $e;
        }
    }

    private function redirectByRole($user)
    {
        // Block storefront customers who found the backend login door.
        // Detection is via the client relationship — no role dependency.
        if ($user->client) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Customers must log in through their specific store URL.',
            ]);
        }

        if ($user->hasRole('super_admin')) {
            return route('platform.dashboard');
        }

        if ($user->hasRole('owner')) {
            return route('admin.dashboard');
        }

        if ($user->employee) {
            return route('admin.hrm.employee.dashboard');
        }

        return route('admin.dashboard');
    }
}
