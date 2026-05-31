<?php

declare(strict_types=1);

namespace App\Services\Shipment;

use App\Services\Shipment\RajaOngkirService;
use RuntimeException;

final class ShippingCostService
{
    public function __construct(
        private readonly RajaOngkirService $rajaOngkirService,
    ) {}

    /**
     * Calculate shipping cost between two cities.
     *
     * @param  array{origin: int, destination: int, weight: int, courier: string}  $data
     * @return array  Shipping cost options from RajaOngkir
     *
     * @throws RuntimeException
     */
    public function calculate(array $data): array
    {
        $origin = $data['origin'];
        $destination = $data['destination'];
        $weight = $data['weight'];
        $courier = $data['courier'];

        if ($weight <= 0) {
            throw new RuntimeException('Berat harus lebih dari 0 gram.');
        }

        $results = $this->rajaOngkirService->calculateCost(
            origin: $origin,
            destination: $destination,
            weight: $weight,
            courier: $courier,
        );

        return $results;
    }

    /**
     * Get all available provinces.
     *
     * @return array
     */
    public function getProvinces(): array
    {
        return $this->rajaOngkirService->getProvinces();
    }

    /**
     * Get all cities in a province.
     *
     * @param  int  $provinceId
     * @return array
     */
    public function getCities(int $provinceId): array
    {
        return $this->rajaOngkirService->getCities($provinceId);
    }
}
