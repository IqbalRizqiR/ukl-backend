<?php

declare(strict_types=1);

namespace App\Enums;

enum EvidenceType: string
{
    case Image = 'image';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Image => 'Gambar',
            self::Text => 'Teks',
        };
    }
}
