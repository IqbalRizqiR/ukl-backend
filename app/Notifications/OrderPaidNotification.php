<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order $order
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
            'type' => 'order_paid',
            'title' => 'Pesanan Dibayar',
            'body' => "Pembeli telah membayar pesanan #{$this->order->order_number}.",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'product_id' => $this->order->product_id,
            'amount' => $this->order->total_amount,
            'buyer_id' => $this->order->buyer_id,
        ];
    }
}
