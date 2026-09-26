@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Kirim Laporan')
@section('page-subtitle', 'Laporkan masalah kesehatan lingkungan di sekitar Anda')

@push('styles')
<link rel="stylesheet" href="{{ asset('cssReport/citizen.css') }}">
@endpush

@section('content')
<div class="stack-6 fade-in">

    <div class="citizen-layout">
        {{-- Form --}}
        <div class="card form-card" x-data="reportForm()">
            <div class="form-card-header">
                <div class="form-card-icon medical-gradient">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <h3 class="form-card-title">Form Pengaduan Baru</h3>
                    <p class="form-card-subtitle">Lengkapi semua data dengan benar</p>
                </div>
            </div>

            <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data" class="report-form">
                @csrf

                {{-- Category --}}
                <div>
                    <label class="form-label"><i class="fas fa-tag field-icon"></i>Kategori Masalah <span class="required-mark">*</span></label>
                    <select id="report-category" name="category" class="form-input" required>
                        <option value="" disabled selected>— Pilih Kategori —</option>
                        <option value="Air Bersih">💧 Air Bersih</option>
                        <option value="Sanitasi">🚿 Sanitasi / Jamban</option>
                        <option value="Sampah">🗑️ Pengelolaan Sampah</option>
                        <option value="Jentik Nyamuk">🦟 Jentik Nyamuk / DBD</option>
                        <option value="Limbah">🏭 Limbah Industri</option>
                        <option value="PHBS">🧼 Perilaku Hidup Bersih (PHBS)</option>
                        <option value="Lainnya">📋 Lainnya</option>
                    </select>
                </div>

                {{-- Description --}}
                <div>
                    <label class="form-label"><i class="fas fa-align-left field-icon"></i>Deskripsi Masalah <span class="required-mark">*</span></label>
                    <textarea id="report-description" name="description" rows="4" class="form-input textarea-input"
                              placeholder="Jelaskan masalah secara detail: lokasi spesifik, sejak kapan, dampak yang dirasakan..." required></textarea>
                    <p class="field-hint">Minimum 10 karakter</p>
                </div>

                {{-- Image Upload --}}
                <div x-data="{ preview: null, dragging: false }">
                    <label class="form-label"><i class="fas fa-camera field-icon"></i>Foto Bukti</label>
                    <div class="upload-box"
                         :class="{ 'dragging': dragging }"
                         @dragover.prevent="dragging = true"
                         @dragleave.prevent="dragging = false"
                         @drop.prevent="dragging = false; preview = URL.createObjectURL($event.dataTransfer.files[0])"
                         @click="$refs.fileInput.click()">
                        <template x-if="!preview">
                            <div>
                                <i class="fas fa-cloud-upload-alt upload-placeholder-icon"></i>
                                <p class="upload-placeholder-text">Klik atau drag & drop foto di sini</p>
                                <p class="upload-placeholder-hint">JPG, PNG, WEBP — Maks. 5 MB</p>
                            </div>
                        </template>
                        <template x-if="preview">
                            <div>
                                <img :src="preview" class="upload-preview-img">
                                <p class="upload-success-text"><i class="fas fa-check-circle"></i> Foto dipilih</p>
                            </div>
                        </template>
                        <input type="file" name="image" x-ref="fileInput" class="file-input-hidden" accept="image/*"
                               @change="preview = URL.createObjectURL($event.target.files[0])">
                    </div>
                </div>

                {{-- Location --}}
                <div>
                    <label class="form-label"><i class="fas fa-map-marker-alt field-icon"></i>Lokasi Kejadian <span class="required-mark">*</span></label>
                    <div class="location-input-wrap">
                        <input id="report-location" type="text" name="location" class="form-input location-input"
                               placeholder="Contoh: Jl. Mawar No.5, RT 03/02, Kelurahan Sumbersari" required>
                        <i class="fas fa-map-pin location-pin-icon"></i>
                    </div>
                    {{-- GPS Placeholder Map --}}
                    <div class="location-map">
                        <div style="text-align:center;">
                            <i class="fas fa-map-marked-alt location-map-icon"></i>
                            <p class="location-map-label">Peta Lokasi (GPS)</p>
                            <button type="button" class="locate-me-btn">
                                <i class="fas fa-crosshairs" style="margin-right:0.25rem;"></i>Gunakan Lokasi Saya
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" id="btn-submit-report" class="btn-primary submit-btn">
                    <i class="fas fa-paper-plane"></i>
                    Kirim Laporan
                </button>
            </form>
        </div>

        {{-- Tips Sidebar --}}
        <div class="sidebar-stack">
            <div class="card tips-card">
                <h4 class="tips-title">
                    <i class="fas fa-lightbulb" style="color:#facc15;"></i> Tips Pelaporan
                </h4>
                <ul class="tips-list">
                    @foreach([
                        ['icon' => 'fa-camera', 'color' => 'blue', 'tip' => 'Sertakan foto yang jelas dan detail'],
                        ['icon' => 'fa-map-marker-alt', 'color' => 'red', 'tip' => 'Berikan alamat selengkap mungkin'],
                        ['icon' => 'fa-align-left', 'color' => 'green', 'tip' => 'Jelaskan sejak kapan masalah terjadi'],
                        ['icon' => 'fa-tag', 'color' => 'purple', 'tip' => 'Pilih kategori yang paling sesuai'],
                    ] as $tip)
                    <li class="tip-item">
                        <div class="tip-icon-box {{ $tip['color'] }}">
                            <i class="fas {{ $tip['icon'] }}"></i>
                        </div>
                        <p class="tip-text">{{ $tip['tip'] }}</p>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="card emergency-card">
                <h4 class="emergency-title">
                    <i class="fas fa-phone-alt"></i> Darurat?
                </h4>
                <p class="emergency-text">Hubungi langsung Puskesmas Sumbersari:</p>
                <a href="tel:+62311234567" class="emergency-phone">
                    <i class="fas fa-phone"></i> (0331) 123-4567
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
function reportForm() { return {}; }
</script>
@endsection
