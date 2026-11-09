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
            'id_jadwal' => ['required', 'exists:trx_jadwal_inspeksi,id_jadwal', 'unique:trx_inspeksi_ikl,id_jadwal'],
            'jenis_sarana_air' => ['required', 'in:sumur_bor,sumur_terlindung,pdam,mata_air,lainnya'],
            'kategori_ikl' => ['required', 'in:sumur_gali,sumur_bor,sarang_nyamuk_dbd,jamban_limbah_stbm,tumpukan_sampah'],
            'jarak_sumber_pencemar' => ['required', 'integer', 'min:0', 'max:999'],

            // Checklist parameter fisik (p1–p5): boolean, wajib diisi
            'p1' => ['required', 'boolean'],
            'p2' => ['required', 'boolean'],
            'p3' => ['required', 'boolean'],
            'p4' => ['required', 'boolean'],
            'p5' => ['required', 'boolean'],
            'p6_indikator_6' => ['required', 'boolean'],
            'p7_indikator_7' => ['required', 'boolean'],

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
            'p1' => $this->boolean('p1') || $this->boolean('p1_dinding_sumur_retak'),
            'p2' => $this->boolean('p2') || $this->boolean('p2_penutup_tidak_rapat'),
            'p3' => $this->boolean('p3') || $this->boolean('p3_lantai_becek_retak'),
            'p4' => $this->boolean('p4') || $this->boolean('p4_spal_tersumbat'),
            'p5' => $this->boolean('p5') || $this->boolean('p5_air_keruh_berbau'),
            'p6_indikator_6' => $this->boolean('p6_indikator_6'),
            'p7_indikator_7' => $this->boolean('p7_indikator_7'),
            'kategori_ikl' => $this->input('kategori_ikl', $this->input('jenis_sarana_air') === 'sumur_bor' ? 'sumur_bor' : 'sumur_gali'),
        ]);
    }
}
