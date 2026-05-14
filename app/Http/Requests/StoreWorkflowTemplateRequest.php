<?php

namespace App\Http\Requests;

use App\Models\WorkflowStage;
use App\Models\WorkflowTemplate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreWorkflowTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', WorkflowTemplate::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:workflow_templates,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'stage_ids' => ['required', 'array', 'min:2'],
            'stage_ids.*' => ['required', 'integer', 'exists:workflow_stages,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $this->validateStageConstraints($validator);
        });
    }

    private function validateStageConstraints(Validator $validator): void
    {
        $stageIds = $this->input('stage_ids', []);
        if (empty($stageIds)) {
            return;
        }

        $stages = WorkflowStage::whereIn('id', $stageIds)->get()->keyBy('id');
        $orderedStages = collect($stageIds)->map(fn ($id) => $stages->get($id))->filter();

        $first = $orderedStages->first();
        $last = $orderedStages->last();

        if (! $first || ! $first->is_locked_first) {
            $validator->errors()->add('stage_ids', 'Tahap "Lamaran" harus selalu menjadi tahap pertama.');
        }

        if (! $last || ! $last->is_locked_last) {
            $validator->errors()->add('stage_ids', 'Tahap "Onboarding" harus selalu menjadi tahap terakhir.');
        }

        $lockedFirst = WorkflowStage::where('is_locked_first', true)->pluck('id');
        $lockedLast = WorkflowStage::where('is_locked_last', true)->pluck('id');

        foreach ($stageIds as $id) {
            if ($lockedFirst->contains($id) && $id != ($stageIds[0] ?? null)) {
                $validator->errors()->add('stage_ids', 'Tahap "Lamaran" hanya boleh berada di posisi pertama.');
                break;
            }
        }

        foreach ($stageIds as $id) {
            if ($lockedLast->contains($id) && $id != ($stageIds[count($stageIds) - 1] ?? null)) {
                $validator->errors()->add('stage_ids', 'Tahap "Onboarding" hanya boleh berada di posisi terakhir.');
                break;
            }
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama template wajib diisi.',
            'name.unique' => 'Nama template sudah digunakan.',
            'stage_ids.required' => 'Pilih minimal dua tahap rekrutmen.',
            'stage_ids.min' => 'Pilih minimal dua tahap rekrutmen.',
            'stage_ids.*.exists' => 'Tahap yang dipilih tidak valid.',
        ];
    }
}
