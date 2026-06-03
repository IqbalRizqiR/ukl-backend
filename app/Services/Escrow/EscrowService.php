<?php

declare(strict_types=1);

namespace App\Services\Escrow;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Events\Escrow\EscrowFrozen;
use App\Events\Escrow\EscrowFunded;
use App\Events\Escrow\EscrowReleased;
use App\Events\Order\OrderCompleted;
use App\Events\Order\OrderPaid;
use App\Exceptions\EscrowException;
use App\Models\EscrowTransaction;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\User\BalanceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class EscrowService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly BalanceService $balanceService,
    ) {}

    /**
     * Fund the escrow: mark as paid.
     *
     * @param  string  $orderId
     * @return EscrowTransaction
     *
     * @throws EscrowException
     */
    public function fund(string $orderId): EscrowTransaction
    {
        return DB::transaction(function () use ($orderId): EscrowTransaction {
            $escrow = $this->getEscrowByOrderId($orderId);

            if ($escrow->status !== EscrowStatus::Pending) {
                throw EscrowException::invalidTransition(
                    $escrow->status->value,
                    EscrowStatus::Paid->value,
                );
            }

            $escrow->update([
                'status' => EscrowStatus::Paid,
                'paid_at' => now(),
            ]);

            $this->orderRepository->updateStatus($escrow->order_id, OrderStatus::Paid);

            event(new EscrowFunded($escrow));
            event(new OrderPaid($escrow->order));

            return $escrow->refresh();
        });
    }

    /**
     * Release escrow funds to the seller.
     *
     * @param  string  $orderId
     * @return EscrowTransaction
     *
     * @throws EscrowException
     */
    public function release(string $orderId): EscrowTransaction
    {
        return DB::transaction(function () use ($orderId): EscrowTransaction {
            $escrow = $this->getEscrowByOrderId($orderId);

            $releasableStatuses = [
                EscrowStatus::Delivered,
                EscrowStatus::AutoCompleted,
            ];

            if (! in_array($escrow->status, $releasableStatuses, true)) {
                throw EscrowException::invalidTransition(
                    $escrow->status->value,
                    EscrowStatus::Completed->value,
                );
            }

            $escrow->update([
                'status' => EscrowStatus::Completed,
                'released_at' => now(),
            ]);

            $order = $escrow->order;

            // Credit seller balance (total_amount minus service_fee goes to seller)
            $sellerAmount = (float) $escrow->amount - (float) $order->service_fee;
            $this->balanceService->creditBalance(
                $order->seller_id,
                $sellerAmount,
                "Escrow released for order #{$order->order_number}",
            );

            $this->orderRepository->updateStatus($order->id, OrderStatus::Completed);
            $order->update(['completed_at' => now()]);

            event(new EscrowReleased($escrow));
            event(new OrderCompleted($order->refresh()));

            return $escrow->refresh();
        });
    }

    /**
     * Freeze escrow for dispute.
     *
     * @param  string  $orderId
     * @return EscrowTransaction
     *
     * @throws EscrowException
     */
    public function freeze(string $orderId): EscrowTransaction
    {
        return DB::transaction(function () use ($orderId): EscrowTransaction {
            $escrow = $this->getEscrowByOrderId($orderId);

            $freezableStatuses = [
                EscrowStatus::Paid,
                EscrowStatus::InDelivery,
                EscrowStatus::Delivered,
            ];

            if (! in_array($escrow->status, $freezableStatuses, true)) {
                throw EscrowException::invalidTransition(
                    $escrow->status->value,
                    EscrowStatus::Disputed->value,
                );
            }

            $escrow->update([
                'status' => EscrowStatus::Disputed,
                'frozen_at' => now(),
            ]);

            $this->orderRepository->updateStatus($escrow->order_id, OrderStatus::Disputed);

            event(new EscrowFrozen($escrow));

            return $escrow->refresh();
        });
    }

    /**
     * Auto-complete an escrow if still in Delivered status.
     *
     * @param  string  $escrowId
     * @return EscrowTransaction
     *
     * @throws EscrowException
     */
    public function autoComplete(string $escrowId): EscrowTransaction
    {
        return DB::transaction(function () use ($escrowId): EscrowTransaction {
            $escrow = EscrowTransaction::findOrFail($escrowId);

            if ($escrow->status !== EscrowStatus::Delivered) {
                throw EscrowException::invalidTransition(
                    $escrow->status->value,
                    EscrowStatus::AutoCompleted->value,
                );
            }

            $escrow->update([
                'status' => EscrowStatus::AutoCompleted,
            ]);

            // Release funds via the release method using the order_id
            return $this->release($escrow->order_id);
        });
    }

    /**
     * Confirm receipt by buyer and release escrow immediately.
     *
     * @param  string  $orderId
     * @param  string  $buyerId
     * @return EscrowTransaction
     *
     * @throws EscrowException
     */
    public function confirmReceived(string $orderId, string $buyerId): EscrowTransaction
    {
        return DB::transaction(function () use ($orderId, $buyerId): EscrowTransaction {
            $order = $this->orderRepository->findById($orderId);

            if (! $order) {
                throw new ModelNotFoundException('Pesanan tidak ditemukan.');
            }

            if ($order->buyer_id !== $buyerId) {
                throw new EscrowException('Anda tidak berwenang mengkonfirmasi pesanan ini.');
            }

            if (! in_array($order->status, [OrderStatus::Shipped, OrderStatus::Delivered], true)) {
                throw new EscrowException('Pesanan harus dalam status dikirim atau diterima untuk mengkonfirmasi penerimaan.');
            }

            $escrow = $this->getEscrowByOrderId($orderId);

            if (! in_array($escrow->status, [EscrowStatus::InDelivery, EscrowStatus::Delivered], true)) {
                throw EscrowException::invalidTransition(
                    $escrow->status->value,
                    EscrowStatus::Completed->value,
                );
            }

            // If we are still in "Shipped", let's update it to "Delivered" first
            // to maintain a correct state flow before releasing.
            if ($escrow->status === EscrowStatus::InDelivery) {
                $escrow->update(['status' => EscrowStatus::Delivered]);
                $this->orderRepository->updateStatus($orderId, OrderStatus::Delivered);
            }

            return $this->release($orderId);
        });
    }

    /**
     * Refund escrow for disputed orders.
     *
     * @param  string  $orderId
     * @return EscrowTransaction
     *
     * @throws EscrowException
     */
    public function refund(string $orderId): EscrowTransaction
    {
        return DB::transaction(function () use ($orderId): EscrowTransaction {
            $escrow = $this->getEscrowByOrderId($orderId);

            if ($escrow->status !== EscrowStatus::Disputed) {
                throw EscrowException::invalidTransition(
                    $escrow->status->value,
                    EscrowStatus::Refunded->value,
                );
            }

            $escrow->update([
                'status' => EscrowStatus::Refunded,
                'released_at' => now(),
            ]);

            $order = $escrow->order;

            // Refund buyer's balance
            $this->balanceService->creditBalance(
                $order->buyer_id,
                (float) $escrow->amount,
                "Escrow refund for order #{$order->order_number}",
            );

            $this->orderRepository->updateStatus($order->id, OrderStatus::Refunded);

            return $escrow->refresh();
        });
    }

    /**
     * Find EscrowTransaction by order ID or throw exception.
     *
     * @param  string  $orderId
     * @return EscrowTransaction
     *
     * @throws EscrowException
     */
    private function getEscrowByOrderId(string $orderId): EscrowTransaction
    {
        $escrow = EscrowTransaction::where('order_id', $orderId)->first();

        if (! $escrow) {
            throw EscrowException::notFound();
        }

        return $escrow;
    }
}
