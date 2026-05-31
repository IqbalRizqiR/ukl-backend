<?php

declare(strict_types=1);

namespace App\Http\Resources\Chat;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender' => new UserResource($this->whenLoaded('sender')),
            'body' => $this->body,
            'type' => $this->type,
            'image_url' => $this->image_url,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
