<?php

namespace Database\Seeders;

use App\Enums\ApplicationStageStatus;
use App\Enums\EmploymentType;
use App\Enums\QuestionType;
use App\Enums\VacancyStatus;
use App\Models\Application;
use App\Models\CallbackInvite;
use App\Models\Candidate;
use App\Models\DiscResult;
use App\Models\DiscSubmission;
use App\Models\JobTemplate;
use App\Models\MbtiResult;
use App\Models\MbtiSubmission;
use App\Models\TestSubmission;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyInterviewTemplate;
use App\Models\VacancyTestSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Adds "filled-state" demo cases on top of DummyCandidateSeeder so the
 * documentation can show the populated variant of each pipeline stage
 * (test completed, interview scheduled with rating form, MCU result form),
 * plus a full callback flow. Idempotent: removes its own marked data first.
 */
class DemoPipelineCaseSeeder extends Seeder
{
    /** Marker emails identify rows this seeder owns (for clean re-runs). */
    private const CASE_EMAILS = [
        'case-teskompetensi@demo.test',
        'case-disc@demo.test',
        'case-mbti@demo.test',
        'case-wuser@demo.test',
        'case-wmanajer@demo.test',
        'case-wdirektur@demo.test',
        'case-mcu@demo.test',
        'callback-a@demo.test',
        'callback-b@demo.test',
        'callback-c@demo.test',
    ];

    public function run(): void
    {
        $vacancy = Vacancy::where('judul_posisi', 'Koordinator Medis (Demo)')->firstOrFail();
        $vacancy->load('workflowTemplateSnapshot.stages');
        $snapshotStages = $vacancy->workflowTemplateSnapshot->stages->sortBy('position')->values();

        $this->cleanup();

        // Callback needs prior Gagal applications under the same JobTemplate.
        $jobTemplate = $vacancy->jobTemplate ?? JobTemplate::firstOrFail();
        if ($vacancy->job_template_id === null) {
            $vacancy->update(['job_template_id' => $jobTemplate->id]);
        }

        $this->assignInterviewTemplates($vacancy, $snapshotStages);
        $this->seedTestCompetencyCase($vacancy, $snapshotStages);
        $this->seedDiscCase($vacancy, $snapshotStages);
        $this->seedMbtiCase($vacancy, $snapshotStages);
        $this->seedInterviewCases($vacancy, $snapshotStages);
        $this->seedMcuCase($vacancy, $snapshotStages);
        $this->seedCallbackFlow($vacancy, $snapshotStages, $jobTemplate->id);
    }

    private function cleanup(): void
    {
        $candidates = Candidate::whereIn('email', self::CASE_EMAILS)->get();

        foreach ($candidates as $candidate) {
            foreach ($candidate->applications()->get() as $application) {
                $application->stages()->delete();
                $application->testSubmission?->answers()->delete();
                $application->testSubmission?->delete();
                $application->discSubmission?->result()->delete();
                $application->discSubmission?->delete();
                $application->mbtiSubmission?->result()->delete();
                $application->mbtiSubmission?->delete();
                $application->delete();
            }
        }

        $candidateIds = $candidates->pluck('id');
        if ($candidateIds->isNotEmpty()) {
            CallbackInvite::whereIn('candidate_id', $candidateIds)->delete();
        }

        Vacancy::where('judul_posisi', 'Koordinator Medis (Periode Lalu)')->each(function (Vacancy $sibling): void {
            Application::where('vacancy_id', $sibling->id)->each(function (Application $a): void {
                $a->stages()->delete();
                $a->delete();
            });
            $sibling->delete();
        });

        Candidate::whereIn('email', self::CASE_EMAILS)->delete();
    }

    private function assignInterviewTemplates(Vacancy $vacancy, Collection $snapshotStages): void
    {
        VacancyInterviewTemplate::where('vacancy_id', $vacancy->id)->delete();

        // kriteria template id per stage + shared kesiapan template (id 4).
        $map = [
            'wawancara_user' => [1, 4],
            'wawancara_manajer_hr' => [2, 4],
            'wawancara_direktur' => [3, 4],
        ];

        foreach ($map as $stageKey => $templateIds) {
            if (! $snapshotStages->contains('key', $stageKey)) {
                continue;
            }

            foreach ($templateIds as $templateId) {
                VacancyInterviewTemplate::create([
                    'vacancy_id' => $vacancy->id,
                    'interview_template_id' => $templateId,
                    'stage_key' => $stageKey,
                ]);
            }
        }
    }

