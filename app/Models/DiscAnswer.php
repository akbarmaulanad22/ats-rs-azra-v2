<?php

namespace App\Models;

use Database\Factories\DiscAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscAnswer extends Model
{
    /** @use HasFactory<DiscAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'disc_submission_id',
        'disc_question_id',
        'most_disc_word_id',
        'least_disc_word_id',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(DiscSubmission::class, 'disc_submission_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(DiscQuestion::class, 'disc_question_id');
    }

    public function mostWord(): BelongsTo
    {
        return $this->belongsTo(DiscQuestionWord::class, 'most_disc_word_id');
    }

    public function leastWord(): BelongsTo
    {
        return $this->belongsTo(DiscQuestionWord::class, 'least_disc_word_id');
    }
}
