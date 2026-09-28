@extends('layouts.app')

@section('content')
<div class="space-y-6 fade-in">

    <div class="flex items-center justify-between mb-3">
        <div>
            <h3 class="font-bold text-gray-800">Kelola Laporan Warga</h3>
            <p class="text-xs text-gray-400">Tinjau, terima, atau tolak laporan yang masuk</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg p-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter status --}}
    <form method="GET" class="flex items-center gap-3 mb-4">
        <select name="status_laporan" class="form-input" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            @foreach(['menunggu','dibaca','diterima','ditolak'] as $status)
                <option value="{{ $status }}" {{ request('status_laporan') === $status ? 'selected' : '' }}>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
    </form>

    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="p-3">Kode Tiket</th>
                    <th class="p-3">Pelapor</th>
                    <th class="p-3">Desa</th>
                    <th class="p-3">Kategori</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporan as $item)
                <tr class="border-t">
                    <td class="p-3 font-mono text-xs">{{ $item->kode_tiket }}</td>
                    <td class="p-3">{{ $item->nama_pelapor }}</td>
                    <td class="p-3">{{ $item->desa->nama_desa ?? '-' }}</td>
                    <td class="p-3">{{ $item->kategori_laporan }}</td>
                    <td class="p-3">
                        <span class="text-xs px-2 py-1 rounded-full
                            @if($item->status_laporan === 'menunggu') bg-yellow-100 text-yellow-700
                            @elseif($item->status_laporan === 'dibaca') bg-blue-100 text-blue-700
                            @elseif($item->status_laporan === 'diterima') bg-green-100 text-green-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ ucfirst($item->status_laporan) }}
                        </span>
                    </td>
                    <td class="p-3">
                        <a href="{{ route('admin.laporan.show', $item->id_laporan) }}" class="text-blue-600 hover:underline text-xs">
                            Lihat Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-400">Belum ada laporan masuk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $laporan->links() }}</div>
</div>
@endsection
