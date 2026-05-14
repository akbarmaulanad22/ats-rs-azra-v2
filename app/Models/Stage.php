<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Stage extends Model
{
    protected $fillable = ['key', 'nama', 'is_locked_first', 'is_locked_last'];

    protected function casts(): array
    {
        return [
            'is_locked_first' => 'boolean',
            'is_locked_last' => 'boolean',
        ];
    }

    public function workflowTemplates(): BelongsToMany
    {
        return $this->belongsToMany(WorkflowTemplate::class, 'stage_workflow_template')
            ->withPivot('position');
    }
}
