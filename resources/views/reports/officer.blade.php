@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Data Laporan Masuk')
@section('page-subtitle', 'Verifikasi dan perbarui status laporan warga')

@push('styles')
<link rel="stylesheet" href="{{ asset('cssReport/officer.css') }}">
@endpush

@section('content')
<div class="stack-6 fade-in">

    {{-- Summary Bar --}}
    <div class="summary-bar">
        @foreach([
            ['label' => 'Total', 'value' => $summary['total'], 'color' => 'gray', 'icon' => 'fa-list'],
            ['label' => 'Menunggu', 'value' => $summary['pending'], 'color' => 'yellow', 'icon' => 'fa-clock'],
            ['label' => 'Proses', 'value' => $summary['in_progress'], 'color' => 'blue', 'icon' => 'fa-spinner'],
            ['label' => 'Selesai', 'value' => $summary['resolved'], 'color' => 'green', 'icon' => 'fa-check'],
        ] as $s)
        <div class="card summary-card">
            <div class="summary-icon-box {{ $s['color'] }}">
                <i class="fas {{ $s['icon'] }}"></i>
            </div>
            <p class="summary-value">{{ $s['value'] }}</p>
            <p class="summary-label">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="card table-card">
        <div class="table-card-header">
            <h3 class="table-card-title">Daftar Laporan Masuk</h3>
            <div>
                <input type="text" id="search-reports" placeholder="Cari laporan..." class="form-input search-input">
            </div>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th class="hide-lg">Lokasi</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr x-data="{ open: false }">
                        <td>
                            <span class="row-id">{{ $report['id'] }}</span>
                            @if($report['image'])
                            <span class="id-cell-icon" title="Ada foto">
                                <i class="fas fa-image"></i>
                            </span>
                            @endif
                        </td>
                        <td>
                            <div class="citizen-cell">
                                <div class="avatar-badge medical-gradient">
                                    {{ strtoupper(substr($report['citizen'], 0, 1)) }}
                                </div>
                                <span class="citizen-name">{{ $report['citizen'] }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="category-pill">
                                {{ $report['category'] }}
                            </span>
                        </td>
                        <td class="hide-lg">
                            <p class="location-cell">{{ $report['location'] }}</p>
                        </td>
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
                        <td><span class="date-cell">{{ $report['date'] }}</span></td>
                        <td>
                            <div class="action-cell">
                                <button @click="open = !open" class="btn-secondary btn-sm">
                                    <i class="fas fa-edit" style="margin-right:0.25rem;"></i> Update
                                </button>
                            </div>
                            {{-- Inline status update --}}
                            <div x-show="open" x-transition class="inline-update-form">
                                <td>
                                    <div class="status-actions">
                                        <form action="{{ route('reports.status', $report->id_laporan) }}" method="POST" class="d-inline">
                                            @csrf
                                            <select name="status" onchange="
                                                if (this.value === 'ditolak') {
                                                    document.getElementById('rejectModal{{ $report->id_laporan }}').classList.add('show');
                                                } else {
                                                    this.form.submit();
                                                }
                                            ">
                                                <option value="menunggu" {{ $report->status_laporan === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                                <option value="in_progress" {{ $report->status_laporan === 'in_progress' ? 'selected' : '' }}>Proses</option>
                                                <option value="selesai" {{ $report->status_laporan === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                <option value="ditolak" {{ $report->status_laporan === 'ditolak' ? 'selected' : '' }}>Tolak</option>
                                            </select>
                                        </form>
                                    </div>

                                    {{-- Modal alasan penolakan --}}
                                    <div id="rejectModal{{ $report->id_laporan }}" class="reject-modal">
                                        <div class="reject-modal-content">
                                            <h4>Alasan Penolakan Laporan #{{ $report->id_laporan }}</h4>
                                            <form action="{{ route('reports.status', $report->id_laporan) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="ditolak">
                                                <textarea name="alasan_penolakan" required placeholder="Contoh: Foto bukti tidak jelas / deskripsi kurang lengkap"></textarea>
                                                <div class="reject-modal-actions">
                                                    <button type="button" onclick="document.getElementById('rejectModal{{ $report->id_laporan }}').classList.remove('show')">Batal</button>
                                                    <button type="submit">Tolak Laporan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
