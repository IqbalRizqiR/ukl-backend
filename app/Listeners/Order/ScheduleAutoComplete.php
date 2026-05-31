<?php

declare(strict_types=1);

namespace App\Listeners\Order;

use App\Events\Order\OrderDelivered;
use App\Jobs\Escrow\AutoCompleteEscrowJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class ScheduleAutoComplete implements ShouldQueue
{
    public function handle(OrderDelivered $event): void
    {
        $order = $event->order;
        $escrow = $order->escrowTransaction;

        if ($escrow) {
            AutoCompleteEscrowJob::dispatch($escrow->id)
                ->delay(now()->addHours(48));
        }
    }
}
