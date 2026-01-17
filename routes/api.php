<?php

use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\Api\NewsController as FrontNewsController;
use App\Http\Controllers\Admin\{
    AuthController,
    DashboardController,
    ActivityController as AdminActivityController,
    ProjectController as AdminProjectController,
    TrainerController as AdminTrainerController,
    ContactMessageController,
    TrainerApplicationController as AdminTrainerApplicationController,
    ConsultationController as AdminConsultationController,
    StatisticsController as AdminStatisticsController,
    SettingController as AdminSettingController,
    ReviewController as AdminReviewController,
    ActivityCarouselController,
    ActivityRequestController,
    NewsController as AdminNewsController
};

// ---------------------------
// API Version 1
// ---------------------------
Route::prefix('v1')->group(function () {

    // --- Public Resources ---
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::get('/activities/{id}', [ActivityController::class, 'show']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);

    Route::get('/trainers', [TrainerController::class, 'index']);
    Route::get('/statistics', [StatisticsController::class, 'index']);
    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/search', [SearchController::class, 'index']);

    // --- Forms & Submissions ---
    Route::post('/contact', [ContactController::class, 'store']);
    Route::post('/trainer-applications', [TrainerApplicationController::class, 'store']);
    Route::post('/consultations', [ConsultationController::class, 'store']);
    Route::post('/donations', [DonationController::class, 'store']);
    Route::post('/activities/requests', [\App\Http\Controllers\Api\ActivityRequestController::class, 'store']);



    // --- Reviews ---
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/activities/{activityId}/reviews', [ReviewController::class, 'activityReviews']);

    // --- Khawatir  ---
    Route::prefix('khawatir')->group(function () {
        Route::get('/categories', [\App\Http\Controllers\Api\Khawatir\CategoryController::class, 'index']);
        Route::get('/categories/{category}/posts', [\App\Http\Controllers\Api\Khawatir\PostController::class, 'index']);
        Route::get('/posts/{id}', [\App\Http\Controllers\Api\Khawatir\PostController::class, 'show']);
    });
    // --- News  ---
    Route::get('/news', [FrontNewsController::class, 'index']);
    Route::get('/news/{id}', [FrontNewsController::class, 'show']);

    // --- Map Locations  ---
    Route::get('/map-locations', [\App\Http\Controllers\Api\MapLocationController::class, 'index']);
});


// ---------------------------
// Admin Panel (Protected)
// ---------------------------
Route::prefix('admin')->group(function () {
    // Public Admin Routes
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Protected Admin Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::get('/check', [AuthController::class, 'check']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Main Resources
        Route::apiResource('activities', AdminActivityController::class);
        Route::apiResource('projects', AdminProjectController::class);
        Route::apiResource('trainers', AdminTrainerController::class);

        // Management
        Route::prefix('contacts')->group(function () {
            Route::get('/', [ContactMessageController::class, 'index']);
            Route::get('/{contact}', [ContactMessageController::class, 'show']);
            Route::put('/{contact}/mark-read', [ContactMessageController::class, 'markAsRead']);
            Route::delete('/{contact}', [ContactMessageController::class, 'destroy']);
        });

        // Activity Requests Management
        Route::prefix('activity-requests')->group(function () {
            Route::get('/', [ActivityRequestController::class, 'index']);
            Route::get('/{id}', [ActivityRequestController::class, 'show']);
            Route::put('/{id}/read', [ActivityRequestController::class, 'markAsRead']);
            Route::delete('/{id}', [ActivityRequestController::class, 'destroy']);
        });


        Route::prefix('trainer-applications')->group(function () {
            Route::get('/', [AdminTrainerApplicationController::class, 'index']);
            Route::get('/{trainerApplication}', [AdminTrainerApplicationController::class, 'show']);
            Route::put('/{trainerApplication}/status', [AdminTrainerApplicationController::class, 'updateStatus']);
            Route::delete('/{trainerApplication}', [AdminTrainerApplicationController::class, 'destroy']);
        });

        Route::prefix('consultations')->group(function () {
            Route::get('/', [AdminConsultationController::class, 'index']);
            Route::get('/{consultation}', [AdminConsultationController::class, 'show']);
            Route::put('/{consultation}/status', [AdminConsultationController::class, 'updateStatus']);
            Route::delete('/{consultation}', [AdminConsultationController::class, 'destroy']);
        });

        Route::get('statistics', [AdminStatisticsController::class, 'index']);
        Route::put('statistics', [AdminStatisticsController::class, 'update']);

        Route::get('settings', [AdminSettingController::class, 'index']);
        Route::put('settings', [AdminSettingController::class, 'update']);

        Route::prefix('reviews')->group(function () {
            Route::get('/', [AdminReviewController::class, 'index']);
            Route::put('/{id}/approve', [AdminReviewController::class, 'approve']);
            Route::delete('/{id}', [AdminReviewController::class, 'destroy']);
        });

        // Images Management
        Route::get('activities/{activityId}/images', [ActivityCarouselController::class, 'index']);
        Route::post('activities/{activityId}/images', [ActivityCarouselController::class, 'store']);
        Route::delete('activities/images/{id}', [ActivityCarouselController::class, 'destroy']);

        // Khawatir Management
        Route::apiResource('khawatir/categories', \App\Http\Controllers\Admin\Khawatir\CategoryController::class);
        Route::apiResource('khawatir/posts', \App\Http\Controllers\Admin\Khawatir\PostController::class);
        Route::post('khawatir/posts/{post}/images', [App\Http\Controllers\Admin\Khawatir\PostImageController::class, 'store']);
        Route::delete('khawatir/images/{image}', [App\Http\Controllers\Admin\Khawatir\PostImageController::class, 'destroy']);

        // News Management
    Route::prefix('news')->group(function () {
        Route::get('/', [AdminNewsController::class, 'index']);
        Route::post('/', [AdminNewsController::class, 'store']);
        Route::get('/{id}', [AdminNewsController::class, 'show']);
        Route::put('/{id}', [AdminNewsController::class, 'update']);
        Route::delete('/{id}', [AdminNewsController::class, 'destroy']);
    });
    Route::apiResource('map-locations', \App\Http\Controllers\Admin\MapLocationController::class);
    });

});
