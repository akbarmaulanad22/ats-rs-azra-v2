<?php

namespace App\Services;

use App\Enums\ApplicationStageStatus;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApplicationService
{
    public function store(Request $request, Vacancy $vacancy): Application
    {
        $cvPath = $request->file('cv')->storeAs(
            'cv',
            Str::random(40).'.pdf',
            'local',
        );

        try {
            return DB::transaction(function () use ($request, $vacancy, $cvPath): Application {
                $candidate = Candidate::updateOrCreate(
                    ['email' => $request->validated('email')],
                    $this->candidateData($request),
                );

                $this->syncCandidateRelations($candidate, $request);

                $application = Application::create([
                    'candidate_id' => $candidate->id,
                    'vacancy_id' => $vacancy->id,
                    'token' => Str::uuid()->toString(),
                    'cv_path' => $cvPath,
                    'alasan_melamar' => $request->validated('alasan_melamar'),
                    'gaji_diharapkan' => $request->validated('gaji_diharapkan'),
                    'fasilitas_diharapkan' => $request->validated('fasilitas_diharapkan'),
                ]);

                $this->createApplicationStages($application, $vacancy);
                $this->createApplicationReferences($application, $request);

                return $application;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($cvPath);
            throw $e;
        }
    }

    /** @return array<string, mixed> */
    private function candidateData(Request $request): array
    {
        return [
            'nama_lengkap' => $request->validated('nama_lengkap'),
            'no_telepon' => $request->validated('no_telepon'),
            'tempat_lahir' => $request->validated('tempat_lahir'),
            'tanggal_lahir' => $request->validated('tanggal_lahir'),
            'jenis_kelamin' => $request->validated('jenis_kelamin'),
            'agama' => $request->validated('agama'),
            'status_perkawinan' => $request->validated('status_perkawinan'),
            'golongan_darah' => $request->validated('golongan_darah'),
            'alamat_ktp' => $request->validated('alamat_ktp'),
            'alamat_domisili' => $request->validated('alamat_domisili'),
            'no_ktp' => $request->validated('no_ktp'),
            'npwp' => $request->validated('npwp'),
            'nama_ibu_kandung' => $request->validated('nama_ibu_kandung'),
            'kontak_darurat_nama' => $request->validated('kontak_darurat_nama'),
            'kontak_darurat_no_telp' => $request->validated('kontak_darurat_no_telp'),
            'kontak_darurat_hubungan' => $request->validated('kontak_darurat_hubungan'),
            'ayah_nama' => $request->validated('ayah_nama'),
            'ayah_usia' => $request->validated('ayah_usia'),
            'ayah_pendidikan_terakhir' => $request->validated('ayah_pendidikan_terakhir'),
            'ayah_pekerjaan' => $request->validated('ayah_pekerjaan'),
            'ibu_nama' => $request->validated('ibu_nama'),
            'ibu_usia' => $request->validated('ibu_usia'),
            'ibu_pendidikan_terakhir' => $request->validated('ibu_pendidikan_terakhir'),
            'ibu_pekerjaan' => $request->validated('ibu_pekerjaan'),
            'saudara_anak_ke' => $request->validated('saudara_anak_ke'),
            'saudara_dari_bersaudara' => $request->validated('saudara_dari_bersaudara'),
            'is_fresh_graduate' => $request->validated('is_fresh_graduate', false),
        ];
    }

    private function syncCandidateRelations(Candidate $candidate, Request $request): void
    {
        $candidate->siblings()->delete();
        foreach ($request->validated('siblings', []) as $row) {
            $candidate->siblings()->create($row);
        }

        $candidate->spouses()->delete();
        foreach ($request->validated('spouses', []) as $row) {
            $candidate->spouses()->create($row);
        }

        $candidate->children()->delete();
        foreach ($request->validated('children', []) as $row) {
            $candidate->children()->create($row);
        }

        $candidate->formalEducations()->delete();
        foreach ($request->validated('formal_educations', []) as $row) {
            $candidate->formalEducations()->create($row);
        }

        $candidate->achievements()->delete();
        foreach ($request->validated('achievements', []) as $row) {
            $candidate->achievements()->create($row);
        }

        $candidate->informalEducations()->delete();
        foreach ($request->validated('informal_educations', []) as $row) {
            $candidate->informalEducations()->create($row);
        }

        $candidate->languageSkills()->delete();
        foreach ($request->validated('language_skills', []) as $row) {
            $candidate->languageSkills()->create($row);
        }

        $candidate->organizationExperiences()->delete();
        foreach ($request->validated('organization_experiences', []) as $row) {
            $candidate->organizationExperiences()->create($row);
        }

        $candidate->workExperiences()->delete();
        foreach ($request->validated('work_experiences', []) as $row) {
            $candidate->workExperiences()->create($row);
        }
    }

    private function createApplicationStages(Application $application, Vacancy $vacancy): void
    {
        $stages = $vacancy->workflowTemplateSnapshot->stages;

        $stagesData = $stages->map(function ($stage, $index) use ($application): array {
            return [
                'application_id' => $application->id,
                'position' => $stage->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $index === 0
                    ? ApplicationStageStatus::Selesai->value
                    : ($index === 1
                        ? ApplicationStageStatus::Aktif->value
                        : ApplicationStageStatus::Pending->value),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        $application->stages()->insert($stagesData);
    }

    private function createApplicationReferences(Application $application, Request $request): void
    {
        foreach ($request->validated('references', []) as $row) {
            $application->references()->create($row);
        }
    }
}
