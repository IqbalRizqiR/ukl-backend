<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Guarded([])]
#[Hidden(['deleted_at'])]
class City extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
