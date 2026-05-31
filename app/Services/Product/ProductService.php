<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

final class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * List products with optional filters.
     *
     * @param  array<string, mixed>  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage, $filters);
    }

    /**
     * Show a single product by slug, incrementing view counter.
     *
     * @param  string  $slug
     * @return Product
     *
     * @throws ModelNotFoundException
     */
    public function show(string $slug): Product
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            throw new ModelNotFoundException('Produk tidak ditemukan.');
        }

        $this->productRepository->incrementViews($product->id);

        return $product;
    }

    /**
     * Create a new product for a seller.
     *
     * @param  string  $sellerId
     * @param  array<string, mixed>  $data
     * @return Product
     */
    public function create(string $sellerId, array $data): Product
    {
        $data['seller_id'] = $sellerId;
        $data['status'] = ProductStatus::Active;

        return $this->productRepository->create($data);
    }

    /**
     * Update a product after verifying seller ownership.
     *
     * @param  string  $sellerId
     * @param  string  $productId
     * @param  array<string, mixed>  $data
     * @return Product
     *
     * @throws ModelNotFoundException
     * @throws \RuntimeException
     */
    public function update(string $sellerId, string $productId, array $data): Product
    {
        $product = $this->productRepository->findById($productId);

        if (! $product) {
            throw new ModelNotFoundException('Produk tidak ditemukan.');
        }

        if ($product->seller_id !== $sellerId) {
            throw new \RuntimeException('Anda tidak berwenang mengubah produk ini.');
        }

        $updatedProduct = $this->productRepository->update($productId, $data);

        return $updatedProduct ?? $product;
    }

    /**
     * Soft-delete a product after verifying seller ownership.
     *
     * @param  string  $sellerId
     * @param  string  $productId
     * @return bool
     *
     * @throws ModelNotFoundException
     * @throws \RuntimeException
     */
    public function delete(string $sellerId, string $productId): bool
    {
        $product = $this->productRepository->findById($productId);

        if (! $product) {
            throw new ModelNotFoundException('Produk tidak ditemukan.');
        }

        if ($product->seller_id !== $sellerId) {
            throw new \RuntimeException('Anda tidak berwenang menghapus produk ini.');
        }

        return $this->productRepository->delete($productId);
    }

    /**
     * Get products belonging to a specific seller.
     *
     * @param  string  $sellerId
     * @param  array<string, mixed>  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getSellerProducts(string $sellerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getBySeller($sellerId, $perPage);
    }
}
