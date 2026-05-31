<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public function __construct(
        public readonly int $requiredAmount,
        public readonly int $currentBalance,
    ) {
        $formattedRequired = number_format($requiredAmount, 0, ',', '.');
        $formattedCurrent = number_format($currentBalance, 0, ',', '.');

        parent::__construct(
            "Saldo tidak mencukupi. Dibutuhkan: Rp {$formattedRequired}, tersedia: Rp {$formattedCurrent}."
        );
    }
}
