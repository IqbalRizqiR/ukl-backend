<?php

declare(strict_types=1);

namespace App\Listeners\Order;

use App\Events\Order\OrderPaid;
use App\Notifications\OrderPaidNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifySellerOnOrderPaid implements ShouldQueue
{
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;
        $seller = $order->seller;

        $seller->notify(new OrderPaidNotification($order));
    }
}
