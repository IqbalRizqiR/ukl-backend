<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Discovery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SearchController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function search(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
        ]);

        $filters = $request->all();

        $products = $this->productService->list($filters);

        return ProductResource::collection($products);
    }
}
