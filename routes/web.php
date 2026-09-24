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

    // Schedules
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/schedules/{id}/assign', [ScheduleController::class, 'assign'])->name('schedules.assign');

    // ── Admin: Kelola Laporan Warga ──────────────────────────────────────────
    Route::prefix('admin/laporan')->name('admin.laporan.')->group(function () {
        Route::get('/', [LaporanAdminController::class, 'index'])->name('index');
        Route::get('/{id_laporan}', [LaporanAdminController::class, 'show'])->name('show');
        Route::post('/{id_laporan}/terima', [LaporanAdminController::class, 'terima'])->name('terima');
        Route::post('/{id_laporan}/tolak', [LaporanAdminController::class, 'tolak'])->name('tolak');
    });

    // ── Admin: Hasil Inspeksi IKL Air ────────────────────────────────────────
    Route::prefix('admin/inspeksi-ikl')->name('admin.inspeksi-ikl.')->group(function () {
        Route::get('/', [InspeksiIklController::class, 'index'])->name('index');
        Route::get('/{id_jadwal}/input', [InspeksiIklController::class, 'create'])->name('create');
        Route::post('/', [InspeksiIklController::class, 'store'])->name('store');
        Route::get('/{id_jadwal}', [InspeksiIklController::class, 'show'])->name('show');
    });
});

// ── API Routes untuk Flutter ─────────────────────────────────────────────────
Route::prefix('api')->name('api.')->group(function () {
    Route::post('/laporan', [LaporanApiController::class, 'store'])->name('laporan.store');
    Route::get('/laporan/{kode_tiket}/status', [LaporanApiController::class, 'cekStatus'])->name('laporan.status');
});
