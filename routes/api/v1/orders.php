<?php

use App\Http\Controllers\Api\V1\Escrow\EscrowController;
use App\Http\Controllers\Api\V1\Order\OrderController;
use App\Http\Controllers\Api\V1\Payment\PaymentController;
use App\Http\Controllers\Api\V1\Shipment\ShipmentController;
use App\Http\Controllers\Api\V1\Shipment\ShippingCostController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Buyer orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    // Seller orders
    Route::get('/seller/orders', [OrderController::class, 'sellerOrders'])
        ->middleware('seller');

    // Payment
    Route::post('/orders/{order}/pay', [PaymentController::class, 'initiate']);

    // Confirm received (buyer)
    Route::post('/orders/{order}/confirm-received', [EscrowController::class, 'confirmReceived']);

    // Cancel order (buyer)
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Shipping (seller)
    Route::post('/orders/{order}/ship', [ShipmentController::class, 'ship'])
        ->middleware('seller');
    Route::put('/shipments/{shipment}/tracking', [ShipmentController::class, 'updateTracking'])
        ->middleware('seller');
    Route::get('/shipments/{shipment}/track', [ShipmentController::class, 'track']);

    // Shipping cost calculator
    Route::post('/shipping/cost', [ShippingCostController::class, 'calculate']);
});
