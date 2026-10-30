<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:16'],
            'nomor_telepon' => ['required', 'string', 'max:255'],
            'village_id' => ['required', 'integer', 'exists:ref_desa,id_desa'],
            'rt' => ['required', 'string', 'max:255'],
            'rw' => ['required', 'string', 'max:255'],
            'kategori_laporan' => ['required', 'in:Masalah air,Jamban/Sanitasi,Lainnya'],
            'deskripsi_masalah' => ['required', 'string', 'max:2000'],
            'foto_bukti' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
