@extends('layouts.app')

@section('title', 'Ubah Petugas')
@section('page-title', 'Ubah Petugas Sanitasi')
@section('page-subtitle', 'Perbarui data dan akses akun petugas')

@section('content')
<div class="card max-w-3xl p-6 fade-in">
    @include('users.form', ['formAction' => route('users.update', $user), 'formMethod' => 'PUT', 'user' => $user])
    <div class="mt-5 border-t border-gray-100 pt-5">
        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun petugas ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100"><i class="fas fa-trash"></i> Hapus akun petugas</button>
        </form>
    </div>
</div>
@endsection
