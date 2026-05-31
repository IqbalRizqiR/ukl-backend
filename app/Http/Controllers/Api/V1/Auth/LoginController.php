<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->authService->login(
            $validated['email'],
            $validated['password'],
        );

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'user' => $result['user'],
                'token' => $result['token'],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
