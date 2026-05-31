<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;

final class EmailVerificationService
{
    /**
     * Verify the user's email address.
     *
     * @param  User  $user
     * @return bool  Whether the email was just now verified (vs. already verified)
     */
    public function verify(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        return true;
    }

    /**
     * Resend the email verification notification.
     *
     * @param  User  $user
     * @return bool  False if already verified, true if notification sent
     */
    public function resend(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->sendEmailVerificationNotification();

        return true;
    }
}
