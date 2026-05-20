<?php

namespace App\Http\Requests;

use App\Enums\McuStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMcuResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<mixed>|string> */
    public function rules(): array
    {
        return [
            'keputusan' => ['required', 'string', Rule::enum(McuStatus::class)],
            'dokumen' => ['nullable', 'file', 'mimes:pdf', 'max:3072'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
