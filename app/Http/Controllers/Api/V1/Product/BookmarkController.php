<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\BookmarkResource;
use App\Services\Product\BookmarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class BookmarkController extends Controller
{
    public function __construct(
        private readonly BookmarkService $bookmarkService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $bookmarks = $this->bookmarkService->getUserBookmarks($request->user()->id);

        return BookmarkResource::collection($bookmarks);
    }

    public function toggle(Request $request, string $productId): JsonResponse
    {
        $result = $this->bookmarkService->toggle($request->user()->id, $productId);

        return response()->json([
            'message' => $result['bookmarked'] ? 'Produk ditambahkan ke bookmark.' : 'Produk dihapus dari bookmark.',
            'data' => [
                'is_bookmarked' => $result['bookmarked'],
            ],
        ]);
    }
}
