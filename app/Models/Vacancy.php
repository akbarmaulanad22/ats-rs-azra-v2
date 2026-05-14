<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use Database\Factories\VacancyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacancy extends Model
{
    /** @use HasFactory<VacancyFactory> */
    use HasFactory;

    protected $fillable = [
        'judul_posisi',
        'unit_id',
        'workflow_template_id',
        'jenis_pekerjaan',
        'deskripsi_pekerjaan',
        'kualifikasi',
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

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', VacancyStatus::Published)
            ->where('tenggat_lamaran', '>=', now()->toDateString());
    }
}
