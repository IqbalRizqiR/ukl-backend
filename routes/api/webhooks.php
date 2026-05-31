<?php

use App\Http\Controllers\Api\V1\Payment\MidtransWebhookController;
use App\Http\Middleware\VerifyMidtransSignature;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/midtrans', [MidtransWebhookController::class, 'handle'])
    ->middleware(VerifyMidtransSignature::class);
