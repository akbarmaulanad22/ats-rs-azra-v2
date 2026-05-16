<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $validCriteria = $this->resolveValidCriteria();

        return [
            'keputusan' => ['required', 'in:lulus,gagal,reserved'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'ratings' => ['required', 'array', 'size:'.count($validCriteria)],
            'ratings.*.nama_kriteria' => ['required', 'string', Rule::in($validCriteria)],
            'ratings.*.nilai' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolveValidCriteria(): array
    {
        $vacancy = $this->route('lowongan');
        $stageKey = match ($this->user()->role) {
            Role::UnitHead => 'wawancara_kepala_unit',
            Role::HrManager => 'wawancara_manajer_hr',
            Role::Director => 'wawancara_direktur',
            default => 'wawancara_kepala_unit',
        };

        return $vacancy->interviewCriteria()
            ->where('stage_key', $stageKey)
            ->pluck('nama')
            ->toArray();
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
