<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\ImageSubmissionController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\SubscriptionController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Get payment configuration (public - no auth required)
Route::get('/payment/config', [SettingController::class, 'getPaymentConfig']);

// Public template routes (for browsing)
Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/templates/popular', [TemplateController::class, 'popular']);
Route::get('/templates/{id}', [TemplateController::class, 'show']);

// Public page routes (for browsing)
Route::get('/pages', [PageController::class, 'index']);
Route::get('/pages/{identifier}', [PageController::class, 'show']);

// Public subscription plans (for browsing available plans)
Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);
Route::get('/subscription/plans/{id}', [SubscriptionController::class, 'showPlan']);

// Public delete account via credentials (GET method with email and password)
Route::get('/delete-account-credentials', [AuthController::class, 'deleteAccountViaCredentials']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    
    // User account deletion (GET method)
    Route::get('/delete-account', [AuthController::class, 'deleteAccount']);
    
    // Device contacts sync
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::delete('/contacts', [ContactController::class, 'deleteAll']);
    
    // Activity tracking
    Route::post('/activity/start', [ActivityController::class, 'startSession']);
    Route::post('/activity/end', [ActivityController::class, 'endSession']);
    Route::get('/activity/history', [ActivityController::class, 'history']);
    
    // Settings (protected - sensitive keys filtered)
    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/settings/group/{group}', [SettingController::class, 'getByGroup']);
    Route::get('/settings/{key}', [SettingController::class, 'show']);
    
    // Subscription management
    Route::post('/subscription/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::get('/subscription/my-subscription', [SubscriptionController::class, 'mySubscription']);
    Route::get('/subscription/history', [SubscriptionController::class, 'subscriptionHistory']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancelSubscription']);
    
    // Admin only - Subscription plan management
    Route::post('/subscription/plans', [SubscriptionController::class, 'createPlan']);
    Route::put('/subscription/plans/{id}', [SubscriptionController::class, 'updatePlan']);
    Route::delete('/subscription/plans/{id}', [SubscriptionController::class, 'deletePlan']);
    
    // Admin only - Deleted Users Management (Soft Delete Enhancement)
    Route::post('/admin/users/{id}/restore', [\App\Http\Controllers\Admin\UserController::class, 'restoreApi']);
    Route::delete('/admin/users/{id}/force-delete', [\App\Http\Controllers\Admin\UserController::class, 'forceDeleteApi']);

    
    // Template management (admin only)
    Route::post('/templates', [TemplateController::class, 'store']);
    Route::put('/templates/{id}', [TemplateController::class, 'update']);
    Route::delete('/templates/{id}', [TemplateController::class, 'destroy']);
    Route::post('/templates/{id}/toggle', [TemplateController::class, 'toggleActive']);
    
    // Image submissions (requires authentication)
    Route::get('/submissions', [ImageSubmissionController::class, 'index']);
    Route::get('/submissions/recent', [ImageSubmissionController::class, 'recent']);
    Route::get('/submissions/statistics', [ImageSubmissionController::class, 'statistics']);
    Route::post('/submissions', [ImageSubmissionController::class, 'store']);
    Route::get('/submissions/{id}', [ImageSubmissionController::class, 'show']);
    Route::post('/submissions/{id}/status', [ImageSubmissionController::class, 'updateStatus']);
    Route::delete('/submissions/{id}', [ImageSubmissionController::class, 'destroy']);
    
    // Generate/Upload endpoints (as per API documentation)
    Route::post('/generate/upload', [ImageSubmissionController::class, 'upload']);
    Route::get('/generate/status/{id}', [ImageSubmissionController::class, 'checkStatus']);
    Route::get('/generate/history', [ImageSubmissionController::class, 'history']);
});
