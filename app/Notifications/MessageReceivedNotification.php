<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MessageReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Message $message
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'message_received',
            'title' => 'Pesan Baru',
            'body' => "Anda menerima pesan baru dari {$this->message->sender->name}.",
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'preview' => \Illuminate\Support\Str::limit($this->message->body, 100),
        ];
    }
}
