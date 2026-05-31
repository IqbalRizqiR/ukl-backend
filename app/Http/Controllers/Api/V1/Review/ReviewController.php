<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Services\Review\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService,
    ) {}

    public function store(StoreReviewRequest $request, string $orderId): JsonResponse
    {
        $review = $this->reviewService->create(
            $orderId,
            $request->user()->id,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Ulasan berhasil ditambahkan.',
            'data' => new ReviewResource($review),
        ], 201);
    }

    public function reply(Request $request, string $reviewId): JsonResponse
    {
        $request->validate([
            'reply' => ['required', 'string', 'max:1000'],
        ]);

        $review = $this->reviewService->reply(
            $reviewId,
            $request->user()->id,
            $request->input('reply'),
        );

        return response()->json([
            'message' => 'Balasan berhasil ditambahkan.',
            'data' => new ReviewResource($review),
        ]);
    }

    public function sellerReviews(Request $request): AnonymousResourceCollection
    {
        $reviews = $this->reviewService->getSellerReviews($request->user()->id);

        return ReviewResource::collection($reviews);
    }
}
