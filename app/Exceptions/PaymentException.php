<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class PaymentException extends RuntimeException
{
    public static function invalidSignature(): self
    {
        return new self(
            'Signature pembayaran tidak valid.'
        );
    }

    public static function gatewayError(string $message): self
    {
        return new self(
            "Kesalahan payment gateway: {$message}"
        );
    }

    public static function notFound(): self
    {
        return new self(
            'Data pembayaran tidak ditemukan.'
        );
    }
}
