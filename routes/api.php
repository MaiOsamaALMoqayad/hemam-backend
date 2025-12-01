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
