<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'position' => $this->position,
        ];
    }
}
