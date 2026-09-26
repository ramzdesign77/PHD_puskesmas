@extends('layouts.app')

@section('title', 'Edukasi Kesehatan')
@section('page-title', 'Edukasi Kesehatan')
@section('page-subtitle', 'Artikel dan informasi seputar kesehatan lingkungan untuk masyarakat')

@section('content')
<div class="space-y-6 fade-in" x-data="educationPage()">

    {{-- ─── Hero Banner ─────────────────────────────── --}}
    <div class="rounded-2xl p-6 md:p-8 relative overflow-hidden"
         style="background: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 50%, #2563EB 100%);">
        <div class="absolute top-0 right-0 w-80 h-80 bg-white/5 rounded-full -translate-y-40 translate-x-40"></div>
        <div class="absolute bottom-0 left-20 w-32 h-32 bg-white/5 rounded-full translate-y-16"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <div class="inline-flex items-center gap-2 bg-white/15 text-blue-100 px-3 py-1.5 rounded-xl text-xs font-semibold mb-3 backdrop-blur">
                    <i class="fas fa-book-open"></i> Pusat Edukasi Kesling
                </div>
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
                    Pengetahuan Untuk Hidup Sehat
                </h2>
                <p class="text-blue-200 text-sm max-w-lg">
                    Perluas wawasan Anda tentang kesehatan lingkungan. Artikel dikurasi oleh tim medis Puskesmas Sumbersari.
                </p>
            </div>
            <div class="hidden md:block">
                <div class="w-20 h-20 bg-white/15 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Search & Filter ──────────────────────────── --}}
    <div class="flex flex-col md:flex-row gap-4">
        {{-- Search --}}
        <div class="relative flex-1">
            <input type="text" x-model="search" id="edu-search"
                   placeholder="Cari artikel kesehatan..."
                   class="form-input pl-10 py-3 w-full">
            <i class="fas fa-search absolute left-3 top-3.5 text-gray-400 text-sm"></i>
        </div>

        {{-- Category Filter --}}
        <div class="flex gap-2 flex-wrap">
            @foreach($categories as $cat)
            <button @click="activeCategory = '{{ $cat }}'"
                    :class="activeCategory === '{{ $cat }}'
                        ? 'bg-red-500 text-white border-transparent shadow-md'
                        : 'bg-white text-gray-600 border-gray-200 hover:border-red-300 hover:text-red-500'"
                    class="px-4 py-2 rounded-xl border text-xs font-semibold transition-all">
                {{ $cat }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- ─── Article Grid ─────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($articles as $article)
        <div class="card overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group"
             x-show="filterArticle('{{ $article['category'] }}', '{{ addslashes($article['title']) }}')"
             x-transition>

            {{-- Thumbnail/Color Block --}}
            <div class="h-44 relative overflow-hidden
                @if($article['color'] === 'blue') bg-gradient-to-br from-blue-400 to-blue-600
                @elseif($article['color'] === 'green') bg-gradient-to-br from-green-400 to-emerald-600
                @elseif($article['color'] === 'red') bg-gradient-to-br from-red-400 to-rose-600
                @elseif($article['color'] === 'yellow') bg-gradient-to-br from-yellow-400 to-orange-500
                @elseif($article['color'] === 'purple') bg-gradient-to-br from-purple-400 to-violet-600
                @else bg-gradient-to-br from-teal-400 to-cyan-600
                @endif">
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fas
                        @if($article['icon'] === 'water') fa-tint
                        @elseif($article['icon'] === 'home') fa-home
                        @elseif($article['icon'] === 'bug') fa-bug
                        @elseif($article['icon'] === 'trash') fa-trash-alt
                        @elseif($article['icon'] === 'factory') fa-industry
                        @else fa-heart
                        @endif
                        text-white/30 text-7xl transform group-hover:scale-110 transition-transform duration-500"></i>
                </div>
                <div class="absolute inset-0 bg-black/10 group-hover:bg-black/5 transition-colors"></div>
                {{-- Category Badge --}}
                <div class="absolute top-4 left-4">
                    <span class="bg-white/90 backdrop-blur text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm">
                        {{ $article['category'] }}
                    </span>
                </div>
                {{-- Read Time --}}
                <div class="absolute top-4 right-4">
                    <span class="bg-black/30 backdrop-blur text-white text-xs font-medium px-2.5 py-1.5 rounded-lg">
                        <i class="fas fa-clock mr-1"></i>{{ $article['read_time'] }}
                    </span>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="p-5">
                <h4 class="font-bold text-gray-800 text-base leading-snug mb-2 group-hover:text-red-600 transition-colors line-clamp-2">
                    {{ $article['title'] }}
                </h4>
                <p class="text-gray-500 text-sm leading-relaxed mb-4 line-clamp-3">
                    {{ $article['excerpt'] }}
                </p>

                {{-- Meta --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 medical-gradient rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-md text-white text-xs"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-600">{{ $article['author'] }}</p>
                            <p class="text-xs text-gray-400">{{ $article['date'] }}</p>
                        </div>
                    </div>
                    <button class="btn-primary btn-sm" id="btn-read-{{ $article['id'] }}">
                        <i class="fas fa-arrow-right text-xs"></i>
                        Baca
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Empty State --}}
    <div x-show="noResults" x-transition class="text-center py-16">
        <i class="fas fa-search text-5xl text-gray-200 mb-4"></i>
        <p class="text-gray-400 font-medium">Artikel tidak ditemukan</p>
        <p class="text-gray-300 text-sm mt-1">Coba kata kunci yang berbeda</p>
        <button @click="search = ''; activeCategory = 'Semua'" class="btn-secondary btn-sm mt-4">
            Reset Filter
        </button>
    </div>

</div>
@endsection

@section('scripts')
<script>
function educationPage() {
    return {
        search: '',
        activeCategory: 'Semua',
        noResults: false,

        filterArticle(category, title) {
            const matchCat  = this.activeCategory === 'Semua' || category === this.activeCategory;
            const matchSearch = title.toLowerCase().includes(this.search.toLowerCase());
            return matchCat && matchSearch;
        },
    };
}
</script>
@endsection
