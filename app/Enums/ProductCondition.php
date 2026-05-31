<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductCondition: string
{
    case NewWithTag = 'new_with_tag';
    case LikeNew = 'like_new';
    case Good = 'good';
    case Fair = 'fair';

    public function label(): string
    {
        return match ($this) {
            self::NewWithTag => 'Baru dengan Tag',
            self::LikeNew => 'Seperti Baru',
            self::Good => 'Bagus',
            self::Fair => 'Cukup Baik',
        };
    }
}
