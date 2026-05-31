<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        protected Order $model
    ) {}

    public function findById(string $id): ?Order
    {
        return $this->model
            ->with(['buyer', 'seller', 'product', 'payment', 'shipment', 'escrow'])
            ->find($id);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model
            ->with(['buyer', 'seller', 'product', 'payment', 'shipment', 'escrow'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['buyer', 'seller', 'product']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['buyer_id'])) {
            $query->where('buyer_id', $filters['buyer_id']);
        }

        if (isset($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?Order
    {
        $order = $this->model->find($id);

        if (! $order) {
            return null;
        }

        $order->update($data);

        return $order->fresh(['buyer', 'seller', 'product', 'payment', 'shipment', 'escrow']);
    }

    public function getByBuyer(string $buyerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['seller', 'product', 'payment', 'shipment'])
            ->where('buyer_id', $buyerId)
            ->latest()
            ->paginate($perPage);
    }

    public function getBySeller(string $sellerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['buyer', 'product', 'payment', 'shipment'])
            ->where('seller_id', $sellerId)
            ->latest()
            ->paginate($perPage);
    }

    public function getByStatus(OrderStatus $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['buyer', 'seller', 'product'])
            ->where('status', $status)
            ->latest()
            ->paginate($perPage);
    }

    public function updateStatus(string $id, OrderStatus $status): ?Order
    {
        $order = $this->model->find($id);

        if (! $order) {
            return null;
        }

        $order->update(['status' => $status]);

        return $order->fresh();
    }
}
