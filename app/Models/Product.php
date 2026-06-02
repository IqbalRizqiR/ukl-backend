<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Concerns\HasAuditLog;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Guarded([])]
#[Hidden(['deleted_at'])]
class Product extends Model
{
    use HasAuditLog;
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'condition' => ProductCondition::class,
            'status' => ProductStatus::class,
            'price' => 'decimal:2',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    /**
     * Add `is_bookmarked` boolean column for a specific user.
     * Uses a raw subquery — works reliably regardless of auth state.
     */
    public function scopeWithBookmarkStatus(Builder $query, ?string $userId): Builder
    {
        if ($userId) {
            return $query->selectRaw(
                '*, EXISTS(SELECT 1 FROM bookmarks WHERE bookmarks.product_id = products.id AND bookmarks.user_id = ? AND bookmarks.deleted_at IS NULL) as is_bookmarked',
                [$userId]
            );
        }

        return $query->selectRaw('*, FALSE as is_bookmarked');
    }
}
