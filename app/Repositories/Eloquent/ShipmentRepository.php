<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Shipment;
use App\Repositories\Contracts\ShipmentRepositoryInterface;

class ShipmentRepository implements ShipmentRepositoryInterface
{
    public function __construct(
        protected Shipment $model
    ) {}

    public function findById(string $id): ?Shipment
    {
        return $this->model->with(['order'])->find($id);
    }

    public function findByOrderId(string $orderId): ?Shipment
    {
        return $this->model
            ->with(['order'])
            ->where('order_id', $orderId)
            ->first();
    }

    public function create(string $orderId, array $data): Shipment
    {
        return $this->model->updateOrCreate($orderId, $data);
    }

    public function update(string $id, array $data): ?Shipment
    {
        $shipment = $this->model->find($id);

        if (! $shipment) {
            return null;
        }

        $shipment->update($data);

        return $shipment->fresh();
    }

    public function updateTrackingNumber(string $id, string $trackingNumber): ?Shipment
    {
        $shipment = $this->model->find($id);

        if (! $shipment) {
            return null;
        }

        $shipment->update(['tracking_number' => $trackingNumber]);

        return $shipment->fresh();
    }
}
