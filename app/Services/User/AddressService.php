<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\UserAddress;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class AddressService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Get all addresses for a user.
     *
     * @param  string  $userId
     * @return Collection
     */
    public function getByUser(string $userId): Collection
    {
        return UserAddress::query()
            ->where('user_id', $userId)
            ->with(['province', 'city'])
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Create a new address.
     *
     * @param  string  $userId
     * @param  array{label: string, recipient_name: string, phone: string, province_id: int, city_id: int, district: string, postal_code: string, full_address: string, is_default?: bool}  $data
     * @return UserAddress
     */
    public function create(string $userId, array $data): UserAddress
    {
        return DB::transaction(function () use ($userId, $data): UserAddress {
            $isDefault = $data['is_default'] ?? false;

            // If setting as default, unset other defaults first
            if ($isDefault) {
                $this->unsetDefaultAddresses($userId);
            }

            // If this is the user's first address, make it default
            $count = UserAddress::where('user_id', $userId)->count();
            if ($count === 0) {
                $isDefault = true;
            }

            return UserAddress::create([
                ...$data,
                'user_id' => $userId,
                'is_default' => $isDefault,
            ]);
        });
    }

    /**
     * Update an existing address.
     *
     * @param  string  $userId
     * @param  string  $addressId
     * @param  array  $data
     * @return UserAddress
     *
     * @throws RuntimeException
     */
    public function update(string $userId, string $addressId, array $data): UserAddress
    {
        $address = UserAddress::where('id', $addressId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return DB::transaction(function () use ($address, $userId, $data): UserAddress {
            if (! empty($data['is_default'])) {
                $this->unsetDefaultAddresses($userId);
            }

            $address->update($data);

            return $address->refresh()->load(['province', 'city']);
        });
    }

    /**
     * Delete an address.
     *
     * @param  string  $userId
     * @param  string  $addressId
     * @return bool
     *
     * @throws RuntimeException
     */
    public function delete(string $userId, string $addressId): bool
    {
        $address = UserAddress::where('id', $addressId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return $address->delete();
    }

    /**
     * Unset all default addresses for a user.
     */
    private function unsetDefaultAddresses(string $userId): void
    {
        UserAddress::where('user_id', $userId)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}
