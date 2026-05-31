<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Escrow;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderResource;
use App\Services\Escrow\EscrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EscrowController extends Controller
{
    public function __construct(
        private readonly EscrowService $escrowService,
    ) {}

    public function confirmReceived(Request $request, string $orderId): JsonResponse
    {
        $escrow = $this->escrowService->confirmReceived($orderId, $request->user()->id);

        return response()->json([
            'message' => 'Penerimaan barang dikonfirmasi. Dana akan dilepaskan ke penjual.',
            'data' => $escrow,
        ]);
    }
}
