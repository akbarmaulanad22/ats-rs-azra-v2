<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacancyTestQuestion extends Model
{
    protected $fillable = [
        'vacancy_test_id',
        'question_id',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function vacancyTest(): BelongsTo
    {
        return $this->belongsTo(VacancyTest::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
