<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\WithdrawalStatus;
use App\Models\Withdrawal;
use App\Repositories\Contracts\WithdrawalRepositoryInterface;
use App\Services\User\BalanceService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class WithdrawalApprovalService
{
    public function __construct(
        private readonly WithdrawalRepositoryInterface $withdrawalRepository,
        private readonly BalanceService $balanceService,
    ) {}

    /**
     * Get all pending withdrawals for admin review.
     *
     * @param  array{status?: string}  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Withdrawal::query()
            ->with(['user', 'bankAccount'])
            ->orderBy('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', WithdrawalStatus::from($filters['status']));
        } else {
            $query->where('status', WithdrawalStatus::Pending);
        }

        return $query->paginate($perPage);
    }

    /**
     * Approve a withdrawal request.
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
     * Reject a withdrawal request and refund the balance.
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

            // Refund full amount back to user balance
            $this->balanceService->creditBalance(
                $withdrawal->user_id,
                (float) $withdrawal->amount,
                "Refund penarikan ditolak: {$reason}",
            );

            return $withdrawal->refresh()->load(['user', 'bankAccount']);
        });
    }
}
