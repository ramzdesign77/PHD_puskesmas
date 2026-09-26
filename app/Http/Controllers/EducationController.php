<?php

namespace App\Http\Controllers;

class EducationController extends Controller
{
    public function index()
    {
        $articles = [
            [
                'id'       => 1,
                'title'    => 'Cara Menjaga Kualitas Air Bersih di Rumah',
                'category' => 'Air Bersih',
                'excerpt'  => 'Air bersih adalah kebutuhan dasar manusia. Pelajari cara menjaga kualitas sumber air di lingkungan Anda agar terhindar dari penyakit berbahaya.',
                'author'   => 'Tim Kesling Puskesmas',
                'date'     => '10 September 2026',
                'read_time'=> '4 menit',
                'color'    => 'blue',
                'icon'     => 'water',
            ],
            [
                'id'       => 2,
                'title'    => 'Pentingnya Sanitasi Dasar untuk Kesehatan Keluarga',
                'category' => 'Sanitasi',
                'excerpt'  => 'Sanitasi yang baik mencegah penyebaran penyakit menular. Temukan tips praktis untuk meningkatkan sanitasi di lingkungan sekitar Anda.',
                'author'   => 'dr. Kepala Puskesmas',
                'date'     => '8 September 2026',
                'read_time'=> '5 menit',
                'color'    => 'green',
                'icon'     => 'home',
            ],
            [
                'id'       => 3,
                'title'    => 'Pengendalian Jentik Nyamuk: Gerakan 3M Plus',
                'category' => 'DBD & Nyamuk',
                'excerpt'  => 'Demam berdarah masih menjadi ancaman. Ketahui cara melakukan gerakan 3M Plus untuk memberantas sarang nyamuk di sekitar rumah.',
                'author'   => 'Petugas Kesling',
                'date'     => '5 September 2026',
                'read_time'=> '3 menit',
                'color'    => 'red',
                'icon'     => 'bug',
            ],
            [
                'id'       => 4,
                'title'    => 'Pengelolaan Sampah Rumah Tangga yang Benar',
                'category' => 'Persampahan',
                'excerpt'  => 'Sampah yang tidak dikelola dengan baik menimbulkan masalah kesehatan serius. Pelajari cara memilah dan mengelola sampah dengan tepat.',
                'author'   => 'Tim Kesling Puskesmas',
                'date'     => '2 September 2026',
                'read_time'=> '6 menit',
                'color'    => 'yellow',
                'icon'     => 'trash',
            ],
            [
                'id'       => 5,
                'title'    => 'Bahaya Limbah Industri bagi Lingkungan Sekitar',
                'category' => 'Limbah',
                'excerpt'  => 'Pencemaran akibat limbah industri sering diabaikan namun berdampak jangka panjang. Kenali tanda-tanda pencemaran dan cara melaporkannya.',
                'author'   => 'dr. Kepala Puskesmas',
                'date'     => '30 Agustus 2026',
                'read_time'=> '7 menit',
                'color'    => 'purple',
                'icon'     => 'factory',
            ],
            [
                'id'       => 6,
                'title'    => 'PHBS: Perilaku Hidup Bersih dan Sehat di Tatanan Rumah Tangga',
                'category' => 'PHBS',
                'excerpt'  => 'PHBS adalah fondasi dari kesehatan masyarakat. Terapkan 10 indikator PHBS di rumah tangga untuk hidup yang lebih sehat dan berkualitas.',
                'author'   => 'Tim Kesling Puskesmas',
                'date'     => '25 Agustus 2026',
                'read_time'=> '5 menit',
                'color'    => 'teal',
                'icon'     => 'heart',
            ],
        ];

        $categories = ['Semua', 'Air Bersih', 'Sanitasi', 'DBD & Nyamuk', 'Persampahan', 'Limbah', 'PHBS'];

        return view('education.index', compact('articles', 'categories'));
    }
}
