<?php

namespace App\Models;

use App\Enums\TingkatKemampuanBahasa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateLanguageSkill extends Model
{
    protected $fillable = [
        'candidate_id',
        'nama_bahasa',
        'berbicara',
        'menulis',
        'membaca',
    ];

    protected function casts(): array
    {
        return [
            'berbicara' => TingkatKemampuanBahasa::class,
            'menulis' => TingkatKemampuanBahasa::class,
            'membaca' => TingkatKemampuanBahasa::class,
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
