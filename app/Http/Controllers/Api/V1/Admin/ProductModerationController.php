<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Services\Admin\ProductModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProductModerationController extends Controller
{
    public function __construct(
        private readonly ProductModerationService $productModerationService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $this->productModerationService->list($request->all());

        return ProductResource::collection($products);
    }

    public function destroy(string $productId): JsonResponse
    {
        $this->productModerationService->remove($productId);

        return response()->json([
            'message' => 'Produk berhasil dihapus oleh admin.',
        ]);
    }
}
