@extends('layouts.app')

@section('title', 'Manajemen Petugas')
@section('page-title', 'Manajemen Petugas')
@section('page-subtitle', 'Kelola akses dan penempatan petugas kesehatan lingkungan')

@section('content')
<div class="space-y-6 fade-in">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ count($users) }} akun terdaftar dalam sistem</p>
        </div>
        <button type="button" class="btn-primary">
            <i class="fas fa-user-plus"></i> Tambah Petugas
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="stat-card"><p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Akun</p><p class="mt-2 text-2xl font-bold text-gray-800">{{ count($users) }}</p></div>
        <div class="stat-card"><p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Akun Aktif</p><p class="mt-2 text-2xl font-bold text-green-600">{{ count($users) }}</p></div>
        <div class="stat-card"><p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Area Terjangkau</p><p class="mt-2 text-2xl font-bold text-blue-600">3</p></div>
    </div>

    <div class="card overflow-hidden">
        <div class="border-b border-gray-100 px-5 py-4"><h3 class="font-bold text-gray-800">Daftar Petugas</h3></div>
        <div class="overflow-x-auto">
            <table class="data-table min-w-180">
                <thead><tr><th>Petugas</th><th>Peran</th><th>Wilayah</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td><div class="flex items-center gap-3"><div class="medical-gradient flex h-9 w-9 items-center justify-center rounded-xl text-sm font-bold text-white">{{ strtoupper(substr($user['name'], 0, 1)) }}</div><div><p class="font-semibold text-gray-800">{{ $user['name'] }}</p><p class="text-xs text-gray-400">{{ $user['username'] }}</p></div></div></td>
                        <td><span class="badge badge-assigned">{{ $user['role'] }}</span></td>
                        <td>{{ $user['area'] }}</td>
                        <td><span class="badge badge-resolved"><i class="fas fa-circle text-[7px]"></i> {{ $user['status'] }}</span></td>
                        <td class="text-right"><button type="button" class="text-sm font-semibold text-red-500 hover:text-red-700"><i class="fas fa-pen"></i> Ubah</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
