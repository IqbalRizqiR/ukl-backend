<?php

declare(strict_types=1);

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'midtrans_order_id' => $this->midtrans_order_id,
            'payment_type' => $this->payment_type,
            'gross_amount' => $this->gross_amount,
            'status' => $this->status,
            'snap_token' => $this->snap_token,
            'redirect_url' => $this->redirect_url,
            'paid_at' => $this->paid_at?->toISOString(),
            'expired_at' => $this->expired_at?->toISOString(),
        ];
    }
}
