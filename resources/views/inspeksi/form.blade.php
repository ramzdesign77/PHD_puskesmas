@extends('layouts.app')

@section('title', 'Form Inspeksi Kualitas Lingkungan (IKL) Air')
@section('page-title', 'Form IKL Air')
@section('page-subtitle', 'Pengisian hasil inspeksi kualitas sarana air bersih oleh petugas Sanitarian')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 fade-in">
    {{-- Header Info Jadwal --}}
    <div class="card p-6 border-t-4 border-blue-500">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-blue-500"></i> Informasi Kunjungan
                </h3>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
                    <div>
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-1">Tanggal Kunjungan</p>
                        <p class="font-medium text-gray-800">{{ $jadwal->tanggal_kunjungan?->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-1">Jenis Kunjungan</p>
                        <p class="font-medium text-gray-800">
                            {{ $jadwal->jenis_kunjungan === 'ikl_laporan_warga' ? 'Menindaklanjuti Laporan Warga' : 'IKL Rutin RT' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-1">Petugas Pelaksana</p>
                        <p class="font-medium text-gray-800">{{ $jadwal->operator?->nama_lengkap ?? session('user_name') }}</p>
                    </div>
                    @if($jadwal->laporan)
                    <div>
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-1">Lokasi (Dari Laporan)</p>
                        <p class="font-medium text-gray-800">
                            {{ $jadwal->laporan->desa?->nama_desa }} RT {{ $jadwal->laporan->rt }} / RW {{ $jadwal->laporan->rw }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            
            @if($jadwal->laporan)
            <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 md:w-1/3">
                <p class="text-xs font-bold text-gray-500 mb-2">Detail Laporan Warga</p>
                <div class="space-y-2">
                    <div class="flex justify-between items-center border-b border-gray-200 pb-1">
                        <span class="text-xs text-gray-500">Tiket</span>
                        <span class="text-xs font-mono font-bold bg-gray-200 px-1.5 rounded">{{ $jadwal->laporan->kode_tiket }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-1">
                        <span class="text-xs text-gray-500">Pelapor</span>
                        <span class="text-xs font-semibold">{{ $jadwal->laporan->nama_pelapor }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block mb-1">Masalah (Keluhan)</span>
                        <p class="text-xs italic text-gray-700 bg-white p-2 rounded border border-gray-100">
                            "{{ $jadwal->laporan->deskripsi }}"
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Form Pengisian IKL --}}
    <form method="POST" action="{{ route('inspeksi.submit', $jadwal->id_jadwal) }}" class="card overflow-hidden" id="form-ikl">
        @csrf
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-bold text-gray-800">Form Checklist Inspeksi Sanitasi</h3>
            <p class="text-xs text-gray-500 mt-1">
                Beri centang (checklist) pada kondisi yang <span class="font-bold text-red-500">BERBAHAYA / BURUK</span>.
                Kondisi yang baik dibiarkan kosong.
            </p>
        </div>

        <div class="p-6 space-y-6">
            {{-- Data Umum Sarana --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Inspeksi Aktual <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_inspeksi" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                        class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Sarana Air <span class="text-red-500">*</span></label>
                    <select name="jenis_sarana_air" required
                        class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" disabled selected>— Pilih Sarana Air —</option>
                        <option value="sumur_bor">Sumur Bor / Pompa</option>
                        <option value="sumur_terlindung">Sumur Gali Terlindung</option>
                        <option value="mata_air">Perlindungan Mata Air (PMA)</option>
                        <option value="pdam">Saluran Pipa PDAM</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jarak dari Sumber Pencemar (Meter) <span class="text-red-500">*</span></label>
                    <p class="text-xs text-gray-500 mb-2">Jarak dari sarana air ke sumber pencemar terdekat (seperti septic tank, jamban, pembuangan sampah).</p>
                    <div class="flex items-center">
                        <input type="number" name="jarak_sumber_pencemar" required min="0" max="999" placeholder="Contoh: 8"
                            class="w-32 text-sm border border-gray-300 rounded-l-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="bg-gray-100 border border-l-0 border-gray-300 rounded-r-xl px-4 py-2.5 text-sm text-gray-600 font-medium">Meter</span>
                    </div>
                </div>
            </div>

            <hr class="border-gray-100">

            {{-- Checklist Kondisi Fisik --}}
            <div>
                <div class="mb-4">
                    <h4 class="font-bold text-gray-800">Kondisi Fisik Sarana (Faktor Risiko)</h4>
                    <p class="text-xs text-gray-500">Centang kotak jika jawaban dari pertanyaan ini adalah <strong>YA</strong> (mengindikasikan risiko).</p>
                </div>

                <div class="space-y-3 bg-white border border-gray-200 rounded-xl p-4 divide-y divide-gray-100">
                    <label class="flex items-start gap-3 p-2 hover:bg-red-50 rounded-lg cursor-pointer transition-colors group">
                        <div class="flex-shrink-0 mt-0.5">
                            <input type="checkbox" name="p1_dinding_sumur_retak" value="1" class="w-5 h-5 rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 group-hover:text-red-800">1. Apakah dinding sumur/bangunan retak atau bocor?</p>
                            <p class="text-xs text-gray-500">Memungkinkan air kotor merembes masuk ke dalam sumber air.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-2 hover:bg-red-50 rounded-lg cursor-pointer transition-colors group pt-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <input type="checkbox" name="p2_penutup_tidak_rapat" value="1" class="w-5 h-5 rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 group-hover:text-red-800">2. Apakah penutup sarana air tidak ada atau tidak rapat?</p>
                            <p class="text-xs text-gray-500">Bisa dimasuki debu, kotoran, atau hewan.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-2 hover:bg-red-50 rounded-lg cursor-pointer transition-colors group pt-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <input type="checkbox" name="p3_lantai_becek_retak" value="1" class="w-5 h-5 rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 group-hover:text-red-800">3. Apakah lantai di sekitar sarana becek, retak, atau kotor?</p>
                            <p class="text-xs text-gray-500">Lantai yang tidak kedap air (radius minimal 1m) menyebabkan genangan.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-2 hover:bg-red-50 rounded-lg cursor-pointer transition-colors group pt-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <input type="checkbox" name="p4_spal_tersumbat" value="1" class="w-5 h-5 rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 group-hover:text-red-800">4. Apakah Saluran Pembuangan Air Limbah (SPAL) tersumbat atau tidak ada?</p>
                            <p class="text-xs text-gray-500">Air sisa cipratan menggenang dan bisa merembes kembali ke sumber.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-2 hover:bg-red-50 rounded-lg cursor-pointer transition-colors group pt-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <input type="checkbox" name="p5_air_keruh_berbau" value="1" class="w-5 h-5 rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer">
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 group-hover:text-red-800">5. Secara fisik, apakah air terlihat keruh, berwarna, atau berbau?</p>
                            <p class="text-xs text-gray-500">Indikator langsung pencemaran kualitas air secara fisik.</p>
                        </div>
                    </label>
                </div>
            </div>

            <hr class="border-gray-100">

            {{-- Rekomendasi & Tindak Lanjut --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Rekomendasi / Tindak Lanjut Sanitarian <span class="text-red-500">*</span></label>
                <textarea name="rekomendasi_sanitarian" required rows="4" minlength="5" placeholder="Saran perbaikan, edukasi yang telah diberikan, atau tindak lanjut klinis..."
                    class="w-full text-sm border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <a href="{{ route('schedules.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Jadwal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-lg shadow-green-200 transition-all transform hover:-translate-y-0.5 flex items-center gap-2"
                onclick="return confirm('Apakah Anda yakin data IKL sudah benar? Data ini akan disimpan dan mengubah status menjadi Selesai.')">
                <i class="fas fa-save"></i> Simpan Hasil IKL
            </button>
        </div>
    </form>
</div>
@endsection
