@extends('layouts.app')

@section('title', 'Detail Warga')
@section('page-title', 'Detail Warga')
@section('page-subtitle', 'Informasi akun warga yang terdaftar')

@section('content')
<div class="card max-w-3xl p-6 fade-in">
    <div class="mb-6 flex items-center gap-4 border-b border-gray-100 pb-5">
        <div class="medical-gradient flex h-16 w-16 items-center justify-center rounded-2xl text-xl font-bold text-white">
            {{ strtoupper(substr($user->nama_lengkap ?: $user->username, 0, 1)) }}
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ $user->nama_lengkap ?: 'Nama belum diisi' }}</h2>
            <p class="text-sm text-gray-400">Username: {{ $user->username }}</p>
        </div>
        <span class="badge ml-auto {{ $user->is_active ? 'badge-resolved' : 'bg-gray-100 text-gray-500' }}">
            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <dl class="grid gap-5 sm:grid-cols-2">
        <div>
            <dt class="text-xs font-semibold uppercase text-gray-400">No. HP</dt>
            <dd class="mt-1 text-sm text-gray-700">{{ $user->no_telepon ?: 'Belum diisi' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase text-gray-400">Email</dt>
            <dd class="mt-1 text-sm text-gray-700">{{ $user->email ?: 'Belum diisi' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase text-gray-400">Desa</dt>
            <dd class="mt-1 text-sm text-gray-700">{{ $user->desa?->nama_desa ?: 'Belum dipilih' }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs font-semibold uppercase text-gray-400">Alamat</dt>
            <dd class="mt-1 text-sm text-gray-700">{{ $user->alamat ?: 'Belum diisi' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase text-gray-400">Tanggal terdaftar</dt>
            <dd class="mt-1 text-sm text-gray-700">{{ $user->created_at?->format('d M Y') ?: '-' }}</dd>
        </div>
    </dl>

    <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5">
        <a href="{{ route('warga.index') }}" class="btn-secondary">Kembali</a>
        <a href="{{ route('warga.edit', $user) }}" class="btn-primary"><i class="fas fa-pen"></i> Ubah</a>
    </div>
</div>
@endsection
