<?php

namespace App\Http\Requests;

use App\Enums\InterviewTemplateType;
use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInterviewResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $templates = $this->resolveAssignedTemplates();
        $base = [
            'keputusan' => ['required', 'in:lulus,gagal,reserved'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];

        if ($templates->isEmpty()) {
            return $base;
        }

        $validTemplateIds = $templates->pluck('id')->toArray();
        $validCriteriaNames = $templates->flatMap(fn ($t) => $t->items->pluck('teks'))->toArray();
        $expectedCount = $templates->sum(fn ($t) => $t->items->count());

        return array_merge($base, [
            'ratings' => ['required', 'array', 'size:'.$expectedCount],
            'ratings.*.interview_template_id' => ['required', 'integer', Rule::in($validTemplateIds)],
            'ratings.*.nama_kriteria' => ['required', 'string', Rule::in($validCriteriaNames)],
            'ratings.*.nilai' => ['required', 'integer', 'min:1', 'max:5'],
        ]);
    }

    private function resolveAssignedTemplates(): Collection
    {
        $vacancy = $this->route('lowongan');
        $stageKey = match ($this->user()->role) {
            Role::UnitHead => 'wawancara_kepala_unit',
            Role::HrManager => 'wawancara_manajer_hr',
            Role::Director => 'wawancara_direktur',
            default => 'wawancara_kepala_unit',
        };

        return $vacancy->interviewTemplates()
            ->wherePivot('stage_key', $stageKey)
            ->where('tipe', InterviewTemplateType::KriteriaPenilaian)
            ->with('items')
            ->get();
    }

    public function messages(): array
    {
        return [
            'keputusan.required' => 'Pilih keputusan wawancara.',
            'keputusan.in' => 'Keputusan tidak valid.',
            'ratings.required' => 'Nilai kriteria wawancara wajib diisi.',
            'ratings.size' => 'Jumlah penilaian tidak sesuai dengan kriteria yang ditentukan.',
            'ratings.*.nilai.required' => 'Setiap kriteria wajib diberi nilai.',
            'ratings.*.nilai.min' => 'Nilai minimal adalah 1.',
            'ratings.*.nilai.max' => 'Nilai maksimal adalah 5.',
        ];
    }
}
