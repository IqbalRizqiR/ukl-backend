<?php

declare(strict_types=1);

namespace App\Jobs\Notification;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly string $userId,
        public readonly string $type,
        public readonly string $title,
        public readonly string $body,
        public readonly array $data = [],
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        DB::table('notifications')->insert([
            'id' => (string) \Illuminate\Support\Str::ulid(),
            'type' => $this->type,
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => $this->title,
                'body' => $this->body,
                ...$this->data,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
