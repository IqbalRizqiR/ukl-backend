<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Exceptions\PaymentException;
use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\Escrow\EscrowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class MidtransWebhookService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly EscrowService $escrowService,
        private readonly MidtransService $midtransService,
    ) {}

    /**
     * Handle incoming Midtrans webhook notification.
     *
     * @param  array{order_id: string, transaction_status: string, fraud_status?: string, payment_type?: string, transaction_id?: string, status_code: string, gross_amount: string, signature_key: string}  $payload
     * @return Payment
     *
     * @throws PaymentException
     */
    public function handle(array $payload): Payment
    {
        if (! $this->midtransService->verifySignature($payload)) {
            throw PaymentException::invalidSignature();
        }

        $payment = $this->paymentRepository->findByMidtransOrderId($payload['order_id']);

        if (! $payment) {
            // Midtrans Dashboard sends a test webhook with order_id starting with "payment_notif_test_"
            if (str_starts_with($payload['order_id'], 'payment_notif_test_')) {
                Log::info('Midtrans webhook: received test ping from dashboard', ['order_id' => $payload['order_id']]);
                // Return a dummy payment or throw a specific exception to return 200?
                // The method expects a Payment return type, but returning null would break it.
                // We can't return a Payment, so we should throw a custom exception that is caught and returns 200.
                // Actually, the simplest fix is in the Controller, but since we are here, we can throw a specific exception.
                throw new \RuntimeException('MIDTRANS_TEST_PING');
            }

            Log::warning('Midtrans webhook: payment not found', ['order_id' => $payload['order_id']]);
            throw new PaymentException('Pembayaran tidak ditemukan untuk order: ' . $payload['order_id']);
        }

        $transactionStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        return DB::transaction(function () use ($payment, $transactionStatus, $fraudStatus, $payload): Payment {
            $newStatus = match ($transactionStatus) {
                'capture' => $fraudStatus === 'accept' ? PaymentStatus::Settlement : PaymentStatus::Deny,
                'settlement' => PaymentStatus::Settlement,
                'pending' => PaymentStatus::Pending,
                'deny' => PaymentStatus::Deny,
                'cancel' => PaymentStatus::Cancel,
                'expire' => PaymentStatus::Expire,
                'refund' => PaymentStatus::Refund,
                default => null,
            };

            if ($newStatus === null) {
                Log::warning('Midtrans webhook: unknown transaction status', ['status' => $transactionStatus]);
                return $payment;
            }

            $updateData = [
                'status' => $newStatus,
                'payment_type' => $payload['payment_type'] ?? null,
                'midtrans_transaction_id' => $payload['transaction_id'] ?? null,
                'midtrans_response' => $payload,
            ];

            if (in_array($newStatus, [PaymentStatus::Settlement, PaymentStatus::Capture], true)) {
                $updateData['paid_at'] = now();
            }

            if ($newStatus === PaymentStatus::Expire) {
                $updateData['expired_at'] = now();
            }

            $this->paymentRepository->update($payment->id, $updateData);

            // If payment successful, fund escrow
            if (in_array($newStatus, [PaymentStatus::Settlement, PaymentStatus::Capture], true)) {
                $this->escrowService->fund($payment->order_id);
            }

            return $payment->refresh();
        });
    }
}
