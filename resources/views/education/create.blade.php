@extends('layouts.app')

@section('title', 'Tambah Artikel')
@section('page-title', 'Tambah Artikel Baru')
@section('page-subtitle', 'Publikasikan informasi kesehatan lingkungan')

@section('content')
<div class="max-w-3xl mx-auto fade-in">
    
    <!-- Tombol Kembali -->
    <div class="mb-6">
        <a href="{{ route('education.manage') }}" class="text-sm font-semibold text-medical-red hover:underline">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke daftar
        </a>
    </div>

    <!-- Form Tambah Artikel -->
    <form method="POST" action="{{ route('education.store') }}" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-sm p-6 md:p-8 space-y-6">
        @csrf

        <!-- Judul Bagian Utama -->
        <div class="border-b border-gray-100 pb-4">
            <h1 class="text-xl font-bold text-gray-800">Informasi Artikel</h1>
            <p class="text-sm text-gray-500 mt-1">Isi konten yang mudah dipahami warga.</p>
        </div>

        <!-- Kotak Peringatan Error Validasi -->
        @if($errors->any())
            <div class="rounded-2xl bg-red-50 border border-red-100 p-4 text-sm text-red-700">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Container: Judul Artikel -->
        <div class="p-4 bg-gray-50/80 rounded-2xl border border-gray-200/70 focus-within:border-gray-800 transition-all">
            <label for="title" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Judul Artikel</label>
            <input id="title" name="title" value="{{ old('title') }}" required maxlength="255" 
                   class="w-full bg-transparent border-0 p-0 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-0 text-sm font-medium shadow-none" 
                   placeholder="Contoh: Menjaga Air Bersih di Rumah">
        </div>

        <!-- Container: Kategori Artikel -->
        <div class="p-4 bg-gray-50/80 rounded-2xl border border-gray-200/70 focus-within:border-gray-800 transition-all">
            <label for="category" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Kategori</label>
            <select id="category" name="category" required 
                    class="w-full bg-transparent border-0 p-0 text-gray-800 focus:outline-none focus:ring-0 text-sm font-medium cursor-pointer shadow-none">
                <option value="" class="text-gray-400">Pilih kategori artikel...</option>
                @foreach(['Air & Sanitasi', 'Pengelolaan Sampah & Vektor', 'PHBS'] as $category)
                    <option value="{{ $category }}" @selected(old('category') === $category)>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Container: Unggah Gambar -->
        <div class="p-4 bg-gray-50/80 rounded-2xl border border-gray-200/70 focus-within:border-gray-800 transition-all">
            <label for="image" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                Gambar Banner <span class="font-normal text-gray-400 lowercase">(opsional, maks. 5 MB)</span>
            </label>
            <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" 
                   class="block w-full bg-transparent border-0 p-0 text-sm text-gray-500 file:mr-4 file:border-0 file:bg-red-50 file:rounded-xl file:px-4 file:py-2 file:font-semibold file:text-medical-red cursor-pointer focus:outline-none focus:ring-0">
        </div>

        <!-- Container: Konten Artikel -->
        <div class="p-4 bg-gray-50/80 rounded-2xl border border-gray-200/70 focus-within:border-gray-800 transition-all">
            <label for="content" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Isi Konten</label>
            <textarea id="content" name="content" rows="10" required 
                      class="w-full bg-transparent border-0 p-0 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-0 text-sm font-medium resize-y shadow-none" 
                      placeholder="Tulis isi artikel secara lengkap di sini...">{{ old('content') }}</textarea>
        </div>

        <!-- Tombol Aksi (Batal & Simpan yang Kontras & Menarik) -->
        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6">
            <a href="{{ route('education.manage') }}" class="rounded-xl px-5 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-100 transition">
                Batal
            </a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-600 hover:bg-red-700 text-white px-7 py-3.5 text-sm font-bold shadow-lg shadow-red-600/30 transition-all transform hover:-translate-y-0.5">
                <i class="fas fa-save text-xs"></i> Simpan Artikel
            </button>
        </div>
    </form>
</div>
@endsection