@extends('layouts.app')

@section('title', 'Jadwal Kunjungan')

@php
    $role = session('role');

    if ($role === 'citizen') {
        $pageTitle = 'Minta Kunjungan';
        $pageSubtitle = 'Ajukan permintaan kunjungan petugas Kesling ke lokasi Anda';
    } elseif ($role === 'officer') {
        $pageTitle = 'Jadwal Kunjungan Saya';
        $pageSubtitle = 'Daftar jadwal kunjungan lapangan yang telah ditetapkan Admin';
    } else {
        $pageTitle = 'Kelola Jadwal Kunjungan';
        $pageSubtitle = 'Tetapkan petugas dan jadwal untuk setiap permintaan kunjungan warga';
    }
@endphp

@section('page-title', $pageTitle)
@section('page-subtitle', $pageSubtitle)

@section('content')

<div class="space-y-6 fade-in">

    {{-- ════════════════════════════════════════════ --}}

    {{-- CITIZEN: REQUEST FORM                         --}}

    {{-- ════════════════════════════════════════════ --}}

    @if($role === 'citizen')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 card p-6">

            <div class="flex items-center gap-3 mb-6">

                <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">

                    <i class="fas fa-calendar-plus text-white"></i>

                </div>

                <div>

                    <h3 class="font-bold text-gray-800">Formulir Permintaan Kunjungan</h3>

                    <p class="text-xs text-gray-400">Admin akan menentukan petugas dan waktu pasti</p>

                </div>

            </div>

            <form method="POST" action="{{ route('schedules.store') }}" class="space-y-5">

                @csrf

                <div>

                    <label class="form-label"><i class="fas fa-hashtag text-blue-400 mr-1.5"></i>Referensi Laporan</label>

                    <input type="text" name="report_ref" class="form-input"

                           placeholder="Contoh: REP-001 (opsional, jika ada nomor laporan sebelumnya)">

                </div>

                <div>

                    <label class="form-label"><i class="fas fa-exclamation-circle text-blue-400 mr-1.5"></i>Jenis Keluhan <span class="text-red-500">*</span></label>

                    <textarea name="complaint" rows="3" class="form-input resize-none"

                              placeholder="Jelaskan masalah yang memerlukan kunjungan petugas..." required></textarea>

                </div>

                <div>

                    <label class="form-label"><i class="fas fa-map-marker-alt text-blue-400 mr-1.5"></i>Alamat Kunjungan <span class="text-red-500">*</span></label>

                    <input type="text" name="location" class="form-input"

                           placeholder="Alamat lengkap yang perlu dikunjungi petugas" required>

                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-3">
                        <label class="form-label"><i class="fas fa-house text-blue-400 mr-1.5"></i>Desa/Kelurahan <span class="text-red-500">*</span></label>
                        <select name="id_desa" class="form-input" required>
                            <option value="">Pilih desa/kelurahan</option>
                            @foreach($villages as $village)
                                <option value="{{ $village->id_desa }}">{{ $village->nama_desa }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">RT <span class="text-red-500">*</span></label>
                        <input type="text" name="rt" class="form-input" maxlength="5" placeholder="03" required>
                    </div>
                    <div>
                        <label class="form-label">RW <span class="text-red-500">*</span></label>
                        <input type="text" name="rw" class="form-input" maxlength="5" placeholder="02" required>
                    </div>
                </div>

                <div>

                    <label class="form-label"><i class="fas fa-calendar text-blue-400 mr-1.5"></i>Preferensi Waktu Kunjungan <span class="text-red-500">*</span></label>

                    <input type="datetime-local" name="preferred" class="form-input" required

                           min="{{ date('Y-m-d\TH:i') }}">

                    <p class="text-xs text-gray-400 mt-1">

                        <i class="fas fa-info-circle"></i>

                        Ini hanya preferensi. Waktu pasti akan ditentukan oleh Admin.

                    </p>

                </div>

                <div>

                    <label class="form-label"><i class="fas fa-sticky-note text-blue-400 mr-1.5"></i>Catatan Tambahan</label>

                    <textarea name="notes" rows="2" class="form-input resize-none"

                              placeholder="Informasi tambahan untuk petugas (misal: gate code, kontak alternatif)"></textarea>

                </div>

                <button type="submit" id="btn-submit-schedule" class="btn-primary w-full justify-center py-3">

                    <i class="fas fa-paper-plane"></i>

                    Kirim Permintaan Kunjungan

                </button>

            </form>

        </div>

        {{-- Info Panel --}}

        <div class="space-y-4">

            <div class="card p-5">

                <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">

                    <i class="fas fa-info-circle text-blue-400"></i> Alur Proses

                </h4>

                <div class="space-y-4">

                    @php
                    $processSteps = [
                        ['step' => '1', 'title' => 'Kirim Permintaan', 'desc' => 'Isi formulir permintaan kunjungan dengan lengkap', 'active' => true],
                        ['step' => '2', 'title' => 'Verifikasi Admin', 'desc' => 'Admin akan menjadwalkan dan menugaskan petugas', 'active' => false],
                        ['step' => '3', 'title' => 'Kunjungan Petugas', 'desc' => 'Petugas Kesling datang ke lokasi sesuai jadwal', 'active' => false],
                        ['step' => '4', 'title' => 'Laporan Selesai', 'desc' => 'Hasil kunjungan dilaporkan ke sistem', 'active' => false],
                    ];
                @endphp

                @foreach($processSteps as $step)

                    <div class="flex items-start gap-3">

                        <div class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-bold shrink-0

                            {{ $step['active'] ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-400' }}">

                            {{ $step['step'] }}

                        </div>

                        <div>

                            <p class="text-sm font-semibold text-gray-700">{{ $step['title'] }}</p>

                            <p class="text-xs text-gray-400">{{ $step['desc'] }}</p>

                        </div>

                    </div>

                    @endforeach

                </div>

            </div>

            <div class="card p-5 bg-green-50 border border-green-100">

                <h4 class="font-bold text-green-700 mb-2 flex items-center gap-2">

                    <i class="fas fa-clock"></i> Waktu Respons

                </h4>

                <p class="text-xs text-green-600">Permintaan kunjungan biasanya diproses dalam <strong>1-3 hari kerja</strong> setelah diterima Admin.</p>

            </div>

        </div>

    </div>

    {{-- ════════════════════════════════════════════ --}}

    {{-- OFFICER: READ-ONLY SCHEDULE VIEW             --}}

    {{-- ════════════════════════════════════════════ --}}

    @elseif($role === 'officer')

    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-center gap-3">

        <i class="fas fa-info-circle text-blue-400 text-xl shrink-0"></i>

        <p class="text-sm text-blue-700">

            <span class="font-semibold">Jadwal ditentukan oleh Admin.</span>

            Jika ada pertanyaan mengenai jadwal, hubungi Kepala Puskesmas.

        </p>

    </div>

    @if(count($schedules) === 0)

    <div class="card p-12 text-center">

        <i class="fas fa-calendar-times text-5xl text-gray-200 mb-4"></i>

        <p class="text-gray-400 font-medium">Belum ada jadwal kunjungan yang ditetapkan</p>

        <p class="text-gray-300 text-sm mt-1">Jadwal akan muncul di sini setelah Admin menetapkannya</p>

    </div>

    @else

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        @foreach($schedules as $schedule)

        <div class="card p-5 border-l-4 {{ $schedule['status'] === 'assigned' ? 'border-l-purple-500' : 'border-l-yellow-400' }}">

            <div class="flex items-start justify-between mb-3">

                <div>

                    <span class="font-mono text-xs font-bold text-gray-400">{{ $schedule['id'] }}</span>

                    <h4 class="font-bold text-gray-800 mt-0.5">{{ $schedule['citizen'] }}</h4>

                </div>

                <span class="badge {{ $schedule['status'] === 'assigned' ? 'badge-assigned' : 'badge-pending' }}">

                    {{ $schedule['status'] === 'assigned' ? 'Ditugaskan' : 'Menunggu' }}

                </span>

            </div>

            <div class="space-y-2 text-sm">

                <div class="flex items-start gap-2 text-gray-600">

                    <i class="fas fa-exclamation-circle text-gray-400 mt-0.5 w-4"></i>

                    <span>{{ $schedule['complaint'] }}</span>

                </div>

                <div class="flex items-start gap-2 text-gray-600">

                    <i class="fas fa-map-marker-alt text-gray-400 mt-0.5 w-4"></i>

                    <span class="text-sm">{{ $schedule['location'] }}</span>

                </div>

                @if($schedule['assigned_date'])

                <div class="flex items-center gap-2 text-gray-600">

                    <i class="fas fa-calendar-check text-purple-400 w-4"></i>

                    <span class="font-semibold text-purple-600">

                        {{ \Carbon\Carbon::parse($schedule['assigned_date'])->locale('id')->isoFormat('dddd, D MMMM Y') }}

                        pukul {{ $schedule['assigned_time'] }} WIB

                    </span>

                </div>

                @endif

                @if($schedule['notes'])

                <div class="mt-3 p-3 bg-yellow-50 border border-yellow-100 rounded-xl">

                    <p class="text-xs text-yellow-700"><i class="fas fa-sticky-note mr-1"></i>{{ $schedule['notes'] }}</p>

                </div>

                @endif

            </div>

        </div>

        @endforeach

    </div>

    @endif

    {{-- ════════════════════════════════════════════ --}}

    {{-- ADMIN: FULL MANAGEMENT INTERFACE --}}

    @else

    @php
        $schedulesArr = is_array($schedules) ? $schedules : iterator_to_array($schedules);
        $total = count($schedulesArr);
        $pending = count(array_filter($schedulesArr, fn($s) => ($s['status'] ?? null) === 'pending'));
        $assigned = count(array_filter($schedulesArr, fn($s) => ($s['status'] ?? null) === 'assigned'));
    @endphp

    <div
        x-data="{
            search: '',
            status: 'all',
            officer: 'all',
            showDetail: false,
            showAssign: false,
            selected: {},
            resetFilters() {
                this.search = '';
                this.status = 'all';
                this.officer = 'all';
            },
            openDetail(item) {
                this.selected = item;
                this.showDetail = true;
            },
            openAssign(item) {
                this.selected = { ...item };
                this.showAssign = true;
            },
            closeModals() {
                this.showDetail = false;
                this.showAssign = false;
            },
            matches(item) {
                const q = this.search.trim().toLowerCase();
                const haystack = [item.id, item.citizen, item.complaint, item.location, item.officer]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();

                const searchMatch = !q || haystack.includes(q);
                const statusMatch = this.status === 'all' || item.status === this.status;
                const officerMatch = this.officer === 'all' || item.officer === this.officer;

                return searchMatch && statusMatch && officerMatch;
            }
        }"
        class="space-y-6"
    >

        {{-- Page header --}}
        <div class="overflow-hidden rounded-2xl border border-blue-100 bg-linear-to-r from-blue-600 via-blue-500 to-indigo-500 p-5 shadow-sm sm:p-6">

        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="card relative overflow-hidden border-blue-100 p-5">
                <div class="absolute inset-x-0 top-0 h-1 bg-blue-500"></div>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Permintaan</p>
                        <p class="mt-2 text-3xl font-bold text-gray-800">{{ $total }}</p>
                        <p class="mt-1 text-xs text-gray-400">Seluruh permintaan yang masuk</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                        <i class="fas fa-inbox text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="card relative overflow-hidden border-orange-100 p-5">
                <div class="absolute inset-x-0 top-0 h-1 bg-orange-400"></div>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Belum Ditugaskan</p>
                        <p class="mt-2 text-3xl font-bold text-orange-500">{{ $pending }}</p>
                        <p class="mt-1 text-xs text-gray-400">Perlu segera dijadwalkan</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-orange-50 text-orange-500">
                        <i class="fas fa-user-clock text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="card relative overflow-hidden border-emerald-100 p-5">
                <div class="absolute inset-x-0 top-0 h-1 bg-emerald-500"></div>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Sudah Ditugaskan</p>
                        <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $assigned }}</p>
                        <p class="mt-1 text-xs text-gray-400">Siap dilaksanakan petugas</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <i class="fas fa-user-check text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Request list --}}
        <div class="card overflow-hidden">
            <div class="border-b border-gray-100 p-5 sm:p-6">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <i class="fas fa-list-check"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Daftar Permintaan Kunjungan</h3>
                            <p class="mt-1 text-xs text-gray-400">Pilih detail atau atur jadwal dari setiap permintaan.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:flex xl:items-center">
                        <div class="relative xl:w-64">
                            <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                            <input type="text" x-model="search" class="form-input w-full pl-9" placeholder="Cari warga, ID, keluhan...">
                        </div>

                        <select x-model="status" class="form-input xl:w-40">
                            <option value="all">Semua Status</option>
                            <option value="pending">Menunggu</option>
                            <option value="assigned">Ditugaskan</option>
                        </select>

                        <select x-model="officer" class="form-input xl:w-48">
                            <option value="all">Semua Petugas</option>
                            @foreach($officers as $itemOfficer)
                                <option value="{{ $itemOfficer['name'] }}">{{ $itemOfficer['name'] }}</option>
                            @endforeach
                        </select>

                        <button type="button" @click="resetFilters()" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:border-gray-300 hover:bg-white hover:text-gray-800">
                            <i class="fas fa-rotate-right text-xs"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            @if($total === 0)
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-300">
                        <i class="fas fa-calendar-times text-2xl"></i>
                    </div>
                    <h4 class="mt-4 font-semibold text-gray-700">Belum ada permintaan kunjungan</h4>
                    <p class="mt-1 text-sm text-gray-400">Data permintaan akan muncul di bagian ini.</p>
                </div>
            @else
                {{-- Column heading --}}
                <div class="hidden border-b border-gray-100 bg-linear-to-r from-gray-50 to-white px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 lg:grid lg:grid-cols-[200px_minmax(300px,1.55fr)_180px_minmax(210px,1.1fr)_120px] lg:gap-5 lg:px-6">
                    <div class="flex items-center justify-center">Warga</div>
                    <div class="flex items-center">Keluhan & Lokasi</div>
                    <div class="flex items-center justify-center">Jadwal</div>
                    <div class="flex items-center justify-center">Petugas & Status</div>
                    <div class="flex items-center justify-center">Aksi</div>
                </div>

                <div class="space-y-3 bg-gray-50/40 p-3 sm:p-4">
                    @foreach($schedulesArr as $schedule)
                        @php
                            $scheduleItem = [
                                'id' => $schedule['id'],
                                'citizen' => $schedule['citizen'],
                                'complaint' => $schedule['complaint'],
                                'location' => $schedule['location'],
                                'preferred' => $schedule['preferred'],
                                'officer' => $schedule['officer'] ?? '',
                                'officer_id' => $schedule['officer_id'] ?? '',
                                'status' => $schedule['status'],
                                'assigned_date' => $schedule['assigned_date'] ?? '',
                                'assigned_time' => $schedule['assigned_time'] ?? '',
                                'assign_url' => route('schedules.assign', $schedule['id']),
                            ];
                        @endphp

                        <div
                            x-show="matches(@js($scheduleItem))"
                            x-cloak
                            class="group card overflow-hidden transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                        >
                            <div class="grid gap-4 p-4 sm:p-5 lg:grid-cols-[200px_minmax(300px,1.55fr)_180px_minmax(210px,1.1fr)_120px] lg:items-center lg:gap-5">

                                {{-- Citizen --}}
                                <div class="min-w-0 flex justify-center">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-linear-to-br from-blue-50 to-indigo-100 text-sm font-bold text-blue-600 ring-1 ring-blue-100">
                                            {{ strtoupper(substr($schedule['citizen'], 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold leading-5 text-gray-800" title="{{ $schedule['citizen'] }}">{{ $schedule['citizen'] }}</p>
                                            <span class="mt-1 inline-flex w-fit rounded-md bg-gray-100 px-1.5 py-0.5 font-mono text-[9px] font-semibold text-gray-500">{{ $schedule['id'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Complaint and location --}}
                                <div class="min-w-0 rounded-xl border border-gray-100 bg-gray-50/80 p-3.5">
                                    <p class="line-clamp-2 text-sm font-semibold leading-5 text-gray-800">{{ $schedule['complaint'] }}</p>
                                    <div class="mt-2 flex items-start gap-2">
                                        <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-500">
                                            <i class="fas fa-location-dot text-[10px]"></i>
                                        </div>
                                        <p class="line-clamp-2 text-xs leading-4 text-gray-500" title="{{ $schedule['location'] }}">{{ $schedule['location'] }}</p>
                                    </div>
                                </div>

                                {{-- Schedule --}}
                                <div class="min-w-0 min-h-16 flex items-center">
                                    <div class="w-full">
                                    <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-400 lg:hidden">Jadwal</p>
                                    @if($schedule['assigned_date'] && $schedule['assigned_time'])
                                        <div class="rounded-xl border border-blue-100 bg-blue-50/70 px-3 py-2.5">
                                            <p class="text-sm font-bold text-blue-700">
                                                {{ \Carbon\Carbon::parse($schedule['assigned_date'])->locale('id')->isoFormat('D MMM Y') }}
                                            </p>
                                            <p class="mt-0.5 text-xs text-blue-500">
                                                <i class="far fa-clock mr-1"></i>{{ $schedule['assigned_time'] }} WIB
                                            </p>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-xl border border-orange-100 bg-orange-50 px-3 py-2 text-xs font-bold text-orange-600">
                                            <i class="fas fa-clock text-[10px]"></i>
                                            Menunggu
                                        </span>
                                    @endif
                                    </div>
                                </div>

                                {{-- Officer + status --}}
                                <div class="min-w-0 min-h-16 flex items-center">
                                    <div class="w-full">
                                    <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-400 lg:hidden">Petugas & Status</p>
                                    @if($schedule['officer'])
                                        <p class="truncate text-sm font-bold text-gray-800" title="{{ $schedule['officer'] }}">{{ $schedule['officer'] }}</p>
                                    @else
                                        <p class="text-sm font-medium text-gray-400">Belum ditugaskan</p>
                                    @endif

                                    <div class="mt-2.5">
                                        @if($schedule['status'] === 'assigned')
                                            <span class="badge badge-assigned">Ditugaskan</span>
                                        @else
                                            <span class="badge badge-pending">Menunggu</span>
                                        @endif
                                    </div>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center justify-end gap-2 lg:flex-col lg:justify-center lg:items-stretch">
                                    <button
                                        type="button"
                                        @click="openDetail(@js($scheduleItem))"
                                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3 text-gray-500 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 lg:w-full"
                                        title="Lihat detail"
                                    >
                                        <i class="fas fa-eye text-xs"></i>
                                        <span class="text-xs font-semibold lg:hidden">Detail</span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="openAssign(@js($scheduleItem))"
                                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-blue-100 bg-blue-50 px-3 text-blue-600 shadow-sm transition hover:bg-blue-100 lg:w-full"
                                        title="{{ $schedule['status'] === 'assigned' ? 'Edit jadwal' : 'Tugaskan petugas' }}"
                                    >
                                        <i class="fas {{ $schedule['status'] === 'assigned' ? 'fa-pen' : 'fa-user-plus' }} text-xs"></i>
                                        <span class="text-xs font-semibold lg:hidden">{{ $schedule['status'] === 'assigned' ? 'Edit' : 'Tugaskan' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-2 border-t border-gray-100 px-5 py-4 text-xs sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <p class="text-gray-400"><span class="font-semibold text-gray-700">{{ $total }}</span> permintaan terdaftar</p>
                    <p class="text-gray-400">Gunakan <span class="font-semibold text-gray-600">Detail</span> untuk melihat informasi lengkap dan <span class="font-semibold text-gray-600">Edit/Tugaskan</span> untuk mengatur jadwal.</p>
                </div>
            @endif
        </div>

        {{-- Detail modal --}}
        <div
            x-show="showDetail"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4"
            @click.self="closeModals()"
            @keydown.escape.window="closeModals()"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl" @click.stop>
                <div class="bg-linear-to-r from-blue-600 to-indigo-500 p-5 text-white sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-mono text-xs font-semibold text-blue-100" x-text="selected?.id"></p>
                            <h4 class="mt-1 text-lg font-bold">Detail Permintaan</h4>
                        </div>
                        <button type="button" @click="closeModals()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/20">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="max-h-[70vh] overflow-y-auto p-5 sm:p-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Warga</p>
                            <p class="mt-1 text-sm font-bold text-gray-800" x-text="selected?.citizen"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Status</p>
                            <div class="mt-2">
                                <template x-if="selected?.status === 'assigned'">
                                    <span class="badge badge-assigned">Ditugaskan</span>
                                </template>
                                <template x-if="selected?.status !== 'assigned'">
                                    <span class="badge badge-pending">Menunggu</span>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:col-span-2">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Keluhan</p>
                            <p class="mt-1 text-sm leading-6 text-gray-700" x-text="selected?.complaint"></p>
                        </div>

                        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:col-span-2">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Lokasi</p>
                            <p class="mt-1 text-sm leading-6 text-gray-700" x-text="selected?.location"></p>
                        </div>

                        <div class="rounded-xl border border-blue-100 bg-blue-50/70 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-400">Jadwal</p>
                            <p class="mt-1 text-sm font-bold text-blue-700" x-text="selected?.assigned_date || 'Belum dijadwalkan'"></p>
                            <p class="mt-0.5 text-xs text-blue-500" x-text="selected?.assigned_time ? selected.assigned_time + ' WIB' : 'Menunggu penetapan'"></p>
                        </div>

                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Petugas</p>
                            <p class="mt-1 text-sm font-bold text-gray-700" x-text="selected?.officer || 'Belum ditugaskan'"></p>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="button" @click="closeModals()" class="btn-secondary">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Assignment modal --}}
        <div
            x-show="showAssign"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4"
            @click.self="closeModals()"
            @keydown.escape.window="closeModals()"
        >
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl" @click.stop>
                <div class="border-b border-gray-100 bg-linear-to-r from-blue-50 to-indigo-50 p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-mono text-xs font-bold text-gray-400" x-text="selected?.id"></p>
                            <h4 class="mt-1 text-lg font-bold text-gray-800" x-text="selected?.status === 'assigned' ? 'Edit Jadwal Kunjungan' : 'Tugaskan Petugas'"></h4>
                        </div>
                        <button type="button" @click="closeModals()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-gray-400 shadow-sm transition hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="mb-5 rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-sm font-bold text-gray-800" x-text="selected?.citizen"></p>
                        <p class="mt-1 text-xs leading-5 text-gray-500" x-text="selected?.complaint"></p>
                        <p class="mt-2 flex items-start gap-2 text-xs text-gray-400">
                            <i class="fas fa-map-pin mt-0.5 text-red-400"></i>
                            <span x-text="selected?.location"></span>
                        </p>
                    </div>

                    <form method="POST" :action="selected?.assign_url || ''" class="space-y-4">
                        @csrf

                        <div>
                            <label class="form-label">Petugas Kesling</label>
                            <select name="officer_id" class="form-input" required x-model="selected.officer_id">
                                <option value="" disabled>— Pilih Petugas —</option>
                                @foreach($officers as $itemOfficer)
                                    <option value="{{ $itemOfficer['id'] }}">{{ $itemOfficer['name'] }} ({{ $itemOfficer['area'] }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="form-label">Tanggal Kunjungan</label>
                                <input type="date" name="assigned_date" class="form-input" required x-model="selected.assigned_date">
                            </div>
                            <div>
                                <label class="form-label">Waktu</label>
                                <input type="time" name="assigned_time" class="form-input" required x-model="selected.assigned_time">
                            </div>
                        </div>

                        <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row">
                            <button type="button" @click="closeModals()" class="btn-secondary flex-1 justify-center">Batal</button>
                            <button type="submit" class="btn-primary flex-1 justify-center">
                                <i class="fas fa-check"></i>
                                <span x-text="selected?.status === 'assigned' ? 'Simpan Perubahan' : 'Tetapkan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @endif
@endsection
