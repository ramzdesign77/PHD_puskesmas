@extends('layouts.app')

@section('title', 'Edukasi Kesehatan')
@section('page-title', 'Edukasi Kesehatan')
@section('page-subtitle', 'Artikel kesehatan lingkungan untuk warga Sumbersari')

@section('content')
<div class="space-y-6 fade-in" x-data="{ search: '', category: 'Semua' }">
    <section class="medical-gradient rounded-2xl p-6 md:p-8 text-white">
        <div class="flex items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-2 bg-white/15 px-3 py-1.5 rounded-xl text-xs font-semibold">
                    <i class="fas fa-book-open"></i> Pusat Edukasi Kesling
                </span>
                <h1 class="text-2xl md:text-3xl font-bold mt-3">Pengetahuan untuk hidup sehat</h1>
                <p class="text-red-100 text-sm mt-2 max-w-xl">Informasi praktis dari tim Puskesmas Sumbersari untuk menjaga kesehatan lingkungan keluarga.</p>
            </div>
            <i class="fas fa-heartbeat text-white/30 text-6xl hidden md:block"></i>
        </div>
    </section>

    <div class="flex flex-col lg:flex-row gap-3">
        <label class="relative flex-1">
            <span class="sr-only">Cari artikel</span>
            <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
            <input type="search" x-model="search" placeholder="Cari artikel kesehatan..." class="w-full rounded-xl border-gray-200 pl-11 py-3 focus:border-medical-red focus:ring-medical-red">
        </label>
        <div class="flex gap-2 flex-wrap">
            <button type="button" @click="category = 'Semua'" :class="category === 'Semua' ? 'bg-medical-red text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-4 py-2 rounded-xl text-xs font-semibold">Semua</button>
            @foreach($categories as $category)
                <button type="button" @click="category = @js($category)" :class="category === @js($category) ? 'bg-medical-red text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-4 py-2 rounded-xl text-xs font-semibold">{{ $category }}</button>
            @endforeach
        </div>
    </div>

    @if($articles->count())
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($articles as $article)
                <article x-show="(category === 'Semua' || category === @js($article->category)) && (@js(strtolower($article->title)).includes(search.toLowerCase()))" class="card overflow-hidden group">
                    @if($article->image)
                        <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" class="h-44 w-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="h-44 medical-gradient flex items-center justify-center"><i class="fas fa-leaf text-white/40 text-7xl"></i></div>
                    @endif
                    <div class="p-5">
                        <span class="text-xs font-semibold text-medical-red">{{ $article->category }}</span>
                        <h2 class="font-bold text-gray-800 text-lg leading-snug mt-2">{{ $article->title }}</h2>
                        <p class="text-gray-500 text-sm leading-relaxed mt-2 line-clamp-3">{{ $article->excerpt }}</p>
                        <div class="flex items-center justify-between border-t border-gray-100 mt-5 pt-4">
                            <time class="text-xs text-gray-400" datetime="{{ $article->created_at->toDateString() }}">{{ $article->created_at->translatedFormat('d F Y') }}</time>
                            <a href="{{ route('education.show', $article->slug) }}" class="text-sm font-semibold text-medical-red hover:text-medical-dark">Baca <i class="fas fa-arrow-right text-xs ml-1"></i></a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div>{{ $articles->links() }}</div>
    @else
        <div class="bg-white rounded-2xl border border-dashed border-gray-200 text-center py-16">
            <i class="fas fa-book-open text-4xl text-gray-200"></i>
            <p class="text-gray-500 mt-4">Belum ada artikel edukasi.</p>
        </div>
    @endif
</div>
@endsection
