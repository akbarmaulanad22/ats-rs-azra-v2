<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacancyTestSnapshotOption extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vacancy_test_snapshot_question_id',
        'teks_opsi',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(VacancyTestSnapshotQuestion::class, 'vacancy_test_snapshot_question_id');
    }
}
