<?php

namespace App\Models;

use Database\Factories\QuestionBankFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuestionBank extends Model
{
    /** @use HasFactory<QuestionBankFactory> */
    use HasFactory;

    protected $fillable = ['nama'];

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_bank_questions')
            ->withPivot('urutan')
            ->orderByPivot('urutan');
    }
}
