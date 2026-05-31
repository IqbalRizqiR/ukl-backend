<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentType: string
{
    case BankTransfer = 'bank_transfer';
    case GoPay = 'gopay';
    case Ovo = 'ovo';
    case Qris = 'qris';
    case ShopeePay = 'shopeepay';
    case Dana = 'dana';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'Transfer Bank',
            self::GoPay => 'GoPay',
            self::Ovo => 'OVO',
            self::Qris => 'QRIS',
            self::ShopeePay => 'ShopeePay',
            self::Dana => 'DANA',
        };
    }
}
