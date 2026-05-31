<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class DisputePolicy
{
    public function create(User $user, Order $order): bool
    {
        return $user->id === $order->buyer_id
            && $order->status === OrderStatus::Delivered;
    }
}
