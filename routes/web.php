<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\EducationManagementController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserManagementController;

// ─────────────────────────────────────────────
// Auth Routes
// ─────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─────────────────────────────────────────────
// Protected Routes (require session role)
// ─────────────────────────────────────────────
Route::middleware('auth.role')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::post('/reports/{id}/status', [ReportController::class, 'updateStatus'])->name('reports.status');
    Route::get('/analytics', [ReportController::class, 'analytics'])->name('analytics.index');

    // Education
    Route::get('/education', [EducationController::class, 'index'])->name('education.index');
    Route::get('/education/manage', [EducationManagementController::class, 'index'])->name('education.manage');

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
