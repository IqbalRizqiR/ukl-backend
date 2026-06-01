<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1 Admin
        User::create([
            'name'              => 'Admin Rewear',
            'email'             => 'admin@rewear.id',
            'phone'             => '081200000001',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'avatar_url'        => 'https://i.pravatar.cc/150?img=68',
            'bio'               => 'Platform administrator',
            'is_seller'         => false,
            'is_admin'          => true,
            'is_seller_verified'=> true,
            'balance'           => 0,
            'rating_avg'        => 0,
            'rating_count'      => 0,
        ]);

        // 5 Sellers (verified)
        $sellers = [
            ['name' => 'Rizky Firmansyah',  'email' => 'rizky@example.com',  'phone' => '081234567801', 'avatar' => 1,  'bio' => 'Vintage collector & thrift enthusiast'],
            ['name' => 'Sinta Amelia',      'email' => 'sinta@example.com',  'phone' => '081234567802', 'avatar' => 5,  'bio' => 'Preloved fashion lover since 2020'],
            ['name' => 'Bagas Pratama',     'email' => 'bagas@example.com',  'phone' => '081234567803', 'avatar' => 15, 'bio' => 'Streetwear curator'],
            ['name' => 'Aulia Rahman',      'email' => 'aulia@example.com',  'phone' => '081234567804', 'avatar' => 20, 'bio' => 'Quality second-hand apparel'],
            ['name' => 'Dewi Lestari',      'email' => 'dewi@example.com',   'phone' => '081234567805', 'avatar' => 9,  'bio' => 'Sustainable fashion advocate'],
        ];

        foreach ($sellers as $s) {
            User::create([
                'name'              => $s['name'],
                'email'             => $s['email'],
                'phone'             => $s['phone'],
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'avatar_url'        => "https://i.pravatar.cc/150?img={$s['avatar']}",
                'bio'               => $s['bio'],
                'is_seller'         => true,
                'is_admin'          => false,
                'is_seller_verified'=> true,
                'balance'           => fake()->randomFloat(2, 50000, 5000000),
                'rating_avg'        => fake()->randomFloat(2, 4.0, 5.0),
                'rating_count'      => fake()->numberBetween(10, 200),
            ]);
        }

        // 5 Buyers
        $buyers = [
            ['name' => 'Andi Saputra',   'email' => 'andi@example.com',   'phone' => '081234567806', 'avatar' => 11],
            ['name' => 'Rina Wulandari', 'email' => 'rina@example.com',   'phone' => '081234567807', 'avatar' => 25],
            ['name' => 'Fajar Nugroho',  'email' => 'fajar@example.com',  'phone' => '081234567808', 'avatar' => 33],
            ['name' => 'Maya Putri',     'email' => 'maya@example.com',   'phone' => '081234567809', 'avatar' => 44],
            ['name' => 'Budi Hartono',   'email' => 'budi@example.com',   'phone' => '081234567810', 'avatar' => 50],
        ];

        foreach ($buyers as $b) {
            User::create([
                'name'              => $b['name'],
                'email'             => $b['email'],
                'phone'             => $b['phone'],
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'avatar_url'        => "https://i.pravatar.cc/150?img={$b['avatar']}",
                'bio'               => fake()->optional()->sentence(),
                'is_seller'         => false,
                'is_admin'          => false,
                'is_seller_verified'=> false,
                'balance'           => 0,
                'rating_avg'        => 0,
                'rating_count'      => 0,
            ]);
        }
    }
}
