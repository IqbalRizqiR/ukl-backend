<?php

declare(strict_types=1);

namespace App\Listeners\Escrow;

use App\Events\Escrow\EscrowFrozen;
use App\Events\Escrow\EscrowFunded;
use App\Events\Escrow\EscrowReleased;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;

class CreateAuditLog implements ShouldQueue
{
    public function handle(EscrowFunded|EscrowReleased|EscrowFrozen $event): void
    {
        $escrow = $event->escrow;
        $eventType = match (true) {
            $event instanceof EscrowFunded => 'escrow.funded',
            $event instanceof EscrowReleased => 'escrow.released',
            $event instanceof EscrowFrozen => 'escrow.frozen',
        };

        AuditLog::create([
            'auditable_type' => $escrow->getMorphClass(),
            'auditable_id' => $escrow->id,
            'action' => $eventType,
            'old_values' => [],
            'new_values' => [
                'status' => $escrow->status,
                'amount' => $escrow->amount,
            ],
            'user_id' => Auth::id(),
        ]);
    }
}
