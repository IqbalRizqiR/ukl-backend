<?php

declare(strict_types=1);

namespace App\Events\Escrow;

use App\Models\EscrowTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EscrowFrozen
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly EscrowTransaction $escrow
    ) {}
}
