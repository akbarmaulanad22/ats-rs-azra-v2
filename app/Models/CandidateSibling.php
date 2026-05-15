<?php

namespace App\Models;

use App\Enums\JenisKelamin;
use App\Enums\JenisPendidikan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateSibling extends Model
{
    protected $fillable = [
        'candidate_id',
        'nama',
        'usia',
        'jenis_kelamin',
        'pendidikan_terakhir',
        'pekerjaan_jabatan',
    ];

    protected function casts(): array
    {
        return [
            'jenis_kelamin' => JenisKelamin::class,
            'pendidikan_terakhir' => JenisPendidikan::class,
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
