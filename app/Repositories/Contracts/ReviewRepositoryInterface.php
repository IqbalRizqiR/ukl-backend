<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Review;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    public function findById(string $id): ?Review;

    public function findByOrderId(string $orderId): ?Review;

    public function getBySellerPaginated(string $sellerId, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Review;

    public function update(string $id, array $data): ?Review;
}
