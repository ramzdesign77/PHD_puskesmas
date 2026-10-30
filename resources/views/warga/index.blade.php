@extends('layouts.app')

@section('title', 'Manajemen Warga')
@section('page-title', 'Manajemen Warga')
@section('page-subtitle', 'Lihat akun warga yang terdaftar di aplikasi')

@section('content')
<div class="space-y-6 fade-in">
    <div class="card flex flex-col gap-2 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-bold text-gray-800">Akun Warga</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $wargas->total() }} akun warga ditemukan</p>
        </div>
    </div>

    <form method="GET" action="{{ route('warga.index') }}" class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            <input
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari nama, username, kontak, atau desa..."
                class="form-input pl-9"
                aria-label="Cari akun warga"
            >
        </div>
        <select name="status" class="form-input sm:w-44" aria-label="Filter status akun">
            <option value="">Semua status</option>
            <option value="active" @selected(request('status') === 'active')>Aktif</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
        </select>
        <button class="btn-primary justify-center" type="submit">
            <i class="fas fa-filter"></i> Terapkan
        </button>
        @if(request()->hasAny(['q', 'status']))
            <a href="{{ route('warga.index') }}" class="btn-secondary justify-center">Reset</a>
        @endif
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table w-full min-w-[1100px]">
                <thead>
                    <tr>
                        <th class="px-6 py-4">Akun Warga</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4">Desa</th>
                        <th class="px-6 py-4">Terdaftar</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wargas as $warga)
                        <tr>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="medical-gradient flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white">
                                        {{ strtoupper(substr($warga->nama_lengkap ?: $warga->username, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-800">{{ $warga->nama_lengkap ?: 'Nama belum diisi' }}</p>
                                        <p class="text-xs text-gray-400">{{ $warga->username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <p>{{ $warga->no_telepon ?: 'No. HP belum diisi' }}</p>
                                <p class="text-xs text-gray-400">{{ $warga->email ?: 'Email belum diisi' }}</p>
                            </td>
                            <td class="px-6 py-5">{{ $warga->desa?->nama_desa ?: 'Belum dipilih' }}</td>
                            <td class="whitespace-nowrap px-6 py-5 text-sm text-gray-500">
                                {{ $warga->created_at?->format('d M Y') ?: '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-5">
                                <span class="badge {{ $warga->is_active ? 'badge-resolved' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $warga->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('warga.show', $warga) }}" class="btn-secondary btn-sm" title="Lihat" aria-label="Lihat akun warga">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('warga.reset-password', $warga) }}" method="POST" onsubmit="return confirm('Reset password warga ini menjadi password123?')">
                                        @csrf
                                        <button class="btn-secondary btn-sm text-amber-600" type="submit" title="Reset Password" aria-label="Reset password warga">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                Tidak ada akun warga yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($wargas->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">{{ $wargas->links() }}</div>
        @endif
    </div>
</div>
@endsection
