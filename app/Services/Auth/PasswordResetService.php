<?php

namespace App\Services\Auth;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    /** Max failed OTP attempts before the code is invalidated. */
    private const MAX_ATTEMPTS = 3;

    // ════════════════════════════════════════════════════
    //  OTP-BASED FLOW (primary)
    // ════════════════════════════════════════════════════

    /**
     * Generate an OTP, cache it, and send it to the user's email.
     * Silently does nothing if SMTP is not configured — the caller
     * still redirects to the verify page so email enumeration is prevented.
     */
    public function generateAndSendOtp(string $email): void
    {
        $expiryMinutes = (int) (get_system_setting('password_reset_expiry_minutes', 60));
        $otpLength = max(4, min(8, (int) (get_system_setting('otp_length', 6))));
        $appName = get_system_setting('app_name') ?: config('app.name');

        // Cryptographically random numeric OTP, zero-padded to the required length.
        $otp = str_pad(
            (string) random_int(0, (10 ** $otpLength) - 1),
            $otpLength,
            '0',
            STR_PAD_LEFT
        );

        // Cache OTP and reset attempt counter.
        Cache::put($this->otpKey($email), $otp, now()->addMinutes($expiryMinutes));
        Cache::forget($this->attemptsKey($email));

        $this->sendOtpEmail($email, $otp, $appName, $expiryMinutes);

        Log::info('[PasswordReset] OTP generated', ['email' => $email]);
    }

    /**
     * Validate the submitted OTP and reset the user's password.
     *
     * @throws ValidationException on invalid / expired OTP or too many attempts
     */
    public function verifyOtpAndReset(string $email, string $otp, string $password): void
    {
        $attemptsKey = $this->attemptsKey($email);
        $attempts = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::forget($this->otpKey($email));
            Cache::forget($attemptsKey);

            throw ValidationException::withMessages([
                'otp' => 'Too many incorrect attempts. Please request a new OTP.',
            ]);
        }

        $cached = Cache::get($this->otpKey($email));

        if (! $cached || ! hash_equals((string) $cached, (string) $otp)) {
            Cache::increment($attemptsKey);
            $remaining = self::MAX_ATTEMPTS - $attempts - 1;

            throw ValidationException::withMessages([
                'otp' => $remaining > 0
                    ? "Invalid or expired OTP. {$remaining} attempt(s) remaining."
                    : 'Invalid OTP. No attempts remaining — please request a new one.',
            ]);
        }

        // OTP is valid — update password.
        $user = User::where('email', strtolower($email))->firstOrFail();

        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ])->save();

        // Revoke all API tokens on password change.
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        event(new PasswordReset($user));

        // Invalidate OTP so it cannot be reused.
        Cache::forget($this->otpKey($email));
        Cache::forget($attemptsKey);

        Log::info('[PasswordReset] Password reset via OTP', ['email' => $email]);
    }

    // ════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ════════════════════════════════════════════════════

    /** Cache key for the OTP value. */
    private function otpKey(string $email): string
    {
        return 'pwd_otp_reset_'.md5(strtolower($email));
    }

    /** Cache key for the attempt counter. */
    private function attemptsKey(string $email): string
    {
        return 'pwd_otp_attempts_'.md5(strtolower($email));
    }

    /**
     * Apply runtime SMTP config and send the OTP Mailable.
     * Logs an error and returns silently if SMTP is not configured.
     */
    private function sendOtpEmail(string $email, string $otp, string $appName, int $expiryMinutes): void
    {
        $host = get_system_setting('mail_host');
        $username = get_system_setting('mail_username');
        $fromEmail = get_system_setting('mail_from_email');

        if (empty($host) || empty($username) || empty($fromEmail)) {
            Log::error('[PasswordReset] SMTP not configured — OTP email not sent', [
                'email' => $email,
                'missing' => array_filter(compact('host', 'username', 'fromEmail'), 'empty'),
            ]);

            return;
        }

        Config::set('mail.mailers.system_smtp', [
            'transport' => get_system_setting('mail_driver', 'smtp') ?: 'smtp',
            'host' => $host,
            'port' => (int) (get_system_setting('mail_port') ?: 587),
            'username' => $username,
            'password' => get_system_setting('mail_password'),
            'encryption' => get_system_setting('mail_encryption', 'tls') ?: null,
        ]);

        Config::set('mail.from.address', $fromEmail);
        Config::set('mail.from.name', get_system_setting('mail_from_name') ?: $appName);

        Mail::mailer('system_smtp')
            ->to($email)
            ->send(new OtpMail($otp, $appName, $expiryMinutes));
    }
}
