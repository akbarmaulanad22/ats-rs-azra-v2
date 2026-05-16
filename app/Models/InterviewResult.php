<?php

namespace App\Models;

use Database\Factories\InterviewResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewResult extends Model
{
    /** @use HasFactory<InterviewResultFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'application_stage_id',
        'interviewer_id',
        'keputusan',
        'catatan',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function applicationStage(): BelongsTo
    {
        return $this->belongsTo(ApplicationStage::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(InterviewResultRating::class);
    }
}
