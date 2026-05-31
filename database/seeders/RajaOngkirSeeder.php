<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RajaOngkirSeeder extends Seeder
{
    /**
     * Seed provinces and cities from RajaOngkir API.
     */
    public function run(): void
    {
        $apiKey = config('rajaongkir.api_key');
        $baseUrl = config('rajaongkir.base_url');

        if (empty($apiKey)) {
            $this->command->error('RAJAONGKIR_API_KEY belum diatur di .env');
            return;
        }

        $this->command->info('Mengambil data provinsi dari RajaOngkir...');

        // ─── Fetch & Seed Provinces ──────────────────────────────────────

        $response = Http::withHeaders(['Key' => $apiKey])
            ->get("{$baseUrl}/destination/province");

        if ($response->failed()) {
            $this->command->error('Gagal mengambil data provinsi: ' . $response->body());
            return;
        }


        $provinces = $response->json('data', []);

        if (empty($provinces)) {
            $this->command->error('Data provinsi kosong.');
            return;
        }

        $this->command->info("Ditemukan " . count($provinces) . " provinsi. Menyimpan...");

        $now = now();

        DB::table('provinces')->truncate();

        foreach ($provinces as $province) {
            DB::table('provinces')->insert([
                'id' => (int) $province['id'],
                'name' => $province['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('✅ Provinsi berhasil disimpan.');

        // ─── Fetch & Seed Cities ─────────────────────────────────────────

        $this->command->info('Mengambil data kota dari RajaOngkir...');


        $provinceIds = DB::table('provinces')->pluck('id')->toArray();

        foreach ($provinceIds as $provinceId) {
            $response = Http::withHeaders(['Key' => $apiKey])
                ->get("{$baseUrl}/destination/city/{$provinceId}");

            if ($response->failed()) {
                $this->command->error("Gagal mengambil data kota untuk provinsi ID {$provinceId}: " . $response->body());
                continue;
            }
            $cities = $response->json('data', []);
            $this->command->info("Ditemukan " . count($cities) . " kota/kabupaten. Menyimpan...");
            $chunks = array_chunk($cities, 50);

            foreach ($chunks as $chunk) {
                $rows = [];
                foreach ($chunk as $city) {
                    $rows[] = [
                        'id' => (int) $city['id'],
                        'province_id' => (int) $provinceId,
                        'name' => $city['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('cities')->insert($rows);
            }

        }

        // Insert in chunks for performance


        $this->command->info('✅ Kota/kabupaten berhasil disimpan.');
    }
}
