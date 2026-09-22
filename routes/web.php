<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// Auth Routes
// ─────────────────────────────────────────────
Route::get('/', [AuthController::class, 'showLogin'])->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─────────────────────────────────────────────
// Protected Routes (require session role)
// ─────────────────────────────────────────────
Route::get('/edukasi', [EducationController::class, 'index'])->name('education.index');
Route::get('/edukasi/{slug}', [EducationController::class, 'show'])->name('education.show');

Route::middleware('auth.role')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::post('/reports/{id}/status', [ReportController::class, 'updateStatus'])->name('reports.status');
    Route::get('/analytics', [ReportController::class, 'analytics'])->name('analytics.index');

    // Education management
    Route::prefix('admin/edukasi')->name('education.')->group(function () {
        Route::get('/', [EducationController::class, 'manage'])->name('manage');
        Route::get('/create', [EducationController::class, 'create'])->name('create');
        Route::post('/', [EducationController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [EducationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EducationController::class, 'update'])->name('update');
        Route::delete('/{id}', [EducationController::class, 'destroy'])->name('destroy');
    });

    // User management
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/export', [UserManagementController::class, 'export'])->name('users.export');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // Schedules
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/schedules/{id}/assign', [ScheduleController::class, 'assign'])->name('schedules.assign');
});
