<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DisputeReason;
use App\Enums\DisputeStatus;
use App\Models\Concerns\HasAuditLog;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Guarded([])]
#[Hidden(['deleted_at'])]
class Dispute extends Model
{
    use HasAuditLog;
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'reason' => DisputeReason::class,
            'status' => DisputeStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function complainant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'complainant_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(DisputeEvidence::class);
    }
}
