<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(private PasswordResetService $passwordResetService) {}

    // -------------------------------------------------------
    // GET /forgot-password — Show "enter your email" form
    // -------------------------------------------------------
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    // -------------------------------------------------------
    // POST /forgot-password — Send reset link
    // -------------------------------------------------------
    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $this->passwordResetService->sendResetLink($request->email);

        // Always show same message — don't leak if email exists or not
        return back()->with(
            'status',
            'If that email is registered, a reset link has been sent.'
        );
    }
}