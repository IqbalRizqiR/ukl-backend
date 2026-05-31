<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class ProfileService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Get user profile by ID.
     *
     * @param  string  $userId
     * @return User
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getProfile(string $userId): User
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Pengguna tidak ditemukan.');
        }

        return $user;
    }

    /**
     * Update user profile fields.
     *
     * @param  string  $userId
     * @param  array<string, mixed>  $data
     * @return User
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateProfile(string $userId, array $data): User
    {
        $user = $this->userRepository->update($userId, $data);

        if (! $user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Pengguna tidak ditemukan.');
        }

        return $user;
    }

    /**
     * Activate user as a seller.
     *
     * @param  string  $userId
     * @return User
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function activateSeller(string $userId): User
    {
        $user = $this->userRepository->update($userId, [
            'is_seller' => true,
        ]);

        if (! $user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Pengguna tidak ditemukan.');
        }

        return $user;
    }

    /**
     * Upload KTP image for seller verification.
     *
     * @param  string  $userId
     * @param  UploadedFile  $file
     * @return User
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function uploadKtp(string $userId, UploadedFile $file): User
    {
        $path = $file->store("ktp/{$userId}", 'public');

        $user = $this->userRepository->update($userId, [
            'ktp_image_url' => $path,
        ]);

        if (! $user) {
            Storage::disk('public')->delete($path);
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Pengguna tidak ditemukan.');
        }

        return $user;
    }
}
