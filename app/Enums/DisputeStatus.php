<?php

declare(strict_types=1);

namespace App\Enums;

enum DisputeStatus: string
{
    case Open = 'open';
    case UnderReview = 'under_review';
    case ResolvedRefund = 'resolved_refund';
    case ResolvedNoRefund = 'resolved_no_refund';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Dibuka',
            self::UnderReview => 'Sedang Ditinjau',
            self::ResolvedRefund => 'Diselesaikan - Refund',
            self::ResolvedNoRefund => 'Diselesaikan - Tanpa Refund',
            self::Closed => 'Ditutup',
        };
    }
}
