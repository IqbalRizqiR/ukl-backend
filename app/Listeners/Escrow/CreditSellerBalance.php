<?php

declare(strict_types=1);

namespace App\Listeners\Escrow;

use App\Events\Escrow\EscrowReleased;
use App\Services\User\BalanceService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreditSellerBalance implements ShouldQueue
{
    public function __construct(
        private readonly BalanceService $balanceService
    ) {}

    public function handle(EscrowReleased $event): void
    {
        $escrow = $event->escrow;
        $order = $escrow->order;

        $this->balanceService->creditBalance(
            $order->seller_id,
            (float) $escrow->amount,
            "Escrow released for order #{$order->order_number}"
        );
    }
}
