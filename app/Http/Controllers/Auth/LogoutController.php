<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(private AuthService $authService) {}

    // -------------------------------------------------------
    // POST /logout
    // -------------------------------------------------------
    public function destroy(Request $request): RedirectResponse
    {
        $this->authService->logout();

        // Invalidate session + regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'You have been logged out.');
    }
}
