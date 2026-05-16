<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAnswer extends Model
{
    protected $fillable = [
        'test_submission_id',
        'question_id',
        'question_option_id',
        'jawaban_teks',
        'skor',
        'is_reviewed',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'integer',
            'is_reviewed' => 'boolean',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(TestSubmission::class, 'test_submission_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}
