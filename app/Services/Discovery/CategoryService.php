<?php

declare(strict_types=1);

namespace App\Services\Discovery;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    /**
     * Get all active root categories with their children.
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get all categories paginated (admin).
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->with('parent')
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    /**
     * Find a category by slug.
     *
     * @param  string  $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->categoryRepository->findBySlug($slug);
    }

    /**
     * Create a new category.
     *
     * @param  array{name: string, slug: string, parent_id?: string, icon_url?: string, sort_order?: int, is_active?: bool}  $data
     * @return Category
     */
    public function create(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    /**
     * Update a category.
     *
     * @param  string  $categoryId
     * @param  array  $data
     * @return Category|null
     */
    public function update(string $categoryId, array $data): ?Category
    {
        return $this->categoryRepository->update($categoryId, $data);
    }

    /**
     * Delete a category (soft delete).
     *
     * @param  string  $categoryId
     * @return bool
     */
    public function delete(string $categoryId): bool
    {
        return $this->categoryRepository->delete($categoryId);
    }
}
