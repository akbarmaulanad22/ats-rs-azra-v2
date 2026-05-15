<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('templateEmail'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'subjek' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
        ];
    }
}
