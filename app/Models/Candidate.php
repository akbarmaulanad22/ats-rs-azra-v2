<?php

namespace App\Models;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use App\Enums\JenisPendidikan;
use App\Enums\StatusPerkawinan;
use Database\Factories\CandidateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    /** @use HasFactory<CandidateFactory> */
    use HasFactory;

    protected $fillable = [
        'nama_lengkap',
        'email',
        'no_telepon',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'status_perkawinan',
        'golongan_darah',
        'alamat_ktp',
        'alamat_domisili',
        'no_ktp',
        'npwp',
        'nama_ibu_kandung',
        'kontak_darurat_nama',
        'kontak_darurat_no_telp',
        'kontak_darurat_hubungan',
        'ayah_nama',
        'ayah_usia',
        'ayah_pendidikan_terakhir',
        'ayah_pekerjaan',
        'ibu_nama',
        'ibu_usia',
        'ibu_pendidikan_terakhir',
        'ibu_pekerjaan',
        'saudara_anak_ke',
        'saudara_dari_bersaudara',
        'is_fresh_graduate',
        'pernah_sakit_serius',
        'diagnosis_sakit',
        'vaksinasi_covid',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'jenis_kelamin' => JenisKelamin::class,
            'status_perkawinan' => StatusPerkawinan::class,
            'golongan_darah' => GolonganDarah::class,
            'ayah_pendidikan_terakhir' => JenisPendidikan::class,
            'ibu_pendidikan_terakhir' => JenisPendidikan::class,
            'is_fresh_graduate' => 'boolean',
            'pernah_sakit_serius' => 'boolean',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(CandidateSibling::class);
    }

    public function spouses(): HasMany
    {
        return $this->hasMany(CandidateSpouse::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(CandidateChild::class);
    }

    public function formalEducations(): HasMany
    {
        return $this->hasMany(CandidateFormalEducation::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(CandidateAchievement::class);
    }

    public function informalEducations(): HasMany
    {
        return $this->hasMany(CandidateInformalEducation::class);
    }

    public function languageSkills(): HasMany
    {
        return $this->hasMany(CandidateLanguageSkill::class);
    }

    public function organizationExperiences(): HasMany
    {
        return $this->hasMany(CandidateOrganizationExperience::class);
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(CandidateWorkExperience::class);
    }
}
