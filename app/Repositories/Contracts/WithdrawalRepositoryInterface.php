<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface WithdrawalRepositoryInterface
{
    public function findById(string $id): ?Withdrawal;

    public function getByUser(string $userId, int $perPage = 15): LengthAwarePaginator;

    public function getPending(): Collection;

    public function create(array $data): Withdrawal;

    public function update(string $id, array $data): ?Withdrawal;

    public function updateStatus(string $id, string $status): ?Withdrawal;
}
