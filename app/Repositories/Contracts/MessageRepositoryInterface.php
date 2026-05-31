<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

interface MessageRepositoryInterface
{
    public function findById(string $id): ?Message;

    public function getByConversation(string $conversationId): Collection;

    public function create(array $data): Message;

    public function markAsRead(string $conversationId, string $userId): int;
}
