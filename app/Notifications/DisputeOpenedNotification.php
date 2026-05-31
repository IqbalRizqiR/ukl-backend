<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DisputeOpenedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Dispute $dispute
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
            'type' => 'dispute_opened',
            'title' => 'Sengketa Dibuka',
            'body' => "Sengketa telah dibuka untuk pesanan #{$this->dispute->order->order_number}.",
            'dispute_id' => $this->dispute->id,
            'order_id' => $this->dispute->order_id,
            'reason' => $this->dispute->reason,
            'opened_by' => $this->dispute->user_id,
        ];
    }
}
