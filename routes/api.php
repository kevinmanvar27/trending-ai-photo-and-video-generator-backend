<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\GenerationController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\Admin\ReferralSettingsController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/google-login', [AuthController::class, 'googleLogin']);

// Public subscription plans (can be viewed without auth)
Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);

// Public referral code validation
Route::post('/referral/validate', [ReferralController::class, 'validateReferralCode']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    
    // Subscription Management
    Route::prefix('subscription')->group(function () {
        Route::get('/my-subscription', [SubscriptionController::class, 'mySubscription']);
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
    });
    
    // Templates
    Route::prefix('templates')->group(function () {
        Route::get('/', [TemplateController::class, 'index']);
        Route::get('/{id}', [TemplateController::class, 'show']);
    });
    
    // Image/Video Generation
    Route::prefix('generate')->group(function () {
        Route::post('/upload', [GenerationController::class, 'upload']);
        Route::get('/status/{submissionId}', [GenerationController::class, 'status']);
        Route::get('/history', [GenerationController::class, 'history']);
    });
    
    // Submissions Management
    Route::delete('/submissions/{submissionId}', [GenerationController::class, 'delete']);
    
    // Activity tracking (existing)
    Route::post('/activity/start', [ActivityController::class, 'startSession']);
    Route::post('/activity/end', [ActivityController::class, 'endSession']);
    Route::get('/activity/history', [ActivityController::class, 'history']);
    
    // Referral System
    Route::prefix('referral')->group(function () {
        Route::get('/info', [ReferralController::class, 'getReferralInfo']);
        Route::get('/list', [ReferralController::class, 'getReferralList']);
        Route::get('/stats', [ReferralController::class, 'getReferralStats']);
        Route::post('/redeem', [ReferralController::class, 'redeemCoins']);
    });
});

// Admin routes (protected with admin middleware)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Referral Settings Management
    Route::prefix('referral-settings')->group(function () {
        Route::get('/', [ReferralSettingsController::class, 'getReferralSettings']);
        Route::put('/coins', [ReferralSettingsController::class, 'updateReferralCoins']);
        Route::put('/bonus', [ReferralSettingsController::class, 'updateNewUserBonus']);
        Route::put('/all', [ReferralSettingsController::class, 'updateAllSettings']);
        Route::post('/toggle', [ReferralSettingsController::class, 'toggleReferralSystem']);
    });
});

