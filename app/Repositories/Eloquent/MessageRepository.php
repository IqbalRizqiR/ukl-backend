<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    public function __construct(
        protected Message $model
    ) {}

    public function findById(string $id): ?Message
    {
        return $this->model->with(['sender'])->find($id);
    }

    public function getByConversation(string $conversationId): Collection
    {
        return $this->model
            ->with(['sender'])
            ->where('conversation_id', $conversationId)
            ->oldest()
            ->get();
    }

    public function create(array $data): Message
    {
        return $this->model->create($data);
    }

    public function markAsRead(string $conversationId, string $userId): int
    {
        return $this->model
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
