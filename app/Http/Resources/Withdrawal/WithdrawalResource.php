<?php

declare(strict_types=1);

namespace App\Http\Resources\Withdrawal;

use App\Http\Resources\User\BankAccountResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'fee' => $this->fee,
            'net_amount' => $this->net_amount,
            'status' => $this->status,
            'user' => new \App\Http\Resources\User\UserResource($this->whenLoaded('user')),
            'bank_account' => new BankAccountResource($this->whenLoaded('bankAccount')),
            'reference_number' => $this->reference_number,
            'processed_at' => $this->processed_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
