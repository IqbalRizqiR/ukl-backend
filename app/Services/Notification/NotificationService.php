<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

final class NotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepository,
    ) {}

    /**
     * Send a notification to a user.
     *
     * @param  string  $userId
     * @param  string  $type
     * @param  string  $title
     * @param  string  $body
     * @param  array<string, mixed>|null  $data
     * @return Notification
     */
    public function send(
        string $userId,
        string $type,
        string $title,
        string $body,
        ?array $data = null,
    ): Notification {
        return $this->notificationRepository->create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }

    /**
     * Get paginated notifications for a user.
     *
     * @param  string  $userId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getNotifications(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get only unread notifications.
     *
     * @param  string  $userId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getUnread(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get unread notification count.
     *
     * @param  string  $userId
     * @return int
     */
    public function getUnreadCount(string $userId): int
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Mark a single notification as read.
     *
     * @param  string  $notificationId
     * @return Notification
     */
    public function markAsRead(string $notificationId): Notification
    {
        $notification = Notification::findOrFail($notificationId);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return $notification;
    }

    /**
     * Mark all notifications as read for a user.
     *
     * @param  string  $userId
     * @return int  Number of notifications marked as read
     */
    public function markAllAsRead(string $userId): int
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
