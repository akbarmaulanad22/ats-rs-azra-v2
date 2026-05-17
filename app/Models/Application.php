<?php

namespace App\Models;

use App\Enums\ApplicationStageStatus;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'gaji_diharapkan' => 'integer',
        ];
    }

    protected $fillable = [
        'candidate_id',
        'vacancy_id',
        'token',
        'cv_path',
        'alasan_melamar',
        'gaji_diharapkan',
        'fasilitas_diharapkan',
        'kesiapan_kerja',
        'str_sip_path',
        'sumber_informasi',
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
        $gagal = $this->stages->firstWhere('status', ApplicationStageStatus::Gagal);
        if ($gagal) {
            return $gagal;
        }

        $aktif = $this->stages->firstWhere('status', ApplicationStageStatus::Aktif);
        if ($aktif) {
            return $aktif;
        }

        if ($this->stages->isNotEmpty() && $this->stages->every(fn ($s) => $s->status === ApplicationStageStatus::Selesai)) {
            return $this->stages->sortByDesc('position')->first();
        }

        return $this->stages
            ->where('status', ApplicationStageStatus::Pending)
            ->sortBy('position')
            ->first();
    }

    public function references(): HasMany
    {
        return $this->hasMany(ApplicationReference::class);
    }

    public function testSubmission(): HasOne
    {
        return $this->hasOne(TestSubmission::class);
    }

    public function discSubmission(): HasOne
    {
        return $this->hasOne(DiscSubmission::class);
    }

    public function mbtiSubmission(): HasOne
    {
        return $this->hasOne(MbtiSubmission::class);
    }

    public function socialMediaAccounts(): HasMany
    {
        return $this->hasMany(ApplicationSocialMediaAccount::class);
    }

    public function interviewResults(): HasMany
    {
        return $this->hasMany(InterviewResult::class);
    }

    public function offeringLetter(): HasOne
    {
        return $this->hasOne(OfferingLetter::class);
    }

    public function mcuResult(): HasOne
    {
        return $this->hasOne(McuResult::class);
    }

    public function onboardingResult(): HasOne
    {
        return $this->hasOne(OnboardingResult::class);
    }
}
