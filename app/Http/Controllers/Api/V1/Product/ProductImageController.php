<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductImageResource;
use App\Services\Product\ProductImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductImageController extends Controller
{
    public function __construct(
        private readonly ProductImageService $productImageService,
    ) {}

    public function store(Request $request, string $productId): JsonResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['required', 'image', 'max:2048'],
        ]);

        $images = $this->productImageService->upload(
            $productId,
            $request->file('images'),
        );

        return response()->json([
            'message' => 'Gambar berhasil diunggah.',
            'data' => $images,
        ], 201);
    }

    public function destroy(Request $request, string $productId, string $imageId): JsonResponse
    {
        $this->productImageService->delete($productId, $imageId);

        return response()->json([
            'message' => 'Gambar berhasil dihapus.',
        ]);
    }
}
