<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Events\Order\OrderCancelled;
use App\Events\Order\OrderCreated;
use App\Exceptions\OrderException;
use App\Models\EscrowTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shipment;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * Create a new order with escrow and update product status.
     *
     * @param  string  $buyerId
     * @param  array<string, mixed>  $data
     * @return Order
     *
     * @throws OrderException
     */
    public function create(string $buyerId, array $data): Order
    {
        return DB::transaction(function () use ($buyerId, $data): Order {
            /** @var Product $product */
            $product = $this->productRepository->findById($data['product_id']);

            if (! $product || $product->status !== ProductStatus::Active) {
                throw OrderException::productUnavailable();
            }

            if ($product->seller_id === $buyerId) {
                throw OrderException::cannotPurchaseOwnProduct();
            }

            $productPrice = (float) $product->price;
            $shippingCost = (float) ($data['shipping_cost'] ?? 0);

            $serviceFee = max(
                $productPrice * (float) config('escrow.service_fee_percentage') / 100,
                (float) config('escrow.minimum_service_fee'),
            );

            $totalAmount = $productPrice + $shippingCost + $serviceFee;

            $order = $this->orderRepository->create([
                'order_number' => 'ORD-' . strtoupper(Str::random(12)),
                'buyer_id' => $buyerId,
                'seller_id' => $product->seller_id,
                'product_id' => $product->id,
                'shipping_address_id' => $data['shipping_address_id'],
                'product_price' => $productPrice,
                'shipping_cost' => $shippingCost,
                'service_fee' => $serviceFee,
                'total_amount' => $totalAmount,
                'status' => OrderStatus::PendingPayment,
                'notes' => $data['notes'] ?? null,
            ]);

            Shipment::create([
                'order_id' => $order->id,
                'courier' => $data['courier'] ?? null,
                'service' => $data['service'] ?? null,
                'estimated_delivery_at' => $data['estimated_delivery_at'] ?? null,
                'weight_grams' => $product->weight_grams ?? 1000,
                'origin_city_id' => $order->seller->defaultAddress?->city_id ?? throw new \Exception('Seller has no origin city.'),
                'shipping_cost' => $shippingCost,
                'destination_city_id' => $order->shippingAddress?->city_id ?? throw new \Exception('No shipping address provided.'),
            ]);

            EscrowTransaction::create([
                'order_id' => $order->id,
                'amount' => $totalAmount,
                'status' => EscrowStatus::Pending,
            ]);

            $this->productRepository->update($product->id, [
                'status' => ProductStatus::Sold,
            ]);

            event(new OrderCreated($order));

            return $order->load(['product', 'escrow', 'buyer', 'seller']);
        });
    }

    /**
     * Get orders by buyer, paginated.
     *
     * @param  string  $buyerId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByBuyer(string $buyerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->getByBuyer($buyerId, $perPage);
    }

    /**
     * Get orders by seller, paginated.
     *
     * @param  string  $sellerId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getBySeller(string $sellerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->getBySeller($sellerId, $perPage);
    }

    /**
     * Show order details.
     *
     * @param  string  $orderId
     * @return Order
     *
     * @throws ModelNotFoundException
     */
    public function show(string $orderId): Order
    {
        $order = $this->orderRepository->findById($orderId);

        if (! $order) {
            throw new ModelNotFoundException('Pesanan tidak ditemukan.');
        }

        return $order->load(['product.images', 'escrow', 'payment', 'shipment', 'buyer', 'seller', 'shippingAddress']);
    }

    /**
     * Cancel an order, restore product status, and dispatch event.
     *
     * @param  string  $orderId
     * @param  string  $cancelledBy
     * @return Order
     *
     * @throws OrderException
     * @throws ModelNotFoundException
     */
    public function cancel(string $orderId, string $cancelledBy): Order
    {
        return DB::transaction(function () use ($orderId, $cancelledBy): Order {
            $order = $this->orderRepository->findById($orderId);

            if (! $order) {
                throw new ModelNotFoundException('Pesanan tidak ditemukan.');
            }

            if ($order->status !== OrderStatus::PendingPayment) {
                throw OrderException::cannotCancel();
            }

            $order = $this->orderRepository->update($orderId, [
                'status' => OrderStatus::Cancelled,
                'cancelled_by' => $cancelledBy,
                'cancelled_at' => now(),
            ]);

            // Restore product to active
            $this->productRepository->update($order->product_id, [
                'status' => ProductStatus::Active,
            ]);

            // Cancel the escrow
            $escrow = $order->escrow;
            if ($escrow) {
                $escrow->update([
                    'status' => EscrowStatus::Expired,
                ]);
            }

            event(new OrderCancelled($order));

            return $order;
        });
    }
}
