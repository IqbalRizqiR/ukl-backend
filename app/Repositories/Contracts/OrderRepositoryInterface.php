<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function findById(string $id): ?Order;

    public function findByOrderNumber(string $orderNumber): ?Order;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function create(array $data): Order;

    public function update(string $id, array $data): ?Order;

    public function getByBuyer(string $buyerId, int $perPage = 15): LengthAwarePaginator;

    public function getBySeller(string $sellerId, int $perPage = 15): LengthAwarePaginator;

    public function getByStatus(OrderStatus $status, int $perPage = 15): LengthAwarePaginator;

    public function updateStatus(string $id, OrderStatus $status): ?Order;
}
