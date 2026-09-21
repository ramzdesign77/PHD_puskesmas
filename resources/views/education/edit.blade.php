@extends('layouts.app')

@section('title', 'Edit Artikel')
@section('page-title', 'Edit Artikel')
@section('page-subtitle', 'Perbarui informasi kesehatan lingkungan')

@section('content')
<div class="max-w-3xl mx-auto fade-in">
    <div class="mb-6"><a href="{{ route('education.manage') }}" class="text-sm font-semibold text-medical-red"><i class="fas fa-arrow-left mr-2"></i>Kembali ke daftar</a></div>
    <form method="POST" action="{{ route('education.update', $article->id) }}" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm p-6 md:p-8 space-y-6">
        @csrf @method('PUT')
        <div><h1 class="text-xl font-bold text-gray-800">Perbarui Artikel</h1><p class="text-sm text-gray-500 mt-1">Perubahan akan langsung terlihat pada halaman publik.</p></div>
        @if($errors->any())<div class="rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-700"><ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div><label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Judul</label><input id="title" name="title" value="{{ old('title', $article->title) }}" required maxlength="255" class="w-full rounded-xl border-gray-200 focus:border-medical-red focus:ring-medical-red"></div>
        <div><label for="category" class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label><select id="category" name="category" required class="w-full rounded-xl border-gray-200 focus:border-medical-red focus:ring-medical-red"><option value="">Pilih kategori</option>@foreach(['Air Bersih', 'Sanitasi', 'DBD & Nyamuk', 'Persampahan', 'Limbah', 'PHBS'] as $category)<option value="{{ $category }}" @selected(old('category', $article->category) === $category)>{{ $category }}</option>@endforeach</select></div>
        <div><label for="image" class="block text-sm font-semibold text-gray-700 mb-2">Gambar Baru <span class="font-normal text-gray-400">(opsional, JPG/PNG/WebP maks. 2 MB)</span></label>@if($article->image)<p class="text-xs text-gray-500 mb-2">Gambar saat ini: {{ basename($article->image) }}</p>@endif<input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-gray-200 text-sm text-gray-500 file:mr-4 file:border-0 file:bg-red-50 file:px-4 file:py-3 file:font-semibold file:text-medical-red"></div>
        <div><label for="content" class="block text-sm font-semibold text-gray-700 mb-2">Konten</label><textarea id="content" name="content" rows="14" required class="w-full rounded-xl border-gray-200 focus:border-medical-red focus:ring-medical-red">{{ old('content', $article->content) }}</textarea></div>
        <div class="flex justify-end gap-3 border-t border-gray-100 pt-6"><a href="{{ route('education.manage') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50">Batal</a><button type="submit" class="btn-medical rounded-xl px-5 py-3 text-sm font-semibold"><i class="fas fa-save mr-2"></i>Perbarui Artikel</button></div>
    </form>
</div>
@endsection
