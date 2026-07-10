<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

// ── Public Home ───────────────────────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ── Auth Redirect ─────────────────────────────────────────────────────────────
Route::get('/dashboard-redirect', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified']);

// ── Authenticated Backoffice Routes ───────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard (backoffice home)
    Route::get('/app/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Forms CRUD
    Route::prefix('app')->group(function () {
        Route::get('/forms', [FormController::class, 'index'])->name('forms.index');
        Route::post('/forms', [FormController::class, 'store'])->name('forms.store');
        Route::delete('/forms/{form}', [FormController::class, 'destroy'])->name('forms.destroy');

        // Form Context Tabs
        Route::get('/forms/{form}/build', [FormController::class, 'build'])->name('forms.build');
        Route::get('/forms/{form}/settings', [FormController::class, 'settings'])->name('forms.settings');
        Route::put('/forms/{form}/settings', [FormController::class, 'updateSettings'])->name('forms.settings.update');
        Route::patch('/forms/{form}/status', [FormController::class, 'updateStatus'])->name('forms.status.update');
        Route::get('/forms/{form}/preview', [FormController::class, 'preview'])->name('forms.preview');
        Route::get('/forms/{form}/share', [FormController::class, 'share'])->name('forms.share');
        Route::post('/forms/{form}/share', [FormController::class, 'updateShare'])->name('forms.share.update');
        Route::delete('/forms/{form}/share/{share}', [FormController::class, 'removeShare'])->name('forms.share.destroy');

        // Field Builder JSON Endpoints (Alpine/AJAX)
        Route::post('/forms/{form}/fields', [FieldController::class, 'store'])->name('fields.store');
        Route::put('/forms/{form}/fields/reorder', [FieldController::class, 'reorder'])->name('fields.reorder');
        Route::put('/forms/{form}/fields/{field}', [FieldController::class, 'update'])->name('fields.update');
        Route::delete('/forms/{form}/fields/{field}', [FieldController::class, 'destroy'])->name('fields.destroy');

        // Responses Backoffice
        Route::get('/forms/{form}/responses', [ResponseController::class, 'index'])->name('forms.responses');
        Route::get('/forms/{form}/responses/data', [ResponseController::class, 'data'])->name('forms.responses.data');
        Route::get('/forms/{form}/responses/{response}', [ResponseController::class, 'show'])->name('forms.responses.show');
        Route::delete('/forms/{form}/responses/{response}', [ResponseController::class, 'destroy'])->name('forms.responses.destroy');
        Route::get('/forms/{form}/files/{file}', [ResponseController::class, 'downloadFile'])->name('forms.files.download');

        // Analytics
        Route::get('/forms/{form}/analytics', [AnalyticsController::class, 'show'])->name('forms.analytics');
        Route::get('/forms/{form}/analytics/data', [AnalyticsController::class, 'data'])->name('forms.analytics.data');

        // Export (per form)
        Route::post('/forms/{form}/export', [ExportController::class, 'export'])->name('forms.export');

        // Exports History
        Route::get('/exports', [ExportController::class, 'index'])->name('exports.index');

        // Templates Gallery
        Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');

        // Admin Pages (Super Admin / Admin)
        Route::prefix('admin')->group(function () {
            Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
            Route::get('/audit', [AdminController::class, 'audit'])->name('admin.audit');
            Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
            Route::get('/trash', [AdminController::class, 'trash'])->name('admin.trash');
            Route::post('/trash/{id}/restore', [AdminController::class, 'restore'])->name('admin.trash.restore');
        });
    });
});

// Redirect /app to /app/dashboard
Route::redirect('/app', '/app/dashboard')->middleware('auth');

// ── Public Respondent Routes ──────────────────────────────────────────────────
// Rate limit: max 10 submits per minute per IP (CLAUDE.md + PRD 7 security)
Route::get('/f/{slug}', [ResponseController::class, 'showPublic'])->name('public.form');
Route::post('/f/{slug}', [ResponseController::class, 'submitPublic'])
    ->middleware('throttle:10,1')
    ->name('public.submit');
Route::get('/f/{slug}/success/{responseUuid}', [ResponseController::class, 'success'])->name('public.success');
Route::get('/f/{slug}/resume/{token}', [ResponseController::class, 'resume'])->name('public.resume');
Route::post('/f/{slug}/upload', [ResponseController::class, 'uploadFile'])
    ->middleware('auth')
    ->name('public.upload');

require __DIR__.'/auth.php';
