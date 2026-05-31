<?php

use App\Http\Controllers\Api\V1\Admin\DisputeController;
use App\Http\Controllers\Api\V1\Admin\ProductModerationController;
use App\Http\Controllers\Api\V1\Admin\ReportController;
use App\Http\Controllers\Api\V1\Admin\SellerVerificationController;
use App\Http\Controllers\Api\V1\Admin\UserManagementController;
use App\Http\Controllers\Api\V1\Admin\WithdrawalApprovalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/admin')->middleware('admin')->group(function () {

    // User management
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::post('/users/{userId}/ban', [UserManagementController::class, 'ban']);
    Route::post('/users/{userId}/unban', [UserManagementController::class, 'unban']);

    // Product moderation
    Route::get('/products', [ProductModerationController::class, 'index']);
    Route::delete('/products/{productId}', [ProductModerationController::class, 'destroy']);

    // Disputes
    Route::get('/disputes', [DisputeController::class, 'index']);
    Route::get('/disputes/{dispute}', [DisputeController::class, 'show']);
    Route::post('/disputes/{dispute}/resolve', [DisputeController::class, 'resolve']);

    // Withdrawal approvals
    Route::get('/withdrawals', [WithdrawalApprovalController::class, 'index']);
    Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalApprovalController::class, 'approve']);
    Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalApprovalController::class, 'reject']);

    // Reports
    Route::get('/reports/transactions', [ReportController::class, 'transactions']);
    Route::get('/reports/overview', [ReportController::class, 'overview']);

    // Seller verification
    Route::get('/seller-verifications', [SellerVerificationController::class, 'index']);
    Route::post('/seller-verifications/{userId}/verify', [SellerVerificationController::class, 'verify']);
});
