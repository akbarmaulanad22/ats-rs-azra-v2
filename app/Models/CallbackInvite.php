<?php

namespace App\Models;

use Database\Factories\CallbackInviteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallbackInvite extends Model
{
    /** @use HasFactory<CallbackInviteFactory> */
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'candidate_id',
        'invited_by',
        'invited_at',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
        ];
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
