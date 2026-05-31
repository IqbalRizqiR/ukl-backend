<?php

declare(strict_types=1);

namespace App\Enums;

enum DisputeReason: string
{
    case ItemNotReceived = 'item_not_received';
    case ItemNotAsDescribed = 'item_not_as_described';
    case ItemDamaged = 'item_damaged';
    case CounterfeitItem = 'counterfeit_item';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ItemNotReceived => 'Barang Tidak Diterima',
            self::ItemNotAsDescribed => 'Barang Tidak Sesuai Deskripsi',
            self::ItemDamaged => 'Barang Rusak',
            self::CounterfeitItem => 'Barang Palsu',
            self::Other => 'Lainnya',
        };
    }
}
