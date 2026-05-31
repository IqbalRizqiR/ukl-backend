<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

final class RegisterController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan cek email untuk verifikasi.',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
        ], 201);
    }
}
