<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Escrow;
use Illuminate\Database\Eloquent\Collection;

interface EscrowRepositoryInterface
{
    public function findById(string $id): ?Escrow;

    public function findByOrderId(string $orderId): ?Escrow;

    public function create(array $data): Escrow;

    public function update(string $id, array $data): ?Escrow;

    public function updateStatus(string $id, string $status): ?Escrow;

    public function getAutoReleaseDue(): Collection;

    public function getPending(): Collection;
}
