<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\DonationController;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/publish', [CampaignController::class, 'publish'])->middleware('permission:campaign.publish');

    Route::post('donations', [DonationController::class, 'store'])->middleware('permission:donation.create');
    Route::get('donations', [DonationController::class, 'index'])->middleware('permission:donation.viewAny');
});


