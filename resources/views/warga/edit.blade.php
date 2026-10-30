@extends('layouts.app')

@section('title', 'Edit Akun Warga')
@section('page-title', 'Edit Akun Warga')
@section('page-subtitle', 'Perbarui informasi akun warga')

@section('content')
<div class="card max-w-3xl p-6 fade-in">
    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('warga.update', $user) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="nama_lengkap" class="mb-1 block text-sm font-semibold text-gray-700">Nama lengkap</label>
                <input id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required class="form-input">
            </div>
            <div>
                <label for="username" class="mb-1 block text-sm font-semibold text-gray-700">Username</label>
                <input id="username" name="username" value="{{ old('username', $user->username) }}" required class="form-input">
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-semibold text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input">
            </div>
            <div>
                <label for="no_telepon" class="mb-1 block text-sm font-semibold text-gray-700">Nomor telepon</label>
                <input id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $user->no_telepon) }}" class="form-input">
            </div>
            <div>
                <label for="id_desa" class="mb-1 block text-sm font-semibold text-gray-700">Desa</label>
                <select id="id_desa" name="id_desa" class="form-input">
                    <option value="">Pilih desa</option>
                    @foreach ($desas as $desa)
                        <option value="{{ $desa->id_desa }}" @selected((string) old('id_desa', $user->id_desa) === (string) $desa->id_desa)>
                            {{ $desa->nama_desa }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="is_active" class="mb-1 block text-sm font-semibold text-gray-700">Status akun</label>
                <select id="is_active" name="is_active" class="form-input">
                    <option value="1" @selected((string) old('is_active', (int) $user->is_active) === '1')>Aktif</option>
                    <option value="0" @selected((string) old('is_active', (int) $user->is_active) === '0')>Nonaktif</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="alamat" class="mb-1 block text-sm font-semibold text-gray-700">Alamat</label>
                <textarea id="alamat" name="alamat" rows="3" class="form-input">{{ old('alamat', $user->alamat) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
            <a href="{{ route('warga.show', $user) }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </form>
</div>
@endsection
