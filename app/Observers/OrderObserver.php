<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderObserver
{
    public function creating(Order $order): void
    {
        $order->order_number = $this->generateOrderNumber();
    }

    protected function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(5));

        $orderNumber = "RW-{$date}-{$random}";

        // Ensure uniqueness
        while (Order::withTrashed()->where('order_number', $orderNumber)->exists()) {
            $random = strtoupper(Str::random(5));
            $orderNumber = "RW-{$date}-{$random}";
        }

        return $orderNumber;
    }
}
