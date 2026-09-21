<?php

namespace App\Http\Controllers;

class EducationManagementController extends Controller
{
    public function index()
    {
        abort_unless(session('role') === 'admin', 403);

        $articles = [
            ['title' => 'Cara Menjaga Kualitas Air Bersih di Rumah', 'category' => 'Air Bersih', 'status' => 'Terbit', 'updated' => '10 Sep 2026'],
            ['title' => 'Pentingnya Sanitasi Dasar untuk Kesehatan Keluarga', 'category' => 'Sanitasi', 'status' => 'Terbit', 'updated' => '8 Sep 2026'],
            ['title' => 'Pengendalian Jentik Nyamuk: Gerakan 3M Plus', 'category' => 'DBD & Nyamuk', 'status' => 'Terbit', 'updated' => '5 Sep 2026'],
            ['title' => 'Pengelolaan Sampah Rumah Tangga yang Benar', 'category' => 'Persampahan', 'status' => 'Draft', 'updated' => '2 Sep 2026'],
        ];

        return view('education.manage', compact('articles'));
    }
}
