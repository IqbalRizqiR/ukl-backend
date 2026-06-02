<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Concerns\HasAuditLog;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

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

    public function currentUserBookmark(): HasOne
    {
        return $this->hasOne(Bookmark::class)->where('user_id', Auth::id());
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
