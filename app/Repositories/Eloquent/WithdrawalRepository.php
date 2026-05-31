<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Withdrawal;
use App\Repositories\Contracts\WithdrawalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WithdrawalRepository implements WithdrawalRepositoryInterface
{
    public function __construct(
        protected Withdrawal $model
    ) {}

    public function findById(string $id): ?Withdrawal
    {
        return $this->model->with(['user'])->find($id);
    }

    public function getByUser(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function getPending(): Collection
    {
        return $this->model
            ->with(['user'])
            ->where('status', 'pending')
            ->oldest()
            ->get();
    }

    public function create(array $data): Withdrawal
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?Withdrawal
    {
        $withdrawal = $this->model->find($id);

        if (! $withdrawal) {
            return null;
        }

        $withdrawal->update($data);

        return $withdrawal->fresh();
    }

    public function updateStatus(string $id, string $status): ?Withdrawal
    {
        $withdrawal = $this->model->find($id);

        if (! $withdrawal) {
            return null;
        }

        $withdrawal->update(['status' => $status]);

        return $withdrawal->fresh();
    }
}
