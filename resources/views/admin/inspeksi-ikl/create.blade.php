@extends('layouts.app')

@section('title', 'Input Hasil IKL')

@section('page-title', 'Input Hasil Inspeksi IKL')
@section('page-subtitle', 'Form pengisian hasil inspeksi kualitas air oleh petugas lapangan')

@section('content')
<div class="space-y-6 fade-in">

    <a href="{{ route('admin.inspeksi-ikl.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 transition-colors">
        <i class="fas fa-arrow-left text-xs"></i> Kembali ke Rekap IKL
    </a>

    {{-- ── Konteks Jadwal ───────────────────────────────────────────────── --}}
    <div class="card p-5 bg-blue-50 border border-blue-100">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-blue-400 font-medium">Kode Tiket</p>
                <p class="font-mono font-semibold text-blue-800">{{ $jadwal->laporan->kode_tiket ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-blue-400 font-medium">Pelapor</p>
                <p class="font-semibold text-blue-800">{{ $jadwal->laporan->nama_pelapor ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-blue-400 font-medium">Lokasi</p>
                <p class="text-blue-800">{{ $jadwal->laporan->desa->nama_desa ?? '-' }}, RT {{ $jadwal->laporan->rt }}/{{ $jadwal->laporan->rw }}</p>
            </div>
            <div>
                <p class="text-xs text-blue-400 font-medium">Petugas</p>
                <p class="font-semibold text-blue-800">{{ $jadwal->petugas->nama_petugas ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- ── Validation Errors ──────────────────────────────────────────────── --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="text-sm font-medium text-red-700 mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Terdapat kesalahan input:</p>
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.inspeksi-ikl.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="id_jadwal" value="{{ $jadwal->id_jadwal }}">

        {{-- ── A. Data Sarana Air ──────────────────────────────────────── --}}
        <div class="card p-6 space-y-5">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2 pb-3 border-b border-gray-100">
                <i class="fas fa-water text-blue-500"></i> A. Data Sarana Air
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jenis Sarana Air <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_sarana_air" id="jenis_sarana_air" class="form-input w-full" required>
                        <option value="" disabled {{ old('jenis_sarana_air') ? '' : 'selected' }}>-- Pilih Jenis Sarana --</option>
                        <option value="sumur_bor"         {{ old('jenis_sarana_air') === 'sumur_bor'         ? 'selected' : '' }}>Sumur Bor</option>
                        <option value="sumur_terlindung"  {{ old('jenis_sarana_air') === 'sumur_terlindung'  ? 'selected' : '' }}>Sumur Terlindung (SGL)</option>
                        <option value="pdam"              {{ old('jenis_sarana_air') === 'pdam'              ? 'selected' : '' }}>PDAM / Perpipaan</option>
                        <option value="mata_air"          {{ old('jenis_sarana_air') === 'mata_air'          ? 'selected' : '' }}>Mata Air Terlindung</option>
                        <option value="lainnya"           {{ old('jenis_sarana_air') === 'lainnya'           ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jarak ke Sumber Pencemar (meter) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="jarak_sumber_pencemar" id="jarak_sumber_pencemar"
                               min="0" max="999" value="{{ old('jarak_sumber_pencemar') }}"
                               class="form-input w-full pr-12" placeholder="Contoh: 15" required>
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">meter</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>Standar Permenkes: minimal 10 m dari jamban/kandang
                    </p>
                </div>
            </div>
        </div>

        {{-- ── B. Checklist Parameter Sanitasi ────────────────────────── --}}
        <div class="card p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2 pb-3 border-b border-gray-100">
                <i class="fas fa-clipboard-check text-medical-red"></i> B. Checklist Parameter Sanitasi
                <span class="text-xs font-normal text-gray-400 ml-auto">Centang jika kondisi tersebut <span class="font-semibold text-red-600">DITEMUKAN / BERMASALAH</span></span>
            </h3>

            <div class="space-y-3" id="checklist-container">
                @foreach($labelParameter as $field => $label)
                <label class="flex items-center gap-3 p-4 border border-gray-100 rounded-xl cursor-pointer
                              hover:border-red-200 hover:bg-red-50 transition-colors group
                              has-[:checked]:border-red-300 has-[:checked]:bg-red-50"
                       for="{{ $field }}">
                    <input type="checkbox" name="{{ $field }}" id="{{ $field }}"
                           value="1" class="w-4 h-4 accent-red-500 cursor-pointer"
                           {{ old($field) ? 'checked' : '' }}
                           onchange="hitungSkor()">
                    <span class="text-sm text-gray-700 group-has-[:checked]:text-red-700 group-has-[:checked]:font-medium">
                        {{ $label }}
                    </span>
                    <i class="fas fa-times-circle text-red-400 ml-auto opacity-0 group-has-[:checked]:opacity-100 transition-opacity"></i>
                </label>
                @endforeach
            </div>

            {{-- Skor Visual (auto-hitung) --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mt-2">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Skor Risiko Sementara</span>
                    <span id="skor-display" class="text-lg font-bold text-gray-800">0/5</span>
                </div>
                <div class="flex gap-1.5" id="skor-bar">
                    @for($i = 0; $i < 5; $i++)
                        <div class="skor-block flex-1 h-3 rounded bg-gray-200 transition-colors duration-200"></div>
                    @endfor
                </div>
                <p id="kategori-preview" class="text-xs text-gray-400 mt-2">Isi checklist untuk melihat prakiraan kategori risiko</p>
            </div>
        </div>

        {{-- ── C. Tanggal & Rekomendasi ────────────────────────────────── --}}
        <div class="card p-6 space-y-5">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2 pb-3 border-b border-gray-100">
                <i class="fas fa-notes-medical text-indigo-500"></i> C. Hasil & Rekomendasi
            </h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tanggal Inspeksi Aktual <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal_inspeksi" id="tanggal_inspeksi"
                       max="{{ date('Y-m-d') }}"
                       value="{{ old('tanggal_inspeksi', date('Y-m-d')) }}"
                       class="form-input w-full md:w-64" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Rekomendasi Sanitarian <span class="text-red-500">*</span>
                </label>
                <textarea name="rekomendasi_sanitarian" id="rekomendasi_sanitarian" rows="5"
                          minlength="20" maxlength="2000" required
                          class="form-input w-full resize-none"
                          placeholder="Tulis rekomendasi spesifik untuk perbaikan sarana air ini. Contoh: 'Warga disarankan memperbaiki dinding sumur yang retak dengan semen, menutup rapat tutup sumur, dan membuat saluran pembuangan air limbah (SPAL) minimal 1 meter dari bibir sumur...'">{{ old('rekomendasi_sanitarian') }}</textarea>
                <div class="flex justify-between mt-1">
                    <p class="text-xs text-gray-400">Minimal 20 karakter. Harap isi dengan saran yang spesifik dan actionable.</p>
                    <span id="char-count" class="text-xs text-gray-400">0/2000</span>
                </div>
            </div>
        </div>

        {{-- ── Submit ──────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 justify-end">
            <a href="{{ route('admin.inspeksi-ikl.index') }}" class="btn-secondary">
                Batal
            </a>
            <button type="submit" id="btn-submit" class="btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan Hasil IKL
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function hitungSkor() {
    const checkboxes = document.querySelectorAll('#checklist-container input[type="checkbox"]');
    let skor = 0;
    checkboxes.forEach(cb => { if (cb.checked) skor++; });

    document.getElementById('skor-display').textContent = skor + '/5';

    const blocks = document.querySelectorAll('.skor-block');
    blocks.forEach((b, i) => {
        b.className = 'skor-block flex-1 h-3 rounded transition-colors duration-200';
        if (i < skor) {
            b.classList.add(skor >= 3 ? 'bg-red-400' : skor >= 1 ? 'bg-amber-400' : 'bg-emerald-400');
        } else {
            b.classList.add('bg-gray-200');
        }
    });

    const preview = document.getElementById('kategori-preview');
    if (skor === 0) {
        preview.textContent = '✅ Prakiraan: AMAN (tidak ditemukan masalah)';
        preview.className = 'text-xs text-emerald-600 mt-2 font-medium';
    } else if (skor <= 2) {
        preview.textContent = '⚠️ Prakiraan: RISIKO SEDANG (' + skor + ' parameter bermasalah)';
        preview.className = 'text-xs text-amber-600 mt-2 font-medium';
    } else {
        preview.textContent = '🔴 Prakiraan: RISIKO TINGGI (' + skor + ' parameter bermasalah)';
        preview.className = 'text-xs text-red-600 mt-2 font-medium';
    }
}

// Character counter untuk textarea
const textarea = document.getElementById('rekomendasi_sanitarian');
const charCount = document.getElementById('char-count');
textarea.addEventListener('input', () => {
    const len = textarea.value.length;
    charCount.textContent = len + '/2000';
    charCount.className = 'text-xs ' + (len < 20 ? 'text-red-400' : 'text-gray-400');
});

// Inisialisasi jika ada old() value
hitungSkor();
if (textarea.value) {
    charCount.textContent = textarea.value.length + '/2000';
}
</script>
@endsection
