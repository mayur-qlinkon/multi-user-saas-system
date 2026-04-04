<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    public function __construct(private UserRepository $userRepo) {}

    // -------------------------------------------------------
    // STEP 1 — Send reset link to email
    // -------------------------------------------------------
    public function sendResetLink(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);

        // Password::RESET_LINK_SENT = 'passwords.sent'
        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }
    }

    // -------------------------------------------------------
    // STEP 2 — Validate token + update password
    // -------------------------------------------------------
    public function reset(array $data): void
    {
        $status = Password::reset(
            $data, // must have: email, password, password_confirmation, token
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Security: revoke all active tokens on password change
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }
    }
}