<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function initiate(Request $request, string $orderId): JsonResponse
    {
        $result = $this->paymentService->initiate($orderId);

        return response()->json([
            'message' => 'Pembayaran berhasil diinisiasi.',
            'data' => [
                'snap_token' => $result['snap_token'],
                'redirect_url' => $result['redirect_url'],
            ],
        ]);
    }
}
