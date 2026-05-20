<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMcuScheduleRequest extends FormRequest
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
        ];
    }
}
