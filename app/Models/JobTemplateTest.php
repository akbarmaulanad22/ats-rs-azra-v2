<?php

namespace App\Models;

use Database\Factories\JobTemplateTestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobTemplateTest extends Model
{
    /** @use HasFactory<JobTemplateTestFactory> */
    use HasFactory;

    protected $fillable = [
        'job_template_id',
        'batas_waktu_menit',
    ];

    protected function casts(): array
    {
        return [
            'batas_waktu_menit' => 'integer',
        ];
    }

    public function jobTemplate(): BelongsTo
    {
        return $this->belongsTo(JobTemplate::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'job_template_test_questions')
            ->withPivot('urutan')
            ->orderByPivot('urutan');
    }
}
