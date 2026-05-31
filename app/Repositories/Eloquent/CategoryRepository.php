<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        protected Category $model
    ) {}

    public function findById(string $id): ?Category
    {
        return $this->model->with(['children', 'parent'])->find($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->model
            ->with(['children', 'parent'])
            ->where('slug', $slug)
            ->first();
    }

    public function getAll(): Collection
    {
        return $this->model
            ->with(['children'])
            ->orderBy('name')
            ->get();
    }

    public function getRootCategories(): Collection
    {
        return $this->model
            ->with(['children'])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    public function getActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Category
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?Category
    {
        $category = $this->model->find($id);

        if (! $category) {
            return null;
        }

        $category->update($data);

        return $category->fresh();
    }

    public function delete(string $id): bool
    {
        $category = $this->model->find($id);

        if (! $category) {
            return false;
        }

        return (bool) $category->delete();
    }
}
