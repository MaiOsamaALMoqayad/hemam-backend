<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AnnualProgramController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\CampController;
use App\Http\Controllers\API\TrainerController;
use App\Http\Controllers\API\StatisticsController;
use App\Http\Controllers\API\SettingController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\TrainerApplicationController;
use App\Http\Controllers\API\ConsultationController;

// ---------------------------
// API Version 1
// ---------------------------
Route::prefix('v1')->group(function () {

    // ---------------------------
    // Annual Programs
    // ---------------------------
    Route::get('/annual-programs', [AnnualProgramController::class, 'index']);
    Route::get('/annual-programs/{id}', [AnnualProgramController::class, 'show']);

    // ---------------------------
    // Projects
    // ---------------------------
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);

    // ---------------------------
    // Camps
    // ---------------------------
    Route::get('/camps', [CampController::class, 'index']); // جميع المخيمات

    Route::get('/camps/open', [CampController::class, 'open']);
    Route::get('/camps/closed', [CampController::class, 'closed']);
    Route::get('/camps/{camp}', [CampController::class, 'show']);

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
});



Route::prefix('admin')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login');

    // بس auth:sanctum - الـ Cookie middleware شغال تلقائياً
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout']);
        Route::get('/user', [\App\Http\Controllers\Admin\AuthController::class, 'user']);
        Route::get('/check', [\App\Http\Controllers\Admin\AuthController::class, 'check']);
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);

        Route::apiResource('annual-programs', \App\Http\Controllers\Admin\AnnualProgramController::class);
        Route::apiResource('projects', \App\Http\Controllers\Admin\ProjectController::class);
        Route::apiResource('camps', \App\Http\Controllers\Admin\CampController::class);
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
    });
});
