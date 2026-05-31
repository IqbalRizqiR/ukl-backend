<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(
        protected Notification $model
    ) {}

    public function findById(string $id): ?Notification
    {
        return $this->model->find($id);
    }

    public function getByUser(string $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function getUnreadByUser(string $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->latest()
            ->get();
    }

    public function create(array $data): Notification
    {
        return $this->model->create($data);
    }

    public function markAsRead(string $id): ?Notification
    {
        $notification = $this->model->find($id);

        if (! $notification) {
            return null;
        }

        $notification->update(['read_at' => now()]);

        return $notification->fresh();
    }

    public function markAllAsRead(string $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
