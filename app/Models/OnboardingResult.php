<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingResult extends Model
{
    protected $fillable = [
        'application_id',
        'tanggal_bergabung',
        'catatan',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bergabung' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
