<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Register a new user and fire Registered event (sends verification email).
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $user = $this->userRepository->create($data);

        // This fires the Registered event which triggers SendEmailVerificationNotification listener
        event(new Registered($user));

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Authenticate user and create Sanctum token.
     *
     * @param  string  $email
     * @param  string  $password
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password): array
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Revoke the current access token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
