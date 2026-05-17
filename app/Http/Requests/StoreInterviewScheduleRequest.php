<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterviewScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<mixed>|string> */
    public function rules(): array
    {
        return [
            'jadwal_interview' => ['required', 'date', 'after:now'],
            'lokasi_interview' => ['required', 'string', 'max:255'],
        ];
    }
}
