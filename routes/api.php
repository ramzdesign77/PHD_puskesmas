<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EducationApiController;
use App\Http\Controllers\Api\LaporanWargaApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\OfficerApiController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – Klinik Sanitasi Puskesmas
|--------------------------------------------------------------------------
| Endpoint ini digunakan oleh Flutter Mobile App (Warga).
| Tidak memerlukan autentikasi untuk pengiriman laporan.
| Prefix: /api
*/

// ── Autentikasi Flutter Mobile ────────────────────────────────────────────
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/register', [MobileAuthController::class, 'register'])->name('register');
    Route::post('/login', [MobileAuthController::class, 'login'])->name('login');
    Route::post('/logout', [MobileAuthController::class, 'logout'])->name('logout');
});

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login');
    Route::post('/register-warga', [AuthController::class, 'registerWarga'])
        ->middleware('throttle:5,1')
        ->name('register-warga');

    Route::middleware('auth.mobile')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

$registerOfficerRoutes = static function (string $prefix, string $name): void {
    Route::prefix($prefix)
        ->name($name)
        ->middleware('mobile.token')
        ->group(function (): void {
            Route::get('/dashboard', [OfficerApiController::class, 'dashboard'])->name('dashboard');
            Route::get('/tasks', [OfficerApiController::class, 'tasks'])->name('tasks');
            Route::post('/reports/{id_laporan}/verify', [OfficerApiController::class, 'verifyReport'])
                ->whereNumber('id_laporan')
                ->name('reports.verify');
            Route::post('/inspections', [OfficerApiController::class, 'storeInspection'])->name('inspections.store');
            Route::get('/stbm-desa', [OfficerApiController::class, 'stbmDesa'])->name('stbm-desa');
        });
};

$registerOfficerRoutes('v1/officer', 'api.v1.officer.');
$registerOfficerRoutes('officer', 'api.officer.');

// ── Laporan Warga (tidak perlu auth) ──────────────────────────────────────
Route::post('/reports', [ReportController::class, 'store'])->name('api.reports.store');

Route::prefix('laporan')->name('api.laporan.')->group(function () {
    // POST /api/laporan — kirim laporan baru dari Flutter
    Route::post('/', [LaporanWargaApiController::class, 'store'])->name('store');

    // GET /api/laporan/{kode_tiket} — cek status laporan by kode tiket
    Route::get('/{kode_tiket}', [LaporanWargaApiController::class, 'statusByTiket'])->name('status');
});

// ── Referensi Data ────────────────────────────────────────────────────────
// GET /api/desa — daftar desa untuk dropdown Flutter
Route::get('/desa', [LaporanWargaApiController::class, 'listDesa'])->name('api.desa.list');
Route::get('/edukasi', [EducationApiController::class, 'index'])->name('api.edukasi.index');

use Illuminate\Support\Facades\Storage;

Route::get('/get-image/{filename}', function ($filename) {
    // Menghapus prefix 'educations/' jika terbawa di URL agar path tidak ganda
    $cleanFilename = str_replace('educations/', '', $filename);
    $path = 'educations/'.$cleanFilename;

    if (! Storage::disk('public')->exists($path)) {
        abort(404, 'Gambar tidak ditemukan');
    }

    return response()->file(storage_path('app/public/'.$path));
})->where('filename', '.*');
