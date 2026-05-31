<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EvidenceType;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Guarded([])]
#[Hidden(['deleted_at'])]
class DisputeEvidence extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => EvidenceType::class,
        ];
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
