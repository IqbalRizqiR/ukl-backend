<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Shipment;

interface ShipmentRepositoryInterface
{
    public function findById(string $id): ?Shipment;

    public function findByOrderId(string $orderId): ?Shipment;

    public function create(int $orderId, array $data): Shipment;

    public function update(string $id, array $data): ?Shipment;

    public function updateTrackingNumber(string $id, string $trackingNumber): ?Shipment;
}
