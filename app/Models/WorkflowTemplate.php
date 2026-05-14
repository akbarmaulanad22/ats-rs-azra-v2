<?php

namespace App\Models;

use Database\Factories\WorkflowTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkflowTemplate extends Model
{
    /** @use HasFactory<WorkflowTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function stages(): BelongsToMany
    {
        return $this->belongsToMany(WorkflowStage::class, 'workflow_template_stages')
            ->withPivot('position')
            ->orderByPivot('position')
            ->withTimestamps();
    }
}
