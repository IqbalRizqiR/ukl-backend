<?php

declare(strict_types=1);

namespace App\Services\Auth;

use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

final class PasswordResetService
{
    /**
     * Send a password reset link to the user's email.
     *
     * @param  string  $email
     * @return string  Status message
     *
     * @throws ValidationException
     */
    public function sendResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    /**
     * Reset the user's password.
     *
     * @param  array{email: string, token: string, password: string, password_confirmation: string}  $data
     * @return string  Status message
     *
     * @throws ValidationException
     */
    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            $data,
            function ($user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                ])->save();

                $user->tokens()->delete(); // Revoke all Sanctum tokens
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }
}
