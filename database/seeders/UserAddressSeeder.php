<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;

class UserAddressSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $provinces = Province::all();
        $cities = City::all();

        // If no provinces/cities exist yet (RajaOngkirSeeder hasn't run), use dummy IDs
        $hasGeo = $provinces->isNotEmpty() && $cities->isNotEmpty();

        foreach ($users as $i => $user) {
            $province = $hasGeo ? $provinces->random() : null;
            $city = $hasGeo ? $cities->where('province_id', $province?->id)->first() ?? $cities->random() : null;

            UserAddress::create([
                'user_id'        => $user->id,
                'label'          => 'Rumah',
                'recipient_name' => $user->name,
                'phone'          => $user->phone ?? fake()->phoneNumber(),
                'province_id'    => $province?->id ?? 6,  // DKI Jakarta fallback
                'city_id'        => $city?->id ?? 152,     // Jakarta Selatan fallback
                'district'       => fake()->randomElement(['Kebayoran Baru', 'Tebet', 'Menteng', 'Setiabudi', 'Tanah Abang', 'Kemang']),
                'postal_code'    => fake()->numerify('#####'),
                'full_address'   => fake()->streetAddress() . ', ' . fake()->randomElement(['RT 05/RW 03', 'RT 02/RW 01', 'RT 10/RW 08']),
                'is_default'     => true,
            ]);

            // Give some users a second address
            if ($i % 2 === 0) {
                $province2 = $hasGeo ? $provinces->random() : null;
                $city2 = $hasGeo ? $cities->where('province_id', $province2?->id)->first() ?? $cities->random() : null;

                UserAddress::create([
                    'user_id'        => $user->id,
                    'label'          => 'Kantor',
                    'recipient_name' => $user->name,
                    'phone'          => $user->phone ?? fake()->phoneNumber(),
                    'province_id'    => $province2?->id ?? 9, // Jawa Barat fallback
                    'city_id'        => $city2?->id ?? 23,     // Bandung fallback
                    'district'       => fake()->randomElement(['Coblong', 'Cicendo', 'Sumur Bandung', 'Dago']),
                    'postal_code'    => fake()->numerify('#####'),
                    'full_address'   => fake()->streetAddress() . ', Gedung Lt. ' . fake()->numberBetween(1, 20),
                    'is_default'     => false,
                ]);
            }
        }
    }
}
