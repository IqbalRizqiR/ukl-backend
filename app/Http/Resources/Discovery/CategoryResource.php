<?php

declare(strict_types=1);

namespace App\Http\Resources\Discovery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
            'icon_url' => $this->icon_url,
            'sort_order' => $this->sort_order,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
