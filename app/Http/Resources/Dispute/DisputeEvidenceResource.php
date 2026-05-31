<?php

declare(strict_types=1);

namespace App\Http\Resources\Dispute;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeEvidenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'type' => $this->type,
            'image_url' => $this->image_url,
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
