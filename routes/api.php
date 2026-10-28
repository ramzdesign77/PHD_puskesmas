<?php

use App\Http\Controllers\Api\EducationApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\LaporanWargaApiController;
use App\Http\Controllers\Api\InspectionController;
use App\Http\Middleware\OfficerAuth;
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

Route::prefix('v1/officer')
    ->middleware(OfficerAuth::class)
    ->name('api.v1.officer.')
    ->group(function (): void {
        Route::post('/inspections', [InspectionController::class, 'store'])
            ->name('inspections.store');
    });

// ── Laporan Warga (tidak perlu auth) ──────────────────────────────────────
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
    $path = 'educations/' . $cleanFilename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404, 'Gambar tidak ditemukan');
    }

    return response()->file(storage_path('app/public/' . $path));
})->where('filename', '.*');
