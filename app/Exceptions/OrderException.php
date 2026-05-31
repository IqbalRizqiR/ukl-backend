<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class OrderException extends RuntimeException
{
    public static function productUnavailable(): self
    {
        return new self(
            'Produk sudah tidak tersedia untuk dibeli.'
        );
    }

    public static function cannotCancel(): self
    {
        return new self(
            'Pesanan tidak dapat dibatalkan pada status saat ini.'
        );
    }

    public static function alreadyPaid(): self
    {
        return new self(
            'Pesanan sudah dibayar.'
        );
    }

    public static function cannotPurchaseOwnProduct(): self
    {
        return new self(
            'Anda tidak dapat membeli produk sendiri.'
        );
    }
}
