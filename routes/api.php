<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    
    // Activity tracking
    Route::post('/activity/start', [ActivityController::class, 'startSession']);
    Route::post('/activity/end', [ActivityController::class, 'endSession']);
    Route::get('/activity/history', [ActivityController::class, 'history']);
});
