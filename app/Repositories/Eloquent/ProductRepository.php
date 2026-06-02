<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        protected Product $model
    ) {
    }

    /**
     * Base eager-load relations for product queries.
     */
    private function baseWith(): array
    {
        return ['seller', 'category', 'images'];
    }

    /**
     * Apply bookmark exists check for the current authenticated user.
     */
    private function withBookmarkStatus($query)
    {
        if (Auth::id()) {
            $query->withExists(['currentUserBookmark as is_bookmarked']);
        }

        return $query;
    }

    public function findById(string $id): ?Product
    {
        $query = $this->model->with($this->baseWith());
        $this->withBookmarkStatus($query);

        return $query->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        $query = $this->model->with($this->baseWith());
        $this->withBookmarkStatus($query);

        return $query->where('slug', $slug)->first();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with($this->baseWith());
        $this->withBookmarkStatus($query);

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

        if (isset($filters['is_bookmarked']) && Auth::check()) {
            $query->whereHas('currentUserBookmark');
        }

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

        return $product->fresh($this->baseWith());
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
            ->where('seller_id', $sellerId);
        $this->withBookmarkStatus($query);

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
        $query = $this->model
            ->with($this->baseWith())
            ->where('status', ProductStatus::Active)
            ->latest();
        $this->withBookmarkStatus($query);

        return $query->paginate($perPage);
    }

    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $builder = $this->model
            ->with($this->baseWith())
            ->where('status', ProductStatus::Active)
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                    ->orWhere('description', 'ilike', "%{$query}%");
            });
        $this->withBookmarkStatus($builder);

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
