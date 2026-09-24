@extends('layouts.app')

@section('title', 'Manajemen Petugas')
@section('page-title', 'Manajemen Petugas')
@section('page-subtitle', 'Kelola akses dan penempatan petugas kesehatan lingkungan')

@section('content')
<div class="space-y-6 fade-in" x-data="{ showCreateModal: {{ $errors->any() ? 'true' : 'false' }} }" @keydown.escape.window="showCreateModal = false">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ $users->total() }} petugas terdaftar dalam sistem</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="btn-primary">
            <i class="fas fa-user-plus"></i> Tambah Petugas
        </button>
    </div>

    <form method="GET" action="{{ route('users.index') }}" class="card flex flex-col gap-3 p-4 lg:flex-row lg:items-center">
        <div class="relative flex-1"><i class="fas fa-search absolute left-3 top-3 text-gray-400"></i><input name="q" value="{{ request('q') }}" placeholder="Cari nama, ID petugas, atau username..." class="form-input pl-9"></div>
        <select name="status" class="form-input lg:w-40"><option value="">Semua status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select>
        <select name="per_page" class="form-input lg:w-32"><option value="10" @selected(request('per_page', '10') === '10')>10 data</option><option value="25" @selected(request('per_page') === '25')>25 data</option><option value="50" @selected(request('per_page') === '50')>50 data</option></select>
        <button class="btn-primary justify-center" type="submit"><i class="fas fa-filter"></i> Terapkan</button>
        <a href="{{ route('users.export') }}" class="btn-secondary justify-center"><i class="fas fa-file-export"></i> Ekspor CSV</a>
    </form>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ count($users) }}</p>
            <p class="mt-1 text-xs font-medium text-gray-400">Total Petugas</p>
        </div>
        <div class="card border-t-2 border-green-400 p-4 text-center">
            <p class="text-2xl font-bold text-green-500">{{ $users->where('is_active', true)->count() }}</p>
            <p class="mt-1 text-xs font-medium text-gray-400">Petugas Aktif</p>
        </div>
        <div class="card border-t-2 border-blue-500 p-4 text-center">
            <p class="text-2xl font-bold text-blue-500">{{ $users->where('is_active', false)->count() }}</p>
            <p class="mt-1 text-xs font-medium text-gray-400">Petugas Nonaktif</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="border-b border-gray-100 px-5 py-4"><h3 class="font-bold text-gray-800">Daftar Petugas</h3></div>
        <div class="overflow-x-auto px-4 pb-3">
            <table class="data-table mx-auto w-full min-w-[1100px]">
                <thead><tr><th class="min-w-[250px] px-6 py-4">Petugas</th><th class="px-6 py-4">Kontak</th><th class="px-6 py-4">Wilayah Kerja</th><th class="px-6 py-4">Peran</th><th class="px-6 py-4">Status</th><th class="min-w-[250px] px-6 py-4 text-right">Aksi</th></tr></thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-5"><div class="flex items-center gap-4">@if($user->foto)<img src="{{ asset('storage/' . $user->foto) }}" alt="Foto {{ $user->nama_lengkap }}" class="h-10 w-10 shrink-0 rounded-xl object-cover">@else<div class="medical-gradient flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white">{{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}</div>@endif<div class="min-w-0"><p class="font-semibold leading-6 text-gray-800">{{ $user->nama_lengkap }}</p><p class="text-xs leading-5 text-gray-400">ID Petugas: {{ $user->nip_nik ?: 'Belum diisi' }}</p><p class="text-xs leading-5 text-gray-400">{{ $user->username }}</p></div></div></td>
                        <td class="px-6 py-5"><p>{{ $user->no_telepon ?: 'No. HP belum diisi' }}</p><p class="text-xs text-gray-400">{{ $user->email ?: 'Email belum diisi' }}</p></td>
                        <td class="px-6 py-5">{{ $user->wilayah_kerja ?: 'Belum ditempatkan' }}</td>
                        <td class="whitespace-nowrap px-6 py-5"><span class="badge badge-assigned">Petugas</span></td>
                        <td class="whitespace-nowrap px-6 py-5"><span class="badge {{ $user->is_active ? 'badge-resolved' : 'bg-gray-100 text-gray-500' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="px-6 py-5 text-right"><div class="flex justify-end gap-2"><a href="{{ route('users.show', $user) }}" class="btn-secondary btn-sm" title="Lihat"><i class="fas fa-eye"></i></a><a href="{{ route('users.edit', $user) }}" class="btn-secondary btn-sm" title="Edit"><i class="fas fa-pen"></i></a><form action="{{ route('users.reset-password', $user) }}" method="POST" onsubmit="return confirm('Reset password menjadi password123?')">@csrf<button class="btn-secondary btn-sm text-amber-600" type="submit" title="Reset Password"><i class="fas fa-key"></i></button></form><form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Nonaktifkan dan hapus petugas ini?')">@csrf @method('DELETE')<button class="btn-secondary btn-sm text-red-600" type="submit" title="Hapus"><i class="fas fa-trash"></i></button></form></div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-6 py-4">{{ $users->links() }}</div>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="showCreateModal" x-transition
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-950/70 p-4"
             @click.self="showCreateModal = false" role="dialog" aria-modal="true" aria-labelledby="create-user-title">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 id="create-user-title" class="text-lg font-bold text-gray-800">Tambah Petugas Sanitasi</h2>
                        <p class="mt-1 text-sm text-gray-500">Buat akun baru untuk petugas kesehatan lingkungan</p>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-gray-400 transition hover:text-red-500" aria-label="Tutup popup">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @include('users.form', ['formAction' => route('users.store'), 'formMethod' => 'POST', 'user' => null, 'isModal' => true])
            </div>
        </div>
    </template>
</div>
@endsection
