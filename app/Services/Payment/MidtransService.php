<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Exceptions\PaymentException;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class MidtransService
{
    /**
     * Create a Snap token for an order via Midtrans API.
     *
     * @param  Order  $order
     * @return array{token: string, redirect_url: string}
     *
     * @throws PaymentException
     */
    public function createSnapToken(Order $order): array
    {
        $serverKey = config('midtrans.server_key');
        $snapUrl = config('midtrans.snap_url');

        $midtransOrderId = 'RWR-' . $order->order_number . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->buyer->name ?? 'Customer',
                'email' => $order->buyer->email ?? '',
                'phone' => $order->buyer->phone ?? '',
            ],
            'item_details' => [
                [
                    'id' => $order->product_id,
                    'price' => (int) $order->product_price,
                    'quantity' => 1,
                    'name' => mb_substr($order->product->name ?? 'Product', 0, 50),
                ],
                [
                    'id' => 'SHIPPING',
                    'price' => (int) $order->shipping_cost,
                    'quantity' => 1,
                    'name' => 'Shipping Cost',
                ],
                [
                    'id' => 'SERVICE_FEE',
                    'price' => (int) $order->service_fee,
                    'quantity' => 1,
                    'name' => 'Service Fee',
                ],
            ],
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->post($snapUrl, $params);

        if (! $response->successful()) {
            Log::error('Midtrans Snap API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $order->id,
            ]);

            throw PaymentException::gatewayError(
                'Failed to create payment token: ' . $response->body()
            );
        }

        $data = $response->json();

        return [
            'token' => $data['token'] ?? '',
            'redirect_url' => $data['redirect_url'] ?? '',
        ];
    }

    /**
     * Verify Midtrans webhook signature using SHA-512 hash.
     *
     * @param  array<string, mixed>  $payload
     * @return bool
     */
    public function verifySignature(array $payload): bool
    {
        $serverKey = config('midtrans.server_key');

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }
}
