<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function findById(string $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function create(array $data): Product;

    public function update(string $id, array $data): ?Product;

    public function delete(string $id): bool;

    public function getBySeller(string $sellerId, int $perPage = 15): LengthAwarePaginator;

    public function getActive(int $perPage = 15): LengthAwarePaginator;

    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function incrementViews(string $id): void;
}
