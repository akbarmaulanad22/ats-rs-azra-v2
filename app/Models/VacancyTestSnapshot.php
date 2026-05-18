<?php

namespace App\Models;

use Database\Factories\VacancyTestSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacancyTestSnapshot extends Model
{
    /** @use HasFactory<VacancyTestSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'vacancy_test_id',
        'batas_waktu_menit',
    ];

    protected function casts(): array
    {
        return [
            'batas_waktu_menit' => 'integer',
        ];
    }

    public function vacancyTest(): BelongsTo
    {
        return $this->belongsTo(VacancyTest::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(VacancyTestSnapshotQuestion::class)
            ->orderBy('urutan');
    }

    public function totalNilaiMaksimal(): int
    {
        return $this->questions->sum('nilai_poin');
    }

    public static function createFromVacancyTest(VacancyTest $vacancyTest): self
    {
        $vacancyTest->load('questions.options');

        $snapshot = self::create([
            'vacancy_test_id' => $vacancyTest->id,
            'batas_waktu_menit' => $vacancyTest->batas_waktu_menit,
        ]);

        $vacancyTest->questions->each(function (Question $question) use ($snapshot) {
            $snapshotQuestion = $snapshot->questions()->create([
                'urutan' => $question->pivot->urutan,
                'tipe' => $question->tipe->value,
                'pertanyaan' => $question->pertanyaan,
                'nilai_poin' => $question->nilai_poin,
            ]);

            $question->options->each(function (QuestionOption $option) use ($snapshotQuestion) {
                $snapshotQuestion->options()->create([
                    'teks_opsi' => $option->teks_opsi,
                    'is_correct' => $option->is_correct,
                ]);
            });
        });

        return $snapshot;
    }
}
