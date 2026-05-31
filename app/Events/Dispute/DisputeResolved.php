<?php

declare(strict_types=1);

namespace App\Events\Dispute;

use App\Models\Dispute;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisputeResolved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Dispute $dispute
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->dispute->order->seller_id),
            new PrivateChannel('user.' . $this->dispute->order->buyer_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'dispute.resolved';
    }
}
