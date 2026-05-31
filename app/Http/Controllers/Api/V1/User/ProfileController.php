<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    public function show(Request $request): UserResource
    {
        $user = $this->profileService->getProfile($request->user()->id);

        return new UserResource($user);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->updateProfile(
            $request->user()->id,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => new UserResource($user),
        ]);
    }
}
