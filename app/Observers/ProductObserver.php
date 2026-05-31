<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductObserver
{
    public function creating(Product $product): void
    {
        $product->slug = $this->generateUniqueSlug($product->title);
    }

    public function updating(Product $product): void
    {
        if ($product->isDirty('status')) {
            // Dispatch event when status changes — implement event class as needed
            // event(new ProductStatusChanged($product, $product->getOriginal('status'), $product->status));
        }
    }

    protected function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
