<?php

declare(strict_types=1);

namespace App\Http\Resources\Escrow;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EscrowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status' => $this->status,
            'paid_at' => $this->paid_at?->toISOString(),
            'released_at' => $this->released_at?->toISOString(),
            'frozen_at' => $this->frozen_at?->toISOString(),
            'auto_release_at' => $this->auto_release_at?->toISOString(),
        ];
    }
}
