<?php

declare(strict_types=1);

namespace App\Jobs\Escrow;

use App\Models\EscrowTransaction;
use App\Services\EscrowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoCompleteEscrowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly string $escrowId
    ) {}

    public function handle(EscrowService $escrowService): void
    {
        $escrow = EscrowTransaction::find($this->escrowId);

        if (! $escrow) {
            return;
        }

        // Only auto-complete if escrow is still in 'delivered' status
        if ($escrow->status !== 'delivered') {
            return;
        }

        $escrowService->autoComplete($escrow);

        $escrow->update([
            'status' => 'auto_completed',
        ]);

        $escrow->order->update([
            'status' => 'completed',
        ]);
    }
}
