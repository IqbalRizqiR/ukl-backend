<?php

declare(strict_types=1);

namespace App\Jobs\Withdrawal;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWithdrawalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly string $withdrawalId
    ) {}

    public function handle(): void
    {
        $withdrawal = Withdrawal::find($this->withdrawalId);

        if (! $withdrawal) {
            Log::warning('ProcessWithdrawalJob: Withdrawal not found', [
                'withdrawal_id' => $this->withdrawalId,
            ]);

            return;
        }

        Log::info('ProcessWithdrawalJob: Processing withdrawal', [
            'withdrawal_id' => $withdrawal->id,
            'user_id' => $withdrawal->user_id,
            'amount' => $withdrawal->amount,
            'bank_name' => $withdrawal->bank_name,
            'account_number' => $withdrawal->account_number,
        ]);

        // TODO: Integrate with actual bank transfer API (e.g., Midtrans Payout, Xendit Disbursement)
        // For now, mark as processed
        $withdrawal->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        Log::info('ProcessWithdrawalJob: Withdrawal processed successfully', [
            'withdrawal_id' => $withdrawal->id,
        ]);
    }
}
