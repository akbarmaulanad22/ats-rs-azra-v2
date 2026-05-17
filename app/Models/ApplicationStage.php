<?php

namespace App\Models;

use App\Enums\ApplicationStageStatus;
use Database\Factories\ApplicationStageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApplicationStage extends Model
{
    /** @use HasFactory<ApplicationStageFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'position',
        'key',
        'nama',
        'status',
        'catatan',
        'jadwal_interview',
        'lokasi_interview',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStageStatus::class,
            'position' => 'integer',
            'jadwal_interview' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function interviewResult(): HasOne
    {
        return $this->hasOne(InterviewResult::class);
    }
}
