<form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif
    @if ($errors->any())
        <div class="rounded-lg bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2"><label for="nama_lengkap" class="mb-1 block text-sm font-semibold text-gray-700">Nama
                lengkap</label><input id="nama_lengkap" name="nama_lengkap"
                value="{{ old('nama_lengkap', $user?->nama_lengkap) }}" required
                class="w-full rounded-lg border-2 border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 shadow-sm outline-none transition focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-100">
        </div>
        @if ($user)
            <div>
                <p class="mb-1 text-sm font-semibold text-gray-700">ID Petugas</p>
                <p class="rounded-lg border-2 border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-600">
                    {{ $user->id_petugas ?: 'Tidak tersedia' }}
                </p>
            </div>
        @else
            <div>
                <p class="mb-1 text-sm font-semibold text-gray-700">ID Petugas</p>
                <p class="rounded-lg border-2 border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500">
                    Dibuat otomatis sesuai peran akun saat disimpan.
                </p>
            </div>
        @endif
        <div><label for="email" class="mb-1 block text-sm font-semibold text-gray-700">Email</label><input
                id="email" type="email" name="email" value="{{ old('email', $user?->email) }}"
                class="w-full rounded-lg border-2 border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 shadow-sm outline-none transition focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-100">
        </div>
        <div><label for="username" class="mb-1 block text-sm font-semibold text-gray-700">Username</label><input
                id="username" name="username" value="{{ old('username', $user?->username) }}" required
                class="w-full rounded-lg border-2 border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 shadow-sm outline-none transition focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-100">
        </div>
        <div><label for="password" class="mb-1 block text-sm font-semibold text-gray-700">Password
                {{ $user ? '(kosongkan jika tidak diubah)' : '' }}</label><input id="password" type="password"
                name="password" {{ $user ? '' : 'required' }}
                class="w-full rounded-lg border-2 border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 shadow-sm outline-none transition focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-100">
        </div>
        <div><label for="no_telepon" class="mb-1 block text-sm font-semibold text-gray-700">Nomor telepon</label><input
                id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $user?->no_telepon) }}"
                class="w-full rounded-lg border-2 border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 shadow-sm outline-none transition focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-100">
        </div>
        <fieldset class="sm:col-span-2">
            <legend class="mb-2 text-sm font-semibold text-gray-700">Role akun <span
                    class="font-normal text-gray-400">(pilih satu atau dua)</span></legend>
            <div class="grid gap-3 sm:grid-cols-2">
                <label
                    class="flex cursor-pointer items-center gap-3 rounded-lg border-2 border-gray-200 bg-gray-50 p-3 transition hover:border-red-300 hover:bg-white">
                    <input type="checkbox" name="roles[]" value="admin" {{ $user?->hasRole('admin') ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <span><span class="block font-semibold text-gray-800">Admin</span><span
                            class="text-xs text-gray-500">Akses manajemen dan administrasi</span></span>
                </label>
                <label
                    class="flex cursor-pointer items-center gap-3 rounded-lg border-2 border-gray-200 bg-gray-50 p-3 transition hover:border-red-300 hover:bg-white">
                    <input type="checkbox" name="roles[]" value="petugas"
                        {{ $user?->hasRole('petugas') ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <span><span class="block font-semibold text-gray-800">Petugas</span><span
                            class="text-xs text-gray-500">Akses tugas dan pekerjaan lapangan</span></span>
                </label>
            </div>
        </fieldset>
        <div>
            <p class="mb-1 text-sm font-semibold text-gray-700">Wilayah kerja / Penempatan</p>
            <p class="rounded-lg border-2 border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-600">
                Sumbersari
            </p>
        </div>
        <div class="sm:col-span-2"><label for="alamat" class="mb-1 block text-sm font-semibold text-gray-700">Alamat
                lengkap</label>
            <textarea id="alamat" name="alamat" rows="2"
                class="w-full rounded-lg border-2 border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 shadow-sm outline-none transition focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-100">{{ old('alamat', $user?->alamat) }}</textarea>
        </div>
        <div><label for="foto" class="mb-1 block text-sm font-semibold text-gray-700">Foto profil <span
                    class="font-normal text-gray-400">(opsional)</span></label><input id="foto" type="file"
                name="foto" accept="image/*"
                class="w-full rounded-lg border-2 border-gray-300 bg-gray-50 p-2 text-sm text-gray-600 focus:border-red-400 focus:outline-none">
        </div>
        @if ($user)
            <div><label for="is_active" class="mb-1 block text-sm font-semibold text-gray-700">Status
                    akun</label><select id="is_active" name="is_active"
                    class="w-full rounded-lg border-gray-200 focus:border-red-400 focus:ring-red-400">
                    <option value="1" @selected(old('is_active', $user->is_active))>Aktif</option>
                    <option value="0" @selected(!old('is_active', $user->is_active))>Nonaktif</option>
                </select></div>
        @endif
    </div>
    <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
        @if ($isModal ?? false)
            <button type="button" @click="showCreateModal = false"
            class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>@else<a
                href="{{ route('users.index') }}"
                class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</a>
        @endif
        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
    </div>
</form>
