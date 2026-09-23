@extends('layouts.app')

@section('title', 'Kelola Edukasi')
@section('page-title', 'Kelola Edukasi')
@section('page-subtitle', 'Tambah, ubah, dan hapus artikel kesehatan lingkungan')

@section('content')
<div class="space-y-6 fade-in">
    
    <!-- Bagian Header & Tombol Tambah -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Daftar Artikel</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $articles->total() }} artikel tersimpan</p>
        </div>
        <a href="{{ route('education.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-600 hover:bg-red-700 text-white px-5 py-3.5 text-sm font-bold shadow-lg shadow-red-600/20 transition-all transform hover:-translate-y-0.5">
            <i class="fas fa-plus text-xs"></i> Tambah Artikel Baru
        </a>
    </div>

    <!-- Tabel Daftar Artikel -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left border-collapse">
                <thead class="bg-gray-50/70 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Judul Artikel</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Kategori</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($articles as $article)
                        <tr class="hover:bg-gray-50/50 transition">
                            <!-- Kolom Judul & Gambar (Tanpa Slug) -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($article->image)
                                        <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" class="h-10 w-10 rounded-xl object-cover shadow-sm">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-medical-red">
                                            <i class="fas fa-leaf text-sm"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-gray-800 text-sm">{{ $article->title }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Kategori -->
                            <td class="px-6 py-4">
                                @php
                                    $badgeColor = match($article->category) {
                                        'Air & Sanitasi' => 'bg-blue-50 text-blue-600',
                                        'Pengelolaan Sampah & Vektor' => 'bg-amber-50 text-amber-600',
                                        'PHBS' => 'bg-emerald-50 text-emerald-600',
                                        default => 'bg-gray-50 text-gray-650',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeColor }}">
                                    {{ $article->category }}
                                </span>
                            </td>

                            <!-- Kolom Tanggal -->
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $article->created_at->translatedFormat('d M Y') }}
                            </td>

                            <!-- Kolom Tombol Aksi (Edit & Hapus) -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Tombol Edit -->
                                    <a href="{{ route('education.edit', $article->id) }}" title="Edit artikel" class="group flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all shadow-sm">
                                        <i class="fas fa-pen text-xs"></i>
                                        <span class="text-xs font-semibold">Edit</span>
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <form method="POST" action="{{ route('education.destroy', $article->id) }}" onsubmit="return confirm('Hapus artikel ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus artikel" class="group flex items-center gap-1.5 px-3 py-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-trash text-xs"></i>
                                            <span class="text-xs font-semibold">Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 text-sm">
                                Belum ada artikel edukasi yang ditambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if($articles->hasPages())
            <div class="border-t border-gray-100 px-6 py-4 bg-gray-50/30">
                {{ $articles->links() }}
            </div>
        @endif
    </div>
</div>
@endsection