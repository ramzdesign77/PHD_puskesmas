@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title',
    session('role') === 'citizen' ? 'Kirim Laporan' :
    (session('role') === 'officer' ? 'Data Laporan Masuk' : 'Rekap Semua Laporan')
)
@section('page-subtitle',
    session('role') === 'citizen' ? 'Laporkan masalah kesehatan lingkungan di sekitar Anda' :
    (session('role') === 'officer' ? 'Verifikasi dan perbarui status laporan warga' : 'Ringkasan seluruh laporan program Kesling')
)

@section('content')
<div class="space-y-6 fade-in">

    {{-- ════════════════════════════════════════════ --}}
    {{-- CITIZEN: SUBMISSION FORM                     --}}
    {{-- ════════════════════════════════════════════ --}}
    @if(session('role') === 'citizen')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Form --}}
        <div class="lg:col-span-2 card p-6" x-data="reportForm()">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 medical-gradient rounded-xl flex items-center justify-center">
                    <i class="fas fa-plus text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Form Pengaduan Baru</h3>
                    <p class="text-xs text-gray-400">Lengkapi semua data dengan benar</p>
                </div>
            </div>

            <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Category --}}
                <div>
                    <label class="form-label"><i class="fas fa-tag text-red-400 mr-1.5"></i>Kategori Masalah <span class="text-red-500">*</span></label>
                    <select id="report-category" name="category" class="form-input" required>
                        <option value="" disabled selected>— Pilih Kategori —</option>
                        <option value="Air Bersih">💧 Air Bersih</option>
                        <option value="Sanitasi">🚿 Sanitasi / Jamban</option>
                        <option value="Sampah">🗑️ Pengelolaan Sampah</option>
                        <option value="Jentik Nyamuk">🦟 Jentik Nyamuk / DBD</option>
                        <option value="Limbah">🏭 Limbah Industri</option>
                        <option value="PHBS">🧼 Perilaku Hidup Bersih (PHBS)</option>
                        <option value="Lainnya">📋 Lainnya</option>
                    </select>
                </div>

                {{-- Description --}}
                <div>
                    <label class="form-label"><i class="fas fa-align-left text-red-400 mr-1.5"></i>Deskripsi Masalah <span class="text-red-500">*</span></label>
                    <textarea id="report-description" name="description" rows="4" class="form-input resize-none"
                              placeholder="Jelaskan masalah secara detail: lokasi spesifik, sejak kapan, dampak yang dirasakan..." required></textarea>
                    <p class="text-xs text-gray-400 mt-1">Minimum 10 karakter</p>
                </div>

                {{-- Image Upload --}}
                <div x-data="{ preview: null, dragging: false }">
                    <label class="form-label"><i class="fas fa-camera text-red-400 mr-1.5"></i>Foto Bukti</label>
                    <div class="border-2 border-dashed rounded-xl p-6 text-center transition-all cursor-pointer"
                         :class="dragging ? 'border-red-400 bg-red-50' : 'border-gray-200 hover:border-red-300 hover:bg-gray-50'"
                         @dragover.prevent="dragging = true"
                         @dragleave.prevent="dragging = false"
                         @drop.prevent="dragging = false; preview = URL.createObjectURL($event.dataTransfer.files[0])"
                         @click="$refs.fileInput.click()">
                        <template x-if="!preview">
                            <div>
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 mb-2"></i>
                                <p class="text-sm text-gray-500 font-medium">Klik atau drag & drop foto di sini</p>
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP — Maks. 5 MB</p>
                            </div>
                        </template>
                        <template x-if="preview">
                            <div>
                                <img :src="preview" class="max-h-40 mx-auto rounded-lg object-cover mb-2">
                                <p class="text-xs text-green-500 font-medium"><i class="fas fa-check-circle"></i> Foto dipilih</p>
                            </div>
                        </template>
                        <input type="file" name="image" x-ref="fileInput" class="sr-only" accept="image/*"
                               @change="preview = URL.createObjectURL($event.target.files[0])">
                    </div>
                </div>

                {{-- Location --}}
                <div>

                    <div>
                        <label class="form-label"><i class="fas fa-map-marker-alt mr-1.5"></i>Desa <span class="text-red-600">*</span></label>
                        <select name="id_desa" class="form-input" required>
                            <option value="" disabled selected>-- Pilih Desa --</option>
                            @foreach(\App\Models\Desa::all() as $desa)
                                <option value="{{ $desa->id_desa }}">{{ $desa->nama_desa }}</option>
                            @endforeach
                        </select>
                    </div>

                    <label class="form-label"><i class="fas fa-map-marker-alt text-red-400 mr-1.5"></i>Lokasi Kejadian <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="report-location" type="text" name="location" class="form-input pl-10"
                               placeholder="Contoh: Jl. Mawar No.5, RT 03/02, Kelurahan Sumbersari" required>
                        <i class="fas fa-map-pin absolute left-3 top-3 text-gray-400 text-sm"></i>
                    </div>
                    {{-- GPS Placeholder Map --}}
                    <div class="mt-2 h-32 bg-gradient-to-br from-green-100 to-blue-100 rounded-xl flex items-center justify-center border border-gray-200">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt text-2xl text-green-500 mb-1"></i>
                            <p class="text-xs text-gray-500 font-medium">Peta Lokasi (GPS)</p>
                            <button type="button"
                                    class="mt-2 text-xs bg-white text-red-500 border border-red-200 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors font-medium">
                                <i class="fas fa-crosshairs mr-1"></i>Gunakan Lokasi Saya
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" id="btn-submit-report" class="btn-primary w-full justify-center py-3">
                    <i class="fas fa-paper-plane"></i>
                    Kirim Laporan
                </button>
            </form>
        </div>

        {{-- Tips Sidebar --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-lightbulb text-yellow-400"></i> Tips Pelaporan
                </h4>
                <ul class="space-y-3">
                    @foreach([
                        ['icon' => 'fa-camera', 'color' => 'blue', 'tip' => 'Sertakan foto yang jelas dan detail'],
                        ['icon' => 'fa-map-marker-alt', 'color' => 'red', 'tip' => 'Berikan alamat selengkap mungkin'],
                        ['icon' => 'fa-align-left', 'color' => 'green', 'tip' => 'Jelaskan sejak kapan masalah terjadi'],
                        ['icon' => 'fa-tag', 'color' => 'purple', 'tip' => 'Pilih kategori yang paling sesuai'],
                    ] as $tip)
                    <li class="flex items-start gap-3">
                        <div class="w-7 h-7 bg-{{ $tip['color'] }}-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas {{ $tip['icon'] }} text-{{ $tip['color'] }}-400 text-xs"></i>
                        </div>
                        <p class="text-xs text-gray-600">{{ $tip['tip'] }}</p>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="card p-5 bg-red-50 border border-red-100">
                <h4 class="font-bold text-red-700 mb-2 flex items-center gap-2">
                    <i class="fas fa-phone-alt"></i> Darurat?
                </h4>
                <p class="text-xs text-red-600 mb-3">Hubungi langsung Puskesmas Sumbersari:</p>
                <a href="tel:+62311234567" class="flex items-center gap-2 text-sm font-bold text-red-700 hover:text-red-800">
                    <i class="fas fa-phone"></i> (0331) 123-4567
                </a>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════ --}}
    {{-- OFFICER: DATA TABLE WITH STATUS UPDATE       --}}
    {{-- ════════════════════════════════════════════ --}}
    @elseif(session('role') === 'officer')
    {{-- Summary Bar --}}
    <div class="grid grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Total', 'value' => $summary['total'], 'color' => 'gray', 'icon' => 'fa-list'],
            ['label' => 'Menunggu', 'value' => $summary['pending'], 'color' => 'yellow', 'icon' => 'fa-clock'],
            ['label' => 'Proses', 'value' => $summary['in_progress'], 'color' => 'blue', 'icon' => 'fa-spinner'],
            ['label' => 'Selesai', 'value' => $summary['resolved'], 'color' => 'green', 'icon' => 'fa-check'],
        ] as $s)
        <div class="card p-4 text-center">
            <div class="w-10 h-10 bg-{{ $s['color'] }}-50 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i class="fas {{ $s['icon'] }} text-{{ $s['color'] }}-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $s['value'] }}</p>
            <p class="text-xs text-gray-400 font-medium">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Daftar Laporan Masuk</h3>
            <div class="flex gap-2">
                <input type="text" id="search-reports" placeholder="Cari laporan..." class="form-input text-sm py-2 w-48">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th class="hidden lg:table-cell">Lokasi</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    @php
                        $status = $report['status'] ?? 'pending';
                        $hasSchedule = !empty($report['scheduled_at']);
                        $scheduleValue = $hasSchedule
                            ? \Carbon\Carbon::parse($report['scheduled_at'])->format('Y-m-d\TH:i')
                            : '';
                    @endphp
                    <tr x-data="{ open: false }">
                        <td>
                            <span class="font-mono text-xs font-bold text-gray-500">{{ $report['id'] }}</span>
                            @if(!empty($report['image']))
                            <span class="ml-1 text-blue-400 text-xs" title="Ada foto">
                                <i class="fas fa-image"></i>
                            </span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 medical-gradient rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($report['citizen'], 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ $report['citizen'] }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-medium">
                                {{ $report['category'] }}
                            </span>
                        </td>
                        <td class="hidden lg:table-cell">
                            <p class="text-xs text-gray-500 max-w-xs truncate">{{ $report['location'] }}</p>
                        </td>
                        <td>
                            @if($status === 'pending')
                                <span class="badge badge-pending"><i class="fas fa-circle text-yellow-400 text-xs"></i> Menunggu</span>
                            @elseif($status === 'approved')
                                <span class="badge bg-emerald-50 text-emerald-700"><i class="fas fa-circle text-emerald-400 text-xs"></i> Disetujui</span>
                            @elseif($status === 'rejected')
                                <span class="badge bg-red-50 text-red-700"><i class="fas fa-circle text-red-400 text-xs"></i> Ditolak</span>
                            @elseif($status === 'in_progress')
                                <span class="badge badge-progress"><i class="fas fa-circle text-blue-400 text-xs"></i> Proses</span>
                            @else
                                <span class="badge badge-resolved"><i class="fas fa-circle text-green-400 text-xs"></i> Selesai</span>
                            @endif
                        </td>
                        <td><span class="text-xs text-gray-400">{{ $report['date'] }}</span></td>
                        <td>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" onclick="openDetailModal(@js($report))" class="text-xs btn-secondary btn-sm">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </button>
                                <button type="button"
                                        onclick="openScheduleModal(@js($report['id']), @js($scheduleValue), @js($report['schedule_note'] ?? ''))"
                                        class="text-xs btn-secondary btn-sm">
                                    <i class="fas {{ $hasSchedule ? 'fa-edit' : 'fa-calendar-plus' }} mr-1"></i>
                                    {{ $hasSchedule ? 'Ubah Jadwal' : 'Buat Jadwal' }}
                                </button>
                                <button @click="open = !open" class="text-xs btn-secondary btn-sm">
                                    <i class="fas fa-edit mr-1"></i> Update
                                </button>
                            </div>
                            {{-- Inline status update --}}
                            <div x-show="open" x-transition class="mt-2">
                                <form method="POST" action="{{ route('reports.status', $report['id']) }}" class="flex gap-2 items-center">
                                    @csrf
                                    <select name="status" class="form-input text-xs py-1.5 w-36">
                                        <option value="pending"     {{ $status === 'pending' ? 'selected' : '' }}>Menunggu</option>
                                        <option value="approved"    {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                        <option value="rejected"    {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                        <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>Dalam Proses</option>
                                        <option value="resolved"    {{ $status === 'resolved' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                    <button type="submit" class="btn-primary btn-sm">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ════════════════════════════════════════════ --}}
    {{-- ADMIN: SUMMARY + MANAGEMENT TABLE            --}}
    {{-- ════════════════════════════════════════════ --}}
    @else
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Total Laporan', 'value' => $summary['total'], 'bg' => 'bg-red-50', 'text' => 'text-red-600', 'icon' => 'fa-file-alt'],
            ['label' => 'Menunggu', 'value' => $summary['pending'], 'bg' => 'bg-yellow-50', 'text' => 'text-yellow-600', 'icon' => 'fa-clock'],
            ['label' => 'Dalam Proses', 'value' => $summary['in_progress'], 'bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'fa-spinner'],
            ['label' => 'Selesai', 'value' => $summary['resolved'], 'bg' => 'bg-green-50', 'text' => 'text-green-600', 'icon' => 'fa-check-circle'],
        ] as $s)
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 {{ $s['bg'] }} rounded-xl flex items-center justify-center">
                    <i class="fas {{ $s['icon'] }} {{ $s['text'] }} text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $s['value'] }}</p>
                    <p class="text-xs text-gray-400 font-medium">{{ $s['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Admin Info Banner --}}
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-center gap-3">
        <i class="fas fa-info-circle text-blue-400 text-xl flex-shrink-0"></i>
        <p class="text-sm text-blue-700">
            <span class="font-semibold">Kelola Laporan:</span>
            Anda dapat mengubah status laporan (Menunggu, Disetujui, Ditolak, Proses, Selesai), membuat penjadwalan penanganan, dan melihat detail lengkap laporan warga di bawah ini.
        </p>
    </div>

    {{-- Interactive Management Table --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Rekap & Tindakan Laporan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table w-full text-left">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th class="hidden md:table-cell">Lokasi</th>
                        <th>Status</th>
                        <th>Jadwal</th>
                        <th>Tanggal Lapor</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    @php
                        $status = $report['status'] ?? 'pending';
                        $hasSchedule = !empty($report['scheduled_at']);
                        $scheduleValue = $hasSchedule
                            ? \Carbon\Carbon::parse($report['scheduled_at'])->format('Y-m-d\TH:i')
                            : '';
                    @endphp
                    <tr>
                        <td><span class="font-mono text-xs font-bold text-gray-500">#{{ $report['id'] }}</span></td>
                        <td><span class="text-sm text-gray-700 font-medium">{{ $report['citizen'] }}</span></td>
                        <td><span class="badge bg-gray-100 text-gray-600">{{ $report['category'] }}</span></td>
                        <td class="hidden md:table-cell"><p class="text-xs text-gray-500 max-w-xs truncate">{{ $report['location'] }}</p></td>

                        {{-- Pilihan Status: Menunggu / Disetujui / Ditolak / Proses / Selesai --}}
                        <td>
                            <form action="{{ route('reports.status', $report['id']) }}" method="POST" class="inline-block">
                                @csrf
                                <select name="status" onchange="this.form.submit()"
                                    class="text-xs font-semibold rounded-lg px-2.5 py-1 border focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer
                                    @if($status === 'pending') bg-yellow-50 text-yellow-700 border-yellow-200
                                    @elseif($status === 'approved') bg-emerald-50 text-emerald-700 border-emerald-200
                                    @elseif($status === 'rejected') bg-red-50 text-red-700 border-red-200
                                    @elseif($status === 'in_progress') bg-blue-50 text-blue-700 border-blue-200
                                    @else bg-green-50 text-green-700 border-green-200 @endif">
                                    <option value="pending"     {{ $status === 'pending' ? 'selected' : '' }}>⏳ Menunggu</option>
                                    <option value="approved"    {{ $status === 'approved' ? 'selected' : '' }}>✅ Disetujui</option>
                                    <option value="rejected"    {{ $status === 'rejected' ? 'selected' : '' }}>❌ Ditolak</option>
                                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>🔄 Proses</option>
                                    <option value="resolved"    {{ $status === 'resolved' ? 'selected' : '' }}>🎉 Selesai</option>
                                </select>
                            </form>
                        </td>

                        {{-- Kolom Jadwal (info saja) --}}
                        <td>
                            @if($hasSchedule)
                                <div class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded border border-blue-100 inline-flex items-center gap-1">
                                    <i class="far fa-calendar-alt"></i>
                                    {{ \Carbon\Carbon::parse($report['scheduled_at'])->format('d M Y - H:i') }}
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Belum dijadwalkan</span>
                            @endif
                        </td>

                        <td><span class="text-xs text-gray-400">{{ $report['date'] }}</span></td>

                        {{-- Aksi: Detail & Buat/Ubah Jadwal --}}
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                    onclick="openDetailModal(@js($report))"
                                    class="px-2.5 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition inline-flex items-center gap-1">
                                    <i class="fas fa-eye text-gray-500"></i> Detail
                                </button>

                                <button type="button"
                                    onclick="openScheduleModal(@js($report['id']), @js($scheduleValue), @js($report['schedule_note'] ?? ''))"
                                    class="px-2.5 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded-lg transition inline-flex items-center gap-1">
                                    @if($hasSchedule)
                                        <i class="fas fa-edit"></i> Ubah Jadwal
                                    @else
                                        <i class="fas fa-plus-circle"></i> Buat Jadwal
                                    @endif
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-sm text-gray-400 py-10">Belum ada laporan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

{{-- MODAL 1: BUAT / UBAH JADWAL --}}
<div id="scheduleModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-blue-600"></i> Atur Jadwal Penanganan
            </h3>
            <button type="button" onclick="closeScheduleModal()" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
        </div>

        <form id="scheduleForm" action="" method="POST" class="mt-4 space-y-4">
            @csrf

            <p class="text-xs text-gray-500">Laporan: <span id="schedule_report_label" class="font-mono font-bold text-gray-700"></span></p>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal & Waktu Penanganan</label>
                <input type="datetime-local" id="modal_scheduled_at" name="scheduled_at" required
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan / Petugas Lapangan (Opsional)</label>
                <textarea id="modal_schedule_note" name="schedule_note" rows="2"
                    placeholder="Contoh: Tim Kesling akan melakukan inspeksi lapangan."
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeScheduleModal()" class="px-4 py-2 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: DETAIL LAPORAN --}}
<div id="detailModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-file-alt text-blue-600"></i> Detail Laporan <span id="detail_id" class="text-blue-600 font-mono"></span>
            </h3>
            <button type="button" onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
        </div>

        <div class="mt-4 space-y-3 text-sm">
            <div class="grid grid-cols-2 gap-2 bg-gray-50 p-3 rounded-xl border border-gray-100">
                <div>
                    <p class="text-xs text-gray-400 font-medium">Pelapor</p>
                    <p id="detail_citizen" class="font-semibold text-gray-700">-</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium">Kategori</p>
                    <p id="detail_category" class="font-semibold text-gray-700">-</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <p class="text-xs text-gray-400 font-medium mb-1">Status</p>
                    <span id="detail_status" class="inline-block text-xs font-semibold px-2.5 py-1 rounded-lg border">-</span>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium">Tanggal Lapor</p>
                    <p id="detail_date" class="text-gray-700">-</p>
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-400 font-medium">Lokasi</p>
                <p id="detail_location" class="text-gray-700 font-medium">-</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 font-medium">Jadwal Penanganan</p>
                <p id="detail_schedule" class="text-gray-700">-</p>
                <p id="detail_schedule_note" class="text-xs text-gray-500 mt-0.5"></p>
            </div>

            <div id="detail_description_container">
                <p class="text-xs text-gray-400 font-medium mb-1">Deskripsi Laporan</p>
                <div id="detail_description" class="p-3 bg-gray-50 rounded-xl text-gray-600 text-xs leading-relaxed border border-gray-100">
                    -
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">Tutup</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function reportForm() { return {}; }

const REPORTS_BASE_URL = "{{ url('reports') }}";

const STATUS_MAP = {
    pending:     { label: '⏳ Menunggu',  cls: 'bg-yellow-50 text-yellow-700 border-yellow-200' },
    approved:    { label: '✅ Disetujui', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    rejected:    { label: '❌ Ditolak',   cls: 'bg-red-50 text-red-700 border-red-200' },
    in_progress: { label: '🔄 Proses',    cls: 'bg-blue-50 text-blue-700 border-blue-200' },
    resolved:    { label: '🎉 Selesai',   cls: 'bg-green-50 text-green-700 border-green-200' },
};

// Helper buka/tutup modal (hidden <-> flex)
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

// ── Modal Buat / Ubah Jadwal ──
function openScheduleModal(reportId, existingDate = '', existingNote = '') {
    document.getElementById('schedule_report_label').innerText = '#' + reportId;
    document.getElementById('modal_scheduled_at').value = existingDate || '';
    document.getElementById('modal_schedule_note').value = existingNote || '';
    // Route: POST /reports/{id}/schedule  (name: reports.schedule)
    document.getElementById('scheduleForm').action = `${REPORTS_BASE_URL}/${reportId}/schedule`;
    showModal('scheduleModal');
}
function closeScheduleModal() {
    hideModal('scheduleModal');
}

// ── Modal Detail Laporan ──
function openDetailModal(report) {
    const status = STATUS_MAP[report.status] || STATUS_MAP.pending;

    document.getElementById('detail_id').innerText = '#' + (report.id || '-');
    document.getElementById('detail_citizen').innerText = report.citizen || '-';
    document.getElementById('detail_category').innerText = report.category || '-';
    document.getElementById('detail_location').innerText = report.location || '-';
    document.getElementById('detail_date').innerText = report.date || '-';
    document.getElementById('detail_description').innerText = report.description || 'Tidak ada deskripsi rinci.';

    const statusEl = document.getElementById('detail_status');
    statusEl.innerText = status.label;
    statusEl.className = 'inline-block text-xs font-semibold px-2.5 py-1 rounded-lg border ' + status.cls;

    document.getElementById('detail_schedule').innerText = report.scheduled_at
        ? new Date(report.scheduled_at.replace(' ', 'T')).toLocaleString('id-ID', { dateStyle: 'long', timeStyle: 'short' })
        : 'Belum dijadwalkan';
    document.getElementById('detail_schedule_note').innerText = report.schedule_note || '';

    showModal('detailModal');
}
function closeDetailModal() {
    hideModal('detailModal');
}

// Tutup modal dengan klik area gelap atau tombol Escape
['scheduleModal', 'detailModal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function (e) {
        if (e.target === this) hideModal(id);
    });
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        hideModal('scheduleModal');
        hideModal('detailModal');
    }
});
</script>
@endsection
