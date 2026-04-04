<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function __construct(private PasswordResetService $passwordResetService) {}

    // -------------------------------------------------------
    // GET /reset-password/{token} — Show new password form
    // -------------------------------------------------------
    public function create(string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'), // pre-fill email from URL query param
        ]);
    }

    // -------------------------------------------------------
    // POST /reset-password — Save new password
    // -------------------------------------------------------
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $this->passwordResetService->reset($request->only(
            'token', 'email', 'password', 'password_confirmation'
        ));

        return redirect()
            ->route('login')
            ->with('success', 'Password reset successful. Please log in.');
    }
}