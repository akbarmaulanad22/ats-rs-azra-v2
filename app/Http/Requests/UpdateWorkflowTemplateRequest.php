<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class UpdateWorkflowTemplateRequest extends StoreWorkflowTemplateRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $templateId = $this->route('templateAlur')?->id;
        $rules['nama'] = ['required', 'string', 'max:255', "unique:workflow_templates,nama,{$templateId}"];

        return $rules;
    }
}