    private function seedTestCompetencyCase(Vacancy $vacancy, Collection $snapshotStages): void
    {
        $candidate = $this->candidate('case-teskompetensi@demo.test', 'Kandidat Tes (Demo)');
        $application = $this->makeApplication($vacancy, $snapshotStages, 'tes_kompetensi', $candidate);

        $snapshot = $this->buildTestSnapshot();
        $submission = TestSubmission::create([
            'application_id' => $application->id,
            'vacancy_test_snapshot_id' => $snapshot->id,
            'token' => Str::uuid()->toString(),
            'started_at' => now()->subMinutes(40),
            'submitted_at' => now()->subMinutes(5),
        ]);

        $total = 0;
        foreach ($snapshot->questions()->with('options')->orderBy('urutan')->get() as $question) {
            if ($question->tipe === QuestionType::Mc) {
                $correct = $question->options->firstWhere('is_correct', true);
                $skor = $correct ? $question->nilai_poin : 0;
                $total += $skor;
                $submission->answers()->create([
                    'vacancy_test_snapshot_question_id' => $question->id,
                    'vacancy_test_snapshot_option_id' => $correct?->id,
                    'skor' => $skor,
                    'is_reviewed' => true,
                ]);
            } else {
                $submission->answers()->create([
                    'vacancy_test_snapshot_question_id' => $question->id,
                    'jawaban_teks' => 'Triase mengelompokkan pasien berdasarkan tingkat kegawatan untuk memastikan kasus terberat ditangani lebih dulu.',
                    'skor' => $question->nilai_poin,
                    'is_reviewed' => true,
                ]);
                $total += $question->nilai_poin;
            }
        }

        $submission->update(['total_skor' => $total]);
    }

    private function seedDiscCase(Vacancy $vacancy, Collection $snapshotStages): void
    {
        $candidate = $this->candidate('case-disc@demo.test', 'Kandidat DiSC (Demo)');
        $application = $this->makeApplication($vacancy, $snapshotStages, 'tes_disc', $candidate);

        $submission = DiscSubmission::create([
            'application_id' => $application->id,
            'token' => Str::uuid()->toString(),
            'started_at' => now()->subMinutes(20),
            'submitted_at' => now()->subMinutes(2),
        ]);

        DiscResult::factory()->create(['disc_submission_id' => $submission->id]);
    }

    private function seedMbtiCase(Vacancy $vacancy, Collection $snapshotStages): void
    {
        $candidate = $this->candidate('case-mbti@demo.test', 'Kandidat MBTI (Demo)');
        $application = $this->makeApplication($vacancy, $snapshotStages, 'tes_mbti', $candidate);

        $submission = MbtiSubmission::create([
            'application_id' => $application->id,
            'token' => Str::uuid()->toString(),
            'started_at' => now()->subMinutes(20),
            'submitted_at' => now()->subMinutes(2),
        ]);

        MbtiResult::factory()->create(['mbti_submission_id' => $submission->id]);
    }

    private function seedInterviewCases(Vacancy $vacancy, Collection $snapshotStages): void
    {
        $interviewer = User::where('username', 'kepala_unit')->first();

        $cases = [
            'wawancara_user' => ['case-wuser@demo.test', 'Kandidat Wawancara User (Demo)'],
            'wawancara_manajer_hr' => ['case-wmanajer@demo.test', 'Kandidat Wawancara Manajer (Demo)'],
            'wawancara_direktur' => ['case-wdirektur@demo.test', 'Kandidat Wawancara Direktur (Demo)'],
        ];

        foreach ($cases as $stageKey => [$email, $name]) {
            if (! $snapshotStages->contains('key', $stageKey)) {
                continue;
            }

            $candidate = $this->candidate($email, $name);
            $application = $this->makeApplication($vacancy, $snapshotStages, $stageKey, $candidate);

            $stage = $application->stages()->where('key', $stageKey)->first();
            $stage->update([
                'jadwal' => now()->addDays(3)->setTime(10, 0),
                'lokasi' => 'Ruang Meeting Lt. 3 / Google Meet',
                'interviewer_id' => $stageKey === 'wawancara_user' ? $interviewer?->id : null,
            ]);
        }
    }

    private function seedMcuCase(Vacancy $vacancy, Collection $snapshotStages): void
    {
        $candidate = $this->candidate('case-mcu@demo.test', 'Kandidat MCU (Demo)');
        $application = $this->makeApplication($vacancy, $snapshotStages, 'mcu', $candidate);

        $stage = $application->stages()->where('key', 'mcu')->first();
        $stage->update([
            'jadwal' => now()->addDays(2)->setTime(8, 0),
            'lokasi' => 'RS Azra Lt. 2 / Laboratorium Klinik',
        ]);
    }

