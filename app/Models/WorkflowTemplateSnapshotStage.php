<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTemplateSnapshotStage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'workflow_template_snapshot_id',
        'position',
        'key',
        'nama',
        'is_locked_first',
        'is_locked_last',
        'question_bank_id',
        'batas_waktu_menit',
    ];

    protected function casts(): array
    {
        return [
            'is_locked_first' => 'boolean',
            'is_locked_last' => 'boolean',
            'batas_waktu_menit' => 'integer',
        ];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplateSnapshot::class, 'workflow_template_snapshot_id');
    }
}
