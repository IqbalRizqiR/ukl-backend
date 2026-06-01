<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Uniqlo',
            'H&M',
            'Zara',
            "Levi's",
            'Erigo',
            'Stradivarius',
            'Pull & Bear',
            'Nikicio',
            'Cotton On',
            'The Executive',
            'Mango',
            'GAP',
        ];

        foreach ($brands as $name) {
            Brand::create([
                'name'      => $name,
                'slug'      => Str::slug($name),
                'is_active' => true,
            ]);
        }
    }
}
