@extends('layouts.app')

@section('content')
<div class="space-y-6 fade-in">

    <a href="{{ route('admin.laporan.index') }}" class="text-sm text-gray-500 hover:underline">&larr; Kembali ke daftar laporan</a>

    {{-- Detail laporan --}}
    <div class="card p-6 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Detail Laporan — {{ $laporan->kode_tiket }}</h3>
            <span class="text-xs px-2 py-1 rounded-full
                @if($laporan->status_laporan === 'menunggu') bg-yellow-100 text-yellow-700
                @elseif($laporan->status_laporan === 'dibaca') bg-blue-100 text-blue-700
                @elseif($laporan->status_laporan === 'diterima') bg-green-100 text-green-700
                @else bg-red-100 text-red-700 @endif">
                {{ ucfirst($laporan->status_laporan) }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-400">Pelapor</span><br>{{ $laporan->nama_pelapor }}</div>
            <div><span class="text-gray-400">Desa</span><br>{{ $laporan->desa->nama_desa ?? '-' }}</div>
            <div><span class="text-gray-400">Kategori</span><br>{{ $laporan->kategori_laporan }}</div>
            <div><span class="text-gray-400">No. WA</span><br>{{ $laporan->no_wa ?? '-' }}</div>
        </div>

        <div>
            <span class="text-gray-400 text-sm">Deskripsi Masalah</span>
            <p class="text-sm mt-1">{{ $laporan->deskripsi }}</p>
        </div>

        @if($laporan->foto_bukti)
        <div>
            <span class="text-gray-400 text-sm">Foto Bukti</span><br>
            <img src="{{ asset('storage/' . $laporan->foto_bukti) }}" class="mt-2 rounded-lg max-w-xs">
        </div>
        @endif

        @if($laporan->status_laporan === 'ditolak' && $laporan->alasan_penolakan)
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
            <strong>Alasan penolakan:</strong> {{ $laporan->alasan_penolakan }}
        </div>
        @endif
    </div>

    {{-- Kalau sudah diterima: tampilkan jadwal yang sudah dibuat --}}
    @if($laporan->status_laporan === 'diterima' && $laporan->jadwal)
    <div class="card p-6">
        <h3 class="font-bold text-gray-800 mb-3">Jadwal Kunjungan</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-400">Tanggal</span><br>{{ $laporan->jadwal->tanggal_kunjungan }}</div>
            <div><span class="text-gray-400">Jenis</span><br>{{ $laporan->jadwal->jenis_kunjungan }}</div>
            <div><span class="text-gray-400">Petugas</span><br>{{ $laporan->jadwal->petugas->nama_petugas ?? '-' }}</div>
            <div><span class="text-gray-400">Paket Alat</span><br>{{ $laporan->jadwal->paketAlat->nama_paket ?? '-' }}</div>
        </div>
    </div>
    @endif

    {{-- Aksi: hanya tampil kalau belum diputuskan --}}
    @if(in_array($laporan->status_laporan, ['menunggu', 'dibaca']))
    <div class="grid grid-cols-2 gap-6">

        {{-- Form Tolak --}}
        <div class="card p-6">
            <h3 class="font-bold text-red-600 mb-3"><i class="fas fa-times-circle mr-1.5"></i>Tolak Laporan</h3>
            <form method="POST" action="{{ route('admin.laporan.tolak', $laporan->id_laporan) }}" class="space-y-3">
                @csrf
                <textarea name="alasan_penolakan" class="form-input w-full" rows="3"
                    placeholder="Alasan penolakan (contoh: laporan duplikat, data tidak valid, dll)" required></textarea>
                <button type="submit" class="btn-primary bg-red-600 hover:bg-red-700 w-full justify-center py-2">
                    Tolak Laporan
                </button>
            </form>
        </div>

        {{-- Form Terima + Buat Jadwal --}}
        <div class="card p-6">
            <h3 class="font-bold text-green-600 mb-3"><i class="fas fa-check-circle mr-1.5"></i>Terima & Jadwalkan Kunjungan</h3>
            <form method="POST" action="{{ route('admin.laporan.terima', $laporan->id_laporan) }}" class="space-y-3">
                @csrf

                <div>
                    <label class="form-label text-xs">Petugas</label>
                    <select name="id_petugas" class="form-input w-full" required>
                        <option value="" disabled selected>-- Pilih Petugas --</option>
                        @foreach($petugas as $p)
                            <option value="{{ $p->id_petugas }}">{{ $p->nama_petugas }} ({{ $p->jabatan }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label text-xs">Paket Alat</label>
                    <select name="id_paket" class="form-input w-full" required>
                        <option value="" disabled selected>-- Pilih Paket Alat --</option>
                        @foreach($paketAlat as $paket)
                            <option value="{{ $paket->id_paket }}">{{ $paket->nama_paket }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label text-xs">Tanggal Kunjungan</label>
                    <input type="date" name="tanggal_kunjungan" class="form-input w-full" required>
                </div>

                <div>
                    <label class="form-label text-xs">Jenis Kunjungan</label>
                    <select name="jenis_kunjungan" class="form-input w-full" required>
                        <option value="Inspeksi Awal">Inspeksi Awal</option>
                        <option value="Verifikasi Lapangan">Verifikasi Lapangan</option>
                        <option value="Tindak Lanjut">Tindak Lanjut</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary bg-green-600 hover:bg-green-700 w-full justify-center py-2">
                    Terima & Buat Jadwal
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
