<?php

declare(strict_types=1);

namespace App\Http\Resources\Shipment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'courier' => $this->courier,
            'service' => $this->service,
            'tracking_number' => $this->tracking_number,
            'weight_grams' => $this->weight_grams,
            'shipping_cost' => $this->shipping_cost,
            'estimated_delivery_at' => $this->estimated_delivery_at?->toISOString(),
            'shipped_at' => $this->shipped_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
        ];
    }
}
