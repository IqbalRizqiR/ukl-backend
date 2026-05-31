<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly EmailVerificationService $emailVerificationService,
    ) {}

    public function verify(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $verified = $this->emailVerificationService->verify($user);

        return response()->json([
            'message' => $verified
                ? 'Email berhasil diverifikasi.'
                : 'Email sudah diverifikasi sebelumnya.',
        ]);
    }

    public function resend(Request $request): JsonResponse
    {
        $sent = $this->emailVerificationService->resend($request->user());

        return response()->json([
            'message' => $sent
                ? 'Link verifikasi email telah dikirim ulang.'
                : 'Email sudah diverifikasi.',
        ]);
    }
}
