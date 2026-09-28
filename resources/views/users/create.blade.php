@extends('layouts.app')

@section('title', 'Tambah Akun')
@section('page-title', 'Tambah Akun')
@section('page-subtitle', 'Buat akun baru untuk petugas atau admin')

@section('content')
<div class="card max-w-3xl p-6 fade-in">
    @include('users.form', ['formAction' => route('users.store'), 'formMethod' => 'POST', 'user' => null])
</div>
@endsection
