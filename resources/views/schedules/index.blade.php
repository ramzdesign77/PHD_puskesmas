@extends('layouts.app')

@section('title', 'Kelola Jadwal Kunjungan')

@php
    $role = session('role');
    $isSanitarian = in_array($role, ['sanitarian', 'staf_backup_kluster4']);
    $isAdmin      = in_array($role, ['admin', 'kepala_puskesmas']);
@endphp

@section('page-title',
    $isAdmin      ? 'Kelola Jadwal Kunjungan' :
    ($isSanitarian ? 'Jadwal Saya'             : 'Jadwal Kunjungan')
)
@section('page-subtitle',
    $isAdmin      ? 'Manajemen kalender kerja, re-assign petugas, dan agenda IKL Rutin RT' :
    ($isSanitarian ? 'Daftar jadwal kunjungan lapangan yang ditetapkan Admin'              :
                     'Ajukan permintaan kunjungan petugas Kesling ke lokasi Anda')
)

@section('content')
<div class="space-y-6 fade-in">

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- SUMMARY STATS (Admin & Sanitarian)                           --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @if($isAdmin || $isSanitarian)
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Total',      'value' => $summary['total'],     'bg' => 'bg-slate-50',  'text' => 'text-slate-600',  'icon' => 'fa-calendar-alt', 'accent' => 'bg-slate-500'],
            ['label' => 'Terjadwal',  'value' => $summary['terjadwal'], 'bg' => 'bg-blue-50',   'text' => 'text-blue-600',   'icon' => 'fa-clock',        'accent' => 'bg-blue-500'],
            ['label' => 'Selesai',    'value' => $summary['selesai'],   'bg' => 'bg-green-50',  'text' => 'text-green-600',  'icon' => 'fa-check-circle', 'accent' => 'bg-green-500'],
            ['label' => 'Batal',      'value' => $summary['batal'],     'bg' => 'bg-red-50',    'text' => 'text-red-600',    'icon' => 'fa-times-circle', 'accent' => 'bg-red-500'],
        ] as $s)
        <div class="card relative overflow-hidden p-5">
            <div class="absolute inset-x-0 top-0 h-1 {{ $s['accent'] }}"></div>
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 {{ $s['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas {{ $s['icon'] }} {{ $s['text'] }} text-lg"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $s['value'] }}</p>
                    <p class="text-xs text-gray-400 font-medium">{{ $s['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- ADMIN: TAMBAH JADWAL IKL RUTIN RT                           --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @if($isAdmin)
    <div class="card p-5"
        x-data="{ open: false }">
        <button type="button" @click="open = !open"
            class="flex items-center justify-between w-full text-left">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-plus text-indigo-500 text-sm"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">Tambah Jadwal IKL Rutin RT</h3>
                    <p class="text-xs text-gray-400">Buat agenda kunjungan rutin tanpa laporan warga</p>
                </div>
            </div>
            <i class="fas fa-chevron-down text-gray-400 text-sm transition-transform" :class="open ? 'rotate-180' : ''"></i>
        </button>

        <div x-show="open" x-transition class="mt-4 pt-4 border-t border-gray-100">
            <form method="POST" action="{{ route('schedules.store') }}" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Petugas Sanitarian <span class="text-red-500">*</span></label>
                    <select name="id_operator" required class="text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-300 min-w-48">
                        <option value="" disabled selected>— Pilih Petugas —</option>
                        @foreach($officers as $officer)
                        <option value="{{ $officer->id_user }}">
                            {{ $officer->nama_lengkap }}{{ $officer->wilayah_kerja ? ' — ' . $officer->wilayah_kerja : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Kunjungan <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_kunjungan" required min="{{ date('Y-m-d') }}"
                        class="text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis</label>
                    <select name="jenis_kunjungan" class="text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="ikl_rutin_rt">IKL Rutin RT</option>
                        <option value="ikl_laporan_warga">IKL Laporan Warga</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Jadwal
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- DAFTAR JADWAL                                                --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-gray-800">Daftar Jadwal Kunjungan</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $schedules->total() }} jadwal terdaftar
                </p>
            </div>
            {{-- Filter --}}
            <form method="GET" action="{{ route('schedules.index') }}" class="flex gap-2 flex-wrap">
                <select name="status_kunjungan" onchange="this.form.submit()"
                    class="text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <option value="">Semua Status</option>
                    <option value="terjadwal" {{ request('status_kunjungan') === 'terjadwal' ? 'selected' : '' }}>Terjadwal</option>
                    <option value="selesai"   {{ request('status_kunjungan') === 'selesai'   ? 'selected' : '' }}>Selesai</option>
                    <option value="batal"     {{ request('status_kunjungan') === 'batal'     ? 'selected' : '' }}>Batal</option>
                </select>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" onchange="this.form.submit()"
                    class="text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-300">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis Kunjungan</th>
                        <th>Laporan Warga</th>
                        <th>Petugas</th>
                        <th>Status</th>
                        <th>Hasil IKL</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $jadwal)
                    @php
                        $statusMap = [
                            'terjadwal' => ['badge-assigned', 'Terjadwal'],
                            'selesai'   => ['badge-resolved', 'Selesai'],
                            'batal'     => ['badge-rejected', 'Batal'],
                        ];
                        [$badgeClass, $statusLabel] = $statusMap[$jadwal->status_kunjungan] ?? ['badge-pending', $jadwal->status_kunjungan];
                    @endphp
                    <tr>
                        {{-- Tanggal --}}
                        <td>
                            <p class="font-semibold text-gray-800 text-sm">
                                {{ $jadwal->tanggal_kunjungan?->format('d M Y') ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $jadwal->tanggal_kunjungan?->locale('id')->isoFormat('dddd') }}
                            </p>
                        </td>

                        {{-- Jenis --}}
                        <td>
                            @if($jadwal->jenis_kunjungan === 'ikl_rutin_rt')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-semibold border border-indigo-100">
                                    <i class="fas fa-map-pin text-[10px]"></i> IKL Rutin RT
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg text-xs font-semibold border border-blue-100">
                                    <i class="fas fa-file-alt text-[10px]"></i> IKL Laporan
                                </span>
                            @endif
                        </td>

                        {{-- Laporan Warga --}}
                        <td>
                            @if($jadwal->laporan)
                                <p class="text-xs font-mono font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded inline-block">
                                    {{ $jadwal->laporan->kode_tiket }}
                                </p>
                                <p class="text-sm text-gray-700 mt-0.5">{{ $jadwal->laporan->nama_pelapor }}</p>
                                <p class="text-xs text-gray-400">{{ $jadwal->laporan->desa?->nama_desa }}</p>
                            @else
                                <span class="text-xs text-gray-400 italic">IKL Rutin (tanpa laporan)</span>
                            @endif
                        </td>

                        {{-- Petugas --}}
                        <td>
                            @if($jadwal->operator)
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 medical-gradient rounded-lg flex items-center justify-center text-white text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($jadwal->operator->nama_lengkap, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-700 truncate">{{ $jadwal->operator->nama_lengkap }}</p>
                                        <p class="text-xs text-gray-400">{{ $jadwal->operator->wilayah_kerja }}</p>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Belum ditugaskan</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td>
                            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>

                        {{-- Hasil IKL --}}
                        <td>
                            @if($jadwal->inspeksiIkl)
                                @php
                                    $risikoMap = [
                                        'aman'          => 'bg-green-100 text-green-700',
                                        'risiko_sedang' => 'bg-yellow-100 text-yellow-700',
                                        'risiko_tinggi' => 'bg-red-100 text-red-700',
                                    ];
                                    $risikoClass = $risikoMap[$jadwal->inspeksiIkl->kategori_risiko] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold {{ $risikoClass }}">
                                    Skor: {{ $jadwal->inspeksiIkl->total_skor_ya }}/5
                                </span>
                            @elseif($jadwal->status_kunjungan === 'terjadwal')
                                <span class="text-xs text-gray-400 italic">Belum diisi</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">

                                {{-- Form IKL (jika terjadwal & petugas yang login) --}}
                                @if($jadwal->status_kunjungan === 'terjadwal' && !$jadwal->inspeksiIkl)
                                <a href="{{ route('inspeksi.form', $jadwal->id_jadwal) }}"
                                    class="btn-secondary btn-sm text-green-600" title="Isi Form IKL">
                                    <i class="fas fa-clipboard-check"></i>
                                </a>
                                @endif

                                {{-- Lihat Hasil IKL --}}
                                @if($jadwal->inspeksiIkl)
                                <a href="{{ route('inspeksi.show', $jadwal->inspeksiIkl->id_inspeksi) }}"
                                    class="btn-secondary btn-sm" title="Lihat Hasil IKL">
                                    <i class="fas fa-eye text-gray-500"></i>
                                </a>
                                @endif

                                @if($isAdmin)
                                    {{-- Re-assign (jika terjadwal) --}}
                                    @if($jadwal->status_kunjungan === 'terjadwal')
                                    <button type="button"
                                        onclick="openAssignModal({{ $jadwal->id_jadwal }}, {{ $jadwal->id_operator }}, '{{ $jadwal->tanggal_kunjungan?->format('Y-m-d') }}')"
                                        class="btn-secondary btn-sm text-blue-600" title="Re-assign Petugas">
                                        <i class="fas fa-user-edit"></i>
                                    </button>

                                    {{-- Batalkan --}}
                                    <form method="POST" action="{{ route('schedules.batal', $jadwal->id_jadwal) }}"
                                        onsubmit="return confirm('Batalkan jadwal ini? Laporan terkait akan dikembalikan ke antrian.')"
                                        class="inline">
                                        @csrf
                                        <button type="submit" class="btn-secondary btn-sm text-red-600" title="Batalkan">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                    @endif
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-calendar-times text-2xl text-gray-300"></i>
                                </div>
                                <p class="text-gray-400 font-medium">Belum ada jadwal kunjungan</p>
                                <p class="text-xs text-gray-300">Jadwal akan muncul setelah laporan dijadwalkan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schedules->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $schedules->links() }}
        </div>
        @endif
    </div>

</div>

{{-- ════════════════════════════════════════ --}}
{{-- MODAL: RE-ASSIGN PETUGAS                --}}
{{-- ════════════════════════════════════════ --}}
@if($isAdmin)
<div id="assignModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-edit text-blue-600"></i> Re-assign / Reschedule
            </h3>
            <button type="button" onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>

        <form id="assignForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Petugas Sanitarian <span class="text-red-500">*</span></label>
                <select id="assign_operator" name="id_operator" required
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <option value="" disabled>— Pilih Petugas —</option>
                    @foreach($officers as $officer)
                    <option value="{{ $officer->id_user }}">
                        {{ $officer->nama_lengkap }}{{ $officer->wilayah_kerja ? ' — ' . $officer->wilayah_kerja : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Kunjungan <span class="text-red-500">*</span></label>
                <input type="date" id="assign_tanggal" name="tanggal_kunjungan" required
                    min="{{ date('Y-m-d') }}"
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-300">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeAssignModal()" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn-primary btn-sm">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
function showModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    el.classList.add('flex');
}
function hideModal(id) {
    const el = document.getElementById(id);
    el.classList.add('hidden');
    el.classList.remove('flex');
}

@if($isAdmin)
function openAssignModal(idJadwal, currentOperatorId, currentTanggal) {
    document.getElementById('assignForm').action = `/schedules/${idJadwal}/assign`;
    const operatorSelect = document.getElementById('assign_operator');
    operatorSelect.value = currentOperatorId || '';
    document.getElementById('assign_tanggal').value = currentTanggal || '';
    showModal('assignModal');
}
function closeAssignModal() { hideModal('assignModal'); }

document.getElementById('assignModal')?.addEventListener('click', function(e) {
    if (e.target === this) hideModal('assignModal');
});
@endif

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        @if($isAdmin) hideModal('assignModal'); @endif
    }
});
</script>
@endsection
