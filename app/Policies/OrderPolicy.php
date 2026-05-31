<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->buyer_id || $user->id === $order->seller_id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->id === $order->buyer_id
            && in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::Paid]);
    }
}
