<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use Database\Factories\VacancyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Vacancy extends Model
{
    /** @use HasFactory<VacancyFactory> */
    use HasFactory;

    protected $fillable = [
        'job_template_id',
        'judul_posisi',
        'unit_id',
        'workflow_template_snapshot_id',
        'jenis_pekerjaan',
        'deskripsi_pekerjaan',
        'kualifikasi',
        'flyer_path',
        'jumlah_posisi',
        'tenggat_lamaran',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'jenis_pekerjaan' => EmploymentType::class,
            'status' => VacancyStatus::class,
            'tenggat_lamaran' => 'date',
            'jumlah_posisi' => 'integer',
        ];
    }

    public function flyerUrl(): ?string
    {
        return $this->flyer_path
            ? Storage::disk('public')->url($this->flyer_path)
            : null;
    }

    public function jobTemplate(): BelongsTo
    {
        return $this->belongsTo(JobTemplate::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function workflowTemplateSnapshot(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplateSnapshot::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function callbackInvites(): HasMany
    {
        return $this->hasMany(CallbackInvite::class);
    }

    public function isOpenForApplications(): bool
    {
        return $this->status === VacancyStatus::Published
            && $this->tenggat_lamaran !== null
            && ! $this->tenggat_lamaran->isBefore(now()->startOfDay());
    }

    /**
     * Vacancies currently accepting applications: Published with a deadline
     * on or after today. Query-side counterpart to {@see isOpenForApplications()}.
     */
    public function scopeOpenForApplications(Builder $query): void
    {
        $query->where('status', VacancyStatus::Published)
            ->whereNotNull('tenggat_lamaran')
            ->whereDate('tenggat_lamaran', '>=', now()->startOfDay());
    }

    public function vacancyTest(): HasOne
    {
        return $this->hasOne(VacancyTest::class);
    }

    public function interviewTemplates(): BelongsToMany
    {
        return $this->belongsToMany(InterviewTemplate::class, 'vacancy_interview_templates')
            ->withPivot('stage_key')
            ->withTimestamps();
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', VacancyStatus::Published)
            ->where('tenggat_lamaran', '>=', now()->toDateString());
    }
}
