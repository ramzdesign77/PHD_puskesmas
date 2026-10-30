<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdminAccess();

        $query = User::query()->where(function ($builder) {
            $builder->where('role', 'like', '%admin%')
                ->orWhere('role', 'like', '%petugas%')
                ->orWhere('role', 'like', '%sanitarian%');
        });
        $search = trim((string) $request->query('q'));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('id_petugas', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $role = $request->query('role');
            $matchingRoles = in_array($role, ['petugas', 'sanitarian'], true)
                ? ['petugas', 'sanitarian'] : [$role];

            $query->where(function (Builder $builder) use ($matchingRoles): void {
                $builder->whereIn('role', $matchingRoles);

                foreach ($matchingRoles as $matchingRole) {
                    $builder->orWhere('role', 'like', "{$matchingRole},%")
                        ->orWhere('role', 'like', "%,{$matchingRole}")
                        ->orWhere('role', 'like', "%,{$matchingRole},%");
                }
            });
        }

        if ($request->query('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true)
            ? (int) $request->query('per_page') : 10;
        $users = $query->orderBy('nama_lengkap')->paginate($perPage)->withQueryString();
        $roles = collect(['sanitarian', 'petugas', 'admin']);

        return view('users.index', compact('users', 'roles'));
    }

    public function indexWarga(Request $request): View
    {
        $this->ensureAdminAccess();

        $query = User::query()
            ->select([
                'id_user',
                'nama_lengkap',
                'username',
                'email',
                'no_telepon',
                'id_desa',
                'is_active',
                'created_at',
            ])
            ->with('desa:id_desa,nama_desa')
            ->where(function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('role', 'warga')
                        ->where(function (Builder $query): void {
                            $query->whereNull('roles')
                                ->orWhereJsonLength('roles', 0)
                                ->orWhere(function (Builder $query): void {
                                    $query->whereJsonContains('roles', 'warga')
                                        ->whereJsonLength('roles', 1);
                                });
                        });
                })->orWhere(function (Builder $query): void {
                    $query->whereJsonContains('roles', 'warga')
                        ->whereJsonLength('roles', 1);
                });
            });
        $search = trim((string) $request->query('q'));

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_telepon', 'like', "%{$search}%")
                    ->orWhereHas('desa', function (Builder $desaQuery) use ($search): void {
                        $desaQuery->where('nama_desa', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->query('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $wargas = $query->orderBy('nama_lengkap')->paginate(15)->withQueryString();

        return view('warga.index', compact('wargas'));
    }

    public function showWarga(User $user): View
    {
        $this->ensureAdminAccess();
        $this->ensureWargaAccount($user);
        $user->load('desa:id_desa,nama_desa');

        return view('warga.show', compact('user'));
    }

    public function editWarga(User $user): View
    {
        $this->ensureAdminAccess();
        $this->ensureWargaAccount($user);
        $desas = Desa::query()->orderBy('nama_desa')->get(['id_desa', 'nama_desa']);

        return view('warga.edit', compact('user', 'desas'));
    }

    public function updateWarga(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminAccess();
        $this->ensureWargaAccount($user);

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('app_users', 'username')->ignore($user->id_user, 'id_user')],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('app_users', 'email')->ignore($user->id_user, 'id_user')],
            'no_telepon' => ['nullable', 'string', 'max:20', Rule::unique('app_users', 'no_telepon')->ignore($user->id_user, 'id_user')],
            'alamat' => ['nullable', 'string', 'max:500'],
            'id_desa' => ['nullable', 'integer', Rule::exists('ref_desa', 'id_desa')],
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update($data);

        return redirect()->route('warga.index')->with('success', 'Data warga berhasil diperbarui.');
    }

    public function resetWargaPassword(User $user): RedirectResponse
    {
        $this->ensureAdminAccess();
        $this->ensureWargaAccount($user);

        $user->mobileTokens()->delete();
        $user->update(['password' => 'password123']);

        return redirect()->route('warga.index')->with('success', "Password {$user->nama_lengkap} berhasil direset ke password123.");
    }

    public function destroyWarga(User $user): RedirectResponse
    {
        $this->ensureAdminAccess();
        $this->ensureWargaAccount($user);

        $user->forceDelete();

        return redirect()->route('warga.index')->with('success', 'Akun warga berhasil dihapus secara permanen.');
    }

    public function create()
    {
        $this->ensureAdminAccess();

        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess();

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150', 'regex:/\p{L}/u'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:app_users,username'],
            'email' => ['nullable', 'email', 'max:150', 'unique:app_users,email'],
            'password' => ['required', 'string', 'min:6'],
            'no_telepon' => ['required', 'string', 'max:20', 'unique:app_users,no_telepon'],
            'roles' => ['required', 'array', 'min:1', 'distinct'],
            'roles.*' => ['string', Rule::in(['sanitarian', 'petugas', 'admin'])],
            'alamat' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $roles = collect($data['roles'])->unique();
        $data['role'] = $roles->implode(',');
        unset($data['roles']);
        $data['wilayah_kerja'] = 'Sumbersari';
        $idPrefix = ($roles->contains('admin') ? 'A' : '')
            .($roles->intersect(['petugas', 'sanitarian'])->isNotEmpty() ? 'P' : '');
        $data['id_petugas'] = $this->generatePetugasId($data['nama_lengkap'], $idPrefix);
        $data['is_active'] = true;
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('petugas', 'public');
        }
        User::create($data);

        $successMessage = $data['role'] === 'admin'
            ? 'Akun admin berhasil dibuat.'
            : 'Akun petugas sanitasi berhasil dibuat.';

        return redirect()->route('users.index')->with('success', $successMessage);
    }

    private function generatePetugasId(string $fullName, string $prefix): string
    {
        $nameParts = preg_split('/[^\p{L}]+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);

        if ($nameParts === false || $nameParts === []) {
            throw new RuntimeException('Nama petugas tidak dapat digunakan untuk membentuk ID.');
        }

        $initials = collect(array_slice($nameParts, 0, 2))
            ->map(fn (string $namePart): string => Str::upper(Str::substr($namePart, 0, 1)))
            ->implode('');
        if (Str::length($initials) === 1) {
            $initials .= Str::upper(Str::substr($nameParts[0], 1, 1)) ?: $initials;
        }
        $usedNumbers = [];

        foreach (User::withTrashed()->whereNotNull('id_petugas')->pluck('id_petugas') as $existingId) {
            if (preg_match('/\A(?:AP|A|P)(\d{4})/u', $existingId, $matches) === 1) {
                $usedNumbers[$matches[1]] = true;
            }
        }

        for ($series = 1; $series <= 9; $series++) {
            for ($serial = 1; $serial <= 9; $serial++) {
                $number = (string) (($series * 1000) + 110 + $serial);

                if (! isset($usedNumbers[$number])) {
                    return $prefix.$number.$initials;
                }
            }
        }

        throw new RuntimeException('Semua nomor seri ID akun sudah digunakan.');
    }

    public function edit(User $user)
    {
        $this->ensureAdminAccess();

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminAccess();

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('app_users', 'username')->ignore($user->id_user, 'id_user')],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('app_users', 'email')->ignore($user->id_user, 'id_user')],
            'password' => ['nullable', 'string', 'min:6'],
            'no_telepon' => ['required', 'string', 'max:20', Rule::unique('app_users', 'no_telepon')->ignore($user->id_user, 'id_user')],
            'roles' => ['required', 'array', 'min:1', 'distinct'],
            'roles.*' => ['string', Rule::in(['sanitarian', 'petugas', 'admin'])],
            'alamat' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ]);

        $data['role'] = collect($data['roles'])->unique()->implode(',');
        unset($data['roles']);
        $data['wilayah_kerja'] = 'Sumbersari';

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
        $this->ensureAdminAccess();

        $user->forceDelete();

        return redirect()->route('users.index')->with('success', 'Akun berhasil dihapus secara permanen.');
    }

    public function show(User $user)
    {
        $this->ensureAdminAccess();

        return view('users.show', compact('user'));
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->ensureAdminAccess();

        $user->update(['password' => 'password123']);

        return redirect()->route('users.index')->with('success', "Password {$user->nama_lengkap} berhasil direset ke password123.");
    }

    public function export(Request $request)
    {
        $this->ensureAdminAccess();

        $users = User::query()->where(function ($builder) {
            $builder->where('role', 'like', '%admin%')
                ->orWhere('role', 'like', '%petugas%')
                ->orWhere('role', 'like', '%sanitarian%');
        })->orderBy('nama_lengkap')->get();
        $filename = 'daftar-petugas-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama Lengkap', 'ID Petugas', 'Username', 'Email', 'No. HP', 'Peran', 'Wilayah Kerja', 'Status']);
            foreach ($users as $user) {
                $roles = implode(' + ', array_map(fn ($role) => ucfirst($role), $user->roles()));
                fputcsv($handle, [$user->nama_lengkap, $user->id_petugas, $user->username, $user->email, $user->no_telepon, $roles, $user->wilayah_kerja, $user->is_active ? 'Aktif' : 'Nonaktif']);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function ensureAdminAccess(): void
    {
        abort_unless(request()->user()?->isAdmin(), 403);
    }

    private function ensureWargaAccount(User $user): void
    {
        abort_unless($user->roles() === ['warga'], 404);
    }
}
