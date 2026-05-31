<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Services\Admin\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class UserManagementController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagementService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $users = $this->userManagementService->list($request->all());

        return UserResource::collection($users);
    }

    public function ban(string $userId): JsonResponse
    {
        $this->userManagementService->ban($userId);

        return response()->json([
            'message' => 'Pengguna berhasil diblokir.',
        ]);
    }

    public function unban(string $userId): JsonResponse
    {
        $this->userManagementService->unban($userId);

        return response()->json([
            'message' => 'Pengguna berhasil dibuka blokirnya.',
        ]);
    }
}
