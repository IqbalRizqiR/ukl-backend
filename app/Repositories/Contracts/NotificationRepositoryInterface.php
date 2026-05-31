<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

interface NotificationRepositoryInterface
{
    public function findById(string $id): ?Notification;

    public function getByUser(string $userId): Collection;

    public function getUnreadByUser(string $userId): Collection;

    public function create(array $data): Notification;

    public function markAsRead(string $id): ?Notification;

    public function markAllAsRead(string $userId): int;
}
