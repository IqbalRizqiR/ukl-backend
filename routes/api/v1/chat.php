<?php

use App\Http\Controllers\Api\V1\Chat\ConversationController;
use App\Http\Controllers\Api\V1\Chat\MessageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);

    // Messages
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
});
