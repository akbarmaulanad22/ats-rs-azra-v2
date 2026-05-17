<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitMbtiTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jawaban' => ['required', 'array', 'min:1'],
            'jawaban.*' => ['required', 'in:A,B'],
        ];
    }
}
