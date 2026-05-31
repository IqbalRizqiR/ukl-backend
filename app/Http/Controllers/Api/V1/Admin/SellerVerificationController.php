<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Services\Admin\SellerVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SellerVerificationController extends Controller
{
    public function __construct(
        private readonly SellerVerificationService $sellerVerificationService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $sellers = $this->sellerVerificationService->list();

        return UserResource::collection($sellers);
    }

    public function verify(string $userId): JsonResponse
    {
        $user = $this->sellerVerificationService->verify($userId);

        return response()->json([
            'message' => 'Penjual berhasil diverifikasi.',
            'data' => new UserResource($user),
        ]);
    }
}
