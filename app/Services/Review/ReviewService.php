<?php

declare(strict_types=1);

namespace App\Services\Review;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ReviewService
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Create a review for a completed order.
     *
     * @param  string  $orderId
     * @param  string  $reviewerId
     * @param  array{rating: int, comment?: string}  $data
     * @return Review
     *
     * @throws RuntimeException
     */
    public function create(string $orderId, string $reviewerId, array $data): Review
    {
        $order = Order::find($orderId);

        if (! $order) {
            throw new RuntimeException('Pesanan tidak ditemukan.');
        }

        if ($order->buyer_id !== $reviewerId) {
            throw new RuntimeException('Hanya pembeli yang dapat memberikan ulasan.');
        }

        if ($order->status !== OrderStatus::Completed) {
            throw new RuntimeException('Ulasan hanya bisa diberikan untuk pesanan yang sudah selesai.');
        }

        // Check if review already exists for this order
        $existing = $this->reviewRepository->findByOrderId($orderId);
        if ($existing) {
            throw new RuntimeException('Anda sudah memberikan ulasan untuk pesanan ini.');
        }

        if ($data['rating'] < 1 || $data['rating'] > 5) {
            throw new RuntimeException('Rating harus antara 1 sampai 5.');
        }

        return DB::transaction(function () use ($orderId, $reviewerId, $order, $data): Review {
            $review = $this->reviewRepository->create([
                'order_id' => $orderId,
                'reviewer_id' => $reviewerId,
                'seller_id' => $order->seller_id,
                'product_id' => $order->product_id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);

            // Recalculate seller rating
            $this->recalculateSellerRating($order->seller_id);

            return $review->load(['reviewer', 'product']);
        });
    }

    /**
     * Seller replies to a review.
     *
     * @param  string  $reviewId
     * @param  string  $sellerId
     * @param  string  $reply
     * @return Review
     *
     * @throws RuntimeException
     */
    public function reply(string $reviewId, string $sellerId, string $reply): Review
    {
        $review = $this->reviewRepository->findById($reviewId);

        if (! $review) {
            throw new RuntimeException('Ulasan tidak ditemukan.');
        }

        if ($review->seller_id !== $sellerId) {
            throw new RuntimeException('Anda tidak bisa membalas ulasan ini.');
        }

        if ($review->seller_reply !== null) {
            throw new RuntimeException('Anda sudah membalas ulasan ini.');
        }

        $this->reviewRepository->update($reviewId, [
            'seller_reply' => $reply,
            'seller_replied_at' => now(),
        ]);

        return $review->refresh()->load(['reviewer', 'product']);
    }

    /**
     * Get paginated reviews for a seller.
     *
     * @param  string  $sellerId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getSellerReviews(string $sellerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->reviewRepository->getBySellerPaginated($sellerId, $perPage);
    }

    /**
     * Recalculate seller's average rating and count.
     *
     * @param  string  $sellerId
     * @return void
     */
    private function recalculateSellerRating(string $sellerId): void
    {
        $stats = Review::where('seller_id', $sellerId)
            ->selectRaw('COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as total_reviews')
            ->first();

        $this->userRepository->update($sellerId, [
            'rating_avg' => round((float) $stats->avg_rating, 2),
            'rating_count' => (int) $stats->total_reviews,
        ]);
    }
}
