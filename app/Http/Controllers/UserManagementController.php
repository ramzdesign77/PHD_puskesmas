<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(session('role') === 'admin', 403);

        $query = User::query()->where('role', 'sanitarian');
        $search = trim((string) $request->query('q'));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('id_petugas', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        if ($request->query('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true)
            ? (int) $request->query('per_page') : 10;
        $users = $query->orderBy('nama_lengkap')->paginate($perPage)->withQueryString();
        $roles = collect(['sanitarian']);

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        abort_unless(session('role') === 'admin', 403);

        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(session('role') === 'admin', 403);

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'id_petugas' => ['nullable', 'string', 'max:30', 'unique:users,id_petugas'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'no_telepon' => ['nullable', 'string', 'max:20', 'unique:users,no_telepon'],
            'role' => ['required', 'in:sanitarian'],
            'wilayah_kerja' => ['nullable', 'string', 'max:150'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['is_active'] = true;
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('petugas', 'public');
        }
        User::create($data);

        return redirect()->route('users.index')->with('success', 'Akun petugas sanitasi berhasil dibuat.');
    }

    public function edit(User $user)
    {
        abort_unless(session('role') === 'admin', 403);
        abort_unless($user->role === 'sanitarian', 404);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(session('role') === 'admin', 403);
        abort_unless($user->role === 'sanitarian', 404);

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'id_petugas' => ['nullable', 'string', 'max:30', Rule::unique('users', 'id_petugas')->ignore($user->id_user, 'id_user')],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username,' . $user->id_user . ',id_user'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id_user, 'id_user')],
            'password' => ['nullable', 'string', 'min:6'],
            'no_telepon' => ['nullable', 'string', 'max:20', Rule::unique('users', 'no_telepon')->ignore($user->id_user, 'id_user')],
            'role' => ['required', 'in:sanitarian'],
            'wilayah_kerja' => ['nullable', 'string', 'max:150'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (blank($data['password'])) {
            unset($data['password']);
        }

        if ($request->hasFile('foto')) {
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $data['foto'] = $request->file('foto')->store('petugas', 'public');
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data petugas sanitasi berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless(session('role') === 'admin', 403);
        abort_unless($user->role === 'sanitarian', 404);

        $user->update(['is_active' => false]);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Petugas berhasil dinonaktifkan dan dihapus dari daftar.');
    }

    public function show(User $user)
    {
        abort_unless(session('role') === 'admin', 403);
        abort_unless($user->role === 'sanitarian', 404);

        return view('users.show', compact('user'));
    }

    public function resetPassword(User $user): RedirectResponse
    {
        abort_unless(session('role') === 'admin', 403);
        abort_unless($user->role !== 'admin', 404);

        $user->update(['password' => 'password123']);

        return redirect()->route('users.index')->with('success', "Password {$user->nama_lengkap} berhasil direset ke password123.");
    }

    public function export(Request $request)
    {
        abort_unless(session('role') === 'admin', 403);

        $users = User::query()->where('role', 'sanitarian')->orderBy('nama_lengkap')->get();
        $filename = 'daftar-petugas-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama Lengkap', 'ID Petugas', 'Username', 'Email', 'No. HP', 'Peran', 'Wilayah Kerja', 'Status']);
            foreach ($users as $user) {
                fputcsv($handle, [$user->nama_lengkap, $user->id_petugas, $user->username, $user->email, $user->no_telepon, 'Petugas', $user->wilayah_kerja, $user->is_active ? 'Aktif' : 'Nonaktif']);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
