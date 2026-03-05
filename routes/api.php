<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\GenerationController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\Admin\ReferralSettingsController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Test endpoint to check Google login configuration
Route::post('/test-google-config', function() {
    $googleLoginEnabled = \App\Models\Setting::getBool('google_login_enabled', false);
    $googleClientId = \App\Models\Setting::get('google_client_id');
    
    return response()->json([
        'success' => true,
        'google_login_enabled' => $googleLoginEnabled,
        'google_client_id' => $googleClientId,
        'google_client_id_set' => !empty($googleClientId),
        'google_client_id_length' => strlen($googleClientId ?? ''),
        'timestamp' => now()->toISOString(),
    ]);
});

Route::post('/google-login', [AuthController::class, 'googleLogin']);

// Public subscription plans (can be viewed without auth)
Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);

// Public referral code validation
Route::post('/referral/validate', [ReferralController::class, 'validateReferralCode']);

// Debug endpoint to list all referral codes (remove in production)
Route::get('/referral/debug-codes', function () {
    $users = \App\Models\User::whereNotNull('referral_code')
        ->select('id', 'name', 'email', 'referral_code')
        ->get();
    
    return response()->json([
        'success' => true,
        'total_users' => $users->count(),
        'users' => $users,
    ]);
});

// Public app configuration
Route::get('/app/config', function () {
    return response()->json([
        'success' => true,
        'data' => [
            'referral_system_enabled' => \App\Models\Setting::getBool('referral_system_enabled', true),
            'referral_bonus_for_new_user' => (int) \App\Models\Setting::get('referral_bonus_for_new_user', 50),
            'signup_bonus_enabled' => \App\Models\Setting::getBool('signup_bonus_enabled', false),
            'signup_bonus_coins' => (int) \App\Models\Setting::get('signup_bonus_coins', 0),
            'google_login_enabled' => \App\Models\Setting::getBool('google_login_enabled', false),
        ]
    ]);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    
    // Account Management
    Route::delete('/account/delete', [AccountController::class, 'deleteAccount']);
    
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
        Route::post('/apply', [ReferralController::class, 'applyReferralCode']);
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

