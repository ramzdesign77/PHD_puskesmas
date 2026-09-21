<?php

namespace App\Http\Controllers;

use App\Models\Education;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EducationController extends Controller
{
    public function index(): View
    {
        $articles = Education::query()->latest()->paginate(9);
        $categories = Education::query()->select('category')->distinct()->orderBy('category')->pluck('category');

        return view('education.index', compact('articles', 'categories'));
    }

    public function show(string $slug): View
    {
        return view('education.show', ['article' => Education::where('slug', $slug)->firstOrFail()]);
    }

    public function manage(): View
    {
        $this->ensureManagementAccess();

        return view('education.manage', ['articles' => Education::latest()->paginate(10)]);
    }

    public function create(): View
    {
        $this->ensureManagementAccess();

        return view('education.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureManagementAccess();
        $validated = $this->validateArticle($request);
        $validated['excerpt'] = Str::limit(strip_tags($validated['content']), 240);

        // Simpan gambar ke storage/app/public/educations agar dapat diakses melalui Storage::url().
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('educations', 'public');
        }

        Education::create($validated);

        return redirect()->route('education.manage')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $this->ensureManagementAccess();

        return view('education.edit', ['article' => Education::findOrFail($id)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->ensureManagementAccess();
        $article = Education::findOrFail($id);
        $validated = $this->validateArticle($request);
        $validated['excerpt'] = Str::limit(strip_tags($validated['content']), 240);

        // File baru disimpan lebih dulu; gambar lama dihapus setelah data berhasil diperbarui.
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

    private function validateArticle(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    private function ensureManagementAccess(): void
    {
        abort_unless(in_array(session('role'), ['admin', 'officer'], true), 403);
    }
}
