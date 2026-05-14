<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkflowStage extends Model
{
    protected $fillable = [
        'key',
        'label',
        'is_locked_first',
        'is_locked_last',
        'default_order',
    ];

    protected function casts(): array
    {
        return [
            'is_locked_first' => 'boolean',
            'is_locked_last' => 'boolean',
            'default_order' => 'integer',
        ];
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(WorkflowTemplate::class, 'workflow_template_stages')
            ->withPivot('position')
            ->withTimestamps();
    }
}
