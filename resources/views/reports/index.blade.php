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
                <i class="fas {{ $s['icon'] }} text-{{ $s['color'] }}-{{ $s['color'] === 'gray' ? '500' : '500' }}"></i>
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
                    <tr x-data="{ open: false }">
                        <td>
                            <span class="font-mono text-xs font-bold text-gray-500">{{ $report['id'] }}</span>
                            @if($report['image'])
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
                            @if($report['status'] === 'pending')
                                <span class="badge badge-pending"><i class="fas fa-circle text-yellow-400 text-xs"></i> Menunggu</span>
                            @elseif($report['status'] === 'in_progress')
                                <span class="badge badge-progress"><i class="fas fa-circle text-blue-400 text-xs"></i> Proses</span>
                            @else
                                <span class="badge badge-resolved"><i class="fas fa-circle text-green-400 text-xs"></i> Selesai</span>
                            @endif
                        </td>
                        <td><span class="text-xs text-gray-400">{{ $report['date'] }}</span></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <button @click="open = !open" class="text-xs btn-secondary btn-sm">
                                    <i class="fas fa-edit mr-1"></i> Update
                                </button>
                            </div>
                            {{-- Inline status update --}}
                            <div x-show="open" x-transition class="mt-2">
                                <form method="POST" action="{{ route('reports.status', $report['id']) }}" class="flex gap-2 items-center">
                                    @csrf
                                    <select name="status" class="form-input text-xs py-1.5 w-36">
                                        <option value="pending" {{ $report['status'] === 'pending' ? 'selected' : '' }}>Menunggu</option>
                                        <option value="in_progress" {{ $report['status'] === 'in_progress' ? 'selected' : '' }}>Dalam Proses</option>
                                        <option value="resolved" {{ $report['status'] === 'resolved' ? 'selected' : '' }}>Selesai</option>
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
    {{-- ADMIN: SUMMARY + READ-ONLY TABLE             --}}
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
            <span class="font-semibold">Tampilan Kepala Puskesmas:</span>
            Data berikut adalah rekap read-only. Untuk mengubah status laporan, silakan hubungi Petugas Kesling terkait.
        </p>
    </div>

    {{-- Read-only Table --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800">Rekap Semua Laporan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th class="hidden md:table-cell">Lokasi</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td><span class="font-mono text-xs font-bold text-gray-500">{{ $report['id'] }}</span></td>
                        <td><span class="text-sm text-gray-700">{{ $report['citizen'] }}</span></td>
                        <td><span class="badge bg-gray-100 text-gray-600">{{ $report['category'] }}</span></td>
                        <td class="hidden md:table-cell"><p class="text-xs text-gray-500 max-w-xs truncate">{{ $report['location'] }}</p></td>
                        <td>
                            @if($report['status'] === 'pending')
                                <span class="badge badge-pending">Menunggu</span>
                            @elseif($report['status'] === 'in_progress')
                                <span class="badge badge-progress">Proses</span>
                            @else
                                <span class="badge badge-resolved">Selesai</span>
                            @endif
                        </td>
                        <td><span class="text-xs text-gray-400">{{ $report['date'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
function reportForm() { return {}; }
</script>
@endsection
