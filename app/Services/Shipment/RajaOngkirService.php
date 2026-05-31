<?php

declare(strict_types=1);

namespace App\Services\Shipment;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class RajaOngkirService
{
    private const CACHE_TTL = 86400; // 24 hours

    /**
     * Get list of provinces from RajaOngkir API.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getProvinces(): array
    {
        return Cache::remember('rajaongkir.provinces', self::CACHE_TTL, function (): array {
            $response = Http::withHeaders([
                'key' => config('rajaongkir.api_key'),
            ])->get(config('rajaongkir.base_url') . '/province');

            return $response->json('rajaongkir.results', []);
        });
    }

    /**
     * Get list of cities for a province from RajaOngkir API.
     *
     * @param  int  $provinceId
     * @return array<int, array<string, mixed>>
     */
    public function getCities(int $provinceId): array
    {
        return Cache::remember("rajaongkir.cities.{$provinceId}", self::CACHE_TTL, function () use ($provinceId): array {
            $response = Http::withHeaders([
                'key' => config('rajaongkir.api_key'),
            ])->get(config('rajaongkir.base_url') . '/city', [
                'province' => $provinceId,
            ]);

            return $response->json('rajaongkir.results', []);
        });
    }

    /**
     * Calculate shipping cost between two cities.
     *
     * @param  int  $origin
     * @param  int  $destination
     * @param  int  $weight  in grams
     * @param  string  $courier
     * @return array<int, array<string, mixed>>
     */
    public function calculateCost(int $origin, int $destination, int $weight, string $courier): array
    {
        $response = Http::withHeaders([
            'key' => config('rajaongkir.api_key'),
        ])->post(config('rajaongkir.base_url') . '/cost', [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier,
        ]);

        return $response->json('rajaongkir.results', []);
    }
}
