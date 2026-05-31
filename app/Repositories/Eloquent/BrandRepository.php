<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Brand;
use App\Repositories\Contracts\BrandRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BrandRepository implements BrandRepositoryInterface
{
    public function __construct(
        protected Brand $model
    ) {}

    public function findById(string $id): ?Brand
    {
        return $this->model->find($id);
    }

    public function findBySlug(string $slug): ?Brand
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function getAll(): Collection
    {
        return $this->model->orderBy('name')->get();
    }

    public function getActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Brand
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?Brand
    {
        $brand = $this->model->find($id);

        if (! $brand) {
            return null;
        }

        $brand->update($data);

        return $brand->fresh();
    }

    public function delete(string $id): bool
    {
        $brand = $this->model->find($id);

        if (! $brand) {
            return false;
        }

        return (bool) $brand->delete();
    }
}
