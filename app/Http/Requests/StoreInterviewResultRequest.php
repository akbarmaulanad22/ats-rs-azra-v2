<?php

namespace App\Http\Requests;

use App\Enums\InterviewTemplateType;
use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInterviewResultRequest extends FormRequest
{
    private ?Collection $assignedTemplates = null;

    private ?Collection $assignedReadinessTemplates = null;

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
        $readinessTemplates = $this->resolveAssignedReadinessTemplates();

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

        $rules = array_merge($base, [
            'ratings' => ['required', 'array', 'size:'.$expectedCount],
            'ratings.*.interview_template_id' => ['required', 'integer', Rule::in($validTemplateIds)],
            'ratings.*.nama_kriteria' => ['required', 'string', Rule::in($validCriteriaNames)],
            'ratings.*.nilai' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        if ($readinessTemplates->isNotEmpty()) {
            $validReadinessTemplateIds = $readinessTemplates->pluck('id')->toArray();
            $expectedReadinessCount = $readinessTemplates->sum(fn ($t) => $t->items->count());

            $rules = array_merge($rules, [
                'readiness_answers' => ['required', 'array', 'size:'.$expectedReadinessCount],
                'readiness_answers.*.interview_template_id' => ['required', 'integer', Rule::in($validReadinessTemplateIds)],
                'readiness_answers.*.pertanyaan' => ['required', 'string'],
                'readiness_answers.*.jawaban' => ['required', 'in:0,1,true,false'],
            ]);
        }

        return $rules;
    }

    /**
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->resolveAssignedTemplates()->isEmpty()) {
                    $validator->errors()->add('interview', 'Belum ada kriteria, hubungi HR Admin.');
                }
            },
        ];
    }

    private function resolveStageKey(): string
    {
        return match ($this->user()->role) {
            Role::UnitHead => 'wawancara_kepala_unit',
            Role::HrManager => 'wawancara_manajer_hr',
            Role::Director => 'wawancara_direktur',
            default => 'wawancara_kepala_unit',
        };
    }

    private function resolveAssignedTemplates(): Collection
    {
        if ($this->assignedTemplates !== null) {
            return $this->assignedTemplates;
        }

        $vacancy = $this->route('lowongan');

        return $this->assignedTemplates = $vacancy->interviewTemplates()
            ->wherePivot('stage_key', $this->resolveStageKey())
            ->where('tipe', InterviewTemplateType::KriteriaPenilaian)
            ->with('items')
            ->get();
    }

    private function resolveAssignedReadinessTemplates(): Collection
    {
        if ($this->assignedReadinessTemplates !== null) {
            return $this->assignedReadinessTemplates;
        }

        $vacancy = $this->route('lowongan');

        return $this->assignedReadinessTemplates = $vacancy->interviewTemplates()
            ->wherePivot('stage_key', $this->resolveStageKey())
            ->where('tipe', InterviewTemplateType::Kesiapan)
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
            'readiness_answers.required' => 'Jawaban kesiapan wajib diisi.',
            'readiness_answers.size' => 'Jumlah jawaban kesiapan tidak sesuai dengan pertanyaan yang ditentukan.',
            'readiness_answers.*.jawaban.required' => 'Setiap pertanyaan kesiapan wajib dijawab.',
        ];
    }
}
