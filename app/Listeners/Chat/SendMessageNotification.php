<?php

declare(strict_types=1);

namespace App\Listeners\Chat;

use App\Events\Chat\MessageSent;
use App\Notifications\MessageReceivedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMessageNotification implements ShouldQueue
{
    public function handle(MessageSent $event): void
    {
        $message = $event->message;
        $conversation = $message->conversation;

        // Notify all participants in the conversation except the sender
        $recipients = $conversation->participants()
            ->where('user_id', '!=', $message->sender_id)
            ->get();

        foreach ($recipients as $participant) {
            $participant->user->notify(new MessageReceivedNotification($message));
        }
    }
}
