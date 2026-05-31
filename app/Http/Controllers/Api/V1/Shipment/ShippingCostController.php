<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shipment;

use App\Http\Controllers\Controller;
use App\Services\Shipment\ShippingCostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShippingCostController extends Controller
{
    public function __construct(
        private readonly ShippingCostService $shippingCostService,
    ) {}

    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'origin' => ['required', 'integer'],
            'destination' => ['required', 'integer'],
            'weight' => ['required', 'integer', 'min:1'],
            'courier' => ['required', 'string'],
        ]);

        $costs = $this->shippingCostService->calculate($request->only(
            'origin',
            'destination',
            'weight',
            'courier',
        ));

        return response()->json([
            'message' => 'Ongkos kirim berhasil dihitung.',
            'data' => $costs,
        ]);
    }

    public function provinces(): JsonResponse
    {
        $provinces = $this->shippingCostService->getProvinces();

        return response()->json([
            'data' => $provinces,
        ]);
    }

    public function cities(Request $request, int $provinceId): JsonResponse
    {
        $cities = $this->shippingCostService->getCities($provinceId);

        return response()->json([
            'data' => $cities,
        ]);
    }
}
