<?php

namespace App\Models;

use Database\Factories\QuestionBankTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBankTemplate extends Model
{
    /** @use HasFactory<QuestionBankTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'nama',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('urutan');
    }
}
