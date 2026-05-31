<?php

use App\Http\Controllers\Api\V1\Product\ProductController;
use App\Http\Controllers\Api\V1\Product\ProductImageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public product routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    // Seller product routes (authenticated)
    Route::middleware(['auth:sanctum', 'seller'])->prefix('/seller')->group(function () {
        Route::get('/products', [ProductController::class, 'sellerProducts']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        // Product images
        Route::post('/products/{product}/images', [ProductImageController::class, 'store']);
        Route::delete('/products/{product}/images/{image}', [ProductImageController::class, 'destroy']);
    });
});
