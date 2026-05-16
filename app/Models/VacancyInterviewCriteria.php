<?php

namespace App\Models;

use Database\Factories\VacancyInterviewCriteriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacancyInterviewCriteria extends Model
{
    /** @use HasFactory<VacancyInterviewCriteriaFactory> */
    use HasFactory;

    protected $table = 'vacancy_interview_criteria';

    protected $fillable = [
        'vacancy_id',
        'stage_key',
        'nama',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }
}
