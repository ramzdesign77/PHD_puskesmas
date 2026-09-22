@extends('layouts.app')

@section('title', 'Kelola Edukasi')
@section('page-title', 'Kelola Edukasi')
@section('page-subtitle', 'Tambah, ubah, dan hapus artikel kesehatan lingkungan')

@section('content')
<div class="space-y-6 fade-in">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Daftar Artikel</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $articles->total() }} artikel tersimpan</p>
        </div>
        <a href="{{ route('education.create') }}" class="btn-medical inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold">
            <i class="fas fa-plus"></i>
            Tambah Artikel Baru
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left">
                <thead class="border-b border-gray-100 bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Judul</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Kategori</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($articles as $article)
                        <tr class="hover:bg-red-50/30">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($article->image)
                                        <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" class="h-10 w-10 rounded-lg object-cover">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50">
                                            <i class="fas fa-leaf text-medical-red"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $article->title }}</p>
                                        <p class="text-xs text-gray-400">/edukasi/{{ $article->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-medical-red">{{ $article->category }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $article->created_at->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('education.edit', $article->id) }}" title="Edit artikel" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-50 text-gray-500 transition hover:bg-red-50 hover:text-medical-red">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <form method="POST" action="{{ route('education.destroy', $article->id) }}" onsubmit="return confirm('Hapus artikel ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus artikel" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-50 text-gray-500 transition hover:bg-red-50 hover:text-red-600">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center text-gray-400">
                                Belum ada artikel edukasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($articles->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">
                {{ $articles->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
