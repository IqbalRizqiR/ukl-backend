<?php

declare(strict_types=1);

namespace App\Http\Resources\Dispute;

use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order' => new OrderResource($this->whenLoaded('order')),
            'complainant' => new UserResource($this->whenLoaded('complainant')),
            'reason' => $this->reason,
            'description' => $this->description,
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'resolution' => $this->resolution,
            'resolved_at' => $this->resolved_at?->toISOString(),
            'evidences' => DisputeEvidenceResource::collection($this->whenLoaded('evidences')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
