<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\JobTemplateStatus;
use Database\Factories\JobTemplateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobTemplate extends Model
{
    /** @use HasFactory<JobTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'judul_posisi',
        'unit_id',
        'workflow_template_id',
        'jenis_pekerjaan',
        'deskripsi_pekerjaan',
        'kualifikasi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'jenis_pekerjaan' => EmploymentType::class,
            'status' => JobTemplateStatus::class,
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class);
    }

    public function jobTemplateTest(): HasOne
    {
        return $this->hasOne(JobTemplateTest::class);
    }

    public function interviewTemplates(): BelongsToMany
    {
        return $this->belongsToMany(InterviewTemplate::class, 'job_template_interview_templates')
            ->withPivot('stage_key')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', JobTemplateStatus::Active);
    }

    /**
     * Whether the workflow carries a competency-test stage that has no test
     * configured yet — publishing as Published is blocked until it is set up.
     */
    public function hasUnconfiguredTestStage(): bool
    {
        return $this->workflowTemplate->stages->contains('key', 'tes_kompetensi')
            && ! $this->jobTemplateTest;
    }
}
