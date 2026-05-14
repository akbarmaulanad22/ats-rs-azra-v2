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

    protected $fillable = ['nama'];

    public function stages(): BelongsToMany
    {
        return $this->belongsToMany(Stage::class, 'stage_workflow_template')
            ->withPivot('position')
            ->orderByPivot('position');
    }
}
