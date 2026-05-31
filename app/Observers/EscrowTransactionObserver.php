<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\EscrowTransaction;
use Illuminate\Support\Facades\Log;

class EscrowTransactionObserver
{
    public function updating(EscrowTransaction $escrowTransaction): void
    {
        if ($escrowTransaction->isDirty('status')) {
            Log::channel('audit')->info('Escrow status changed', [
                'escrow_transaction_id' => $escrowTransaction->id,
                'order_id' => $escrowTransaction->order_id,
                'old_status' => $escrowTransaction->getOriginal('status'),
                'new_status' => $escrowTransaction->status,
                'amount' => $escrowTransaction->amount,
                'changed_at' => now()->toISOString(),
            ]);
        }
    }
}
