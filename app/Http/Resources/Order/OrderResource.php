<?php

declare(strict_types=1);

namespace App\Http\Resources\Order;

use App\Http\Resources\Escrow\EscrowResource;
use App\Http\Resources\Payment\PaymentResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Shipment\ShipmentResource;
use App\Http\Resources\User\AddressResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'buyer' => new UserResource($this->whenLoaded('buyer')),
            'seller' => new UserResource($this->whenLoaded('seller')),
            'product' => new ProductResource($this->whenLoaded('product')),
            'shipping_address' => new AddressResource($this->whenLoaded('shippingAddress')),
            'product_price' => $this->product_price,
            'shipping_cost' => $this->shipping_cost,
            'service_fee' => $this->service_fee,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'notes' => $this->notes,
            'escrow' => new EscrowResource($this->whenLoaded('escrow')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'shipment' => new ShipmentResource($this->whenLoaded('shipment')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
