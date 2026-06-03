<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

final class SellerVerificationService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Get all sellers pending verification.
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->where('is_seller', 'true')
            ->where('is_seller_verified', 'false')
            ->whereNotNull('ktp_image_url')
            ->orderBy('created_at')
            ->paginate($perPage);
    }

    /**
     * Verify a seller's KTP/identity.
     *
     * @param  string  $userId
     * @return User
     *
     * @throws RuntimeException
     */
    public function verify(string $userId): User
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new RuntimeException('Pengguna tidak ditemukan.');
        }

        if (! $user->is_seller) {
            throw new RuntimeException('Pengguna ini bukan penjual.');
        }

        if ($user->is_seller_verified) {
            throw new RuntimeException('Penjual sudah diverifikasi.');
        }

        if (! $user->ktp_image_url) {
            throw new RuntimeException('Penjual belum mengunggah foto KTP.');
        }

        $this->userRepository->update($userId, [
            'is_seller_verified' => 'true',
        ]);

        return $user->refresh();
    }
}
