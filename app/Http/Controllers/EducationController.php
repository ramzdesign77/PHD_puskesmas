<?php

namespace App\Http\Controllers;

use App\Models\Education;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EducationController extends Controller
{
    // Menampilkan daftar artikel untuk publik
    public function index(): View
    {
        $articles = Education::query()->latest()->paginate(9);
        $categories = Education::query()->select('category')->distinct()->orderBy('category')->pluck('category');

        return view('education.index', compact('articles', 'categories'));
    }

    // Menampilkan detail artikel berdasarkan slug
    public function show(string $slug): View
    {
        return view('education.show', ['article' => Education::where('slug', $slug)->firstOrFail()]);
    }

    // Menampilkan halaman kelola artikel untuk admin/petugas
    public function manage(): View
    {
        $this->ensureManagementAccess();

        return view('education.manage', ['articles' => Education::latest()->paginate(10)]);
    }

    // Menampilkan form tambah artikel baru
    public function create(): View
    {
        $this->ensureManagementAccess();

        return view('education.create');
    }

    // Menyimpan artikel baru ke database
    public function store(Request $request): RedirectResponse
    {
        $this->ensureManagementAccess();

        $validated = $this->validateArticle($request, null);
        $validated['title'] = trim($validated['title']);
        $validated['category'] = trim($validated['category']);
        $validated['content'] = trim($validated['content']);
        $validated['excerpt'] = Str::limit(strip_tags($validated['content']), 240);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('educations', 'public');
        }

        Education::create($validated);

        return redirect()->route('education.manage')->with('success', 'Artikel berhasil ditambahkan.');
    }

    // Menampilkan form edit artikel
    public function edit(int $id): View
    {
        $this->ensureManagementAccess();

        return view('education.edit', ['article' => Education::findOrFail($id)]);
    }

    // Memperbarui data artikel di database
    public function update(Request $request, int $id): RedirectResponse
    {
        $this->ensureManagementAccess();

        $article = Education::findOrFail($id);
        $validated = $this->validateArticle($request, $id);

        $validated['title'] = trim($validated['title']);
        $validated['category'] = trim($validated['category']);
        $validated['content'] = trim($validated['content']);
        $validated['excerpt'] = Str::limit(strip_tags($validated['content']), 240);

        if ($request->hasFile('image')) {
            $newImage = $request->file('image')->store('educations', 'public');
            $validated['image'] = $newImage;
        }

        $oldImage = $article->image;
        $article->update($validated);

        if (isset($newImage) && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('education.manage')->with('success', 'Artikel berhasil diperbarui.');
    }

    // Menghapus artikel dari database
    public function destroy(int $id): RedirectResponse
    {
        $this->ensureManagementAccess();

        $article = Education::findOrFail($id);

        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }

        $article->delete();

        return redirect()->route('education.manage')->with('success', 'Artikel berhasil dihapus.');
    }

    // Validasi input data artikel
    private function validateArticle(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'title' => [
                'required',
                'string',
                'min:5',
                'max:255',
                'regex:/^[\pL\pN\s]+$/u', // Hanya mengizinkan huruf, angka, dan spasi
                Rule::unique('ref_edukasi', 'title')->ignore($id),
            ],

            // Pengaturan Upload Gambar

            'category' => ['required', 'string', 'max:100', 'in:Air & Sanitasi,Pengelolaan Sampah & Vektor,PHBS'],
            'content' => ['required', 'string', 'min:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5048'],
        ], [
            'title.required' => 'Judul artikel wajib diisi.',
            'title.min' => 'Judul artikel minimal 5 karakter.',
            'title.regex' => 'Judul artikel hanya boleh memasukkan huruf, angka, dan spasi.',
            'title.unique' => 'Judul artikel ini sudah ada.',
            'category.required' => 'Kategori artikel wajib dipilih.',
            'category.in' => 'Kategori yang dipilih tidak valid.',
            'content.required' => 'Isi artikel wajib diisi.',
            'content.min' => 'Konten artikel minimal 50 karakter.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);
    }

    // Membatasi akses khusus admin dan petugas
    private function ensureManagementAccess(): void
    {
        $user = request()->user();

        abort_unless(
            $user?->isAdmin() || $user?->isSanitarian() || session('role') === 'officer',
            403,
        );
    }
}
