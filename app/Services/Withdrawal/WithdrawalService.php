<?php

declare(strict_types=1);

namespace App\Services\Withdrawal;

use App\Enums\WithdrawalStatus;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Withdrawal;
use App\Repositories\Contracts\WithdrawalRepositoryInterface;
use App\Services\User\BalanceService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class WithdrawalService
{
    public function __construct(
        private readonly WithdrawalRepositoryInterface $withdrawalRepository,
        private readonly BalanceService $balanceService,
    ) {}

    /**
     * Request a withdrawal.
     *
     * @param  string  $userId
     * @param  array{bank_account_id: string, amount: float}  $data
     * @return Withdrawal
     *
     * @throws InsufficientBalanceException
     * @throws RuntimeException
     */
    public function request(string $userId, array $data): Withdrawal
    {
        $amount = (float) $data['amount'];
        $minimumAmount = (float) config('escrow.withdrawal.minimum_amount', 10000);

        if ($amount < $minimumAmount) {
            throw new RuntimeException("Minimal penarikan adalah Rp " . number_format($minimumAmount, 0, ',', '.'));
        }

        $fee = (float) config('escrow.withdrawal.fee', 0);
        $netAmount = $amount - $fee;

        if ($netAmount <= 0) {
            throw new RuntimeException('Jumlah penarikan tidak valid setelah dipotong biaya.');
        }

        return DB::transaction(function () use ($userId, $data, $amount, $fee, $netAmount): Withdrawal {
            // Debit balance immediately (will throw InsufficientBalanceException if not enough)
            $this->balanceService->debitBalance(
                $userId,
                $amount,
                'Penarikan dana',
            );

            $withdrawal = $this->withdrawalRepository->create([
                'user_id' => $userId,
                'bank_account_id' => $data['bank_account_id'],
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'status' => WithdrawalStatus::Pending,
            ]);

            return $withdrawal->load('bankAccount');
        });
    }

    /**
     * Admin approves a withdrawal.
     *
     * @param  string  $withdrawalId
     * @return Withdrawal
     *
     * @throws RuntimeException
     */
    public function approve(string $withdrawalId): Withdrawal
    {
        $withdrawal = $this->withdrawalRepository->findById($withdrawalId);

        if (! $withdrawal) {
            throw new RuntimeException('Penarikan tidak ditemukan.');
        }

        if ($withdrawal->status !== WithdrawalStatus::Pending) {
            throw new RuntimeException('Penarikan tidak dalam status menunggu.');
        }

        $this->withdrawalRepository->update($withdrawalId, [
            'status' => WithdrawalStatus::Completed,
            'processed_at' => now(),
        ]);

        return $withdrawal->refresh()->load(['user', 'bankAccount']);
    }

    /**
     * Admin rejects a withdrawal and refunds the balance.
     *
     * @param  string  $withdrawalId
     * @param  string  $reason
     * @return Withdrawal
     *
     * @throws RuntimeException
     */
    public function reject(string $withdrawalId, string $reason): Withdrawal
    {
        $withdrawal = $this->withdrawalRepository->findById($withdrawalId);

        if (! $withdrawal) {
            throw new RuntimeException('Penarikan tidak ditemukan.');
        }

        if ($withdrawal->status !== WithdrawalStatus::Pending) {
            throw new RuntimeException('Penarikan tidak dalam status menunggu.');
        }

        return DB::transaction(function () use ($withdrawal, $withdrawalId, $reason): Withdrawal {
            $this->withdrawalRepository->update($withdrawalId, [
                'status' => WithdrawalStatus::Rejected,
                'rejection_reason' => $reason,
                'rejected_at' => now(),
            ]);

            // Refund the full amount back to user balance
            $this->balanceService->creditBalance(
                $withdrawal->user_id,
                (float) $withdrawal->amount,
                "Refund penarikan ditolak: {$reason}",
            );

            return $withdrawal->refresh()->load(['user', 'bankAccount']);
        });
    }

    /**
     * Get paginated withdrawals for a user.
     *
     * @param  string  $userId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getUserWithdrawals(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Withdrawal::query()
            ->where('user_id', $userId)
            ->with('bankAccount')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get all pending withdrawals (admin).
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getPendingWithdrawals(int $perPage = 15): LengthAwarePaginator
    {
        return Withdrawal::query()
            ->where('status', WithdrawalStatus::Pending)
            ->with(['user', 'bankAccount'])
            ->orderBy('created_at')
            ->paginate($perPage);
    }
}
