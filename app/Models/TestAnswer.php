<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAnswer extends Model
{
    protected $fillable = [
        'test_submission_id',
        'vacancy_test_snapshot_question_id',
        'vacancy_test_snapshot_option_id',
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
        return $this->belongsTo(VacancyTestSnapshotQuestion::class, 'vacancy_test_snapshot_question_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(VacancyTestSnapshotOption::class, 'vacancy_test_snapshot_option_id');
    }
}
