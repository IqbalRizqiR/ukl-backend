<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ShipmentCourier;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Guarded([])]
#[Hidden(['deleted_at'])]
class Shipment extends Model
{
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'courier' => ShipmentCourier::class,
            'shipping_cost' => 'decimal:2',
            'estimated_delivery_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
