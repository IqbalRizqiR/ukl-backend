<?php

declare(strict_types=1);

namespace App\Services\Discovery;

use App\Models\Brand;
use App\Repositories\Contracts\BrandRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class BrandService
{
    public function __construct(
        private readonly BrandRepositoryInterface $brandRepository,
    ) {}

    /**
     * Get all active brands.
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->brandRepository->getActive();
    }

    /**
     * Get all brands paginated (admin).
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Brand::query()
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Find a brand by slug.
     *
     * @param  string  $slug
     * @return Brand|null
     */
    public function findBySlug(string $slug): ?Brand
    {
        return $this->brandRepository->findBySlug($slug);
    }

    /**
     * Create a new brand.
     *
     * @param  array{name: string, slug: string, logo_url?: string, is_active?: bool}  $data
     * @return Brand
     */
    public function create(array $data): Brand
    {
        return $this->brandRepository->create($data);
    }

    /**
     * Update a brand.
     *
     * @param  string  $brandId
     * @param  array  $data
     * @return Brand|null
     */
    public function update(string $brandId, array $data): ?Brand
    {
        return $this->brandRepository->update($brandId, $data);
    }

    /**
     * Delete a brand (soft delete).
     *
     * @param  string  $brandId
     * @return bool
     */
    public function delete(string $brandId): bool
    {
        return $this->brandRepository->delete($brandId);
    }
}
