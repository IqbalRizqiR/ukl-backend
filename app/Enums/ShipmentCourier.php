<?php

declare(strict_types=1);

namespace App\Enums;

enum ShipmentCourier: string
{
    case JNE = 'jne';
    case TIKI = 'tiki';
    case POS = 'pos';
    case JNT = 'jnt';
    case SiCepat = 'sicepat';
    case AnterAja = 'anteraja';

    public function label(): string
    {
        return match ($this) {
            self::JNE => 'JNE',
            self::TIKI => 'TIKI',
            self::POS => 'POS Indonesia',
            self::JNT => 'J&T Express',
            self::SiCepat => 'SiCepat',
            self::AnterAja => 'AnterAja',
        };
    }
}
