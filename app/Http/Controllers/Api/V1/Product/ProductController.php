<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $this->productService->list($request->all());

        return ProductResource::collection($products);
    }

    public function show(string $slug): ProductResource
    {
        $product = $this->productService->show($slug);

        return new ProductResource($product);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create(
            $request->user()->id,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function update(UpdateProductRequest $request, string $productId): JsonResponse
    {
        $product = $this->productService->update(
            $request->user()->id,
            $productId,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'data' => new ProductResource($product),
        ]);
    }

    public function destroy(Request $request, string $productId): JsonResponse
    {
        $this->productService->delete(
            $request->user()->id,
            $productId,
        );

        return response()->json([
            'message' => 'Produk berhasil dihapus.',
        ]);
    }

    public function sellerProducts(Request $request): AnonymousResourceCollection
    {
        $products = $this->productService->getSellerProducts(
            $request->user()->id,
            $request->all(),
        );

        return ProductResource::collection($products);
    }
}
