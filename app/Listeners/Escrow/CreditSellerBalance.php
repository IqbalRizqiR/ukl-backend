<?php

declare(strict_types=1);

namespace App\Listeners\Escrow;

use App\Events\Escrow\EscrowReleased;
use App\Services\BalanceService;
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

        $this->balanceService->credit(
            userId: $order->seller_id,
            amount: $escrow->amount,
            description: "Escrow released for order #{$order->order_number}",
            referenceType: 'escrow_release',
            referenceId: $escrow->id,
        );
    }
}
