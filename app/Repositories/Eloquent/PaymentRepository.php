<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(
        protected Payment $model
    ) {}

    public function findById(string $id): ?Payment
    {
        return $this->model->with(['order'])->find($id);
    }

    public function findByMidtransOrderId(string $midtransOrderId): ?Payment
    {
        return $this->model
            ->with(['order'])
            ->where('midtrans_order_id', $midtransOrderId)
            ->first();
    }

    public function create(array $data): Payment
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): ?Payment
    {
        $payment = $this->model->find($id);

        if (! $payment) {
            return null;
        }

        $payment->update($data);

        return $payment->fresh();
    }

    public function updateStatus(string $id, string $status): ?Payment
    {
        $payment = $this->model->find($id);

        if (! $payment) {
            return null;
        }

        $payment->update(['status' => $status]);

        return $payment->fresh();
    }
}
