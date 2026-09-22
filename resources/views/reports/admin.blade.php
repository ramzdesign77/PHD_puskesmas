
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
