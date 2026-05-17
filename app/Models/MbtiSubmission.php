<?php

namespace App\Models;

use Database\Factories\MbtiSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MbtiSubmission extends Model
{
    /** @use HasFactory<MbtiSubmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'token',
        'started_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(MbtiAnswer::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(MbtiResult::class);
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }
}
