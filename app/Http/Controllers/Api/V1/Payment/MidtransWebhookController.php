<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\MidtransWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MidtransWebhookController extends Controller
{
    public function __construct(
        private readonly MidtransWebhookService $webhookService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        try {
            $this->webhookService->handle($request->all());
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'MIDTRANS_TEST_PING') {
                return response()->json([
                    'message' => 'Test webhook Midtrans berhasil diterima.',
                ]);
            }
            throw $e;
        }

        return response()->json([
            'message' => 'Webhook berhasil diproses.',
        ]);
    }
}
