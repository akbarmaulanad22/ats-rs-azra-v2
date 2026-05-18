<?php

namespace App\Models;

use Database\Factories\WorkflowTemplateSnapshotStageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTemplateSnapshotStage extends Model
{
    /** @use HasFactory<WorkflowTemplateSnapshotStageFactory> */
    use HasFactory;

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

    public function questions(): HasMany
    {
        return $this->hasMany(StageSnapshotQuestion::class)
            ->orderBy('urutan');
    }

    public function totalNilaiMaksimal(): int
    {
        return $this->questions->sum('nilai_poin');
    }
}
