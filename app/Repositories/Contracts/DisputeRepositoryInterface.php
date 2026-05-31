<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Dispute;
use Illuminate\Database\Eloquent\Collection;

interface DisputeRepositoryInterface
{
    public function findById(string $id): ?Dispute;

    public function findByOrderId(string $orderId): ?Dispute;

    public function getOpen(): Collection;

    public function create(array $data): Dispute;

    public function update(string $id, array $data): ?Dispute;

    public function updateStatus(string $id, string $status): ?Dispute;
}
