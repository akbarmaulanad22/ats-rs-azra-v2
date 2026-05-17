<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadMcuDocumentRequest extends FormRequest
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
            'dokumen' => ['required', 'file', 'mimes:pdf', 'max:3072'],
        ];
    }

    public function attributes(): array
    {
        return [
            'dokumen' => 'dokumen MCU',
        ];
    }
}
