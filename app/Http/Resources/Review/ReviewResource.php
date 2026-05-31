<?php

declare(strict_types=1);

namespace App\Http\Resources\Review;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'rating' => $this->rating,
            'comment' => $this->comment,
            'seller_reply' => $this->seller_reply,
            'seller_replied_at' => $this->seller_replied_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
