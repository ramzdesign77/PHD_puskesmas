@extends('layouts.app')

<<<<<<< HEAD
@section('title', 'Kelola Edukasi')
@section('page-title', 'Kelola Edukasi')
@section('page-subtitle', 'Tambah, ubah, dan hapus artikel kesehatan lingkungan')

@section('content')
<div class="space-y-6 fade-in">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-800">Daftar Artikel</h1><p class="text-sm text-gray-500 mt-1">{{ $articles->total() }} artikel tersimpan</p></div>
        <a href="{{ route('education.create') }}" class="btn-medical inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"><i class="fas fa-plus"></i> Tambah Artikel Baru</a>
    </div>
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-[680px]">
                <thead class="bg-gray-50 border-b border-gray-100"><tr><th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Judul</th><th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Kategori</th><th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Tanggal</th><th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-right">Aksi</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($articles as $article)
                        <tr class="hover:bg-red-50/30"><td class="px-6 py-4"><div class="flex items-center gap-3">@if($article->image)<img src="{{ Storage::url($article->image) }}" alt="" class="w-10 h-10 rounded-lg object-cover">@else<div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center"><i class="fas fa-leaf text-medical-red"></i></div>@endif<div><p class="font-semibold text-gray-800">{{ $article->title }}</p><p class="text-xs text-gray-400">/edukasi/{{ $article->slug }}</p></div></div></td><td class="px-6 py-4"><span class="rounded-full bg-red-50 text-medical-red px-3 py-1 text-xs font-semibold">{{ $article->category }}</span></td><td class="px-6 py-4 text-sm text-gray-500">{{ $article->created_at->translatedFormat('d M Y') }}</td><td class="px-6 py-4"><div class="flex justify-end gap-2"><a href="{{ route('education.edit', $article->id) }}" title="Edit artikel" class="w-9 h-9 rounded-lg bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-medical-red inline-flex items-center justify-center"><i class="fas fa-pen text-xs"></i></a><form method="POST" action="{{ route('education.destroy', $article->id) }}" onsubmit="return confirm('Hapus artikel ini?')">@csrf @method('DELETE')<button type="submit" title="Hapus artikel" class="w-9 h-9 rounded-lg bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form></div></td></tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-14 text-center text-gray-400">Belum ada artikel edukasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($articles->hasPages())<div class="border-t border-gray-100 px-6 py-4">{{ $articles->links() }}</div>@endif
=======
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
>>>>>>> 50be3d49032f3e699f0bb9f2350e61942281d41d
    </div>
</div>
@endsection
