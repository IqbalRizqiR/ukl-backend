<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ProductImageService
{
    /**
     * Upload images for a product (max 5 total).
     *
     * @param  string  $productId
     * @param  array<int, UploadedFile>  $files
     * @return Collection<int, ProductImage>
     *
     * @throws \RuntimeException
     */
    public function upload(string $productId, array $files): Collection
    {
        $existingCount = ProductImage::where('product_id', $productId)->count();
        $maxImages = 5;

        if ($existingCount + count($files) > $maxImages) {
            throw new \RuntimeException(
                "Maksimal {$maxImages} gambar diperbolehkan. Saat ini sudah ada {$existingCount} gambar."
            );
        }

        return DB::transaction(function () use ($productId, $files, $existingCount): Collection {
            $images = new Collection();
            $position = $existingCount;

            foreach ($files as $file) {
                $path = $file->store("products/{$productId}", 'public');

                $image = ProductImage::create([
                    'product_id' => $productId,
                    'image_url' => $path,
                    'position' => $position++,
                ]);

                $images->push($image);
            }

            return $images;
        });
    }

    /**
     * Delete a product image record and its associated file.
     *
     * @param  string  $productId
     * @param  string  $imageId
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function delete(string $productId, string $imageId): void
    {
        $image = ProductImage::where('product_id', $productId)
            ->where('id', $imageId)
            ->firstOrFail();

        Storage::disk('public')->delete($image->image_url);

        $image->delete();
    }

    /**
     * Reorder product images by updating positions.
     *
     * @param  string  $productId
     * @param  array<string, int>  $positions  Map of image_id => position
     * @return void
     */
    public function reorder(string $productId, array $positions): void
    {
        DB::transaction(function () use ($productId, $positions): void {
            foreach ($positions as $imageId => $position) {
                ProductImage::where('product_id', $productId)
                    ->where('id', $imageId)
                    ->update(['position' => $position]);
            }
        });
    }
}
