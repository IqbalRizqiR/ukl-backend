<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RajaOngkirSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            UserAddressSeeder::class,
            UserBankAccountSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
        ]);
    }
}

