<?php

namespace App\Models;

use Database\Factories\WorkflowTemplateSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class WorkflowTemplateSnapshot extends Model
{
    /** @use HasFactory<WorkflowTemplateSnapshotFactory> */
    use HasFactory;

    protected $fillable = ['nama', 'workflow_template_id'];

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(WorkflowTemplateSnapshotStage::class)
            ->orderBy('position');
    }

    public static function createFromTemplate(WorkflowTemplate $template): self
    {
        return DB::transaction(function () use ($template) {
            $snapshot = self::create([
                'nama' => $template->nama,
                'workflow_template_id' => $template->id,
            ]);

            $template->stages->each(function (Stage $stage) use ($snapshot) {
                $snapshotStage = $snapshot->stages()->create([
                    'position' => $stage->pivot->position,
                    'key' => $stage->key,
                    'nama' => $stage->nama,
                    'is_locked_first' => $stage->is_locked_first,
                    'is_locked_last' => $stage->is_locked_last,
                    'question_bank_id' => $stage->pivot->question_bank_id,
                    'batas_waktu_menit' => $stage->pivot->batas_waktu_menit,
                ]);

                if ($stage->pivot->question_bank_id) {
                    $questionBank = QuestionBank::with('questions.options')
                        ->find($stage->pivot->question_bank_id);

                    $questionBank?->questions->each(function (Question $question) use ($snapshotStage) {
                        $snapshotQuestion = $snapshotStage->questions()->create([
                            'urutan' => $question->pivot->urutan,
                            'tipe' => $question->tipe->value,
                            'pertanyaan' => $question->pertanyaan,
                            'nilai_poin' => $question->nilai_poin,
                        ]);

                        $question->options->each(function (QuestionOption $option) use ($snapshotQuestion) {
                            $snapshotQuestion->options()->create([
                                'teks_opsi' => $option->teks_opsi,
                                'is_correct' => $option->is_correct,
                            ]);
                        });
                    });
                }
            });

            return $snapshot;
        });
    }
}
