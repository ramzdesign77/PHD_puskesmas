@extends('layouts.app')

@section('title', 'Tambah Petugas')
@section('page-title', 'Tambah Petugas Sanitasi')
@section('page-subtitle', 'Buat akun baru untuk petugas kesehatan lingkungan')

@section('content')
<div class="card max-w-3xl p-6 fade-in">
    @include('users.form', ['formAction' => route('users.store'), 'formMethod' => 'POST', 'user' => null])
</div>
@endsection
