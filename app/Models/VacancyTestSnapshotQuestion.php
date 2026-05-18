<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacancyTestSnapshotQuestion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vacancy_test_snapshot_id',
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

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(VacancyTestSnapshot::class, 'vacancy_test_snapshot_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(VacancyTestSnapshotOption::class);
    }
}
