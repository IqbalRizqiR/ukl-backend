<?php

declare(strict_types=1);

namespace App\Enums;

enum EscrowStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case InDelivery = 'in_delivery';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Disputed = 'disputed';
    case Refunded = 'refunded';
    case AutoCompleted = 'auto_completed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Paid => 'Dibayar',
            self::InDelivery => 'Dalam Pengiriman',
            self::Delivered => 'Diterima',
            self::Completed => 'Selesai',
            self::Disputed => 'Dalam Sengketa',
            self::Refunded => 'Dikembalikan',
            self::AutoCompleted => 'Otomatis Selesai',
            self::Expired => 'Kedaluwarsa',
        };
    }
}
