<?php

use App\Http\Controllers\Api\EducationApiController;
use App\Http\Controllers\Api\LaporanWargaApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – Klinik Sanitasi Puskesmas
|--------------------------------------------------------------------------
| Endpoint ini digunakan oleh Flutter Mobile App (Warga).
| Tidak memerlukan autentikasi untuk pengiriman laporan.
| Prefix: /api
*/

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