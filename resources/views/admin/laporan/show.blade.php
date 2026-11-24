@extends('layouts.app')

@section('title', 'Detail Laporan ' . ($laporan->kode_tiket ?? ''))

@section('content')
<div class="space-y-6 fade-in max-w-7xl mx-auto pb-12">

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- HEADER RINGKAS (FULL WIDTH)                                  --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1.5">
            <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-ocean-600 transition-colors">
                <i class="fas fa-arrow-left text-[11px]"></i> Kembali ke daftar laporan
            </a>
            <div class="flex flex-wrap items-center gap-2.5 pt-0.5">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight font-heading">
                    Detail Laporan
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-mono font-bold bg-ocean-50 text-ocean-700 border border-ocean-200/80">
                    {{ $laporan->kode_tiket }}
                </span>
            </div>
            @if($laporan->created_at)
                <p class="text-xs text-slate-400 flex items-center gap-1.5">
                    <i class="far fa-clock text-[11px]"></i> Masuk pada {{ $laporan->created_at->translatedFormat('d F Y, H:i') }} WIB
                </p>
            @endif
        </div>

        {{-- Badge Status Laporan --}}
        <div class="flex items-center gap-2 self-start md:self-center shrink-0">
            @php
                $statusConfig = [
                    'menunggu' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'icon' => 'fa-clock', 'label' => 'Menunggu Verifikasi'],
                    'dibaca' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'fa-envelope-open-text', 'label' => 'Dibaca Admin'],
                    'diterima' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'fa-calendar-check', 'label' => 'Diterima & Terjadwal'],
                    'dijadwalkan' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'fa-calendar-check', 'label' => 'Dijadwalkan'],
                    'selesai' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'border' => 'border-teal-200', 'icon' => 'fa-check-double', 'label' => 'Selesai'],
                    'ditolak' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'icon' => 'fa-circle-xmark', 'label' => 'Ditolak'],
                ];
                $currStatus = $statusConfig[$laporan->status_laporan] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'icon' => 'fa-circle-info', 'label' => ucfirst($laporan->status_laporan)];
            @endphp
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-semibold border {{ $currStatus['bg'] }} {{ $currStatus['text'] }} {{ $currStatus['border'] }} shadow-xs">
                <i class="fas {{ $currStatus['icon'] }} text-[12px]"></i>
                {{ $currStatus['label'] }}
            </span>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- SPLIT VIEW (GRID 12 KOLOM)                                   --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- ──────────────────────────────────────────────────────────── --}}
        {{-- KOLOM KIRI (lg:col-span-7) — INFORMASI LAPORAN & BUKTI FOTO --}}
        {{-- ──────────────────────────────────────────────────────────── --}}
        <div class="lg:col-span-7 space-y-6">

            {{-- Kartu Utama Informasi Laporan --}}
            <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-100 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-file-lines text-ocean-500"></i> Informasi Pelapor & Lokasi
                    </h2>
                </div>

                {{-- Metadata Grid 2 Kolom --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-sm">
                    {{-- 1. Pelapor --}}
                    <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3.5 flex flex-col justify-between">
                        <span class="text-xs font-medium text-slate-400 flex items-center gap-1.5">
                            <i class="fas fa-user text-slate-400 text-[11px]"></i> Pelapor
                        </span>
                        <div class="mt-1">
                            <p class="font-bold text-slate-800">{{ $laporan->nama_pelapor }}</p>
                            @if(!empty($laporan->nik_pelapor))
                                <p class="text-[11px] text-slate-400 mt-0.5">NIK: {{ $laporan->nik_pelapor }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Desa / Lokasi --}}
                    <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3.5 flex flex-col justify-between">
                        <span class="text-xs font-medium text-slate-400 flex items-center gap-1.5">
                            <i class="fas fa-location-dot text-slate-400 text-[11px]"></i> Desa / Wilayah
                        </span>
                        <div class="mt-1">
                            <p class="font-bold text-slate-800">{{ $laporan->desa->nama_desa ?? '-' }}</p>
                            @if(!empty($laporan->rt) || !empty($laporan->rw))
                                <p class="text-[11px] text-slate-500 mt-0.5">RT {{ $laporan->rt ?? '-' }} / RW {{ $laporan->rw ?? '-' }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- 3. Kategori Laporan --}}
                    <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3.5 flex flex-col justify-between">
                        <span class="text-xs font-medium text-slate-400 flex items-center gap-1.5">
                            <i class="fas fa-tag text-slate-400 text-[11px]"></i> Kategori Masalah
                        </span>
                        <div class="mt-1.5">
                            @php
                                $kategoriLabel = $laporan->kategori_label ?? match ($laporan->kategori_laporan) {
                                    'air_sumur_keruh_berbau' => 'Air Sumur Keruh / Berbau',
                                    'sarang_nyamuk_dbd' => 'Sarang Nyamuk / DBD',
                                    'jamban_limbah_stbm' => 'Jamban & Limbah (STBM)',
                                    'tumpukan_sampah' => 'Tumpukan Sampah',
                                    default => ucwords(str_replace(['_', '-'], ' ', $laporan->kategori_laporan)),
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200/70">
                                <i class="fas fa-circle-dot text-[8px] text-sky-500"></i>
                                {{ $kategoriLabel }}
                            </span>
                        </div>
                    </div>

                    {{-- 4. Kontak WhatsApp --}}
                    <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3.5 flex flex-col justify-between">
                        <span class="text-xs font-medium text-slate-400 flex items-center gap-1.5">
                            <i class="fab fa-whatsapp text-emerald-500 text-[12px]"></i> No. WhatsApp
                        </span>
                        <div class="mt-1 flex items-center justify-between gap-2 flex-wrap">
                            @if(!empty($laporan->no_wa))
                                @php
                                    $rawWa = preg_replace('/[^0-9]/', '', $laporan->no_wa);
                                    if (str_starts_with($rawWa, '0')) {
                                        $waTarget = '62' . substr($rawWa, 1);
                                    } elseif (str_starts_with($rawWa, '62')) {
                                        $waTarget = $rawWa;
                                    } else {
                                        $waTarget = '62' . $rawWa;
                                    }
                                    $waGreeting = urlencode("Halo {$laporan->nama_pelapor}, kami dari Tim Sanitarian Puskesmas Sumbersari mengenai laporan {$laporan->kode_tiket}.");
                                @endphp
                                <span class="font-bold text-slate-800">{{ $laporan->no_wa }}</span>
                                <a href="https://wa.me/{{ $waTarget }}?text={{ $waGreeting }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-500 hover:bg-emerald-600 text-white shadow-xs hover:shadow transition-colors"
                                    title="Hubungi pelapor via WhatsApp">
                                    <i class="fab fa-whatsapp"></i> Chat WA
                                </a>
                            @else
                                <span class="text-slate-400 text-xs italic">Tidak tersedia</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Alamat Detail & Patokan (Jika Diisi) --}}
                @if(!empty($laporan->alamat_detail) || !empty($laporan->patokan_alamat))
                    <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3.5 text-xs space-y-1">
                        <span class="font-semibold text-slate-700 flex items-center gap-1.5">
                            <i class="fas fa-map-location-dot text-ocean-500"></i> Alamat Lengkap & Patokan
                        </span>
                        <p class="text-slate-600 leading-relaxed">
                            {{ $laporan->alamat_detail ?? '-' }}
                            @if(!empty($laporan->patokan_alamat))
                                <span class="text-slate-500 font-medium">(Patokan: {{ $laporan->patokan_alamat }})</span>
                            @endif
                        </p>
                    </div>
                @endif

                {{-- Deskripsi Masalah --}}
                <div class="space-y-1.5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <i class="fas fa-align-left text-[11px]"></i> Deskripsi Masalah
                    </span>
                    <div class="bg-slate-50 border border-slate-200/70 rounded-xl p-4 text-slate-700 text-sm leading-relaxed whitespace-pre-line font-normal">
                        {{ $laporan->deskripsi ?: 'Tidak ada keterangan deskripsi tambahan.' }}
                    </div>
                </div>

                {{-- Foto Bukti (Interaktif Lightbox) --}}
                <div class="space-y-2 pt-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <i class="fas fa-camera text-[11px]"></i> Foto Bukti Laporan
                    </span>

                    @if(!empty($laporan->foto_bukti))
                        @php
                            $fotoUrl = \Illuminate\Support\Str::startsWith($laporan->foto_bukti, ['http://', 'https://'])
                                ? $laporan->foto_bukti
                                : asset('storage/' . $laporan->foto_bukti);
                        @endphp
                        <div class="relative group cursor-pointer overflow-hidden rounded-xl border border-slate-200 bg-slate-900 shadow-xs"
                             data-image-viewer-trigger
                             data-image-url="{{ $fotoUrl }}"
                             data-image-caption="Foto bukti laporan {{ $laporan->kode_tiket }} — {{ $laporan->nama_pelapor }}"
                             role="button"
                             tabindex="0"
                             aria-label="Lihat dan perbesar foto bukti">
                            <img src="{{ $fotoUrl }}"
                                 alt="Foto bukti {{ $laporan->kode_tiket }}"
                                 class="w-full max-h-72 object-cover rounded-xl transition-transform duration-300 group-hover:scale-[1.02] group-hover:opacity-90 block">
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-slate-950/45 text-white opacity-0 backdrop-blur-[2px] transition-all duration-200 group-hover:opacity-100 group-focus-visible:opacity-100">
                                <span class="rounded-full bg-white/20 p-3 backdrop-blur-md shadow-lg transform transition-transform group-hover:scale-110">
                                    <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12s-3.5 6.75-9.75 6.75S2.25 12 2.25 12Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </span>
                                <span class="text-xs font-bold tracking-wide flex items-center gap-1.5 drop-shadow-md">
                                    <i class="fas fa-magnifying-glass-plus text-[11px]"></i> Klik untuk Lihat & Zoom Foto
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="p-6 text-center rounded-xl border border-dashed border-slate-200 bg-slate-50/60 text-slate-400 text-xs">
                            <i class="far fa-image text-slate-300 text-2xl mb-1.5 block"></i>
                            Pelapor tidak melampirkan foto bukti.
                        </div>
                    @endif
                </div>

                {{-- Banner Jika Laporan Telah Ditolak --}}
                @if($laporan->status_laporan === 'ditolak' && !empty($laporan->alasan_penolakan))
                    <div class="bg-rose-50 border border-rose-200/90 rounded-xl p-4 text-rose-800 text-sm">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-circle-exclamation text-rose-500 text-base mt-0.5 shrink-0"></i>
                            <div>
                                <p class="font-bold text-rose-900">Laporan Telah Ditolak</p>
                                <p class="text-rose-700 text-xs mt-1 leading-relaxed">
                                    <span class="font-semibold">Alasan:</span> {{ $laporan->alasan_penolakan }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Kartu Info Jadwal (Jika Status Diterima / Sudah Ada Jadwal) --}}
            @if($laporan->jadwal)
                <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-emerald-100 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                        <h3 class="text-sm font-bold text-emerald-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-calendar-check text-emerald-600"></i> Jadwal Inspeksi Lapangan
                        </h3>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i class="fas fa-circle-check text-[10px]"></i>
                            {{ ucfirst($laporan->jadwal->status_kunjungan ?? 'terjadwal') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="bg-emerald-50/50 p-3.5 rounded-xl border border-emerald-100/80">
                            <span class="text-slate-400 block font-medium mb-1">Tanggal Kunjungan</span>
                            <span class="font-bold text-slate-800 text-sm">
                                {{ \Carbon\Carbon::parse($laporan->jadwal->tanggal_kunjungan)->translatedFormat('l, d F Y') }}
                            </span>
                        </div>
                        <div class="bg-emerald-50/50 p-3.5 rounded-xl border border-emerald-100/80">
                            <span class="text-slate-400 block font-medium mb-1">Jenis Kunjungan</span>
                            <span class="font-bold text-slate-800 text-sm">
                                {{ $laporan->jadwal->jenis_kunjungan }}
                            </span>
                        </div>
                        <div class="bg-emerald-50/50 p-3.5 rounded-xl border border-emerald-100/80">
                            <span class="text-slate-400 block font-medium mb-1">Petugas Sanitarian</span>
                            <span class="font-bold text-slate-800 text-sm">
                                {{ $laporan->jadwal->petugas->nama_petugas ?? $laporan->jadwal->operator->nama_lengkap ?? '-' }}
                            </span>
                        </div>
                        <div class="bg-emerald-50/50 p-3.5 rounded-xl border border-emerald-100/80">
                            <span class="text-slate-400 block font-medium mb-1">Paket Alat</span>
                            <span class="font-bold text-slate-800 text-sm">
                                {{ $laporan->jadwal->paketAlat->nama_paket ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- ──────────────────────────────────────────────────────────── --}}
        {{-- KOLOM KANAN (lg:col-span-5) — PANEL AKSI VERIFIKASI (STICKY) --}}
        {{-- ──────────────────────────────────────────────────────────── --}}
        <div class="lg:col-span-5 lg:sticky lg:top-6 space-y-6">

            @if(in_array($laporan->status_laporan, ['menunggu', 'dibaca']))
                {{-- Panel Aksi Berbasis Tab Switcher --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    {{-- Header Panel & Tab Switcher --}}
                    <div class="p-5 border-b border-slate-100 bg-slate-50/60 space-y-3.5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <i class="fas fa-clipboard-check text-ocean-600"></i> Aksi Verifikasi
                            </h2>
                            <span class="text-[11px] font-semibold text-ocean-700 bg-ocean-50 border border-ocean-200/70 px-2 py-0.5 rounded-md">
                                Perlu Tindakan
                            </span>
                        </div>

                        {{-- Tab Switcher --}}
                        <div class="grid grid-cols-2 p-1 bg-slate-200/70 rounded-xl gap-1 text-xs font-semibold">
                            <button type="button" id="tab-btn-terima" onclick="switchActionTab('terima')"
                                class="tab-action-btn flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg transition-all duration-200 bg-white text-ocean-700 shadow-xs font-bold">
                                <i class="fas fa-check-circle text-emerald-600 text-[11px]"></i> Terima & Jadwalkan
                            </button>
                            <button type="button" id="tab-btn-tolak" onclick="switchActionTab('tolak')"
                                class="tab-action-btn flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg transition-all duration-200 text-slate-600 hover:text-rose-600 font-medium">
                                <i class="fas fa-times-circle text-rose-500 text-[11px]"></i> Tolak Laporan
                            </button>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6">
                        {{-- ── TAB 1: FORM TERIMA & BUAT JADWAL ── --}}
                        <div id="tab-content-terima" class="space-y-4">
                            <form method="POST" action="{{ route('admin.laporan.terima', $laporan->id_laporan) }}" class="space-y-3.5">
                                @csrf

                                {{-- Petugas --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Petugas Sanitarian <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="id_petugas" class="form-input w-full text-sm rounded-xl border-slate-200 focus:border-ocean-500 focus:ring-ocean-500" required>
                                        <option value="" disabled selected>-- Pilih Petugas --</option>
                                        @foreach($petugas as $p)
                                            <option value="{{ $p->id_petugas }}">{{ $p->nama_petugas }} ({{ $p->jabatan }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Paket Alat --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Paket Alat Inspeksi <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="id_paket" class="form-input w-full text-sm rounded-xl border-slate-200 focus:border-ocean-500 focus:ring-ocean-500" required>
                                        <option value="" disabled selected>-- Pilih Paket Alat --</option>
                                        @foreach($paketAlat as $paket)
                                            <option value="{{ $paket->id_paket }}">{{ $paket->nama_paket }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Tanggal & Jenis Kunjungan (2 Kolom Sejajar) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                                            Tanggal Kunjungan <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="date" name="tanggal_kunjungan" min="{{ date('Y-m-d') }}"
                                            class="form-input w-full text-sm rounded-xl border-slate-200 focus:border-ocean-500 focus:ring-ocean-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                                            Jenis Kunjungan <span class="text-rose-500">*</span>
                                        </label>
                                        <select name="jenis_kunjungan" class="form-input w-full text-sm rounded-xl border-slate-200 focus:border-ocean-500 focus:ring-ocean-500" required>
                                            <option value="Inspeksi Awal" selected>Inspeksi Awal</option>
                                            <option value="Verifikasi Lapangan">Verifikasi Lapangan</option>
                                            <option value="Tindak Lanjut">Tindak Lanjut</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <button type="submit"
                                        class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold py-2.5 px-4 rounded-xl shadow-xs hover:shadow transition-all duration-200 flex items-center justify-center gap-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                                        <i class="fas fa-calendar-check text-[13px]"></i> Simpan & Jadwalkan Kunjungan
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- ── TAB 2: FORM TOLAK LAPORAN ── --}}
                        <div id="tab-content-tolak" class="hidden space-y-4">
                            <form method="POST" action="{{ route('admin.laporan.tolak', $laporan->id_laporan) }}" class="space-y-3.5">
                                @csrf

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Alasan Penolakan <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="alasan_penolakan" rows="4"
                                        class="form-input w-full text-sm rounded-xl border-slate-200 focus:border-rose-500 focus:ring-rose-500"
                                        placeholder="Tulis alasan penolakan secara jelas (contoh: Laporan duplikat, data tidak valid, bukan wilayah kerja Puskesmas, dll)"
                                        required minlength="5"></textarea>
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        Alasan penolakan akan dicatat dan status laporan berubah menjadi <strong>Ditolak</strong>.
                                    </p>
                                </div>

                                <div class="pt-2">
                                    <button type="submit"
                                        class="w-full bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-semibold py-2.5 px-4 rounded-xl shadow-xs hover:shadow transition-all duration-200 flex items-center justify-center gap-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1">
                                        <i class="fas fa-ban text-[13px]"></i> Konfirmasi Tolak Laporan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                {{-- Status Card Jika Laporan Sudah Diputuskan --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-100 text-center space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center {{ $currStatus['bg'] }} {{ $currStatus['text'] }}">
                        <i class="fas {{ $currStatus['icon'] }} text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Laporan Telah Diproses</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Status saat ini: <strong class="{{ $currStatus['text'] }}">{{ $currStatus['label'] }}</strong>.
                            Tidak ada tindakan verifikasi lanjutan yang diperlukan saat ini.
                        </p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('admin.laporan.index') }}"
                            class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                            <i class="fas fa-list-check"></i> Kembali ke Rekap Laporan
                        </a>
                    </div>
                </div>
            @endif

            {{-- Petunjuk Cepat untuk Admin --}}
            <div class="bg-gradient-to-br from-slate-50 to-ocean-50/40 rounded-2xl p-4 border border-ocean-100/70 text-xs text-slate-600 space-y-2">
                <p class="font-semibold text-slate-700 flex items-center gap-1.5">
                    <i class="fas fa-lightbulb text-amber-500"></i> Petunjuk Verifikasi Admin:
                </p>
                <ul class="space-y-1.5 list-disc list-inside text-slate-500 text-[11px] leading-relaxed">
                    <li>Periksa kesesuaian kategori masalah dan foto bukti yang dikirim warga.</li>
                    <li>Gunakan tombol <strong>Chat WA</strong> untuk mengonfirmasi detail lokasi jika diperlukan.</li>
                    <li>Jadwalkan kunjungan kepada Sanitarian yang bertugas sesuai jadwal shift.</li>
                </ul>
            </div>

        </div>

    </div>

</div>

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- JAVASCRIPT: TAB SWITCHER & KEYBOARD ACCESS                  --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<script>
function switchActionTab(activeTab) {
    const btnTerima = document.getElementById('tab-btn-terima');
    const btnTolak = document.getElementById('tab-btn-tolak');
    const contentTerima = document.getElementById('tab-content-terima');
    const contentTolak = document.getElementById('tab-content-tolak');

    if (!btnTerima || !btnTolak || !contentTerima || !contentTolak) return;

    if (activeTab === 'terima') {
        contentTerima.classList.remove('hidden');
        contentTolak.classList.add('hidden');

        btnTerima.className = 'tab-action-btn flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg transition-all duration-200 bg-white text-ocean-700 shadow-xs font-bold';
        btnTolak.className = 'tab-action-btn flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg transition-all duration-200 text-slate-600 hover:text-rose-600 font-medium';
    } else {
        contentTerima.classList.add('hidden');
        contentTolak.classList.remove('hidden');

        btnTolak.className = 'tab-action-btn flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg transition-all duration-200 bg-white text-rose-700 shadow-xs font-bold';
        btnTerima.className = 'tab-action-btn flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg transition-all duration-200 text-slate-600 hover:text-ocean-700 font-medium';
    }
}

// Aksesibilitas keyboard untuk pembuka gambar lightbox
document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.querySelector('[data-image-viewer-trigger]');
    if (trigger) {
        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                trigger.click();
            }
        });
    }
});
</script>
@endsection

@section('modals')
    @include('partials.image-viewer')
@endsection
