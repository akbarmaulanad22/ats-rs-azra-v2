<?php

namespace App\Models;

use App\Enums\QuestionType;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    protected $fillable = [
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

    public function questionBanks(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBank::class, 'question_bank_questions')
            ->withPivot('urutan');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function correctOption(): ?QuestionOption
    {
        return $this->options->firstWhere('is_correct', true);
    }
}
