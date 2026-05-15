<?php

namespace App\Models;

use App\Enums\ApplicationStageStatus;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'vacancy_id',
        'token',
        'cv_path',
        'alasan_melamar',
        'gaji_diharapkan',
        'fasilitas_diharapkan',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ApplicationStage::class)->orderBy('position');
    }

    public function currentStage(): ?ApplicationStage
    {
        return $this->stages
            ->whereIn('status', [ApplicationStageStatus::Aktif, ApplicationStageStatus::Pending])
            ->sortBy('position')
            ->first();
    }

    public function references(): HasMany
    {
        return $this->hasMany(ApplicationReference::class);
    }
}
