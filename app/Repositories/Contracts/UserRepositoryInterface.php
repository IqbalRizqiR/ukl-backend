<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function create(array $data): User;

    public function update(string $id, array $data): ?User;

    public function delete(string $id): bool;

    public function getSellers(int $perPage = 15): LengthAwarePaginator;

    public function getAdmins(): Collection;

    public function updateBalance(string $id, float $amount): ?User;
}
