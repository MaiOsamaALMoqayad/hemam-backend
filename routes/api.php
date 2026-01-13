<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CampController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\SettingController;
use App\Http\Controllers\API\TrainerController;
use App\Http\Controllers\API\ActivityController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\API\StatisticsController;
use App\Http\Controllers\API\ConsultationController;
use App\Http\Controllers\API\TrainerApplicationController;

// ---------------------------
// API Version 1
// ---------------------------
Route::prefix('v1')->group(function () {

    // ---------------------------
    // Annual Programs
    // ---------------------------
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::get('/activities/{id}', [ActivityController::class, 'show']);

    // ---------------------------
    // Projects
    // ---------------------------
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);



    // ---------------------------
    // Trainers
    // ---------------------------
    Route::get('/trainers', [TrainerController::class, 'index']);

    // ---------------------------
    // Statistics & Settings
    // ---------------------------
    Route::get('/statistics', [StatisticsController::class, 'index']);
    Route::get('/settings', [SettingController::class, 'index']);

    // ---------------------------
    // Search
    // ---------------------------
    Route::get('/search', [SearchController::class, 'index']);

    // ---------------------------
    // Forms
    // ---------------------------
    Route::post('/contact', [ContactController::class, 'store']);
    Route::post('/trainer-applications', [TrainerApplicationController::class, 'store']);
    Route::post('/consultations', [ConsultationController::class, 'store']);
    Route::post('/donations', [DonationController::class, 'store']);


    // ---------------------------
    // Reviews
    // ---------------------------
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/activities/{activityId}/reviews', [ReviewController::class, 'activityReviews']);
    Route::get('/reviews', [ReviewController::class, 'index']);
});



Route::prefix('admin')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout']);
        Route::get('/user', [\App\Http\Controllers\Admin\AuthController::class, 'user']);
        Route::get('/check', [\App\Http\Controllers\Admin\AuthController::class, 'check']);
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);

        Route::apiResource('activities', \App\Http\Controllers\Admin\ActivityController::class);
        Route::apiResource('projects', \App\Http\Controllers\Admin\ProjectController::class);
        Route::apiResource('trainers', \App\Http\Controllers\Admin\TrainerController::class);

        Route::get('contacts', [\App\Http\Controllers\Admin\ContactMessageController::class, 'index']);
        Route::get('contacts/{contact}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'show']);
        Route::put('contacts/{contact}/mark-read', [\App\Http\Controllers\Admin\ContactMessageController::class, 'markAsRead']);
        Route::delete('contacts/{contact}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'destroy']);

        Route::get('trainer-applications', [\App\Http\Controllers\Admin\TrainerApplicationController::class, 'index']);
        Route::get('trainer-applications/{trainerApplication}', [\App\Http\Controllers\Admin\TrainerApplicationController::class, 'show']);
        Route::put('trainer-applications/{trainerApplication}/status', [\App\Http\Controllers\Admin\TrainerApplicationController::class, 'updateStatus']);
        Route::delete('trainer-applications/{trainerApplication}', [\App\Http\Controllers\Admin\TrainerApplicationController::class, 'destroy']);

        Route::get('consultations', [\App\Http\Controllers\Admin\ConsultationController::class, 'index']);
        Route::get('consultations/{consultation}', [\App\Http\Controllers\Admin\ConsultationController::class, 'show']);
        Route::put('consultations/{consultation}/status', [\App\Http\Controllers\Admin\ConsultationController::class, 'updateStatus']);
        Route::delete('consultations/{consultation}', [\App\Http\Controllers\Admin\ConsultationController::class, 'destroy']);

        Route::get('statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index']);
        Route::put('statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'update']);

        Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index']);
        Route::put('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update']);

        Route::get('reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index']);
        Route::put('reviews/{id}/approve', [\App\Http\Controllers\Admin\ReviewController::class, 'approve']);
        Route::delete('reviews/{id}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy']);

        // Activity Carousel Image Management
        Route::get('activities/{activityId}/images', [\App\Http\Controllers\Admin\ActivityCarouselController::class, 'index']);
        Route::post('activities/{activityId}/images', [\App\Http\Controllers\Admin\ActivityCarouselController::class, 'store']);
        Route::delete('activities/images/{id}', [\App\Http\Controllers\Admin\ActivityCarouselController::class, 'destroy']);
    });
});
