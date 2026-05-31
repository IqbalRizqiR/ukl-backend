<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class EscrowException extends RuntimeException
{
    public static function invalidTransition(string $from, string $to): self
    {
        return new self(
            "Transisi status escrow dari '{$from}' ke '{$to}' tidak diizinkan."
        );
    }

    public static function alreadyCompleted(): self
    {
        return new self(
            'Transaksi escrow sudah selesai dan tidak dapat diubah.'
        );
    }

    public static function notFound(): self
    {
        return new self(
            'Transaksi escrow tidak ditemukan.'
        );
    }
}
