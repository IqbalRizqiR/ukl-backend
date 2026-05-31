<?php

declare(strict_types=1);

namespace App\Enums;

enum MessageType: string
{
    case Text = 'text';
    case Image = 'image';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Teks',
            self::Image => 'Gambar',
            self::System => 'Sistem',
        };
    }
}
