<?php

use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\User\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function () {

    // Public auth routes
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');

    // Email verification (signed URL from email, no auth needed)
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    // Authenticated auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::get('/me', [ProfileController::class, 'show']);
        Route::put('/me', [ProfileController::class, 'update']);

        // Resend email verification
        Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle:6,1');
    });
});
