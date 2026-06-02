<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Exceptions\PaymentException;
use App\Models\Order;
use App\Models\Payment;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\Escrow\EscrowService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class PaymentService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly MidtransService $midtransService,
        private readonly EscrowService $escrowService,
    ) {}

    /**
     * Initiate a payment for an order via Midtrans Snap.
     *
     * @param  string  $orderId
     * @return Payment
     *
     * @throws ModelNotFoundException
     * @throws PaymentException
     */
    public function initiate(string $orderId): Payment
    {
        $order = $this->orderRepository->findById($orderId);

        if (! $order) {
            throw new ModelNotFoundException('Pesanan tidak ditemukan.');
        }

        // Check if there's a pending payment with a valid snap token
        $existingPayment = $order->payment()
            ->where('status', PaymentStatus::Pending)
            ->whereNotNull('snap_token')
            ->where('expired_at', '>', now())
            ->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        $order->load(['buyer', 'product']);

        $snapResult = $this->midtransService->createSnapToken($order);

        $midtransOrderId = 'RWR-' . $order->order_number . '-' . time();

        return $this->paymentRepository->create([
            'order_id' => $order->id,
            'midtrans_order_id' => $midtransOrderId,
            'gross_amount' => $order->total_amount,
            'status' => PaymentStatus::Pending,
            'snap_token' => $snapResult['token'],
            'redirect_url' => $snapResult['redirect_url'],
            'expired_at' => now()->addHours(24),
        ]);
    }

    /**
     * Handle Midtrans webhook/notification callback.
     *
     * @param  array<string, mixed>  $payload
     * @return Payment
     *
     * @throws PaymentException
     */
    public function handleWebhook(array $payload): Payment
    {
        if (! $this->midtransService->verifySignature($payload)) {
            throw PaymentException::invalidSignature();
        }

        $midtransOrderId = $payload['order_id'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        $payment = $this->paymentRepository->findByMidtransOrderId($midtransOrderId);

        if (! $payment) {
            throw new ModelNotFoundException('Pembayaran tidak ditemukan.');
        }

        $newStatus = $this->mapTransactionStatus($transactionStatus, $fraudStatus);

        return DB::transaction(function () use ($payment, $newStatus, $payload): Payment {
            $payment = $this->paymentRepository->update($payment->id, [
                'status' => $newStatus,
                'payment_type' => $payload['payment_type'] ?? null,
                'midtrans_response' => $payload,
                'paid_at' => in_array($newStatus, [PaymentStatus::Settlement, PaymentStatus::Capture], true)
                    ? now()
                    : $payment->paid_at,
            ]);

            // If payment successful, fund escrow
            if (in_array($newStatus, [PaymentStatus::Settlement, PaymentStatus::Capture], true)) {
                $this->escrowService->fund($payment->order_id);
            }

            return $payment;
        });
    }

    /**
     * Map Midtrans transaction_status to PaymentStatus enum.
     *
     * @param  string  $transactionStatus
     * @param  string  $fraudStatus
     * @return PaymentStatus
     */
    private function mapTransactionStatus(string $transactionStatus, string $fraudStatus): PaymentStatus
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? PaymentStatus::Capture : PaymentStatus::Deny,
            'settlement' => PaymentStatus::Settlement,
            'pending' => PaymentStatus::Pending,
            'deny' => PaymentStatus::Deny,
            'cancel' => PaymentStatus::Cancel,
            'expire' => PaymentStatus::Expire,
            'refund', 'partial_refund' => PaymentStatus::Refund,
            default => PaymentStatus::Pending,
        };
    }
}
