<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Exceptions\InsufficientBalanceException;
use App\Models\AuditLog;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class BalanceService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Get user's current balance.
     *
     * @param  string  $userId
     * @return float
     */
    public function getBalance(string $userId): float
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Pengguna tidak ditemukan.');
        }

        return (float) $user->balance;
    }

    /**
     * Credit (add) amount to user's balance within a transaction.
     *
     * @param  string  $userId
     * @param  float  $amount
     * @param  string  $description
     * @return User
     */
    public function creditBalance(string $userId, float $amount, string $description): User
    {
        return DB::transaction(function () use ($userId, $amount, $description): User {
            /** @var User $user */
            $user = User::query()->where('id', $userId)->lockForUpdate()->firstOrFail();

            $oldBalance = (float) $user->balance;
            $newBalance = $oldBalance + $amount;

            $user->update(['balance' => $newBalance]);

            AuditLog::create([
                'user_id' => $userId,
                'auditable_type' => User::class,
                'auditable_id' => $userId,
                'event' => 'balance_credit',
                'old_values' => ['balance' => $oldBalance],
                'new_values' => ['balance' => $newBalance],
                'description' => $description,
            ]);

            return $user->refresh();
        });
    }

    /**
     * Debit (subtract) amount from user's balance within a transaction.
     *
     * @param  string  $userId
     * @param  float  $amount
     * @param  string  $description
     * @return User
     *
     * @throws InsufficientBalanceException
     */
    public function debitBalance(string $userId, float $amount, string $description): User
    {
        return DB::transaction(function () use ($userId, $amount, $description): User {
            /** @var User $user */
            $user = User::query()->where('id', $userId)->lockForUpdate()->firstOrFail();

            $oldBalance = (float) $user->balance;

            if ($oldBalance < $amount) {
                throw new InsufficientBalanceException(
                    requiredAmount: (int) $amount,
                    currentBalance: (int) $oldBalance,
                );
            }

            $newBalance = $oldBalance - $amount;

            $user->update(['balance' => $newBalance]);

            AuditLog::create([
                'user_id' => $userId,
                'auditable_type' => User::class,
                'auditable_id' => $userId,
                'event' => 'balance_debit',
                'old_values' => ['balance' => $oldBalance],
                'new_values' => ['balance' => $newBalance],
                'description' => $description,
            ]);

            return $user->refresh();
        });
    }
}
