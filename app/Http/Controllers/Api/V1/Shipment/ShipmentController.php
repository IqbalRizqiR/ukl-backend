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
        // For simplicity, we assume the shipmentService has a getById method or we just query it
        // Or we just get it through eloquent
        $shipment = \App\Models\Shipment::findOrFail($shipmentId);
        
        if (!$shipment->tracking_number || !$shipment->courier) {
            return response()->json([
                'message' => 'Resi atau kurir tidak tersedia untuk pelacakan.',
            ], 400);
        }

        try {
            $trackingData = $this->rajaOngkirService->trackWaybill(
                $shipment->tracking_number,
                $shipment->courier->value ?? (string) $shipment->courier
            );
            
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
