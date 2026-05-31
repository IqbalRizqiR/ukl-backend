<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Sold = 'sold';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Sold => 'Terjual',
            self::Archived => 'Diarsipkan',
        };
    }
}
