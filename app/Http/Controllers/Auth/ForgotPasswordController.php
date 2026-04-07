<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(private readonly PasswordResetService $passwordResetService) {}

    // ──────────────────────────────────────────────────
    //  STEP 1 — Email entry
    // ──────────────────────────────────────────────────

    /** GET /forgot-password */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /** POST /forgot-password — validate email, generate OTP, redirect to verify */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'No account found with this email address.',
        ]);

        try {
            $this->passwordResetService->generateAndSendOtp($request->email);
        } catch (\Throwable $e) {
            // OTP is still cached even if the mail send fails.
            // Log the error but don't surface it — prevents email enumeration.
            Log::error('[ForgotPassword] OTP send error: '.$e->getMessage());
        }

        // Store email in session (not URL) to prevent parameter tampering.
        session(['pwd_reset_email' => strtolower($request->email)]);

        return redirect()
            ->route('password.verify')
            ->with('status', 'A 6-digit OTP has been sent to your email address.');
    }

    // ──────────────────────────────────────────────────
    //  STEP 2 — OTP + new password
    // ──────────────────────────────────────────────────

    /** GET /forgot-password/verify */
    public function showVerify(Request $request): View|RedirectResponse
    {
        if (! session('pwd_reset_email')) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Please enter your email to start the reset process.']);
        }

        return view('auth.verify-otp', [
            'email' => session('pwd_reset_email'),
        ]);
    }

    /** POST /forgot-password/verify — validate OTP + update password */
    public function storeVerify(Request $request): RedirectResponse
    {
        $email = session('pwd_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $otpLength = (int) get_system_setting('otp_length', 6);

        $request->validate([
            'otp' => ['required', 'digits:'.$otpLength],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'otp.required' => 'Please enter the OTP sent to your email.',
            'otp.digits' => "OTP must be exactly {$otpLength} digits.",
        ]);

        try {
            $this->passwordResetService->verifyOtpAndReset($email, $request->otp, $request->password);
        } catch (ValidationException $e) {
            throw $e;
        }

        session()->forget('pwd_reset_email');

        return redirect()
            ->route('login')
            ->with('success', 'Password reset successfully. Please log in.');
    }
}
