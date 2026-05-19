<?php

namespace App\Models;

use Database\Factories\InterviewReadinessAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewReadinessAnswer extends Model
{
    /** @use HasFactory<InterviewReadinessAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'interview_result_id',
        'pertanyaan',
        'jawaban',
        'interview_template_id',
    ];

    protected function casts(): array
    {
        return [
            'jawaban' => 'boolean',
            'interview_template_id' => 'integer',
        ];
    }

    public function interviewResult(): BelongsTo
    {
        return $this->belongsTo(InterviewResult::class);
    }

    public function interviewTemplate(): BelongsTo
    {
        return $this->belongsTo(InterviewTemplate::class);
    }
}
