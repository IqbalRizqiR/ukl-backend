<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Conversation;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ConversationRepository implements ConversationRepositoryInterface
{
    public function __construct(
        protected Conversation $model
    ) {}

    public function findById(string $id): ?Conversation
    {
        return $this->model
            ->with(['buyer', 'seller', 'product', 'messages'])
            ->find($id);
    }

    public function getByUser(string $userId): Collection
    {
        return $this->model
            ->with(['buyer', 'seller', 'product', 'latestMessage'])
            ->where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->latest('updated_at')
            ->get();
    }

    public function findOrCreate(string $buyerId, string $sellerId, ?string $productId = null): Conversation
    {
        $query = $this->model
            ->where('buyer_id', $buyerId)
            ->where('seller_id', $sellerId);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $conversation = $query->first();

        if ($conversation) {
            return $conversation;
        }

        return $this->model->create([
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'product_id' => $productId,
        ]);
    }

    public function update(string $id, array $data): ?Conversation
    {
        $conversation = $this->model->find($id);

        if (! $conversation) {
            return null;
        }

        $conversation->update($data);

        return $conversation->fresh();
    }
}
