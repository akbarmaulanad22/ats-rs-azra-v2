<?php

namespace App\Models;

use Database\Factories\TestSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSubmission extends Model
{
    /** @use HasFactory<TestSubmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'vacancy_test_id',
        'token',
        'started_at',
        'submitted_at',
        'total_skor',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'total_skor' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function vacancyTest(): BelongsTo
    {
        return $this->belongsTo(VacancyTest::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TestAnswer::class);
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function isExpired(): bool
    {
        if ($this->started_at === null) {
            return false;
        }

        return $this->started_at->diffInMinutes(now()) >= $this->vacancyTest->batas_waktu_menit;
    }

    public function remainingSeconds(): int
    {
        if ($this->started_at === null) {
            return $this->vacancyTest->batas_waktu_menit * 60;
        }

        $elapsed = $this->started_at->diffInSeconds(now());
        $total = $this->vacancyTest->batas_waktu_menit * 60;

        return max(0, $total - (int) $elapsed);
    }
}
