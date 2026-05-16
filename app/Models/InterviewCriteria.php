<?php

namespace App\Models;

use Database\Factories\InterviewCriteriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewCriteria extends Model
{
    /** @use HasFactory<InterviewCriteriaFactory> */
    use HasFactory;

    protected $table = 'interview_criteria';

    protected $fillable = [
        'stage_key',
        'nama',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }
}
