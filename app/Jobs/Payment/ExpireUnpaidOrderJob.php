<?php

declare(strict_types=1);

namespace App\Jobs\Payment;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireUnpaidOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly string $orderId
    ) {}

    public function handle(): void
    {
        $order = Order::with('escrowTransaction')->find($this->orderId);

        if (! $order) {
            return;
        }

        // Only expire if order is still pending payment
        if ($order->status !== 'pending_payment') {
            return;
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Payment expired',
        ]);

        if ($order->escrowTransaction) {
            $order->escrowTransaction->update([
                'status' => 'expired',
            ]);
        }

        // Restore product availability
        $order->product()->update([
            'status' => 'available',
        ]);
    }
}
