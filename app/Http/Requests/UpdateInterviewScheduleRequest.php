<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInterviewScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<mixed>|string> */
    public function rules(): array
    {
        return [
            'jadwal' => ['required', 'date', 'after:now'],
            'lokasi' => ['required', 'string', 'max:255'],
            'interviewer_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
        ];
    }
}
