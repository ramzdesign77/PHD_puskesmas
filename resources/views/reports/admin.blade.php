@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Rekap Semua Laporan')
@section('page-subtitle', 'Ringkasan seluruh laporan program Kesling')

@push('styles')
<link rel="stylesheet" href="{{ asset('cssReport/admin.css') }}?v={{ filemtime(public_path('cssReport/admin.css')) }}">
@endpush

@section('content')
<div class="stack-6 fade-in">

    {{-- Summary Cards --}}
    <div class="admin-summary-grid">
        @foreach([
            ['label' => 'Total Laporan', 'value' => $summary['total'], 'color' => 'red', 'icon' => 'fa-file-alt'],
            ['label' => 'Menunggu', 'value' => $summary['pending'], 'color' => 'yellow', 'icon' => 'fa-clock'],
            ['label' => 'Dalam Proses', 'value' => $summary['in_progress'], 'color' => 'blue', 'icon' => 'fa-spinner'],
            ['label' => 'Selesai', 'value' => $summary['resolved'], 'color' => 'green', 'icon' => 'fa-check-circle'],
        ] as $s)
        <div class="card admin-summary-card">
            <div class="admin-summary-inner">
                <div class="admin-summary-icon {{ $s['color'] }}">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <p class="admin-summary-value">{{ $s['value'] }}</p>
                    <p class="admin-summary-label">{{ $s['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Admin Info Banner --}}
    <div class="info-banner">
        <i class="fas fa-info-circle info-banner-icon"></i>
        <p class="info-banner-text">
            <span class="info-banner-strong">Tampilan Kepala Puskesmas:</span>
            Data berikut adalah rekap read-only. Untuk mengubah status laporan, silakan hubungi Petugas Kesling terkait.
        </p>
    </div>

    {{-- Read-only Table --}}
    <div class="card table-card">
        <div class="table-card-header">
            <h3 class="table-card-title">Rekap Semua Laporan</h3>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th class="hide-md">Lokasi</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td><span class="row-id">{{ $report->id_laporan }}</span></td>
                        <td><span class="citizen-name">{{ $report->nama_pelapor }}</span></td>
                        <td><span class="badge readonly-category-badge">{{ $report->kategori_laporan }}</span></td>
                        <td class="hide-md"><p class="location-cell">{{ $report->id_desa }}</p></td>
                        <td>
                            @if($report->status_laporan === 'menunggu')
                                <span class="badge badge-pending">Menunggu</span>
                            @elseif($report->status_laporan === 'in_progress')
                                <span class="badge badge-progress">Proses</span>
                            @elseif($report->status_laporan === 'ditolak')
                                <span class="badge badge-rejected">Ditolak</span>
                            @else
                                <span class="badge badge-resolved">Selesai</span>
                            @endif
                        </td>
                        <td><span class="date-cell">{{ $report->created_at }}</span></td>
                        <td><a href="{{ route('reports.show', $report->id_laporan) }}">Lihat Detail</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
