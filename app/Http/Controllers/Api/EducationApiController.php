<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Education;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class EducationApiController extends Controller
{
    public function index(): JsonResponse
    {
        $articles = Education::query()
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'slug', 'category', 'excerpt', 'content', 'image', 'created_at'])
            ->map(fn (Education $article): array => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'category' => $article->category,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'image' => $article->image ? asset('storage/' . $article->image) : null,
                'created_at' => $article->created_at?->toISOString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }
}
