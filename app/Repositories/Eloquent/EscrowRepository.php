<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Escrow;
use App\Repositories\Contracts\EscrowRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EscrowRepository implements EscrowRepositoryInterface
{
    public function __construct(
        protected Escrow $model
    ) {}

    public function findById(string $id): ?Escrow
    {
        return $this->model->with(['order'])->find($id);
    }

    public function findByOrderId(string $orderId): ?Escrow
    {
        return $this->model
            ->with(['order'])
            ->where('order_id', $orderId)
            ->first();
    }

    public function create(array $data): Escrow
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?Escrow
    {
        $escrow = $this->model->find($id);

        if (! $escrow) {
            return null;
        }

        $escrow->update($data);

        return $escrow->fresh();
    }

    public function updateStatus(string $id, string $status): ?Escrow
    {
        $escrow = $this->model->find($id);

        if (! $escrow) {
            return null;
        }

        $escrow->update(['status' => $status]);

        return $escrow->fresh();
    }

    public function getAutoReleaseDue(): Collection
    {
        return $this->model
            ->where('status', 'held')
            ->whereNotNull('auto_release_at')
            ->where('auto_release_at', '<=', now())
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->model
            ->with(['order'])
            ->where('status', 'held')
            ->get();
    }
}
