<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateWorkExperience extends Model
{
    protected $fillable = [
        'candidate_id',
        'nama_perusahaan',
        'jabatan',
        'alamat_perusahaan',
        'periode_mulai',
        'periode_selesai',
        'rincian_tugas',
        'gaji_terakhir',
        'alasan_meninggalkan',
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
