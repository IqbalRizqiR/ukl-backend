<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        protected Product $model
    ) {
    }

    public function findById(string $id): ?Product
    {
        return $this->model->with(['seller', 'category', 'images', 'seller.defaultAddress',])->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->model
            ->with(['seller', 'category', 'images', 'seller.defaultAddress',])
            ->where('slug', $slug)
            ->first();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with([
            'seller',
            'category',
            'images',
            'seller.defaultAddress',
        ])->withBookmarkStatus(auth('sanctum')->id());



        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        if (isset($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', ProductStatus::Active);
        }

        $query->whereHas('seller.defaultAddress', function ($q) {
            $q->whereNotNull('city_id');
        });

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?Product
    {
        $product = $this->model->find($id);

        if (!$product) {
            return null;
        }

        $product->update($data);

        return $product->fresh(['seller', 'category', 'images']);
    }

    public function delete(string $id): bool
    {
        $product = $this->model->find($id);

        if (!$product) {
            return false;
        }

        return (bool) $product->delete();
    }

    public function getBySeller(string $sellerId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['category', 'images'])
            ->withBookmarkStatus(auth('sanctum')->id())
            ->where('seller_id', $sellerId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['seller', 'category', 'images'])
            ->withBookmarkStatus(auth('sanctum')->id())
            ->where('status', ProductStatus::Active)
            ->whereHas('seller.defaultAddress', function ($q) {
                $q->whereNotNull('city_id');
            })
            ->latest()
            ->paginate($perPage);
    }

    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $builder = $this->model
            ->with(['seller', 'category', 'images'])
            ->withBookmarkStatus(auth('sanctum')->id())
            ->where('status', ProductStatus::Active)
            ->whereHas('seller.defaultAddress', function ($q) {
                $q->whereNotNull('city_id');
            })
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                    ->orWhere('description', 'ilike', "%{$query}%");
            });

        if (isset($filters['category_id'])) {
            $builder->where('category_id', $filters['category_id']);
        }

        if (isset($filters['brand'])) {
            $builder->where('brand', $filters['brand']);
        }

        if (isset($filters['condition'])) {
            $builder->where('condition', $filters['condition']);
        }

        if (isset($filters['min_price'])) {
            $builder->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $builder->where('price', '<=', $filters['max_price']);
        }

        return $builder->latest()->paginate($perPage);
    }

    public function incrementViews(string $id): void
    {
        $this->model->where('id', $id)->increment('views_count');
    }
}
