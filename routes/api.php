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

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    
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
});
