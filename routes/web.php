<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\UserSubscriptionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ImagePromptController;
use App\Http\Controllers\Admin\ImagePromptTemplateController;
use App\Http\Controllers\ImageSubmissionController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Image Submission Routes (Frontend - for users)
Route::get('/image-effects', [ImageSubmissionController::class, 'index'])->name('image-submission.index');
Route::get('/image-effects/{template}', [ImageSubmissionController::class, 'create'])->name('image-submission.create');
Route::post('/image-effects/{template}', [ImageSubmissionController::class, 'store'])->name('image-submission.store');
Route::get('/my-images/{submission}', [ImageSubmissionController::class, 'show'])->name('image-submission.show');
Route::get('/my-images/{submission}/download', [ImageSubmissionController::class, 'download'])->name('image-submission.download');

// User Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// Admin Panel Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', UserController::class);
    Route::post('users/{id}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::post('users/{id}/unsuspend', [UserController::class, 'unsuspend'])->name('users.unsuspend');
    Route::get('users/{id}/activity', [UserController::class, 'activity'])->name('users.activity');

    // Subscription Plans
    Route::resource('plans', SubscriptionPlanController::class);
    Route::post('plans/{id}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus'])->name('plans.toggle-status');

    // User Subscriptions
    Route::get('subscriptions', [UserSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/create', [UserSubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('subscriptions', [UserSubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::post('subscriptions/{id}/cancel', [UserSubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('subscriptions/{id}/renew', [UserSubscriptionController::class, 'renew'])->name('subscriptions.renew');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/delete-file', [SettingController::class, 'deleteFile'])->name('settings.delete-file');
    Route::post('settings/test-api-key', [SettingController::class, 'testApiKey'])->name('settings.test-api-key');

    // Image Prompt Templates (Backend - Admin manages templates)
    Route::resource('image-templates', ImagePromptTemplateController::class);
    Route::post('image-templates/{id}/toggle-status', [ImagePromptTemplateController::class, 'toggleStatus'])->name('image-templates.toggle-status');
    Route::get('image-templates/{id}/submissions', [ImagePromptTemplateController::class, 'submissions'])->name('image-templates.submissions');

    // Image Prompts (Old module - keep for backward compatibility)
    Route::resource('image-prompts', ImagePromptController::class);
    Route::post('image-prompts/{id}/reprocess', [ImagePromptController::class, 'reprocess'])->name('image-prompts.reprocess');
    Route::get('image-prompts/{id}/download', [ImagePromptController::class, 'download'])->name('image-prompts.download');
});
