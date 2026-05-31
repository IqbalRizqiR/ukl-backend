<?php

use App\Http\Controllers\Api\V1\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/read-all', [NotificationController::class, 'markAllAsRead']);
});
