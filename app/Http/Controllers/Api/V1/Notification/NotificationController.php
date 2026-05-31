<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\Notification\NotificationResource;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = $this->notificationService->getNotifications(
            $request->user()->id,
            $request->integer('per_page', 20),
        );

        return NotificationResource::collection($notifications);
    }

    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $this->notificationService->markAsRead($notificationId);

        return response()->json([
            'message' => 'Notifikasi ditandai sudah dibaca.',
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user()->id);

        return response()->json([
            'message' => 'Semua notifikasi ditandai sudah dibaca.',
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user()->id);

        return response()->json([
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }
}
