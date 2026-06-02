<?php

declare(strict_types=1);

namespace App\Services\Shipment;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Events\Order\OrderShipped;
use App\Models\EscrowTransaction;
use App\Models\Shipment;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ShipmentRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class ShipmentService
{
    public function __construct(
        private readonly ShipmentRepositoryInterface $shipmentRepository,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    /**
     * Create a shipment for an order.
     *
     * @param  string  $orderId
     * @param  array<string, mixed>  $data
     * @return Shipment
     *
     * @throws ModelNotFoundException
     */
    public function create(string $orderId, array $data): Shipment
    {
        return DB::transaction(function () use ($orderId, $data): Shipment {
            $order = $this->orderRepository->findById($orderId);

            if (! $order) {
                throw new ModelNotFoundException('Pesanan tidak ditemukan.');
            }

            $shipment = $this->shipmentRepository->create([
                'order_id' => $orderId,
                'courier' => $data['courier'] ?? $order->courier,
                'service' => $data['service'] ?? $order->courier_service ?? 'REG',
                'tracking_number' => $data['tracking_number'] ?? null,
                'shipping_cost' => $order->shipping_cost,
                'weight_grams' => 1000, // Standard flat weight for MVP
                'origin_city_id' => $order->seller->defaultAddress?->city_id ?? throw new \Exception('Seller has no origin city.'),
                'destination_city_id' => $order->shippingAddress?->city_id ?? throw new \Exception('No shipping address provided.'),
                'estimated_delivery_at' => $data['estimated_delivery_at'] ?? now()->addDays(3),
                'shipped_at' => now(),
            ]);

            $this->orderRepository->updateStatus($orderId, OrderStatus::Shipped);

            // Update escrow to InDelivery
            $escrow = EscrowTransaction::where('order_id', $orderId)->first();
            if ($escrow) {
                $escrow->update([
                    'status' => EscrowStatus::InDelivery,
                    'auto_release_at' => now()->addHours((int) config('escrow.auto_complete_hours')),
                ]);
            }

            event(new OrderShipped($order->refresh()));

            return $shipment;
        });
    }

    /**
     * Update tracking information for a shipment.
     *
     * @param  string  $shipmentId
     * @param  string  $trackingNumber
     * @param  string|null  $estimatedDelivery
     * @return Shipment
     *
     * @throws ModelNotFoundException
     */
    public function updateTracking(string $shipmentId, string $trackingNumber, ?string $estimatedDelivery = null): Shipment
    {
        $updateData = [
            'tracking_number' => $trackingNumber,
        ];

        if ($estimatedDelivery !== null) {
            $updateData['estimated_delivery_at'] = $estimatedDelivery;
        }

        $shipment = $this->shipmentRepository->update($shipmentId, $updateData);

        if (! $shipment) {
            throw new ModelNotFoundException('Pengiriman tidak ditemukan.');
        }

        return $shipment;
    }
}
