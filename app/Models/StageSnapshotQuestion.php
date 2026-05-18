<?php

namespace App\Models;

use App\Enums\QuestionType;
use Database\Factories\StageSnapshotQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StageSnapshotQuestion extends Model
{
    /** @use HasFactory<StageSnapshotQuestionFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'workflow_template_snapshot_stage_id',
        'urutan',
        'tipe',
        'pertanyaan',
        'nilai_poin',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => QuestionType::class,
            'nilai_poin' => 'integer',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplateSnapshotStage::class, 'workflow_template_snapshot_stage_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(StageSnapshotOption::class);
    }

    public function correctOption(): ?StageSnapshotOption
    {
        return $this->options->firstWhere('is_correct', true);
    }
}
