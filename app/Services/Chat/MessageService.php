<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Enums\MessageType;
use App\Events\Chat\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

final class MessageService
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
    ) {}

    /**
     * Get paginated messages for a conversation.
     *
     * @param  string  $conversationId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getMessages(string $conversationId, int $perPage = 50): LengthAwarePaginator
    {
        return Message::query()
            ->where('conversation_id', $conversationId)
            ->with('sender')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Send a message in a conversation.
     *
     * @param  string  $conversationId
     * @param  string  $senderId
     * @param  array{body?: string, type?: MessageType, image?: UploadedFile}  $data
     * @return Message
     */
    public function send(string $conversationId, string $senderId, array $data): Message
    {
        $imageUrl = null;

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $imageUrl = $data['image']->store('chat/images', 'public');
        }

        $type = isset($data['type'])
            ? (is_string($data['type']) ? MessageType::from($data['type']) : $data['type'])
            : ($imageUrl ? MessageType::Image : MessageType::Text);

        $message = $this->messageRepository->create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $data['body'] ?? null,
            'type' => $type,
            'image_url' => $imageUrl ? Storage::disk('public')->url($imageUrl) : null,
        ]);

        // Update conversation last_message_at
        Conversation::where('id', $conversationId)->update([
            'last_message_at' => now(),
        ]);

        $message->load('sender');

        event(new MessageSent($message));

        return $message;
    }

    /**
     * Mark all messages from the other user as read.
     *
     * @param  string  $conversationId
     * @param  string  $userId  The current user reading the messages
     * @return int  Number of messages marked as read
     */
    public function markAsRead(string $conversationId, string $userId): int
    {
        return Message::query()
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
