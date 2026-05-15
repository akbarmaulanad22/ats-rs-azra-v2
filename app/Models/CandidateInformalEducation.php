<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateInformalEducation extends Model
{
    protected $table = 'candidate_informal_educations';

    protected $fillable = [
        'candidate_id',
        'nama',
        'topik',
        'periode_mulai',
        'periode_selesai',
        'penyelenggara',
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
