<?php

namespace App\Services\Auth;

use App\Models\User;

class TokenService
{
    public function createToken(User $user, bool $remember = false): string
    {
        // Remember me = 30 days, normal session = 24 hours
        $expiresAt = $remember
            ? now()->addDays(30)
            : now()->addHours(24);

        return $user->createToken(
            name: 'auth_token',
            expiresAt: $expiresAt
        )->plainTextToken;
    }

    /** Revoke only the token used in this request */
    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /** Revoke ALL tokens → "logout from all devices" */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}
