<?php

declare(strict_types=1);

namespace App\Services\Shipment;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class RajaOngkirService
{
    private const CACHE_TTL = 86400; // 24 hours

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'key'    => config('rajaongkir.api_key'),
            'Accept' => 'application/json',
        ])->timeout(10);
    }

    /**
     * @throws RuntimeException
     */
    private function handleResponse(Response $response, string $context): array
    {
        if ($response->failed()) {
            $message = $response->json('meta.message') ?? $response->body();

            Log::error("RajaOngkir [{$context}] failed", [
                'status'  => $response->status(),
                'message' => $message,
            ]);

            throw new RuntimeException(
                "RajaOngkir {$context} failed ({$response->status()}): {$message}"
            );
        }

        return $response->json('data', []);
    }

    /**
     * Get list of provinces from RajaOngkir API.
     *
     * @return array<int, array<string, mixed>>
     * @throws RuntimeException|ConnectionException
     */
    public function getProvinces(): array
    {
        return Cache::remember('rajaongkir.provinces', self::CACHE_TTL, function (): array {
            $response = $this->client()
                ->get(config('rajaongkir.base_url') . '/destination/province');

            return $this->handleResponse($response, 'getProvinces');
        });
    }

    /**
     * Search destinations (subdistrict level) by keyword.
     *
     * @return array<int, array<string, mixed>>
     * @throws RuntimeException|ConnectionException
     */
    public function searchDestinations(string $search): array
    {
        if (blank($search)) {
            throw new \InvalidArgumentException('Search keyword must not be empty.');
        }

        return Cache::remember("rajaongkir.destinations.{$search}", self::CACHE_TTL, function () use ($search): array {
            $response = $this->client()
                ->get(config('rajaongkir.base_url') . '/destination/domestic-destination', [
                    'search' => $search,
                    'limit'  => 10,
                    'offset' => 0,
                ]);

            return $this->handleResponse($response, 'searchDestinations');
        });
    }

    /**
     * Calculate shipping cost between two destinations.
     *
     * @param  int     $origin       subdistrict ID
     * @param  int     $destination  subdistrict ID
     * @param  int     $weight       in grams
     * @param  string  $courier
     * @return array<int, array<string, mixed>>
     * @throws RuntimeException|ConnectionException
     */
    public function calculateCost(int $origin, int $destination, int $weight, string $courier): array
    {
        if ($weight <= 0) {
            throw new \InvalidArgumentException('Weight must be greater than 0.');
        }

        if (blank($courier)) {
            throw new \InvalidArgumentException('Courier must not be empty.');
        }

        $response = $this->client()
            ->asForm()
            ->post(config('rajaongkir.base_url') . '/calculate/domestic-cost', [
                'origin'      => $origin,
                'destination' => $destination,
                'weight'      => $weight,
                'courier'     => $courier,
            ]);

        return $this->handleResponse($response, 'calculateCost');
    }

    /**
     * Get waybill (tracking) information from RajaOngkir API.
     *
     * @param  string  $waybill
     * @param  string  $courier
     * @return array<string, mixed>
     * @throws RuntimeException|ConnectionException
     */
    public function trackWaybill(string $waybill, string $courier): array
    {
        if (blank($waybill) || blank($courier)) {
            throw new \InvalidArgumentException('Waybill and courier must not be empty.');
        }

        $response = $this->client()
            ->asForm()
            ->post(config('rajaongkir.base_url') . '/track/waybill', [
                'waybill' => $waybill,
                'courier' => $courier,
            ]);

        // The RajaOngkir waybill endpoint returns a different structure in data,
        // it usually returns an object instead of array of options.
        return $this->handleResponse($response, 'trackWaybill');
    }
}