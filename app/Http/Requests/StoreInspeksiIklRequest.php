<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInspeksiIklRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'id_jadwal' => ['required', 'exists:jadwal_inspeksi,id_jadwal', 'unique:inspeksi_ikl,id_jadwal'],
            'jenis_sarana_air' => ['required', 'in:sumur_bor,sumur_terlindung,pdam,mata_air,lainnya'],
            'jarak_sumber_pencemar' => ['required', 'integer', 'min:0', 'max:999'],

            // Checklist parameter fisik (p1–p5): boolean, wajib diisi
            'p1_dinding_sumur_retak' => ['required', 'boolean'],
            'p2_penutup_tidak_rapat' => ['required', 'boolean'],
            'p3_lantai_becek_retak' => ['required', 'boolean'],
            'p4_spal_tersumbat' => ['required', 'boolean'],
            'p5_air_keruh_berbau' => ['required', 'boolean'],

            'rekomendasi_sanitarian' => ['required', 'string', 'min:20', 'max:2000'],
            'tanggal_inspeksi' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_jadwal.unique' => 'Hasil IKL untuk jadwal ini sudah pernah diinput.',
            'jarak_sumber_pencemar.min' => 'Jarak sumber pencemar tidak boleh negatif.',
            'rekomendasi_sanitarian.min' => 'Rekomendasi sanitarian minimal 20 karakter — harap isi dengan saran yang spesifik.',
            'tanggal_inspeksi.before_or_equal' => 'Tanggal inspeksi tidak boleh di masa depan.',
        ];
    }

    /**
     * Hitung total_skor_ya dan kategori_risiko secara otomatis
     * sebelum data disimpan.
     */
    public function prepareForValidation(): void
    {
        // Normalisasi checkbox: nilai string '1'/'0' dari form HTML → boolean
        $this->merge([
            'p1_dinding_sumur_retak' => $this->boolean('p1_dinding_sumur_retak'),
            'p2_penutup_tidak_rapat' => $this->boolean('p2_penutup_tidak_rapat'),
            'p3_lantai_becek_retak' => $this->boolean('p3_lantai_becek_retak'),
            'p4_spal_tersumbat' => $this->boolean('p4_spal_tersumbat'),
            'p5_air_keruh_berbau' => $this->boolean('p5_air_keruh_berbau'),
        ]);
    }
}
