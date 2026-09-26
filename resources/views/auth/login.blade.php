@extends('layouts.auth')

@section('content')
<div x-data="{
    selectedRole: '',
    loading: false,
    selectRole(role) {
        this.selectedRole = role;
    }
}">
    <h2 class="text-2xl font-bold text-gray-800 text-center mb-1">Selamat Datang</h2>
    <p class="text-gray-400 text-sm text-center mb-7">Masuk ke sistem informasi kesehatan lingkungan</p>

    @if ($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <p class="text-red-700 text-sm">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" @submit="loading = true">
        @csrf

        {{-- Role Selection --}}
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-3">
                <i class="fas fa-user-tag text-medical-red mr-1.5"></i>
                Masuk sebagai
            </label>
            <div class="grid grid-cols-3 gap-3">

                {{-- Citizen --}}
                <label @click="selectRole('citizen')"
                       class="role-card rounded-2xl p-3 text-center cursor-pointer transition-all select-none"
                       :class="selectedRole === 'citizen' ? 'selected' : ''">
                    <input type="radio" name="role" value="citizen" class="sr-only" required
                           :checked="selectedRole === 'citizen'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-2 transition-all"
                         :class="selectedRole === 'citizen' ? 'bg-red-500' : 'bg-blue-100'">
                        <i class="fas fa-user text-sm transition-all"
                           :class="selectedRole === 'citizen' ? 'text-white' : 'text-blue-500'"></i>
                    </div>
                    <p class="text-xs font-semibold text-gray-700 leading-tight">Masyarakat</p>
                    <p class="text-xs text-gray-400 mt-0.5">Citizen</p>
                </label>

                {{-- Officer --}}
                <label @click="selectRole('officer')"
                       class="role-card rounded-2xl p-3 text-center cursor-pointer transition-all select-none"
                       :class="selectedRole === 'officer' ? 'selected' : ''">
                    <input type="radio" name="role" value="officer" class="sr-only"
                           :checked="selectedRole === 'officer'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-2 transition-all"
                         :class="selectedRole === 'officer' ? 'bg-red-500' : 'bg-green-100'">
                        <i class="fas fa-user-md text-sm transition-all"
                           :class="selectedRole === 'officer' ? 'text-white' : 'text-green-500'"></i>
                    </div>
                    <p class="text-xs font-semibold text-gray-700 leading-tight">Petugas</p>
                    <p class="text-xs text-gray-400 mt-0.5">Kesling</p>
                </label>

                {{-- Admin --}}
                <label @click="selectRole('admin')"
                       class="role-card rounded-2xl p-3 text-center cursor-pointer transition-all select-none"
                       :class="selectedRole === 'admin' ? 'selected' : ''">
                    <input type="radio" name="role" value="admin" class="sr-only"
                           :checked="selectedRole === 'admin'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-2 transition-all"
                         :class="selectedRole === 'admin' ? 'bg-red-500' : 'bg-orange-100'">
                        <i class="fas fa-user-shield text-sm transition-all"
                           :class="selectedRole === 'admin' ? 'text-white' : 'text-orange-500'"></i>
                    </div>
                    <p class="text-xs font-semibold text-gray-700 leading-tight">Admin</p>
                    <p class="text-xs text-gray-400 mt-0.5">Kepala PKM</p>
                </label>
            </div>
        </div>

        {{-- Email --}}
        <div class="mb-4">
            <label for="login-email" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-envelope text-gray-400 mr-1.5"></i>Email
            </label>
            <div class="relative">
                <input id="login-email" type="email" name="email" value="{{ old('email') }}"
                       placeholder="contoh@email.com"
                       class="input-focus w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-700 bg-gray-50 focus:bg-white transition-all"
                       required>
                <i class="fas fa-at absolute left-3 top-3.5 text-gray-400 text-sm"></i>
            </div>
        </div>

        {{-- Password --}}
        <div class="mb-6" x-data="{ show: false }">
            <label for="login-password" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-lock text-gray-400 mr-1.5"></i>Password
            </label>
            <div class="relative">
                <input id="login-password" :type="show ? 'text' : 'password'" name="password"
                       placeholder="Masukkan password"
                       class="input-focus w-full pl-10 pr-10 py-3 border border-gray-200 rounded-xl text-sm text-gray-700 bg-gray-50 focus:bg-white transition-all"
                       required>
                <i class="fas fa-lock absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                <button type="button" @click="show = !show"
                        class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600">
                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" class="text-sm"></i>
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-1.5">
                <i class="fas fa-info-circle"></i>
                Untuk demo: gunakan email & password apapun
            </p>
        </div>

        {{-- Submit Button --}}
        <button type="submit" id="btn-login"
                class="btn-medical w-full py-3.5 rounded-xl font-semibold text-white flex items-center justify-center gap-2 text-sm"
                :disabled="loading || !selectedRole">
            <span x-show="!loading">
                <i class="fas fa-sign-in-alt mr-1"></i>
                Masuk ke Sistem
            </span>
            <span x-show="loading" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memproses...
            </span>
        </button>

        <p x-show="!selectedRole" class="text-xs text-center text-red-400 mt-3">
            <i class="fas fa-arrow-up mr-1"></i>Pilih peran Anda terlebih dahulu
        </p>
    </form>

    {{-- Info Banner --}}
    <div class="mt-6 p-3 bg-blue-50 border border-blue-100 rounded-xl">
        <p class="text-xs text-blue-600 text-center font-medium">
            <i class="fas fa-shield-alt mr-1"></i>
            Data Anda dilindungi oleh sistem keamanan SIMKES
        </p>
    </div>
</div>
@endsection
