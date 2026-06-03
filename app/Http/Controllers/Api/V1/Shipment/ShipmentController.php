<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shipment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shipment\ShipmentResource;
use App\Services\Shipment\ShipmentService;
use App\Services\Shipment\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShipmentController extends Controller
{
    public function __construct(
        private readonly ShipmentService $shipmentService,
        private readonly RajaOngkirService $rajaOngkirService,
    ) {}

    public function ship(Request $request, string $orderId): JsonResponse
    {
        $request->validate([
            'courier' => ['required', 'string'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'estimated_delivery_at' => ['nullable', 'date'],
        ]);

        $shipment = $this->shipmentService->create(
            $orderId,
            $request->only('courier', 'tracking_number', 'estimated_delivery_at'),
        );

        return response()->json([
            'message' => 'Pengiriman berhasil dikonfirmasi.',
            'data' => new ShipmentResource($shipment),
        ]);
    }

    public function updateTracking(Request $request, string $shipmentId): JsonResponse
    {
        $request->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
            'estimated_delivery_at' => ['nullable', 'date'],
        ]);

        $shipment = $this->shipmentService->updateTracking(
            $shipmentId,
            $request->input('tracking_number'),
            $request->input('estimated_delivery_at'),
        );

        return response()->json([
            'message' => 'Nomor resi berhasil diperbarui.',
            'data' => new ShipmentResource($shipment),
        ]);
    }

    public function track(Request $request, string $shipmentId): JsonResponse
    {
        // Load the shipment along with the order's shipping address to get the phone number
        $shipment = \App\Models\Shipment::with('order.shippingAddress')->findOrFail($shipmentId);
        
        if (!$shipment->tracking_number || !$shipment->courier) {
            return response()->json([
                'message' => 'Resi atau kurir tidak tersedia untuk pelacakan.',
            ], 400);
        }

        try {
            $phoneNumber = $shipment->order?->shippingAddress?->phone;
            
            $trackingData = $this->rajaOngkirService->trackWaybill(
                $shipment->tracking_number,
                $shipment->courier->value ?? (string) $shipment->courier,
                $phoneNumber
            );
            
            // Sync status based on tracking
            if ($shipment->order && isset($trackingData['summary']['status'])) {
                $trackingStatus = strtolower($trackingData['summary']['status']);
                $orderStatus = $shipment->order->status;
                
                if ($trackingStatus === 'delivered' && $orderStatus !== \App\Enums\OrderStatus::Delivered && $orderStatus !== \App\Enums\OrderStatus::Completed) {
                    $shipment->order->update(['status' => \App\Enums\OrderStatus::Delivered]);
                    
                    $escrow = \App\Models\EscrowTransaction::where('order_id', $shipment->order->id)->first();
                    if ($escrow && $escrow->status !== \App\Enums\EscrowStatus::Delivered && $escrow->status !== \App\Enums\EscrowStatus::Completed) {
                        $escrow->update(['status' => \App\Enums\EscrowStatus::Delivered]);
                    }
                } elseif (in_array($trackingStatus, ['on process', 'in transit', 'allocated']) && $orderStatus === \App\Enums\OrderStatus::Paid) {
                    $shipment->order->update(['status' => \App\Enums\OrderStatus::Shipped]);
                    $escrow = \App\Models\EscrowTransaction::where('order_id', $shipment->order->id)->first();
                    if ($escrow && $escrow->status === \App\Enums\EscrowStatus::Pending) {
                        $escrow->update(['status' => \App\Enums\EscrowStatus::InDelivery]);
                    }
                }
            }
            
            return response()->json([
                'message' => 'Berhasil mengambil data pelacakan.',
                'data' => $trackingData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal melacak resi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
