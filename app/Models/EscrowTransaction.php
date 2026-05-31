<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EscrowStatus;
use App\Models\Concerns\HasAuditLog;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Guarded([])]
#[Hidden(['deleted_at'])]
class EscrowTransaction extends Model
{
    use HasAuditLog;
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => EscrowStatus::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'released_at' => 'datetime',
            'frozen_at' => 'datetime',
            'auto_release_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
