<?php

namespace App\Models;

use Database\Factories\VacancyInterviewTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacancyInterviewTemplate extends Model
{
    /** @use HasFactory<VacancyInterviewTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'interview_template_id',
        'stage_key',
    ];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function interviewTemplate(): BelongsTo
    {
        return $this->belongsTo(InterviewTemplate::class);
    }
}
