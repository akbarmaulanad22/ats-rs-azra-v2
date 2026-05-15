<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateAchievement extends Model
{
    protected $fillable = [
        'candidate_id',
        'nama_prestasi',
        'tahun',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
