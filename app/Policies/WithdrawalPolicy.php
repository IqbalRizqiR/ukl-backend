<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class WithdrawalPolicy
{
    public function create(User $user): bool
    {
        return $user->is_seller && $user->balance > 0;
    }
}
