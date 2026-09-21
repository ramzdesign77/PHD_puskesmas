@extends('layouts.app')

@section('title', $article->title)
@section('page-title', 'Detail Edukasi')
@section('page-subtitle', 'Informasi kesehatan lingkungan')

@section('content')
<article class="max-w-4xl mx-auto fade-in">
    <a href="{{ route('education.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-medical-red hover:text-medical-dark mb-6"><i class="fas fa-arrow-left"></i> Kembali ke edukasi</a>
    <div class="bg-white rounded-2xl overflow-hidden shadow-sm">
        @if($article->image)
            <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" class="w-full max-h-96 object-cover">
        @else
            <div class="h-56 md:h-72 medical-gradient flex items-center justify-center"><i class="fas fa-leaf text-white/35 text-8xl"></i></div>
        @endif
        <div class="p-6 md:p-10">
            <span class="inline-flex rounded-full bg-red-50 text-medical-red px-3 py-1 text-xs font-bold">{{ $article->category }}</span>
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight mt-4">{{ $article->title }}</h1>
            <p class="text-sm text-gray-400 mt-3">Diterbitkan {{ $article->created_at->translatedFormat('d F Y') }}</p>
            <div class="border-t border-gray-100 mt-8 pt-8 text-gray-700 leading-8 whitespace-normal">
                {!! nl2br(e($article->content)) !!}
            </div>
        </div>
    </div>
</article>
@endsection
