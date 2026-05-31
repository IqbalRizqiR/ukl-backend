<?php

declare(strict_types=1);

namespace App\Listeners\Order;

use App\Events\Order\OrderPaid;
use Illuminate\Contracts\Queue\ShouldQueue;

class MarkProductAsSold implements ShouldQueue
{
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;

        $order->product()->update([
            'status' => 'sold',
        ]);
    }
}
