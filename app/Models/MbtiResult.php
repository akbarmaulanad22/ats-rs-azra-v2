<?php

namespace App\Models;

use Database\Factories\MbtiResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MbtiResult extends Model
{
    /** @use HasFactory<MbtiResultFactory> */
    use HasFactory;

    protected $fillable = [
        'mbti_submission_id',
        'skor_e',
        'skor_i',
        'skor_s',
        'skor_n',
        'skor_t',
        'skor_f',
        'skor_j',
        'skor_p',
        'tipe',
        'kekuatan_ei',
        'kekuatan_sn',
        'kekuatan_tf',
        'kekuatan_jp',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(MbtiSubmission::class, 'mbti_submission_id');
    }
}
