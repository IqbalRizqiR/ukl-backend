<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        protected User $model
    ) {}

    public function findById(string $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'ilike', "%{$filters['search']}%")
                  ->orWhere('email', 'ilike', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_seller'])) {
            $query->where('is_seller', $filters['is_seller']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?User
    {
        $user = $this->findById($id);

        if (! $user) {
            return null;
        }

        $user->update($data);

        return $user->fresh();
    }

    public function delete(string $id): bool
    {
        $user = $this->findById($id);

        if (! $user) {
            return false;
        }

        return (bool) $user->delete();
    }

    public function getSellers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('is_seller', 'true')
            ->latest()
            ->paginate($perPage);
    }

    public function getAdmins(): Collection
    {
        return $this->model
            ->where('is_admin', 'true')
            ->get();
    }

    public function updateBalance(string $id, float $amount): ?User
    {
        $user = $this->findById($id);

        if (! $user) {
            return null;
        }

        $user->increment('balance', $amount);

        return $user->fresh();
    }
}
