<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function __construct(
        protected Review $model
    ) {}

    public function findById(string $id): ?Review
    {
        return $this->model->with(['reviewer', 'order', 'product'])->find($id);
    }

    public function findByOrderId(string $orderId): ?Review
    {
        return $this->model
            ->with(['reviewer', 'order', 'product'])
            ->where('order_id', $orderId)
            ->first();
    }

    public function getBySellerPaginated(string $sellerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['reviewer', 'product'])
            ->where('seller_id', $sellerId)
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Review
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?Review
    {
        $review = $this->model->find($id);

        if (! $review) {
            return null;
        }

        $review->update($data);

        return $review->fresh();
    }
}
