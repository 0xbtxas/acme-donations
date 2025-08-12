<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\TenantController;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

Route::get('tenants', [TenantController::class, 'index']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/publish', [CampaignController::class, 'publish'])->middleware('permission:campaign.publish');
    Route::post('campaigns/{campaign}/cancel', [CampaignController::class, 'cancel']);
    Route::post('campaigns/{campaign}/close', [CampaignController::class, 'close']);

    Route::post('donations', [DonationController::class, 'store'])->middleware('permission:donation.create');
    Route::get('donations', [DonationController::class, 'index'])->middleware('permission:donation.viewAny');
    Route::get('donations/{donation}', [DonationController::class, 'show']);

    Route::get('payment-methods', [PaymentMethodController::class, 'index']);
    Route::post('payment-methods', [PaymentMethodController::class, 'store']);
});

// Payment provider webhooks (no auth, secured by secret)
Route::post('webhooks/payments', [WebhookController::class, 'payments']);


