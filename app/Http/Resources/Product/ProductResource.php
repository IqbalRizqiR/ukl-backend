<?php

declare(strict_types=1);

namespace App\Http\Resources\Product;

use App\Http\Resources\Discovery\BrandResource;
use App\Http\Resources\Discovery\CategoryResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seller' => new UserResource($this->whenLoaded('seller')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => $this->brand,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'size' => $this->size,
            'condition' => $this->condition,
            'color' => $this->color,
            'price' => $this->price,
            'weight_grams' => $this->weight_grams,
            'status' => $this->status,
            'views_count' => $this->views_count,
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'is_bookmarked' => (bool) $this->resource->getAttribute('is_bookmarked'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