    private function seedCallbackFlow(Vacancy $vacancy, Collection $snapshotStages, int $jobTemplateId): void
    {
        $admin = User::where('username', 'admin')->first();

        $sibling = Vacancy::factory()->create([
            'judul_posisi' => 'Koordinator Medis (Periode Lalu)',
            'unit_id' => $vacancy->unit_id,
            'job_template_id' => $jobTemplateId,
            'workflow_template_snapshot_id' => $vacancy->workflow_template_snapshot_id,
            'jenis_pekerjaan' => EmploymentType::FullTime,
            'deskripsi_pekerjaan' => 'Periode rekrutmen sebelumnya (data demo callback).',
            'kualifikasi' => 'Data demo.',
            'jumlah_posisi' => 3,
            'tenggat_lamaran' => now()->subMonth()->format('Y-m-d'),
            'status' => VacancyStatus::Closed,
        ]);

        $rows = [
            ['callback-a@demo.test', 'Kandidat Callback A (Demo)', false, false],
            ['callback-b@demo.test', 'Kandidat Callback B (Demo)', true, false],
            ['callback-c@demo.test', 'Kandidat Callback C (Demo)', true, true],
        ];

        foreach ($rows as [$email, $name, $invited, $reapplied]) {
            $candidate = $this->candidate($email, $name);

            // Prior Gagal application in the sibling vacancy (failed at a
            // non-screening stage so it shows under the default filter).
            $this->makeApplication($sibling, $snapshotStages, 'wawancara_user', $candidate, ApplicationStageStatus::Gagal);

            if ($invited) {
                $vacancy->callbackInvites()->updateOrCreate(
                    ['candidate_id' => $candidate->id],
                    ['invited_by' => $admin?->id, 'invited_at' => now()],
                );
            }

            if ($reapplied) {
                // Candidate re-applied to the target vacancy after the invite.
                $this->makeApplication($vacancy, $snapshotStages, 'lamaran', $candidate);
            }
        }
    }

    private function candidate(string $email, string $name): Candidate
    {
        return Candidate::factory()->create([
            'email' => $email,
            'nama_lengkap' => $name,
        ]);
    }

    /**
     * Create an Application whose stages are Selesai before the target stage,
     * the given status at the target stage, and Pending after.
     */
    private function makeApplication(
        Vacancy $vacancy,
        Collection $snapshotStages,
        string $targetKey,
        Candidate $candidate,
        ApplicationStageStatus $targetStatus = ApplicationStageStatus::Aktif,
    ): Application {
        $application = Application::create([
            'candidate_id' => $candidate->id,
            'vacancy_id' => $vacancy->id,
            'token' => Str::uuid()->toString(),
            'cv_path' => 'cv/'.Str::random(40).'.pdf',
            'alasan_melamar' => fake()->sentence(),
        ]);

        $targetIndex = $snapshotStages->search(fn ($s) => $s->key === $targetKey);

        $stages = $snapshotStages->map(function ($stage, $index) use ($application, $targetIndex, $targetStatus): array {
            $status = match (true) {
                $index < $targetIndex => ApplicationStageStatus::Selesai->value,
                $index === $targetIndex => $targetStatus->value,
                default => ApplicationStageStatus::Pending->value,
            };

            return [
                'application_id' => $application->id,
                'position' => $stage->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        $application->stages()->insert($stages);

        return $application;
    }

    private function buildTestSnapshot(): VacancyTestSnapshot
    {
        $snapshot = VacancyTestSnapshot::factory()->create(['batas_waktu_menit' => 60]);

        $mc = [
            ['Apa tindakan pertama pada pasien henti jantung?', ['Cek respons dan panggil bantuan', 'Beri makan', 'Pulangkan pasien'], 0],
            ['Berapa rasio kompresi banding ventilasi RJP dewasa?', ['30:2', '5:1', '15:5'], 0],
        ];

        $urutan = 1;
        foreach ($mc as [$pertanyaan, $opsi, $benarIndex]) {
            $question = $snapshot->questions()->create([
                'urutan' => $urutan++,
                'tipe' => QuestionType::Mc->value,
                'pertanyaan' => $pertanyaan,
                'nilai_poin' => 50,
            ]);

            foreach ($opsi as $i => $teks) {
                $question->options()->create(['teks_opsi' => $teks, 'is_correct' => $i === $benarIndex]);
            }
        }

        $snapshot->questions()->create([
            'urutan' => $urutan,
            'tipe' => QuestionType::Essay->value,
            'pertanyaan' => 'Jelaskan prinsip triase di IGD.',
            'nilai_poin' => 20,
        ]);

        return $snapshot;
    }
}
