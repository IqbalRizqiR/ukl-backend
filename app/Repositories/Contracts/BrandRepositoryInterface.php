<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

interface BrandRepositoryInterface
{
    public function findById(string $id): ?Brand;

    public function findBySlug(string $slug): ?Brand;

    public function getAll(): Collection;

    public function getActive(): Collection;

    public function create(array $data): Brand;

    public function update(string $id, array $data): ?Brand;

    public function delete(string $id): bool;
}
