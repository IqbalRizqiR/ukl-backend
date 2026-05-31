<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\Order\OrderResource;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create(
            $request->user()->id,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'data' => new OrderResource($order),
        ], 201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $this->orderService->getByBuyer($request->user()->id);

        return OrderResource::collection($orders);
    }

    public function sellerOrders(Request $request): AnonymousResourceCollection
    {
        $orders = $this->orderService->getBySeller($request->user()->id);

        return OrderResource::collection($orders);
    }

    public function show(string $orderId): OrderResource
    {
        $order = $this->orderService->show($orderId);

        return new OrderResource($order);
    }

    public function cancel(Request $request, string $orderId): JsonResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $order = $this->orderService->cancel(
            $request->user()->id,
            $orderId,
            $request->input('reason'),
        );

        return response()->json([
            'message' => 'Pesanan berhasil dibatalkan.',
            'data' => new OrderResource($order),
        ]);
    }
}
