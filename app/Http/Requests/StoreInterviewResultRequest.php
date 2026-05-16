<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInterviewResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'keputusan' => ['required', 'in:lulus,gagal,reserved'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'ratings' => ['required', 'array', 'min:1'],
            'ratings.*.nama_kriteria' => ['required', 'string', 'max:255'],
            'ratings.*.nilai' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'keputusan.required' => 'Pilih keputusan wawancara.',
            'keputusan.in' => 'Keputusan tidak valid.',
            'ratings.required' => 'Nilai kriteria wawancara wajib diisi.',
            'ratings.*.nilai.required' => 'Setiap kriteria wajib diberi nilai.',
            'ratings.*.nilai.min' => 'Nilai minimal adalah 1.',
            'ratings.*.nilai.max' => 'Nilai maksimal adalah 5.',
        ];
    }
}
