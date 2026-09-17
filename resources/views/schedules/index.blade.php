@extends('layouts.app')

@section('title', 'Jadwal Kunjungan')
@section('page-title',
    session('role') === 'citizen' ? 'Minta Kunjungan' :
    (session('role') === 'officer' ? 'Jadwal Kunjungan Saya' : 'Kelola Jadwal Kunjungan')
)
@section('page-subtitle',
    session('role') === 'citizen' ? 'Ajukan permintaan kunjungan petugas Kesling ke lokasi Anda' :
    (session('role') === 'officer' ? 'Daftar jadwal kunjungan lapangan yang telah ditetapkan Admin' : 'Tetapkan petugas dan jadwal untuk setiap permintaan kunjungan warga')
)

@section('content')
<div class="space-y-6 fade-in">

    {{-- ════════════════════════════════════════════ --}}
    {{-- CITIZEN: REQUEST FORM                         --}}
    {{-- ════════════════════════════════════════════ --}}
    @if(session('role') === 'citizen')
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
                    @foreach([
                        ['step' => '1', 'title' => 'Kirim Permintaan', 'desc' => 'Isi formulir permintaan kunjungan dengan lengkap', 'active' => true],
                        ['step' => '2', 'title' => 'Verifikasi Admin', 'desc' => 'Admin akan menjadwalkan dan menugaskan petugas', 'active' => false],
                        ['step' => '3', 'title' => 'Kunjungan Petugas', 'desc' => 'Petugas Kesling datang ke lokasi sesuai jadwal', 'active' => false],
                        ['step' => '4', 'title' => 'Laporan Selesai', 'desc' => 'Hasil kunjungan dilaporkan ke sistem', 'active' => false],
                    ] as $step)
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-bold flex-shrink-0
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
    @elseif(session('role') === 'officer')
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-center gap-3">
        <i class="fas fa-info-circle text-blue-400 text-xl flex-shrink-0"></i>
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
    {{-- ADMIN: FULL MANAGEMENT INTERFACE             --}}
    {{-- ════════════════════════════════════════════ --}}
    @else
    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        @php
            $schedulesArr = is_array($schedules) ? $schedules : iterator_to_array($schedules);
            $pending  = count(array_filter($schedulesArr, fn($s) => $s['status'] === 'pending'));
            $assigned = count(array_filter($schedulesArr, fn($s) => $s['status'] === 'assigned'));
        @endphp
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ count($schedulesArr) }}</p>
            <p class="text-xs text-gray-400 font-medium mt-1">Total Permintaan</p>
        </div>
        <div class="card p-4 text-center border-t-2 border-yellow-400">
            <p class="text-2xl font-bold text-yellow-500">{{ $pending }}</p>
            <p class="text-xs text-gray-400 font-medium mt-1">Belum Ditugaskan</p>
        </div>
        <div class="card p-4 text-center border-t-2 border-purple-500">
            <p class="text-2xl font-bold text-purple-500">{{ $assigned }}</p>
            <p class="text-xs text-gray-400 font-medium mt-1">Sudah Ditugaskan</p>
        </div>
    </div>

    {{-- Schedule Management Table --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800">Daftar Permintaan Kunjungan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Warga</th>
                        <th>Keluhan</th>
                        <th class="hidden lg:table-cell">Lokasi</th>
                        <th>Preferensi</th>
                        <th>Petugas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedulesArr as $schedule)
                    <tr x-data="{ showModal: false }">
                        <td><span class="font-mono text-xs font-bold text-gray-400">{{ $schedule['id'] }}</span></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-xs font-bold">
                                    {{ strtoupper(substr($schedule['citizen'], 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ $schedule['citizen'] }}</span>
                            </div>
                        </td>
                        <td><p class="text-xs text-gray-600 max-w-xs">{{ $schedule['complaint'] }}</p></td>
                        <td class="hidden lg:table-cell"><p class="text-xs text-gray-500 max-w-xs truncate">{{ $schedule['location'] }}</p></td>
                        <td><span class="text-xs text-gray-500">{{ $schedule['preferred'] }}</span></td>
                        <td>
                            @if($schedule['officer'])
                            <span class="badge badge-assigned">
                                <i class="fas fa-user-md text-xs"></i>
                                {{ Str::limit($schedule['officer'], 15) }}
                            </span>
                            @else
                            <span class="text-xs text-gray-300 italic">Belum ditugaskan</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $schedule['status'] === 'assigned' ? 'badge-assigned' : 'badge-pending' }}">
                                {{ $schedule['status'] === 'assigned' ? 'Ditugaskan' : 'Menunggu' }}
                            </span>
                        </td>
                        <td>
                            <button @click="showModal = true" id="btn-assign-{{ $schedule['id'] }}"
                                    class="btn-primary btn-sm">
                                <i class="fas {{ $schedule['status'] === 'assigned' ? 'fa-edit' : 'fa-plus' }} text-xs"></i>
                                {{ $schedule['status'] === 'assigned' ? 'Edit' : 'Tugaskan' }}
                            </button>
                        </td>

                        {{-- Assignment Modal --}}
                        <td class="p-0 border-none">
                        <div x-show="showModal" x-transition
                             class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
                             @click.self="showModal = false">
                            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                                <div class="flex items-center justify-between mb-5">
                                    <h4 class="font-bold text-gray-800">Tugaskan Petugas</h4>
                                    <button @click="showModal = false" class="text-gray-400 hover:text-red-500">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 mb-5">
                                    <p class="text-xs text-gray-500 font-medium">Permintaan dari: <span class="text-gray-800 font-bold">{{ $schedule['citizen'] }}</span></p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $schedule['complaint'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1"><i class="fas fa-map-pin text-red-400"></i> {{ $schedule['location'] }}</p>
                                </div>
                                <form method="POST" action="{{ route('schedules.assign', $schedule['id']) }}" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="form-label">Petugas Kesling</label>
                                        <select name="officer" class="form-input" required>
                                            <option value="" disabled selected>— Pilih Petugas —</option>
                                            @foreach($officers as $officer)
                                            <option value="{{ $officer['name'] }}" {{ $schedule['officer'] === $officer['name'] ? 'selected' : '' }}>
                                                {{ $officer['name'] }} ({{ $officer['area'] }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="form-label">Tanggal Kunjungan</label>
                                            <input type="date" name="assigned_date" class="form-input"
                                                   value="{{ $schedule['assigned_date'] }}" required>
                                        </div>
                                        <div>
                                            <label class="form-label">Waktu</label>
                                            <input type="time" name="assigned_time" class="form-input"
                                                   value="{{ $schedule['assigned_time'] }}" required>
                                        </div>
                                    </div>
                                    <div class="flex gap-3 mt-2">
                                        <button type="button" @click="showModal = false" class="btn-secondary flex-1 justify-center">
                                            Batal
                                        </button>
                                        <button type="submit" class="btn-primary flex-1 justify-center">
                                            <i class="fas fa-check"></i> Tetapkan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
