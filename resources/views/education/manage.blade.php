@extends('layouts.app')

@section('title', 'Manajemen Edukasi')
@section('page-title', 'Manajemen Edukasi')
@section('page-subtitle', 'Kelola artikel dan materi kesehatan lingkungan')

@section('content')
<div class="space-y-6 fade-in">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="text-sm text-gray-500">{{ count($articles) }} materi edukasi terakhir</p></div>
        <button type="button" class="btn-primary"><i class="fas fa-plus"></i> Tambah Artikel</button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="stat-card"><p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Artikel</p><p class="mt-2 text-2xl font-bold text-gray-800">15</p></div>
        <div class="stat-card"><p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Sudah Terbit</p><p class="mt-2 text-2xl font-bold text-green-600">12</p></div>
        <div class="stat-card"><p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Draft</p><p class="mt-2 text-2xl font-bold text-yellow-600">3</p></div>
    </div>

    <div class="card overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="font-bold text-gray-800">Daftar Materi Edukasi</h3>
            <div class="relative sm:w-64"><input type="search" placeholder="Cari artikel..." class="form-input pl-9"><i class="fas fa-search absolute left-3 top-3 text-xs text-gray-400"></i></div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table min-w-180">
                <thead><tr><th>Judul Artikel</th><th>Kategori</th><th>Status</th><th>Diperbarui</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @foreach($articles as $article)
                    <tr>
                        <td><p class="max-w-md font-semibold text-gray-800">{{ $article['title'] }}</p></td>
                        <td><span class="badge badge-progress">{{ $article['category'] }}</span></td>
                        <td><span class="badge {{ $article['status'] === 'Terbit' ? 'badge-resolved' : 'badge-pending' }}">{{ $article['status'] }}</span></td>
                        <td>{{ $article['updated'] }}</td>
                        <td class="text-right"><button type="button" class="text-sm font-semibold text-red-500 hover:text-red-700"><i class="fas fa-pen"></i> Ubah</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
