<?php

namespace App\Models;

use Database\Factories\QuestionBankQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionBankQuestion extends Model
{
    /** @use HasFactory<QuestionBankQuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'question_bank_id',
        'question_id',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
