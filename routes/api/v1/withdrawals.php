<?php

use App\Http\Controllers\Api\V1\Withdrawal\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('seller')->group(function () {
    Route::get('/withdrawals', [WithdrawalController::class, 'index']);
    Route::post('/withdrawals', [WithdrawalController::class, 'store']);
});
