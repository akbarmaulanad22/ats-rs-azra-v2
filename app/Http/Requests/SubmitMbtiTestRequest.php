<?php

namespace App\Http\Requests;

use App\Models\MbtiQuestion;
use Illuminate\Foundation\Http\FormRequest;

class SubmitMbtiTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $totalQuestions = MbtiQuestion::count();

        return [
            'jawaban' => ['required', 'array', 'size:'.$totalQuestions],
            'jawaban.*' => ['required', 'in:A,B'],
        ];
    }
}
