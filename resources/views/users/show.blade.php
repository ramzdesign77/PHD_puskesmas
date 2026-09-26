@extends('layouts.app')

@section('title', 'Detail Petugas')
@section('page-title', 'Detail Petugas')
@section('page-subtitle', 'Informasi lengkap dan penempatan petugas')

@section('content')
<div class="card max-w-3xl p-6 fade-in">
    <div class="mb-6 flex items-center gap-4 border-b border-gray-100 pb-5">
        @if($user->foto)
            <img src="{{ asset('storage/' . $user->foto) }}" alt="Foto {{ $user->nama_lengkap }}" class="h-16 w-16 rounded-2xl object-cover">
        @else
            <div class="medical-gradient flex h-16 w-16 items-center justify-center rounded-2xl text-xl font-bold text-white">{{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}</div>
        @endif
        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ $user->nama_lengkap }}</h2>
            <p class="text-sm text-gray-400">ID Petugas: {{ $user->id_petugas ?: 'Belum diisi' }}</p>
        </div>
        <span class="badge ml-auto {{ $user->is_active ? 'badge-resolved' : 'bg-gray-100 text-gray-500' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
    </div>
    <dl class="grid gap-5 sm:grid-cols-2">
        <div><dt class="text-xs font-semibold uppercase text-gray-400">Username</dt><dd class="mt-1 text-sm text-gray-700">{{ $user->username }}</dd></div>
        <div><dt class="text-xs font-semibold uppercase text-gray-400">Jenis akun</dt><dd class="mt-1 text-sm text-gray-700">Petugas</dd></div>
        <div><dt class="text-xs font-semibold uppercase text-gray-400">No. HP</dt><dd class="mt-1 text-sm text-gray-700">{{ $user->no_telepon ?: 'Belum diisi' }}</dd></div>
        <div><dt class="text-xs font-semibold uppercase text-gray-400">Email</dt><dd class="mt-1 text-sm text-gray-700">{{ $user->email ?: 'Belum diisi' }}</dd></div>
        <div><dt class="text-xs font-semibold uppercase text-gray-400">Wilayah Kerja / Penempatan</dt><dd class="mt-1 text-sm text-gray-700">{{ $user->wilayah_kerja ?: 'Belum ditempatkan' }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase text-gray-400">Alamat</dt><dd class="mt-1 text-sm text-gray-700">{{ $user->alamat ?: 'Belum diisi' }}</dd></div>
    </dl>
    <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5">
        <a href="{{ route('users.index') }}" class="btn-secondary">Kembali</a>
        <a href="{{ route('users.edit', $user) }}" class="btn-primary"><i class="fas fa-pen"></i> Ubah</a>
    </div>
</div>
@endsection
