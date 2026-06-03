<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EscrowStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ShipmentCourier;
use App\Models\EscrowTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shipment;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $buyers  = User::where('is_seller', 'false')->where('is_admin', 'false')->get();
        $products = Product::with('seller')->get();

        if ($buyers->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No buyers or products found — skipping OrderSeeder.');
            return;
        }

        $serviceFee = 3000;
        $couriers   = ShipmentCourier::cases();
        $services   = ['REG', 'YES', 'OKE', 'JTR'];

        // Generate 10 orders with varying statuses
        $statuses = [
            OrderStatus::PendingPayment,
            OrderStatus::Paid,
            OrderStatus::Paid,
            OrderStatus::Processing,
            OrderStatus::Shipped,
            OrderStatus::Shipped,
            OrderStatus::Delivered,
            OrderStatus::Completed,
            OrderStatus::Completed,
            OrderStatus::Completed,
        ];

        foreach ($statuses as $i => $status) {
            $buyer   = $buyers->random();
            $product = $products->random();
            $seller  = $product->seller;

            // Prevent buyer buying own product
            if ($buyer->id === $seller->id) {
                $buyer = $buyers->where('id', '!=', $seller->id)->first() ?? $buyers->first();
            }

            $address = UserAddress::where('user_id', $buyer->id)->first();
            if (!$address) continue;

            $shippingCost = fake()->randomElement([9000, 12000, 15000, 18000, 22000]);
            $totalAmount  = (float) $product->price + $shippingCost + $serviceFee;

            $order = Order::create([
                'order_number'        => 'ORD-' . str_pad((string) ($i + 1), 7, '0', STR_PAD_LEFT),
                'buyer_id'            => $buyer->id,
                'seller_id'           => $seller->id,
                'product_id'          => $product->id,
                'shipping_address_id' => $address->id,
                'product_price'       => $product->price,
                'shipping_cost'       => $shippingCost,
                'service_fee'         => $serviceFee,
                'total_amount'        => $totalAmount,
                'status'              => $status->value,
                'notes'               => fake()->optional(0.3)->sentence(),
                'completed_at'        => $status === OrderStatus::Completed ? now()->subDays(rand(1, 7)) : null,
            ]);

            // --- Payment (for all orders that are at least Paid) ---
            if (!in_array($status, [OrderStatus::PendingPayment])) {
                $paymentStatus = match ($status) {
                    OrderStatus::Cancelled                           => PaymentStatus::Cancel,
                    default                                          => PaymentStatus::Settlement,
                };

                Payment::create([
                    'order_id'                 => $order->id,
                    'midtrans_transaction_id'  => 'TXN-' . strtoupper(fake()->bothify('????####')),
                    'midtrans_order_id'        => $order->order_number,
                    'payment_type'             => fake()->randomElement(PaymentType::cases())->value,
                    'gross_amount'             => $totalAmount,
                    'status'                   => $paymentStatus->value,
                    'snap_token'               => fake()->sha256(),
                    'redirect_url'             => null,
                    'midtrans_response'        => null,
                    'paid_at'                  => now()->subDays(rand(1, 14)),
                    'expired_at'               => now()->addDay(),
                ]);
            }

            // --- Escrow (for all orders that are at least Paid) ---
            if (!in_array($status, [OrderStatus::PendingPayment])) {
                $escrowStatus = match ($status) {
                    OrderStatus::Paid, OrderStatus::Processing       => EscrowStatus::Paid,
                    OrderStatus::Shipped                             => EscrowStatus::InDelivery,
                    OrderStatus::Delivered                           => EscrowStatus::Delivered,
                    OrderStatus::Completed                           => EscrowStatus::Completed,
                    default                                          => EscrowStatus::Pending,
                };

                EscrowTransaction::create([
                    'order_id'        => $order->id,
                    'amount'          => $totalAmount,
                    'status'          => $escrowStatus->value,
                    'paid_at'         => now()->subDays(rand(5, 14)),
                    'released_at'     => $status === OrderStatus::Completed ? now()->subDays(rand(1, 3)) : null,
                    'auto_release_at' => now()->addDays(3),
                ]);
            }

            // --- Shipment (for Shipped, Delivered, Completed) ---
            if (in_array($status, [OrderStatus::Shipped, OrderStatus::Delivered, OrderStatus::Completed])) {
                $courier = fake()->randomElement($couriers);

                Shipment::create([
                    'order_id'              => $order->id,
                    'courier'               => $courier->value,
                    'service'               => fake()->randomElement($services),
                    'tracking_number'       => strtoupper(fake()->bothify('??########')),
                    'weight_grams'          => $product->weight_grams ?? 500,
                    'origin_city_id'        => fake()->numberBetween(1, 500),
                    'destination_city_id'   => $address->city_id,
                    'shipping_cost'         => $shippingCost,
                    'estimated_delivery_at' => now()->addDays(rand(2, 5)),
                    'shipped_at'            => now()->subDays(rand(2, 7)),
                    'delivered_at'          => in_array($status, [OrderStatus::Delivered, OrderStatus::Completed]) ? now()->subDays(rand(0, 2)) : null,
                ]);
            }

            // --- Review (only for Completed orders) ---
            if ($status === OrderStatus::Completed) {
                $sellerReply = fake()->optional(0.4)->paragraph();

                Review::create([
                    'order_id'    => $order->id,
                    'reviewer_id' => $buyer->id,
                    'seller_id'   => $seller->id,
                    'product_id'  => $product->id,
                    'rating'      => fake()->numberBetween(3, 5),
                    'comment'     => fake()->randomElement([
                        'Barang sesuai deskripsi, pengiriman cepat!',
                        'Kondisi masih bagus, terima kasih seller!',
                        'Overall puas, packing aman.',
                        'Recommended seller, barang ori.',
                        'Sedikit berbeda dari foto tapi masih oke.',
                    ]),
                    'seller_reply'     => $sellerReply,
                    'seller_replied_at' => $sellerReply ? fake()->dateTimeBetween($order->created_at, 'now') : null,
                ]);
            }
        }
    }
}
