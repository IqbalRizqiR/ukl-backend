<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Kaos',
            'Kemeja',
            'Jaket',
            'Hoodie & Sweater',
            'Jeans',
            'Celana Chino',
            'Celana Jogger',
            'Celana Pendek',
            'Rok',
            'Dress',
            'Outerwear',
            'Lainnya',
        ];

        foreach ($categories as $i => $name) {
            Category::create([
                'name'       => $name,
                'slug'       => Str::slug($name),
                'parent_id'  => null,
                'icon_url'   => null,
                'sort_order' => $i,
                'is_active'  => true,
            ]);
        }
    }
}
