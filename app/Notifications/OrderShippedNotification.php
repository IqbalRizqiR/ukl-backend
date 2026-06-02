<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
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
            'type' => 'order_shipped',
            'title' => 'Pesanan Dikirim',
            'body' => "Penjual telah mengirim pesanan #{$this->order->order_number}.",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'product_id' => $this->order->product_id,
            'tracking_number' => $this->order->shipment->tracking_number,
            'shipping_courier' => $this->order->shipment->courier,
            'seller_id' => $this->order->seller_id,
        ];
    }
}
