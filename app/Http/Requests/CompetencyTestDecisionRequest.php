<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompetencyTestDecisionRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'keputusan' => ['required', 'in:lulus,gagal,reserved'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'keputusan.required' => 'Pilih keputusan tes kompetensi.',
            'keputusan.in' => 'Keputusan tidak valid.',
        ];
    }
}
