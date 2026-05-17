<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendOfferingLetterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'jabatan_ditawarkan' => ['required', 'string', 'max:255'],
            'gaji' => ['required', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'jabatan_ditawarkan' => 'jabatan yang ditawarkan',
            'gaji' => 'gaji',
            'tanggal_mulai' => 'tanggal mulai kerja',
        ];
    }
}
