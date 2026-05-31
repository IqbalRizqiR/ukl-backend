<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Disputed = 'disputed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Menunggu Pembayaran',
            self::Paid => 'Dibayar',
            self::Processing => 'Diproses',
            self::Shipped => 'Dikirim',
            self::Delivered => 'Diterima',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Disputed => 'Dalam Sengketa',
            self::Refunded => 'Dikembalikan',
        };
    }
}
