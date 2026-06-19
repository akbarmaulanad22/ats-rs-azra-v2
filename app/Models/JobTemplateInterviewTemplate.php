<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobTemplateInterviewTemplate extends Model
{
    protected $fillable = [
        'job_template_id',
        'interview_template_id',
        'stage_key',
    ];

    public function jobTemplate(): BelongsTo
    {
        return $this->belongsTo(JobTemplate::class);
    }

    public function interviewTemplate(): BelongsTo
    {
        return $this->belongsTo(InterviewTemplate::class);
    }
}
