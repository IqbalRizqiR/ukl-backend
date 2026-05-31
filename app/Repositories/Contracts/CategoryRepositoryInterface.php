<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function findById(string $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    public function getAll(): Collection;

    public function getRootCategories(): Collection;

    public function getActive(): Collection;

    public function create(array $data): Category;

    public function update(string $id, array $data): ?Category;

    public function delete(string $id): bool;
}
