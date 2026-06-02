<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $sellers    = User::where('is_seller', true)->get();
        $categories = Category::all();
        $brands     = Brand::all();

        $items = [
            ['title' => 'Kemeja Flanel Uniqlo Merah Hitam',        'desc' => 'Kemeja flanel lengan panjang, bahan tebal dan hangat. Kondisi 95%.', 'size' => 'L',   'price' => 150000, 'weight' => 350, 'color' => 'Merah'],
            ['title' => 'Jaket Denim Levi\'s Vintage 90s',         'desc' => 'Jaket denim asli era 90-an, washed effect alami. Kancing lengkap.',  'size' => 'M',   'price' => 350000, 'weight' => 700, 'color' => 'Biru'],
            ['title' => 'Celana Chino Erigo Premium Krem',          'desc' => 'Beli kekecilan, belum pernah dipakai. Tag masih ada.',              'size' => 'S',   'price' => 120000, 'weight' => 400, 'color' => 'Krem'],
            ['title' => 'Hoodie H&M Polos Abu-abu',                'desc' => 'Hoodie basic warna misty grey, nyaman untuk daily wear.',           'size' => 'XL',  'price' => 135000, 'weight' => 550, 'color' => 'Abu'],
            ['title' => 'Kaos Oversize Zara Man Hitam',            'desc' => 'Bahan tebal, tidak belel. Sangat bagus.',                           'size' => 'L',   'price' => 90000,  'weight' => 200, 'color' => 'Hitam'],
            ['title' => 'Stradivarius Mom Fit Jeans Medium Blue',  'desc' => 'Mom fit jeans, cuttingan bagus. Alasan jual: bosan.',               'size' => 'M',   'price' => 180000, 'weight' => 600, 'color' => 'Biru'],
            ['title' => 'Kemeja Nikicio Basic Hitam Lengan Panjang','desc' => 'Formal casual, cocok ngantor atau hangout. Sedikit luntur wajar.',  'size' => 'M',   'price' => 210000, 'weight' => 300, 'color' => 'Hitam'],
            ['title' => 'Zara Cotton Jogger Pants Olive',          'desc' => 'Karet pinggang masih kencang. Warna hijau olive.',                  'size' => 'L',   'price' => 160000, 'weight' => 450, 'color' => 'Olive'],
            ['title' => 'Pull & Bear Graphic Tee White',           'desc' => 'Kaos grafis edisi terbatas, bahan katun 100%.',                     'size' => 'M',   'price' => 85000,  'weight' => 180, 'color' => 'Putih'],
            ['title' => 'Cotton On Slim Fit Chinos Navy',          'desc' => 'Celana chino slim fit warna navy, cocok semi-formal.',               'size' => 'L',   'price' => 145000, 'weight' => 420, 'color' => 'Navy'],
            ['title' => 'GAP Logo Hoodie Classic Red',             'desc' => 'Hoodie klasik dengan logo GAP besar, bulu dalam masih tebal.',      'size' => 'XL',  'price' => 220000, 'weight' => 600, 'color' => 'Merah'],
            ['title' => 'Mango Linen Shirt Off-White',             'desc' => 'Kemeja linen tipis, perfect untuk musim panas. Barely used.',       'size' => 'S',   'price' => 175000, 'weight' => 250, 'color' => 'Off-White'],
            ['title' => 'Rok Midi Stradivarius Floral Print',      'desc' => 'Rok midi motif bunga, bahan polyester premium.',                    'size' => 'M',   'price' => 155000, 'weight' => 300, 'color' => 'Multi'],
            ['title' => 'The Executive Blazer Slim Charcoal',      'desc' => 'Blazer semi-formal, cocok untuk interview atau meeting.',            'size' => 'L',   'price' => 280000, 'weight' => 500, 'color' => 'Charcoal'],
            ['title' => 'Celana Pendek Uniqlo Dry-EX Hitam',       'desc' => 'Celana pendek sport, bahan quick-dry. Dipakai 2x.',                 'size' => 'M',   'price' => 75000,  'weight' => 150, 'color' => 'Hitam'],
        ];

        $conditions = ProductCondition::cases();

        foreach ($items as $i => $item) {
            $seller   = $sellers[$i % $sellers->count()];
            $category = $categories->random();
            $brand    = $brands->random();
            $slug     = Str::slug($item['title']) . '-' . Str::random(5);

            $product = Product::create([
                'seller_id'    => $seller->id,
                'category_id'  => $category->id,
                'brand_id'     => $brand->id,
                'title'        => $item['title'],
                'slug'         => $slug,
                'description'  => $item['desc'],
                'size'         => $item['size'],
                'condition'    => fake()->randomElement($conditions)->value,
                'color'        => $item['color'],
                'price'        => $item['price'],
                'weight_grams' => $item['weight'],
                'status'       => ProductStatus::Active->value,
                'views_count'  => fake()->numberBetween(5, 500),
            ]);

            // Create 2-4 fake images per product
            $imgCount = fake()->numberBetween(2, 4);
            for ($j = 0; $j < $imgCount; $j++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url'  => "",
                    'position'   => $j,
                ]);
            }
        }
    }
}
