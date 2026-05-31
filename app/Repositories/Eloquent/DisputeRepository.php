<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Dispute;
use App\Repositories\Contracts\DisputeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DisputeRepository implements DisputeRepositoryInterface
{
    public function __construct(
        protected Dispute $model
    ) {}

    public function findById(string $id): ?Dispute
    {
        return $this->model
            ->with(['order', 'complainant', 'admin'])
            ->find($id);
    }

    public function findByOrderId(string $orderId): ?Dispute
    {
        return $this->model
            ->with(['order', 'complainant', 'admin'])
            ->where('order_id', $orderId)
            ->first();
    }

    public function getOpen(): Collection
    {
        return $this->model
            ->with(['order', 'complainant'])
            ->where('status', 'open')
            ->oldest()
            ->get();
    }

    public function create(array $data): Dispute
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?Dispute
    {
        $dispute = $this->model->find($id);

        if (! $dispute) {
            return null;
        }

        $dispute->update($data);

        return $dispute->fresh();
    }

    public function updateStatus(string $id, string $status): ?Dispute
    {
        $dispute = $this->model->find($id);

        if (! $dispute) {
            return null;
        }

        $dispute->update(['status' => $status]);

        return $dispute->fresh();
    }
}
