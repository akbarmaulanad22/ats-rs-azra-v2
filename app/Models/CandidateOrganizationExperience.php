<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateOrganizationExperience extends Model
{
    protected $fillable = [
        'candidate_id',
        'nama_organisasi',
        'jabatan',
        'periode_mulai',
        'periode_selesai',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'periode_mulai' => 'date',
            'periode_selesai' => 'date',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
