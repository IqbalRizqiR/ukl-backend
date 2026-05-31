<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PasswordResetController extends Controller
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService,
    ) {}

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $this->passwordResetService->sendResetLink($request->input('email'));

        return response()->json([
            'message' => 'Link reset password telah dikirim ke email Anda.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->passwordResetService->resetPassword($request->only(
            'email',
            'password',
            'password_confirmation',
            'token',
        ));

        return response()->json([
            'message' => 'Password berhasil direset.',
        ]);
    }
}
