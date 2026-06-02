<?php

namespace App\Http\Requests;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use App\Enums\JenisPendidikan;
use App\Enums\StatusPerkawinan;
use App\Enums\TingkatKemampuanBahasa;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Canonical map of wizard step number to the field-name prefixes that
     * belong to that step. Single source of truth shared by the per-step
     * validation endpoint and the blade error-step jump logic.
     *
     * @return array<int, array<int, string>>
     */
    public static function stepFields(): array
    {
        return [
            1 => ['nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'status_perkawinan', 'golongan_darah', 'alamat_ktp', 'alamat_domisili', 'no_telepon', 'email', 'no_ktp', 'npwp', 'nama_ibu_kandung', 'kontak_darurat_'],
            2 => ['ayah_', 'ibu_', 'saudara_', 'siblings', 'spouses', 'children'],
            3 => ['formal_educations', 'achievements', 'informal_educations', 'language_skills'],
            4 => ['organization_experiences'],
            5 => ['is_fresh_graduate', 'work_experiences'],
            6 => ['alasan_melamar', 'gaji_diharapkan', 'fasilitas_diharapkan'],
            7 => ['references'],
            8 => ['pernah_sakit_serius', 'diagnosis_sakit', 'kesiapan_kerja', 'cv', 'str_sip', 'vaksinasi_covid', 'social_media_accounts', 'sumber_informasi', 'pernyataan'],
        ];
    }

    /**
     * Subset of rules() whose keys belong to a single wizard step.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rulesForStep(int $step): array
    {
        $prefixes = self::stepFields()[$step] ?? [];

        return array_filter(
            $this->rules(),
            function (string $key) use ($prefixes): bool {
                foreach ($prefixes as $prefix) {
                    // Exact scalar key (`email`), array subkey (`siblings.*.nama`),
                    // or an explicit group prefix ending in `_` (`ayah_`, `kontak_darurat_`).
                    // Anchoring avoids `email` falsely matching a future `emailx`.
                    if ($key === $prefix
                        || str_starts_with($key, $prefix.'.')
                        || (str_ends_with($prefix, '_') && str_starts_with($key, $prefix))) {
                        return true;
                    }
                }

                return false;
            },
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $jenisKelaminValues = array_column(JenisKelamin::cases(), 'value');
        $jenisPendidikanValues = array_column(JenisPendidikan::cases(), 'value');
        $tingkatValues = array_column(TingkatKemampuanBahasa::cases(), 'value');

        return [
            // Step 1 — Identitas Diri
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', Rule::enum(JenisKelamin::class)],
            'agama' => ['required', 'string', 'max:50'],
            'status_perkawinan' => ['required', Rule::enum(StatusPerkawinan::class)],
            'golongan_darah' => ['nullable', Rule::enum(GolonganDarah::class)],
            'alamat_ktp' => ['required', 'string', 'max:500'],
            'alamat_domisili' => ['required', 'string', 'max:500'],
            'no_telepon' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'no_ktp' => ['required', 'string', 'size:16'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'nama_ibu_kandung' => ['nullable', 'string', 'max:255'],
            'kontak_darurat_nama' => ['nullable', 'string', 'max:255'],
            'kontak_darurat_no_telp' => ['nullable', 'string', 'max:20'],
            'kontak_darurat_hubungan' => ['nullable', 'string', 'max:100'],

            // Step 2 — Latar Belakang Keluarga
            'ayah_nama' => ['nullable', 'string', 'max:255'],
            'ayah_usia' => ['nullable', 'integer', 'min:1', 'max:150'],
            'ayah_pendidikan_terakhir' => ['nullable', Rule::enum(JenisPendidikan::class)],
            'ayah_pekerjaan' => ['nullable', 'string', 'max:255'],
            'ibu_nama' => ['nullable', 'string', 'max:255'],
            'ibu_usia' => ['nullable', 'integer', 'min:1', 'max:150'],
            'ibu_pendidikan_terakhir' => ['nullable', Rule::enum(JenisPendidikan::class)],
            'ibu_pekerjaan' => ['nullable', 'string', 'max:255'],
            'saudara_anak_ke' => ['nullable', 'integer', 'min:1'],
            'saudara_dari_bersaudara' => ['nullable', 'integer', 'min:1'],

            'siblings' => ['nullable', 'array'],
            'siblings.*.nama' => ['required_with:siblings.*.usia,siblings.*.jenis_kelamin', 'string', 'max:255'],
            'siblings.*.usia' => ['required_with:siblings.*.nama', 'integer', 'min:0', 'max:150'],
            'siblings.*.jenis_kelamin' => ['required_with:siblings.*.nama', Rule::in($jenisKelaminValues)],
            'siblings.*.pendidikan_terakhir' => ['required_with:siblings.*.nama', Rule::in($jenisPendidikanValues)],
            'siblings.*.pekerjaan_jabatan' => ['required_with:siblings.*.nama', 'string', 'max:255'],

            'spouses' => ['nullable', 'array'],
            'spouses.*.nama' => ['required_with:spouses.*.usia,spouses.*.jenis_kelamin', 'string', 'max:255'],
            'spouses.*.usia' => ['required_with:spouses.*.nama', 'integer', 'min:0', 'max:150'],
            'spouses.*.jenis_kelamin' => ['required_with:spouses.*.nama', Rule::in($jenisKelaminValues)],
            'spouses.*.pendidikan_terakhir' => ['required_with:spouses.*.nama', Rule::in($jenisPendidikanValues)],
            'spouses.*.pekerjaan_jabatan' => ['required_with:spouses.*.nama', 'string', 'max:255'],

            'children' => ['nullable', 'array'],
            'children.*.nama' => ['required_with:children.*.usia,children.*.jenis_kelamin', 'string', 'max:255'],
            'children.*.usia' => ['required_with:children.*.nama', 'integer', 'min:0', 'max:150'],
            'children.*.jenis_kelamin' => ['required_with:children.*.nama', Rule::in($jenisKelaminValues)],
            'children.*.pendidikan_terakhir' => ['required_with:children.*.nama', Rule::in($jenisPendidikanValues)],
            'children.*.pekerjaan_jabatan' => ['required_with:children.*.nama', 'string', 'max:255'],

            // Step 3 — Pendidikan
            'formal_educations' => ['required', 'array', 'min:1'],
            'formal_educations.*.jenis_pendidikan' => ['required', Rule::in($jenisPendidikanValues)],
            'formal_educations.*.nama_sekolah' => ['required', 'string', 'max:255'],
            'formal_educations.*.kota' => ['required', 'string', 'max:100'],
            'formal_educations.*.tahun_lulus' => ['required', 'integer', 'min:1900', 'max:2100'],
            'formal_educations.*.ip_nilai' => ['nullable', 'string', 'max:20'],
            'formal_educations.*.jurusan' => ['nullable', 'string', 'max:255'],

            'achievements' => ['nullable', 'array'],
            'achievements.*.nama_prestasi' => ['required_with:achievements.*.tahun', 'string', 'max:255'],
            'achievements.*.tahun' => ['required_with:achievements.*.nama_prestasi', 'integer', 'min:1900', 'max:2100'],

            'informal_educations' => ['required', 'array', 'min:1'],
            'informal_educations.*.nama' => ['required', 'string', 'max:255'],
            'informal_educations.*.topik' => ['required', 'string', 'max:255'],
            'informal_educations.*.periode_mulai' => ['required', 'date'],
            'informal_educations.*.periode_selesai' => ['required', 'date', 'after_or_equal:informal_educations.*.periode_mulai'],
            'informal_educations.*.penyelenggara' => ['required', 'string', 'max:255'],

            'language_skills' => ['nullable', 'array'],
            'language_skills.*.nama_bahasa' => ['required_with:language_skills.*.berbicara', 'string', 'max:100'],
            'language_skills.*.berbicara' => ['required_with:language_skills.*.nama_bahasa', Rule::in($tingkatValues)],
            'language_skills.*.menulis' => ['required_with:language_skills.*.nama_bahasa', Rule::in($tingkatValues)],
            'language_skills.*.membaca' => ['required_with:language_skills.*.nama_bahasa', Rule::in($tingkatValues)],

            // Step 4 — Organisasi
            'organization_experiences' => ['nullable', 'array'],
            'organization_experiences.*.nama_organisasi' => ['required_with:organization_experiences.*.jabatan', 'string', 'max:255'],
            'organization_experiences.*.jabatan' => ['required_with:organization_experiences.*.nama_organisasi', 'string', 'max:255'],
            'organization_experiences.*.periode_mulai' => ['required_with:organization_experiences.*.nama_organisasi', 'date'],
            'organization_experiences.*.periode_selesai' => ['nullable', 'date', 'after_or_equal:organization_experiences.*.periode_mulai'],
            'organization_experiences.*.keterangan' => ['nullable', 'string', 'max:1000'],

            // Step 5 — Pengalaman Kerja
            'is_fresh_graduate' => ['required', 'boolean'],
            'work_experiences' => array_filter([$this->boolean('is_fresh_graduate') ? 'nullable' : 'required', 'array', $this->boolean('is_fresh_graduate') ? null : 'min:1']),
            'work_experiences.*.nama_perusahaan' => ['required_with:work_experiences.*.jabatan', 'string', 'max:255'],
            'work_experiences.*.jabatan' => ['required_with:work_experiences.*.nama_perusahaan', 'string', 'max:255'],
            'work_experiences.*.alamat_perusahaan' => ['required_with:work_experiences.*.nama_perusahaan', 'string', 'max:500'],
            'work_experiences.*.periode_mulai' => ['required_with:work_experiences.*.nama_perusahaan', 'date'],
            'work_experiences.*.periode_selesai' => ['nullable', 'date', 'after_or_equal:work_experiences.*.periode_mulai'],
            'work_experiences.*.rincian_tugas' => ['required_with:work_experiences.*.nama_perusahaan', 'string', 'max:2000'],
            'work_experiences.*.gaji_terakhir' => ['nullable', 'string', 'max:50'],
            'work_experiences.*.alasan_meninggalkan' => ['nullable', 'string', 'max:1000'],

            // Step 6 — Minat
            'alasan_melamar' => ['required', 'string', 'max:2000'],
            'gaji_diharapkan' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'fasilitas_diharapkan' => ['nullable', 'string', 'max:1000'],

            // Step 7 — Referensi
            'references' => ['nullable', 'array'],
            'references.*.nama_karyawan' => ['required_with:references.*.hubungan', 'string', 'max:255'],
            'references.*.hubungan' => ['required_with:references.*.nama_karyawan', 'string', 'max:100'],
            'references.*.keterangan' => ['nullable', 'string', 'max:1000'],

            // Step 8 — Lain-Lain
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:3072'],
            'pernah_sakit_serius' => ['required', Rule::in(['ya', 'tidak'])],
            'diagnosis_sakit' => ['nullable', 'required_if:pernah_sakit_serius,ya', 'string', 'max:2000'],
            'kesiapan_kerja' => ['required', 'string', 'max:2000'],
            'str_sip' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:3072'],
            'vaksinasi_covid' => ['required', Rule::in(['sudah_1', 'sudah_2', 'belum'])],
            'social_media_accounts' => ['nullable', 'array'],
            'social_media_accounts.*.platform' => ['required_with:social_media_accounts.*.link', 'string', Rule::in(['Facebook', 'Instagram', 'LinkedIn', 'TikTok', 'Twitter/X', 'Lainnya'])],
            'social_media_accounts.*.link' => ['required_with:social_media_accounts.*.platform', 'string', 'max:500'],
            'sumber_informasi' => ['required', 'string', 'max:100'],
            'pernyataan' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        $attributes = [
            'nama_lengkap' => 'nama lengkap',
            'tempat_lahir' => 'tempat lahir',
            'tanggal_lahir' => 'tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
            'agama' => 'agama',
            'status_perkawinan' => 'status perkawinan',
            'golongan_darah' => 'golongan darah',
            'alamat_ktp' => 'alamat KTP',
            'alamat_domisili' => 'alamat domisili',
            'no_telepon' => 'nomor telepon',
            'email' => 'email',
            'no_ktp' => 'nomor KTP',
            'npwp' => 'NPWP',
            'cv' => 'CV/resume',
            'pernah_sakit_serius' => 'riwayat penyakit serius',
            'diagnosis_sakit' => 'diagnosis penyakit',
            'kesiapan_kerja' => 'kesiapan kerja',
            'str_sip' => 'STR/SIP/STRA/STRTTK',
            'vaksinasi_covid' => 'vaksinasi Covid-19',
            'sumber_informasi' => 'sumber informasi',
            'pernyataan' => 'pernyataan',
            'formal_educations' => 'pendidikan formal',
            'informal_educations' => 'pendidikan informal',
            'work_experiences' => 'pengalaman kerja',
            'alasan_melamar' => 'alasan melamar',
            'gaji_diharapkan' => 'gaji yang diharapkan',
        ];

        $arrayFields = [
            'formal_educations' => [
                'jenis_pendidikan' => 'jenis pendidikan',
                'nama_sekolah' => 'nama sekolah/institusi',
                'kota' => 'kota',
                'tahun_lulus' => 'tahun lulus',
                'ip_nilai' => 'IP/nilai',
                'jurusan' => 'jurusan',
            ],
            'informal_educations' => [
                'nama' => 'nama pendidikan',
                'topik' => 'topik',
                'periode_mulai' => 'periode mulai',
                'periode_selesai' => 'periode selesai',
                'penyelenggara' => 'penyelenggara',
            ],
            'achievements' => [
                'nama_prestasi' => 'nama prestasi',
                'tahun' => 'tahun',
            ],
            'language_skills' => [
                'nama_bahasa' => 'nama bahasa',
                'berbicara' => 'berbicara',
                'menulis' => 'menulis',
                'membaca' => 'membaca',
            ],
            'siblings' => [
                'nama' => 'nama',
                'usia' => 'usia',
                'jenis_kelamin' => 'jenis kelamin',
                'pendidikan_terakhir' => 'pendidikan terakhir',
                'pekerjaan_jabatan' => 'pekerjaan/jabatan',
            ],
            'spouses' => [
                'nama' => 'nama',
                'usia' => 'usia',
                'jenis_kelamin' => 'jenis kelamin',
                'pendidikan_terakhir' => 'pendidikan terakhir',
                'pekerjaan_jabatan' => 'pekerjaan/jabatan',
            ],
            'children' => [
                'nama' => 'nama',
                'usia' => 'usia',
                'jenis_kelamin' => 'jenis kelamin',
                'pendidikan_terakhir' => 'pendidikan terakhir',
                'pekerjaan_jabatan' => 'pekerjaan/jabatan',
            ],
            'organization_experiences' => [
                'nama_organisasi' => 'nama organisasi',
                'jabatan' => 'jabatan',
                'periode_mulai' => 'periode mulai',
                'periode_selesai' => 'periode selesai',
                'keterangan' => 'keterangan',
            ],
            'work_experiences' => [
                'nama_perusahaan' => 'nama perusahaan',
                'jabatan' => 'jabatan',
                'alamat_perusahaan' => 'alamat perusahaan',
                'periode_mulai' => 'periode mulai',
                'periode_selesai' => 'periode selesai',
                'gaji_terakhir' => 'gaji terakhir',
                'rincian_tugas' => 'rincian tugas',
                'alasan_meninggalkan' => 'alasan meninggalkan',
            ],
            'references' => [
                'nama_karyawan' => 'nama karyawan',
                'hubungan' => 'hubungan',
                'keterangan' => 'keterangan',
            ],
            'social_media_accounts' => [
                'platform' => 'platform',
                'link' => 'link/username',
            ],
        ];

        foreach ($arrayFields as $prefix => $fields) {
            $items = $this->input($prefix, []);
            if (! is_array($items)) {
                continue;
            }
            foreach (array_keys($items) as $index) {
                foreach ($fields as $field => $label) {
                    $attributes["{$prefix}.{$index}.{$field}"] = $label;
                }
            }
        }

        return $attributes;
    }
}
