<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isHrAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'stages' => ['required', 'array', 'min:2'],
            'stages.*' => ['required', 'integer', 'exists:stages,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama template wajib diisi.',
            'stages.required' => 'Template harus memiliki minimal 2 tahap.',
            'stages.min' => 'Template harus memiliki minimal 2 tahap.',
        ];
    }
}
