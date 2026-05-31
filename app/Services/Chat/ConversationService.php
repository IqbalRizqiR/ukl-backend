<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Models\Conversation;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

final class ConversationService
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
    ) {}

    /**
     * Get all conversations for a user (as buyer or seller).
     *
     * @param  string  $userId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getConversations(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Conversation::query()
            ->where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->with(['buyer', 'seller', 'product', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }

    /**
     * Find existing conversation or create a new one.
     *
     * @param  string  $buyerId
     * @param  string  $sellerId
     * @param  string|null  $productId
     * @return Conversation
     */
    public function findOrCreate(string $buyerId, string $sellerId, ?string $productId = null): Conversation
    {
        return Conversation::firstOrCreate(
            [
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'product_id' => $productId,
            ],
            [
                'last_message_at' => now(),
            ],
        );
    }

    /**
     * Find a conversation by ID.
     *
     * @param  string  $conversationId
     * @return Conversation|null
     */
    public function findById(string $conversationId): ?Conversation
    {
        return $this->conversationRepository->findById($conversationId);
    }
}
