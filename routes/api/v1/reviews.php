<?php

use App\Http\Controllers\Api\V1\Review\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Create review for an order
    Route::post('/orders/{order}/reviews', [ReviewController::class, 'store']);

    // Seller reply to a review
    Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply'])
        ->middleware('seller');
});
