<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Settlement = 'settlement';
    case Capture = 'capture';
    case Deny = 'deny';
    case Cancel = 'cancel';
    case Expire = 'expire';
    case Refund = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Settlement => 'Berhasil',
            self::Capture => 'Ditangkap',
            self::Deny => 'Ditolak',
            self::Cancel => 'Dibatalkan',
            self::Expire => 'Kedaluwarsa',
            self::Refund => 'Dikembalikan',
        };
    }
}
