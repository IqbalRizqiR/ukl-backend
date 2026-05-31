<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Payment;

interface PaymentRepositoryInterface
{
    public function findById(string $id): ?Payment;

    public function findByMidtransOrderId(string $midtransOrderId): ?Payment;

    public function create(array $data): Payment;

    public function update(string $id, array $data): ?Payment;

    public function updateStatus(string $id, string $status): ?Payment;
}
