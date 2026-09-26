@extends('layouts.app') <!-- Sesuaikan dengan nama layout utama Anda -->

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Detail Laporan: {{ $report->kode_tiket }}</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="200">Nama Pelapor</th>
                    <td>{{ $report->nama_pelapor }}</td>
                </tr>
                <tr>
                    <th>NIK Pelapor</th>
                    <td>{{ $report->nik_pelapor }}</td>
                </tr>
                <tr>
                    <th>Nomor WhatsApp</th>
                    <td>{{ $report->no_wa }}</td>
                </tr>
                <tr>
                    <th>Kategori Laporan</th>
                    <td>{{ ucfirst(str_replace('_', ' ', $report->kategori_laporan)) }}</td>
                </tr>
                <tr>
                    <th>Lokasi (Desa / RT / RW)</th>
                    <td>Desa ID: {{ $report->id_desa }} - RT {{ $report->rt }} / RW {{ $report->rw }}</td>
                </tr>
                <tr>
                    <th>Deskripsi Laporan</th>
                    <td>{{ $report->deskripsi }}</td>
                </tr>
                <tr>
                    <th>Status Saat Ini</th>
                    <td>
                        @if($report->status_laporan === 'menunggu')
                            <span class="badge bg-warning text-dark">Menunggu</span>
                        @elseif($report->status_laporan === 'in_progress')
                            <span class="badge bg-info text-dark">Dalam Proses</span>
                        @elseif($report->status_laporan === 'ditolak')
                            <span class="badge bg-danger">Ditolak</span>
                        @else
                            <span class="badge bg-success">Selesai</span>
                        @endif
                    </td>
                </tr>

                @if($report->status_laporan === 'ditolak' && $report->alasan_penolakan)
                <tr>
                    <th>Alasan Penolakan</th>
                    <td class="text-danger">
                        <i class="fas fa-exclamation-circle" style="margin-right:4px;"></i>
                        {{ $report->alasan_penolakan }}
                    </td>
                </tr>
                @endif
                <tr>
                    <th>Waktu Laporan</th>
                    <td>{{ $report->created_at }}</td>
                </tr>
            </table>

            <div class="mt-3">
                <a href="{{ url('/reports') }}" class="btn btn-secondary">Kembali ke Daftar Laporan</a>
            </div>
        </div>
    </div>
</div>
@endsection
