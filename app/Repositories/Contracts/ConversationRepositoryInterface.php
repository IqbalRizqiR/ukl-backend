<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Collection;

interface ConversationRepositoryInterface
{
    public function findById(string $id): ?Conversation;

    public function getByUser(string $userId): Collection;

    public function findOrCreate(string $buyerId, string $sellerId, ?string $productId = null): Conversation;

    public function update(string $id, array $data): ?Conversation;
}
