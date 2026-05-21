<?php

namespace App\Http\Requests;

use App\Enums\ApplicationStageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInterviewScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<mixed>|string> */
    public function rules(): array
    {
        $rules = [
            'jadwal' => ['required', 'date', 'after:now'],
            'lokasi' => ['required', 'string', 'max:255'],
            'interviewer_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
        ];

        if ($this->isWawancaraUserStage()) {
            $rules['interviewer_id'] = ['required', 'integer', Rule::exists('users', 'id')->where('is_active', true)];
        }

        return $rules;
    }

    private function isWawancaraUserStage(): bool
    {
        $application = $this->route('application');
        $vacancy = $this->route('lowongan');

        if (! $application) {
            return false;
        }

        if ($vacancy && $application->vacancy_id !== $vacancy->id) {
            return false;
        }

        $application->loadMissing('stages');

        $stage = $application->stages
            ->whereIn('key', ['wawancara_user', 'wawancara_manajer_hr', 'wawancara_direktur'])
            ->where('status', ApplicationStageStatus::Aktif)
            ->first();

        return $stage?->key === 'wawancara_user';
    }
}
