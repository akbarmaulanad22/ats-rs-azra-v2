<?php

namespace App\Models;

use Database\Factories\VacancyTestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VacancyTest extends Model
{
    /** @use HasFactory<VacancyTestFactory> */
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'batas_waktu_menit',
    ];

    protected function casts(): array
    {
        return [
            'batas_waktu_menit' => 'integer',
        ];
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'vacancy_test_questions')
            ->withPivot('urutan')
            ->orderByPivot('urutan');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(VacancyTestSnapshot::class);
    }

    public function latestSnapshot(): HasOne
    {
        return $this->hasOne(VacancyTestSnapshot::class)->latestOfMany();
    }

    public function totalNilaiMaksimal(): int
    {
        return $this->questions->sum('nilai_poin');
    }
}
