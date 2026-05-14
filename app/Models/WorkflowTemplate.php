<?php

namespace App\Models;

use Database\Factories\WorkflowTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTemplate extends Model
{
    /** @use HasFactory<WorkflowTemplateFactory> */
    use HasFactory;

    protected $fillable = ['nama'];

    public function stages(): BelongsToMany
    {
        return $this->belongsToMany(Stage::class, 'stage_workflow_template')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    /**
     * Placeholder for vacancy guard — resolves when Vacancy model is created in issue #6.
     *
     * @return HasMany
     */
    public function vacancies()
    {
        // Will be HasMany when Vacancy model exists. Returns empty builder now.
        return $this->hasMany(WorkflowTemplate::class, 'id', 'id')->whereNull('id');
    }
}
