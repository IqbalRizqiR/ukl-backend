<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_url,
            'bio' => $this->bio,
            'is_seller' => $this->is_seller,
            'is_seller_verified' => $this->is_seller_verified,
            'balance' => $this->when(
                $request->user()?->id === $this->id,
                fn () => $this->balance
            ),
            'rating_avg' => $this->rating_avg,
            'rating_count' => $this->rating_count,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
