<?php

namespace App\Models;

use App\Enums\JenisPendidikan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateFormalEducation extends Model
{
    protected $table = 'candidate_formal_educations';

    protected $fillable = [
        'candidate_id',
        'jenis_pendidikan',
        'nama_sekolah',
        'kota',
        'tahun_lulus',
        'ip_nilai',
        'jurusan',
    ];

    protected function casts(): array
    {
        return [
            'jenis_pendidikan' => JenisPendidikan::class,
            'tahun_lulus' => 'integer',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
