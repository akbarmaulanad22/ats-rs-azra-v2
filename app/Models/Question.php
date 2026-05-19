<?php

namespace App\Models;

use App\Enums\QuestionType;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'question_bank_template_id',
        'tipe',
        'pertanyaan',
        'nilai_poin',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => QuestionType::class,
            'nilai_poin' => 'integer',
            'urutan' => 'integer',
        ];
    }

    public function questionBankTemplate(): BelongsTo
    {
        return $this->belongsTo(QuestionBankTemplate::class);
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
