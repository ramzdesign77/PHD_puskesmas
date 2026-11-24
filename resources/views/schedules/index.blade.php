@extends('layouts.app')

@section('title', 'Kelola Jadwal Kunjungan')

@php
    $role = session('role');
    $isSanitarian = in_array($role, ['officer', 'petugas', 'sanitarian', 'staf_backup_kluster4'], true);
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
        <button type="button" @click="open = !open; if (open) $refs.scheduleForm.reset()"
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
            <form x-ref="scheduleForm" method="POST" action="{{ route('schedules.store') }}"
                class="flex flex-wrap gap-3 items-end">
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
                    @include('schedules._date-picker')
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_mulai" required
                        class="text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-300 w-24 @error('jam_mulai') border border-red-500 @else border border-gray-200 @enderror">
                    @error('jam_mulai') <p class="text-red-500 text-xs mt-1 max-w-xs">{{ $message }}</p> @enderror
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
            {{-- Filter --}}
            @php
                $hasAdvancedSched = collect(['id_desa', 'jenis', 'date_from', 'date_to'])
                    ->contains(fn ($filter) => filled(request($filter)));
                $hasAnySched = collect(['q', 'status_kunjungan', 'status', 'id_operator', 'range', 'tanggal', 'id_desa', 'jenis', 'date_from', 'date_to'])
                    ->contains(fn ($filter) => filled(request($filter)));
            @endphp
           <form method="GET" action="{{ route('schedules.index') }}" id="filter-form-sched" class="w-full block">
    <div class="w-full bg-white rounded-2xl border border-slate-200/80 shadow-sm p-3 sm:p-4 mb-5">
        <div class="w-full flex flex-wrap lg:flex-nowrap gap-3 items-center">
            {{-- Search: flex-1 agar melar penuh mengisi ruang kosong --}}
            <div class="relative flex-1 min-w-[220px] w-full">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Cari kode tiket, pelapor, petugas..."
                    class="w-full pl-9 pr-8 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none transition">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <i class="fas fa-search text-xs"></i>
                </span>
                @if(request('q'))
                    <button type="button" onclick="clearFilterField('q', 'filter-form-sched')"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-lg leading-none">×</button>
                @endif
            </div>

            {{-- Status --}}
            <select name="status_kunjungan" class="shrink-0 text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none">
                <option value="">Semua Status</option>
                <option value="terjadwal" {{ (request('status_kunjungan') ?? request('status')) === 'terjadwal' ? 'selected' : '' }}>Terjadwal</option>
                <option value="selesai"   {{ (request('status_kunjungan') ?? request('status')) === 'selesai'   ? 'selected' : '' }}>Selesai</option>
                <option value="batal"     {{ (request('status_kunjungan') ?? request('status')) === 'batal'     ? 'selected' : '' }}>Batal</option>
            </select>

            @if($isAdmin)
            {{-- Petugas --}}
            <select name="id_operator" class="shrink-0 text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none">
                <option value="">Semua Petugas</option>
                @foreach($officers as $officer)
                    <option value="{{ $officer->id_user }}" {{ request('id_operator') == $officer->id_user ? 'selected' : '' }}>{{ $officer->nama_lengkap }}</option>
                @endforeach
            </select>
            @endif

            {{-- Quick period --}}
            <select name="range" class="shrink-0 text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none">
                <option value="">Semua Tanggal</option>
                <option value="today"      {{ request('range') === 'today'      ? 'selected' : '' }}>Hari Ini</option>
                <option value="this_week"  {{ request('range') === 'this_week'  ? 'selected' : '' }}>Minggu Ini</option>
                <option value="this_month" {{ request('range') === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
            </select>

            {{-- Toggle Filter Lanjutan --}}
            <button type="button" onclick="toggleAdvancedFilter('sched')"
                class="shrink-0 flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 transition">
                <i class="fas fa-sliders text-[11px]"></i> Filter Lanjutan
                @if($hasAdvancedSched)<span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block"></span>@endif
            </button>

            <button type="submit" class="shrink-0 text-sm font-semibold px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition">
                Terapkan
            </button>

            @if($hasAnySched)
                <a href="{{ route('schedules.index') }}" class="shrink-0 text-xs text-slate-500 hover:text-red-600 font-medium px-2 transition">Reset</a>
            @endif
        </div>

        {{-- Filter Lanjutan --}}
        <div id="advanced-panel-sched" class="{{ $hasAdvancedSched ? 'block' : 'hidden' }} mt-3 pt-3 border-t border-slate-100">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">Desa</label>
                    <select name="id_desa" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                        <option value="">Semua Desa</option>
                        @foreach($desaList as $desa)
                            <option value="{{ $desa->id_desa }}" {{ request('id_desa') == $desa->id_desa ? 'selected' : '' }}>{{ $desa->nama_desa }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">Jenis Kunjungan</label>
                    <select name="jenis" class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                        <option value="">Semua Jenis</option>
                        <option value="ikl_laporan_warga" {{ request('jenis') === 'ikl_laporan_warga' ? 'selected' : '' }}>IKL Laporan Warga</option>
                        <option value="ikl_rutin_rt"      {{ request('jenis') === 'ikl_rutin_rt'      ? 'selected' : '' }}>IKL Rutin RT</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                </div>
            </div>
        </div>
    </div>
</form>

            {{-- Active Filter Chips --}}
            @if($hasAnySched)
            <div class="flex flex-wrap items-center gap-2 mb-4 text-xs">
                <span class="text-slate-500 font-medium">Filter aktif:</span>
                @if(request('q'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-700 font-medium">
                        Cari: "{{ request('q') }}"
                        <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                @if(request('status_kunjungan') ?? request('status'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-medium">
                        Status: {{ ucfirst(request('status_kunjungan') ?? request('status')) }}
                        <a href="{{ request()->fullUrlWithQuery(['status_kunjungan' => null, 'status' => null]) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                @if(request('range'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-medium">
                        Periode: {{ ['today'=>'Hari Ini','this_week'=>'Minggu Ini','this_month'=>'Bulan Ini'][request('range')] ?? request('range') }}
                        <a href="{{ request()->fullUrlWithQuery(['range' => null]) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                @if(request('date_from') || request('date_to'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-medium">
                        Tgl: {{ request('date_from') ?? '...' }} – {{ request('date_to') ?? '...' }}
                        <a href="{{ request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null]) }}" class="hover:text-red-600 ml-0.5">×</a>
                    </span>
                @endif
                <span class="ml-auto text-slate-400">{{ $schedules->total() }} jadwal ditemukan</span>
            </div>
            @endif
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
                            <p class="text-xs text-gray-400 font-medium mt-0.5">
                                {{ $jadwal->tanggal_kunjungan?->locale('id')->isoFormat('dddd') }}
                                @if($jadwal->jam_mulai)
                                    &bull; <span class="text-blue-500">{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}</span>
                                @endif
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
                                <span class="text-xs text-gray-400 italic">Menunggu hasil dari aplikasi mobile</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">

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
                                        onclick="openAssignModal({{ $jadwal->id_jadwal }}, {{ $jadwal->id_operator }}, '{{ $jadwal->tanggal_kunjungan?->format('Y-m-d') }}', '{{ $jadwal->jam_mulai ? \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') : '' }}')"
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
                                    @else
                                        @if(in_array($jadwal->status_kunjungan, ['selesai', 'batal'], true))
                                        <button type="button"
                                            data-history-edit
                                            data-id="{{ $jadwal->id_jadwal }}"
                                            data-operator-id="{{ $jadwal->id_operator }}"
                                            data-date="{{ $jadwal->tanggal_kunjungan?->format('Y-m-d') }}"
                                            data-time="{{ $jadwal->jam_mulai ? \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') : '' }}"
                                            class="btn-secondary btn-sm text-blue-600" title="Edit Riwayat Jadwal">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        @endif

                                        @if($jadwal->status_kunjungan === 'batal' && !$jadwal->inspeksiIkl)
                                        <form method="POST" action="{{ route('schedules.destroy', $jadwal->id_jadwal) }}"
                                            onsubmit="return confirm('Hapus riwayat jadwal batal ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-secondary btn-sm text-red-600" title="Hapus Riwayat">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
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
        <div class="flex flex-col gap-3 border-t border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <span class="text-xs font-medium text-slate-500">{{ $schedules->firstItem() ?? 0 }}–{{ $schedules->lastItem() ?? 0 }} dari {{ $schedules->total() }} jadwal</span>
            @include('partials.pagination-circles', ['paginator' => $schedules])
        </div>
        @endif
    </div>

</div>

{{-- MODAL: EDIT RIWAYAT JADWAL --}}
@if($isAdmin)
<div id="historyEditModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100 mb-4">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-pen text-blue-600"></i> Edit Riwayat Jadwal
            </h3>
            <button type="button" onclick="closeHistoryEditModal()" class="text-gray-400 hover:text-gray-600 text-xl" aria-label="Tutup">&times;</button>
        </div>

        <form id="historyEditForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="history_operator" class="block text-xs font-semibold text-gray-600 mb-1">Petugas Sanitarian</label>
                <select id="history_operator" name="id_operator" required
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
                <label for="history_date" class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Kunjungan</label>
                <input id="history_date" type="date" name="tanggal_kunjungan" required
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-300">
            </div>
            <div>
                <label for="history_time" class="block text-xs font-semibold text-gray-600 mb-1">Jam Mulai</label>
                <input id="history_time" type="time" name="jam_mulai" required
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-300">
            </div>
            <p class="text-xs text-gray-500">Status jadwal dan hasil IKL tidak berubah.</p>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeHistoryEditModal()" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn-primary btn-sm">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

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
                @include('schedules._date-picker', ['exceptId' => ''])
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                <input type="time" id="assign_jam_mulai" name="jam_mulai" required
                    class="w-full text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-300 @error('jam_mulai') border border-red-500 @else border border-gray-200 @enderror">
                @error('jam_mulai') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
function toggleAdvancedFilter(id) {
    const panel = document.getElementById('advanced-panel-' + id);
    if (panel) { panel.classList.toggle('hidden'); }
}
function clearFilterField(name, formId) {
    const form = document.getElementById(formId);
    const input = form ? form.querySelector('[name="' + name + '"]') : document.querySelector('[name="' + name + '"]');
    if (input) { input.value = ''; input.form ? input.form.submit() : form.submit(); }
}

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
function openAssignModal(idJadwal, currentOperatorId, currentTanggal, currentJamMulai) {
    const assignForm = document.getElementById('assignForm');
    assignForm.reset();
    assignForm.action = `/schedules/${idJadwal}/assign`;
    const operatorSelect = document.getElementById('assign_operator');
    operatorSelect.value = currentOperatorId || '';
    assignForm.querySelector('[data-schedule-calendar]').dataset.exceptId = idJadwal;
    assignForm.scheduleCalendar.setDate(currentTanggal || '');
    document.getElementById('assign_jam_mulai').value = currentJamMulai || '';
    showModal('assignModal');
}
function closeAssignModal() { hideModal('assignModal'); }

function openHistoryEditModal(button) {
    const form = document.getElementById('historyEditForm');
    form.action = `/schedules/${button.dataset.id}/history`;
    document.getElementById('history_operator').value = button.dataset.operatorId;
    document.getElementById('history_date').value = button.dataset.date;
    document.getElementById('history_time').value = button.dataset.time;
    showModal('historyEditModal');
}

function closeHistoryEditModal() { hideModal('historyEditModal'); }

document.querySelectorAll('[data-history-edit]').forEach(button => {
    button.addEventListener('click', () => openHistoryEditModal(button));
});

document.getElementById('assignModal')?.addEventListener('click', function(e) {
    if (e.target === this) hideModal('assignModal');
});

document.getElementById('historyEditModal')?.addEventListener('click', function(e) {
    if (e.target === this) hideModal('historyEditModal');
});
@endif

window.setupScheduleCalendars();

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        @if($isAdmin)
            hideModal('assignModal');
            hideModal('historyEditModal');
        @endif
    }
});
</script>
@endsection
