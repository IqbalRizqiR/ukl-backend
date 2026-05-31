<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Models\Concerns\HasAuditLog;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Guarded([])]
#[Hidden(['deleted_at'])]
class Order extends Model
{
    use HasAuditLog;
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'product_price' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'shipping_address_id');
    }

    public function escrow(): HasOne
    {
        return $this->hasOne(EscrowTransaction::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
