<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Brand;
use App\Repositories\Contracts\BrandRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class BrandRepository implements BrandRepositoryInterface
{
    public function __construct(
        protected Brand $model
    ) {}

    public function findById(string $id): ?Brand
    {
        return Cache::tags(['brands'])->rememberForever("brand:{$id}", function () use ($id) {
            return $this->model->find($id);
        });
    }

    public function findBySlug(string $slug): ?Brand
    {
        return Cache::tags(['brands'])->rememberForever("brand:slug:{$slug}", function () use ($slug) {
            return $this->model->where('slug', $slug)->first();
        });
    }

    public function getAll(): Collection
    {
        return Cache::tags(['brands'])->rememberForever('brands:all', function () {
            return $this->model->orderBy('name')->get();
        });
    }

    public function getActive(): Collection
    {
        return Cache::tags(['brands'])->rememberForever('brands:active', function () {
            return $this->model
                ->where('is_active', 'true')
                ->orderBy('name')
                ->get();
        });
    }

    public function create(array $data): Brand
    {
        $brand = $this->model->create($data);
        Cache::tags(['brands'])->flush();
        return $brand;
    }

    public function update(string $id, array $data): ?Brand
    {
        $brand = $this->model->find($id);

        if (! $brand) {
            return null;
        }

        $brand->update($data);
        Cache::tags(['brands'])->flush();

        return $brand->fresh();
    }

    public function delete(string $id): bool
    {
        $brand = $this->model->find($id);

        if (! $brand) {
            return false;
        }

        $deleted = $brand->delete();
        if ($deleted) {
            Cache::tags(['brands'])->flush();
        }

        return (bool) $deleted;
    }
}
