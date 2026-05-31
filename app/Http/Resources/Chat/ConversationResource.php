<?php

declare(strict_types=1);

namespace App\Http\Resources\Chat;

use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buyer' => new UserResource($this->whenLoaded('buyer')),
            'seller' => new UserResource($this->whenLoaded('seller')),
            'product' => new ProductResource($this->whenLoaded('product')),
            'last_message_at' => $this->last_message_at?->toISOString(),
            'latest_message' => new MessageResource($this->whenLoaded('latestMessage')),
        ];
    }
}
