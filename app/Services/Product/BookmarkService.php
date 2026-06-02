<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\Bookmark;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class BookmarkService
{
    /**
     * Toggle a bookmark for a product. Creates if not exists, deletes if exists.
     *
     * @param  string  $userId
     * @param  string  $productId
     * @return array{bookmarked: bool}
     */
    public function toggle(string $userId, string $productId): array
    {
        $bookmark = Bookmark::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($bookmark) {
            $bookmark->delete();

            return ['bookmarked' => false];
        }

        Bookmark::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return ['bookmarked' => true];
    }

    /**
     * Get paginated bookmarks for a user.
     *
     * @param  string  $userId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getUserBookmarks(string $userId, int $perPage = 15)
    {
        return DB::table('bookmarks')
            ->where('user_id', $userId)
            ->paginate($perPage);
    }
}
