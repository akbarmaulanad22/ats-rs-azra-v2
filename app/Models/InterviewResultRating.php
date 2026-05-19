<?php

namespace App\Models;

use Database\Factories\InterviewResultRatingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewResultRating extends Model
{
    /** @use HasFactory<InterviewResultRatingFactory> */
    use HasFactory;

    protected $fillable = [
        'interview_result_id',
        'nama_kriteria',
        'nilai',
        'interview_template_id',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'integer',
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
