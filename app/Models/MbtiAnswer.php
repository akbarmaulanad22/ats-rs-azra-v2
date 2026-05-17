<?php

namespace App\Models;

use Database\Factories\MbtiAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MbtiAnswer extends Model
{
    /** @use HasFactory<MbtiAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'mbti_submission_id',
        'mbti_question_id',
        'pilihan',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(MbtiSubmission::class, 'mbti_submission_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(MbtiQuestion::class, 'mbti_question_id');
    }
}
