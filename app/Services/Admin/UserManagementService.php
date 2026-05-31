<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

final class UserManagementService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Get paginated list of all users.
     *
     * @param  array{search?: string, is_seller?: bool, is_admin?: bool}  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()->orderByDesc('created_at');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if (isset($filters['is_seller'])) {
            $query->where('is_seller', $filters['is_seller']);
        }

        if (isset($filters['is_admin'])) {
            $query->where('is_admin', $filters['is_admin']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Ban a user (soft delete).
     *
     * @param  string  $userId
     * @return User
     *
     * @throws RuntimeException
     */
    public function ban(string $userId): User
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new RuntimeException('Pengguna tidak ditemukan.');
        }

        if ($user->is_admin) {
            throw new RuntimeException('Tidak dapat menonaktifkan admin.');
        }

        $user->delete();

        // Revoke all tokens
        $user->tokens()->delete();

        return $user;
    }

    /**
     * Unban a user (restore soft delete).
     *
     * @param  string  $userId
     * @return User
     *
     * @throws RuntimeException
     */
    public function unban(string $userId): User
    {
        $user = User::withTrashed()->find($userId);

        if (! $user) {
            throw new RuntimeException('Pengguna tidak ditemukan.');
        }

        if (! $user->trashed()) {
            throw new RuntimeException('Pengguna tidak dalam status banned.');
        }

        $user->restore();

        return $user->refresh();
    }
}
