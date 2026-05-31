<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\EscrowTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EscrowReleasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly EscrowTransaction $escrow
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
            'type' => 'escrow_released',
            'title' => 'Dana Escrow Dicairkan',
            'body' => "Dana escrow untuk pesanan #{$this->escrow->order->order_number} telah dicairkan ke saldo Anda.",
            'escrow_id' => $this->escrow->id,
            'order_id' => $this->escrow->order_id,
            'amount' => $this->escrow->amount,
        ];
    }
}
