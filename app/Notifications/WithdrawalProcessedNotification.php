<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WithdrawalProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Withdrawal $withdrawal
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_processed',
            'title' => 'Penarikan Dana Diproses',
            'body' => "Penarikan dana sebesar Rp " . number_format($this->withdrawal->amount) . " telah diproses ke rekening Anda.",
            'withdrawal_id' => $this->withdrawal->id,
            'amount' => $this->withdrawal->amount,
            'bank_name' => $this->withdrawal->bank_name,
            'account_number' => $this->withdrawal->account_number,
            'processed_at' => $this->withdrawal->processed_at?->toISOString(),
        ];
    }
}
