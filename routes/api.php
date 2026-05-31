<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| rewear.id API v1 routes
|
*/

// Public routes
require __DIR__ . '/api/v1/auth.php';
require __DIR__ . '/api/v1/discovery.php';
require __DIR__ . '/api/v1/products.php';

// Webhook routes (no auth)
require __DIR__ . '/api/webhooks.php';

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    require __DIR__ . '/api/v1/users.php';
    require __DIR__ . '/api/v1/orders.php';
    require __DIR__ . '/api/v1/chat.php';
    require __DIR__ . '/api/v1/reviews.php';
    require __DIR__ . '/api/v1/withdrawals.php';
    require __DIR__ . '/api/v1/notifications.php';
    require __DIR__ . '/api/v1/admin.php';
});
