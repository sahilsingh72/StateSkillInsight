<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RespondentController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SurveyController;
use App\Http\Controllers\Admin\UniversityController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoiceManagerController;
use App\Http\Controllers\PublicSurveyController;
use App\Http\Controllers\VoiceResponseController;
use Illuminate\Support\Facades\Route;

// Public Respondent Survey Routes
Route::get('/', [PublicSurveyController::class, 'landing'])->name('survey.landing');
Route::get('/locale/{locale}', [PublicSurveyController::class, 'setLocale'])->name('survey.locale');
Route::get('/survey/category/{category}', [PublicSurveyController::class, 'registerCategory'])->name('survey.register');
Route::post('/survey/start', [PublicSurveyController::class, 'startSurvey'])->name('survey.start');
Route::get('/survey/start/{token}', [PublicSurveyController::class, 'startByToken'])->name('survey.start_token');
Route::get('/survey/{token}', [PublicSurveyController::class, 'takeSurvey'])->name('survey.take');
Route::post('/survey/{token}/auto-save', [PublicSurveyController::class, 'autoSave'])->name('survey.autosave');
Route::post('/survey/{token}/voice-upload', [VoiceResponseController::class, 'upload'])->name('survey.voice_upload');
Route::get('/survey/{token}/review', [PublicSurveyController::class, 'review'])->name('survey.review');
Route::post('/survey/{token}/submit', [PublicSurveyController::class, 'submit'])->name('survey.submit');
Route::get('/survey/{token}/thankyou', [PublicSurveyController::class, 'thankYou'])->name('survey.thankyou');

// Protected Portal Routes for All Roles
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Institutions & Colleges Directory (Super Admin & Institution Admins)
        Route::get('/universities', [UniversityController::class, 'index'])->name('university.index');
        Route::post('/universities', [UniversityController::class, 'store'])->name('university.store');
        Route::get('/university/{university?}', [UniversityController::class, 'edit'])->name('university.edit');
        Route::post('/university/{university?}', [UniversityController::class, 'update'])->name('university.update');

        // Surveys Builder
        Route::get('/surveys', [SurveyController::class, 'index'])->name('surveys.index');
        Route::get('/surveys/create', [SurveyController::class, 'create'])->name('surveys.create');
        Route::post('/surveys', [SurveyController::class, 'store'])->name('surveys.store');
        Route::get('/surveys/{survey}', [SurveyController::class, 'show'])->name('surveys.show');
        Route::get('/surveys/{survey}/edit', [SurveyController::class, 'edit'])->name('surveys.edit');
        Route::put('/surveys/{survey}', [SurveyController::class, 'update'])->name('surveys.update');
        Route::post('/surveys/{survey}/clone', [SurveyController::class, 'clone'])->name('surveys.clone');
        Route::get('/surveys/{survey}/preview', [SurveyController::class, 'preview'])->name('surveys.preview');

        // Categories & Sections
        Route::post('/surveys/{survey}/categories/sync', [CategoryController::class, 'syncSurveyCategories'])->name('surveys.categories.sync');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::put('/sections/{section}', [SectionController::class, 'update'])->name('sections.update');

        // Question Bank & Builder
        Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
        Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');

        // Respondents
        Route::get('/respondents', [RespondentController::class, 'index'])->name('respondents.index');
        Route::get('/respondents/{respondent}', [RespondentController::class, 'show'])->name('respondents.show');

        // Analytics & Dashboards
        Route::get('/analytics/overview', [AnalyticsController::class, 'overview'])->name('analytics.overview');
        Route::get('/analytics/category-1', [AnalyticsController::class, 'category1'])->name('analytics.category1');
        Route::get('/analytics/category-2', [AnalyticsController::class, 'category2'])->name('analytics.category2');
        Route::get('/analytics/category-3', [AnalyticsController::class, 'category3'])->name('analytics.category3');
        Route::get('/analytics/category-4', [AnalyticsController::class, 'category4'])->name('analytics.category4');
        Route::get('/analytics/cross-analysis', [AnalyticsController::class, 'crossAnalysis'])->name('analytics.cross_analysis');
        Route::get('/analytics/comparison', [AnalyticsController::class, 'comparison'])->name('analytics.comparison');
        Route::get('/analytics/trends', [AnalyticsController::class, 'trends'])->name('analytics.trends');
        Route::get('/analytics/interventions', [AnalyticsController::class, 'interventions'])->name('analytics.interventions');

        // Reports & Exports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/view/{type}', [ReportController::class, 'generate'])->name('reports.view');
        Route::get('/exports/csv', [ExportController::class, 'exportCsv'])->name('exports.csv');

        // Voice & Media
        Route::get('/responses/voice', [VoiceManagerController::class, 'index'])->name('responses.voice');

        // Invitations & Audit Logs
        Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations.index');
        Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');

        // User & Role Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });
});

require __DIR__.'/auth.php';
