<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

final class ProductModerationService
{
    /**
     * Get all products for moderation (with filters).
     *
     * @param  array{status?: string, search?: string}  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['seller', 'category', 'brand', 'images'])
            ->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', ProductStatus::from($filters['status']));
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Remove a product (admin moderation - soft delete).
     *
     * @param  string  $productId
     * @return Product
     *
     * @throws RuntimeException
     */
    public function remove(string $productId): Product
    {
        $product = Product::find($productId);

        if (! $product) {
            throw new RuntimeException('Produk tidak ditemukan.');
        }

        $product->update(['status' => ProductStatus::Archived]);
        $product->delete();

        return $product;
    }
}
