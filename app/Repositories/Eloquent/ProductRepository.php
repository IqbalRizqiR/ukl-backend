<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    // Optimized eager load columns
    protected array $optimizedWith = [
        'seller',
        'category',
        'images',
        'seller.defaultAddress'
    ];

    public function __construct(
        protected Product $model
    ) {
    }

    protected function attachBookmarkStatus(Product|LengthAwarePaginator|Collection|null $data): Product|LengthAwarePaginator|Collection|null
    {
        if (!$data) {
            return $data;
        }

        $userId = auth('sanctum')->id();
        
        if (!$userId) {
            // Set false for all
            if ($data instanceof Product) {
                $data->setAttribute('is_bookmarked', false);
            } elseif ($data instanceof LengthAwarePaginator) {
                $data->getCollection()->transform(function ($product) {
                    $product->setAttribute('is_bookmarked', false);
                    return $product;
                });
            } elseif ($data instanceof Collection) {
                $data->transform(function ($product) {
                    $product->setAttribute('is_bookmarked', false);
                    return $product;
                });
            }
            return $data;
        }

        if ($data instanceof Product) {
            $isBookmarked = DB::table('bookmarks')
                ->where('product_id', $data->id)
                ->where('user_id', $userId)
                ->exists();
            $data->setAttribute('is_bookmarked', $isBookmarked);
        } else {
            $collection = $data instanceof LengthAwarePaginator ? $data->getCollection() : $data;
            $productIds = $collection->pluck('id')->toArray();
            
            $bookmarkedIds = DB::table('bookmarks')
                ->whereIn('product_id', $productIds)
                ->where('user_id', $userId)
                ->pluck('product_id')
                ->toArray();
                
            $collection->transform(function ($product) use ($bookmarkedIds) {
                $product->setAttribute('is_bookmarked', in_array($product->id, $bookmarkedIds));
                return $product;
            });
        }

        return $data;
    }

    public function findById(string $id): ?Product
    {
        $product = Cache::tags(['products'])->rememberForever("product:{$id}", function () use ($id) {
            return $this->model->with($this->optimizedWith)->find($id);
        });

        return $this->attachBookmarkStatus($product);
    }

    public function findBySlug(string $slug): ?Product
    {
        $product = Cache::tags(['products'])->rememberForever("product:slug:{$slug}", function () use ($slug) {
            return $this->model->with($this->optimizedWith)->where('slug', $slug)->first();
        });

        return $this->attachBookmarkStatus($product);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        // For dynamic filters, we rely on DB index, but we optimize the eager load columns!
        $query = $this->model->with($this->optimizedWith);

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

        $paginator = $query->paginate($perPage);
        return $this->attachBookmarkStatus($paginator);
    }

    public function create(array $data): Product
    {
        $product = $this->model->create($data);
        Cache::tags(['products'])->flush();
        return $product;
    }

    public function update(string $id, array $data): ?Product
    {
        $product = $this->model->find($id);

        if (!$product) {
            return null;
        }

        $product->update($data);
        
        // Flush specific product cache
        Cache::tags(['products'])->forget("product:{$id}");
        Cache::tags(['products'])->forget("product:slug:{$product->slug}");
        Cache::tags(['products', 'products_lists'])->flush();

        return $product->fresh($this->optimizedWith);
    }

    public function delete(string $id): bool
    {
        $product = $this->model->find($id);

        if (!$product) {
            return false;
        }

        $deleted = $product->delete();
        if ($deleted) {
            Cache::tags(['products'])->forget("product:{$id}");
            Cache::tags(['products'])->forget("product:slug:{$product->slug}");
            Cache::tags(['products_lists'])->flush();
        }
        
        return (bool) $deleted;
    }

    public function getBySeller(string $sellerId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['category', 'images'])->where('seller_id', $sellerId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        $paginator = $query->paginate($perPage);
        return $this->attachBookmarkStatus($paginator);
    }
    
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        
        // Cache the default active browse view for 5 minutes since it's highly hit
        $paginator = Cache::tags(['products', 'products_lists'])->remember("products:active:page:{$page}:limit:{$perPage}", 300, function () use ($perPage) {
            return $this->model
                ->with($this->optimizedWith)
                ->where('status', ProductStatus::Active)
                ->whereHas('seller.defaultAddress', function ($q) {
                    $q->whereNotNull('city_id');
                })
                ->latest()
                ->paginate($perPage);
        });

        return $this->attachBookmarkStatus($paginator);
    }

    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $builder = $this->model
            ->with($this->optimizedWith)
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

        $paginator = $builder->latest()->paginate($perPage);
        return $this->attachBookmarkStatus($paginator);
    }

    public function incrementViews(string $id): void
    {
        $this->model->where('id', $id)->increment('views_count');
    }
}
