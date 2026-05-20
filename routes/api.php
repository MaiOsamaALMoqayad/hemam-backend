<?php

use App\Http\Controllers\Admin\{AuthController, DashboardController, ActivityController as AdminActivityController, ProjectController as AdminProjectController, TrainerController as AdminTrainerController, ContactMessageController, TrainerApplicationController as AdminTrainerApplicationController, ConsultationController as AdminConsultationController, StatisticsController as AdminStatisticsController, SettingController as AdminSettingController, ReviewController as AdminReviewController, ActivityCarouselController, ActivityRequestController, NewsController as AdminNewsController};
use App\Http\Controllers\Admin\CompanyReviewController as AdminCompanyReviewController;
use App\Http\Controllers\Admin\MapLocationController;
use App\Http\Controllers\API\ActivityController;
use App\Http\Controllers\API\CompanyReviewController as PublicReviewController;
use App\Http\Controllers\API\ConsultationController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\DonationController;
use App\Http\Controllers\API\NewsController as FrontNewsController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\SettingController;
use App\Http\Controllers\API\StatisticsController;
use App\Http\Controllers\API\TrainerApplicationController;
use App\Http\Controllers\API\TrainerController;
use Illuminate\Support\Facades\Route;

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
    Route::post('/activities/requests', [\App\Http\Controllers\API\ActivityRequestController::class, 'store']);



    // --- Reviews ---
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/activities/{activityId}/reviews', [ReviewController::class, 'activityReviews']);

    // --- Khawatir  ---
    Route::prefix('khawatir')->group(function () {
        Route::get('/categories', [\App\Http\Controllers\API\Khawatir\CategoryController::class, 'index']);
        Route::get('/categories/{category}/posts', [\App\Http\Controllers\API\Khawatir\PostController::class, 'index']);
        Route::get('/posts/{id}', [\App\Http\Controllers\API\Khawatir\PostController::class, 'show']);
    });
    // --- News  ---
    Route::get('/news', [FrontNewsController::class, 'index']);
    Route::get('/news/{id}', [FrontNewsController::class, 'show']);

    // --- Map Locations  ---
    Route::get('/map-locations', [\App\Http\Controllers\API\MapLocationController::class, 'index']);

    //تعليقات عامة
    Route::post('/company-reviews', [PublicReviewController::class, 'store']);
    Route::get('/company-reviews', [PublicReviewController::class, 'index']);

    // Partners
    Route::get('/partners', [\App\Http\Controllers\API\PartnerController::class, 'index']);

    // Video
    Route::get('/video', [\App\Http\Controllers\API\VideoController::class, 'show']);
    // --- Goals ---
    Route::get('/goals', [\App\Http\Controllers\API\GoalController::class, 'index']);
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

// Statistics Management
        Route::get('statistics/icons', [AdminStatisticsController::class, 'getAvailableIcons']);
        Route::get('statistics', [AdminStatisticsController::class, 'index']);
        Route::post('statistics', [AdminStatisticsController::class, 'store']);
        Route::put('statistics/{id}', [AdminStatisticsController::class, 'update']);
        Route::delete('statistics/{id}', [AdminStatisticsController::class, 'destroy']);


        Route::get('settings', [AdminSettingController::class, 'index']);
        Route::put('settings', [AdminSettingController::class, 'update']);

        Route::prefix('reviews')->group(function () {
            Route::get('/', [AdminReviewController::class, 'index']);
            Route::put('/{id}/approve', [AdminReviewController::class, 'approve']);
            Route::delete('/{id}', [AdminReviewController::class, 'destroy']);
        });

        Route::get('company-reviews', [AdminCompanyReviewController::class, 'index']);
        Route::put('company-reviews/{id}/approve', [AdminCompanyReviewController::class, 'approve']);
        Route::delete('company-reviews/{id}', [AdminCompanyReviewController::class, 'destroy']);


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
        Route::delete('map-locations/images/{id}', [MapLocationController::class, 'deleteImage']);
    });

    // --- Partners Management ---
     Route::get('/partners', [\App\Http\Controllers\Admin\PartnerController::class, 'index']);
    Route::post('/partners', [\App\Http\Controllers\Admin\PartnerController::class, 'store']);
    Route::delete('/partners/{id}', [\App\Http\Controllers\Admin\PartnerController::class, 'destroy']);

        // --- Video Management ---
        Route::get('/video', [\App\Http\Controllers\Admin\VideoController::class, 'index']);
        Route::post('/video', [\App\Http\Controllers\Admin\VideoController::class, 'store']);
        Route::delete('/video', [\App\Http\Controllers\Admin\VideoController::class, 'destroy']);

        // --- Goals Management ---
        Route::get('/goals', [\App\Http\Controllers\Admin\GoalController::class, 'index']);
        Route::post('/goals', [\App\Http\Controllers\Admin\GoalController::class, 'store']);
        Route::put('/goals/{id}', [\App\Http\Controllers\Admin\GoalController::class, 'update']);
        Route::delete('/goals/{id}', [\App\Http\Controllers\Admin\GoalController::class, 'destroy']);
});

