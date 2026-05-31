<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shipment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shipment\ShipmentResource;
use App\Services\Shipment\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShipmentController extends Controller
{
    public function __construct(
        private readonly ShipmentService $shipmentService,
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
}
