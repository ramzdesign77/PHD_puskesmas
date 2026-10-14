<?php

use App\Http\Controllers\Admin\InspeksiIklController as AdminInspeksiIklController;
use App\Http\Controllers\Admin\LaporanAdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\InspeksiIklController as OperatorInspeksiIklController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::get('/edukasi', [EducationController::class, 'index'])->name('education.index');
Route::get('/edukasi/{slug}', [EducationController::class, 'show'])->name('education.show');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin,petugas')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/admin', [DashboardController::class, 'index'])
            ->middleware('role:admin')
            ->name('dashboard.admin');
        Route::get('/dashboard/petugas', [DashboardController::class, 'index'])
            ->middleware('role:petugas')
            ->name('dashboard.petugas');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
        Route::post('/reports/{id}/status', [ReportController::class, 'updateStatus'])->name('reports.status');
        Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::get('/inspeksi/{id_jadwal}/form', [OperatorInspeksiIklController::class, 'form'])->name('inspeksi.form');
        Route::post('/inspeksi/{id_jadwal}/submit', [OperatorInspeksiIklController::class, 'submit'])->name('inspeksi.submit');
        Route::get('/inspeksi/{id_inspeksi}', [OperatorInspeksiIklController::class, 'show'])->name('inspeksi.show');

        Route::prefix('admin/edukasi')->name('education.')->group(function (): void {
            Route::get('/', [EducationController::class, 'manage'])->name('manage');
            Route::get('/create', [EducationController::class, 'create'])->name('create');
            Route::post('/', [EducationController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [EducationController::class, 'edit'])->name('edit');
            Route::put('/{id}', [EducationController::class, 'update'])->name('update');
            Route::delete('/{id}', [EducationController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/analytics', [ReportController::class, 'analytics'])->name('analytics.index');
        Route::post('/reports/{id_laporan}/jadwalkan', [ReportController::class, 'jadwalkan'])->name('reports.jadwalkan');
        Route::post('/reports/{id_laporan}/tolak', [ReportController::class, 'tolak'])->name('reports.tolak');
        Route::post('/reports/{id_jadwal}/reschedule', [ReportController::class, 'reschedule'])->name('reports.reschedule');

        Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::post('/schedules/{id}/assign', [ScheduleController::class, 'assign'])->name('schedules.assign');
        Route::put('/schedules/{id}/history', [ScheduleController::class, 'updateHistory'])
            ->name('schedules.history.update');
        Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
        Route::post('/schedules/{id}/batal', [ScheduleController::class, 'batal'])->name('schedules.batal');
        Route::get('/schedules/unavailable-dates', [ScheduleController::class, 'unavailableDates'])
            ->name('schedules.unavailable-dates');

        Route::get('/users/export', [UserManagementController::class, 'export'])->name('users.export');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
            ->name('users.reset-password');
        Route::resource('users', UserManagementController::class);

        Route::prefix('admin/laporan')->name('admin.laporan.')->group(function (): void {
            Route::get('/', [LaporanAdminController::class, 'index'])->name('index');
            Route::get('/{id_laporan}', [LaporanAdminController::class, 'show'])->name('show');
            Route::post('/{id_laporan}/terima', [LaporanAdminController::class, 'terima'])->name('terima');
            Route::post('/{id_laporan}/tolak', [LaporanAdminController::class, 'tolak'])->name('tolak');
        });

        Route::prefix('admin/inspeksi-ikl')->name('admin.inspeksi-ikl.')->group(function (): void {
            Route::get('/', [AdminInspeksiIklController::class, 'index'])->name('index');
            Route::get('/{id_jadwal}/input', [AdminInspeksiIklController::class, 'create'])->name('create');
            Route::post('/', [AdminInspeksiIklController::class, 'store'])->name('store');
            Route::get('/{id_jadwal}', [AdminInspeksiIklController::class, 'show'])->name('show');
        });
    });
});
