<?php

use App\Http\Controllers\Api\V1\Product\BookmarkController;
use App\Http\Controllers\Api\V1\User\AddressController;
use App\Http\Controllers\Api\V1\User\BankAccountController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Addresses
    Route::apiResource('addresses', AddressController::class)->except(['show']);

    // Bank accounts
    Route::get('/bank-accounts', [BankAccountController::class, 'index']);
    Route::post('/bank-accounts', [BankAccountController::class, 'store']);
    Route::delete('/bank-accounts/{bankAccount}', [BankAccountController::class, 'destroy']);

    // Bookmarks
    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/bookmarks/{product}/toggle', [BookmarkController::class, 'toggle']);
    Route::delete('/bookmarks/{product}', [BookmarkController::class, 'destroy']);
});
