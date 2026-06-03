<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        protected Category $model
    ) {}

    public function findById(string $id): ?Category
    {
        return Cache::tags(['categories'])->rememberForever("category:{$id}", function () use ($id) {
            return $this->model->with(['children', 'parent'])->find($id);
        });
    }

    public function findBySlug(string $slug): ?Category
    {
        return Cache::tags(['categories'])->rememberForever("category:slug:{$slug}", function () use ($slug) {
            return $this->model
                ->with(['children', 'parent'])
                ->where('slug', $slug)
                ->first();
        });
    }

    public function getAll(): Collection
    {
        return Cache::tags(['categories'])->rememberForever('categories:all', function () {
            return $this->model
                ->with(['children'])
                ->orderBy('name')
                ->get();
        });
    }

    public function getRootCategories(): Collection
    {
        return Cache::tags(['categories'])->rememberForever('categories:root', function () {
            return $this->model
                ->with(['children'])
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();
        });
    }

    public function getActive(): Collection
    {
        return Cache::tags(['categories'])->rememberForever('categories:active', function () {
            return $this->model
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    public function create(array $data): Category
    {
        $category = $this->model->create($data);
        Cache::tags(['categories'])->flush();
        return $category;
    }

    public function update(string $id, array $data): ?Category
    {
        $category = $this->model->find($id);

        if (! $category) {
            return null;
        }

        $category->update($data);
        Cache::tags(['categories'])->flush();

        return $category->fresh();
    }

    public function delete(string $id): bool
    {
        $category = $this->model->find($id);

        if (! $category) {
            return false;
        }

        $deleted = $category->delete();
        if ($deleted) {
            Cache::tags(['categories'])->flush();
        }

        return (bool) $deleted;
    }
}
