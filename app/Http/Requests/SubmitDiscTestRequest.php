<?php

namespace App\Http\Requests;

use App\Models\DiscQuestion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitDiscTestRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $questionCount = DiscQuestion::count();

        return [
            'most' => ['required', 'array', "min:{$questionCount}"],
            'most.*' => ['required', 'integer'],
            'least' => ['required', 'array', "min:{$questionCount}"],
            'least.*' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'most.required' => 'Semua pertanyaan harus dijawab.',
            'most.min' => 'Semua pertanyaan harus dijawab.',
            'least.required' => 'Semua pertanyaan harus dijawab.',
            'least.min' => 'Semua pertanyaan harus dijawab.',
        ];
    }
}
