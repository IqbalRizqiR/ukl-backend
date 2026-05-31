<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WithdrawalStatus;
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
class Withdrawal extends Model
{
    use HasAuditLog;
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => WithdrawalStatus::class,
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'processed_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(UserBankAccount::class);
    }
}
