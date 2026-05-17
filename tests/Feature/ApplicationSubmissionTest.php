<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Enums\VacancyStatus;
use App\Mail\TemplatedMail;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\EmailTemplate;
use App\Models\Stage;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    private function createPublishedVacancyWithStages(array $stageKeys = ['lamaran', 'skrining_cv_hr', 'onboarding']): Vacancy
    {
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->published()->create([
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);
    }

    private function validPayload(): array
    {
        Storage::fake('local');

        return [
            // Step 1
            'nama_lengkap' => 'Budi Santoso',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'belum_menikah',
            'golongan_darah' => 'A',
            'alamat_ktp' => 'Jl. Sudirman No. 1, Jakarta',
            'alamat_domisili' => 'Jl. Sudirman No. 1, Jakarta',
            'no_telepon' => '081234567890',
            'email' => 'budi@example.com',
            'no_ktp' => '3174012301900001',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            // Step 3 — required min:1
            'formal_educations' => [
                [
                    'jenis_pendidikan' => 'd4_s1',
                    'nama_sekolah' => 'Universitas Indonesia',
                    'kota' => 'Depok',
                    'tahun_lulus' => 2015,
                    'ip_nilai' => '3.50',
                    'jurusan' => 'Teknik Informatika',
                ],
            ],
            'informal_educations' => [
                [
                    'nama' => 'Pelatihan Laravel',
                    'topik' => 'Web Development',
                    'periode_mulai' => '2022-01-01',
                    'periode_selesai' => '2022-01-14',
                    'penyelenggara' => 'Laracasts',
                ],
            ],
            // Step 5
            'is_fresh_graduate' => '0',
            'work_experiences' => [
                [
                    'nama_perusahaan' => 'PT Contoh Teknologi',
                    'jabatan' => 'Software Engineer',
                    'alamat_perusahaan' => 'Jl. Gatot Subroto No. 10, Jakarta',
                    'periode_mulai' => '2016-01-01',
                    'periode_selesai' => '2020-12-31',
                    'rincian_tugas' => 'Mengembangkan dan memelihara aplikasi web perusahaan.',
                    'gaji_terakhir' => '5000000',
                    'alasan_meninggalkan' => 'Mencari tantangan baru.',
                ],
            ],
            // Step 6
            'alasan_melamar' => 'Tertarik berkarir di bidang kesehatan dan berkontribusi untuk RS Azra.',
            'gaji_diharapkan' => 6000000,
            // Step 8
            'pernah_sakit_serius' => 'tidak',
            'kesiapan_kerja' => 'Siap bekerja segera, saat ini tidak sedang bekerja.',
            'vaksinasi_covid' => 'sudah_2',
            'sumber_informasi' => 'Instagram',
            'pernyataan' => '1',
        ];
    }

    // ── Form page ─────────────────────────────────────────────────────────────

    public function test_application_form_accessible_for_published_vacancy(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->get(route('karier.lamar', $vacancy));

        $response->assertStatus(200);
        $response->assertViewIs('career.apply');
    }

    public function test_application_form_returns_404_for_draft_vacancy(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->create(['status' => VacancyStatus::Draft]);

        $response = $this->get(route('karier.lamar', $vacancy));

        $response->assertStatus(404);
    }

    public function test_application_form_returns_404_for_expired_vacancy(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();
        $vacancy->update(['tenggat_lamaran' => now()->subDay()->format('Y-m-d')]);

        $response = $this->get(route('karier.lamar', $vacancy));

        $response->assertStatus(404);
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_candidate_can_submit_application(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $response->assertRedirect();

        $this->assertDatabaseHas('candidates', [
            'email' => 'budi@example.com',
            'nama_lengkap' => 'Budi Santoso',
        ]);
        $this->assertDatabaseCount('applications', 1);
    }

    public function test_application_stores_cv_file_on_local_disk(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $application = Application::first();
        Storage::disk('local')->assertExists($application->cv_path);
    }

    public function test_application_generates_unique_token(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $application = Application::first();
        $this->assertNotNull($application->token);
        $this->assertNotEmpty($application->token);
    }

    public function test_successful_submission_redirects_to_confirmation_page(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $application = Application::first();
        $response->assertRedirect(route('karier.lamaran.konfirmasi', ['token' => $application->token]));
    }

    public function test_extended_candidate_data_is_stored(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $this->assertDatabaseHas('candidates', [
            'email' => 'budi@example.com',
            'tempat_lahir' => 'Jakarta',
            'jenis_kelamin' => 'laki-laki',
            'no_ktp' => '3174012301900001',
        ]);
        $this->assertDatabaseCount('candidate_formal_educations', 1);
        $this->assertDatabaseCount('candidate_informal_educations', 1);
    }

    public function test_application_interest_data_is_stored(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $this->assertDatabaseHas('applications', [
            'alasan_melamar' => 'Tertarik berkarir di bidang kesehatan dan berkontribusi untuk RS Azra.',
            'gaji_diharapkan' => 6000000,
        ]);
    }

    // ── Pipeline initialization ───────────────────────────────────────────────

    public function test_pipeline_initialized_from_workflow_snapshot(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages(['lamaran', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $application = Application::first();
        $this->assertCount(3, $application->stages);
    }

    public function test_first_stage_is_selesai_on_submission(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages(['lamaran', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $application = Application::first();
        $firstStage = $application->stages->firstWhere('key', 'lamaran');

        $this->assertEquals(ApplicationStageStatus::Selesai, $firstStage->status);
    }

    public function test_second_stage_is_aktif_on_submission(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages(['lamaran', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $application = Application::first();
        $secondStage = $application->stages->firstWhere('key', 'skrining_cv_hr');

        $this->assertEquals(ApplicationStageStatus::Aktif, $secondStage->status);
    }

    public function test_remaining_stages_are_pending_on_submission(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages(['lamaran', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $application = Application::first();
        $lastStage = $application->stages->firstWhere('key', 'onboarding');

        $this->assertEquals(ApplicationStageStatus::Pending, $lastStage->status);
    }

    public function test_stages_preserve_position_ordering(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages(['lamaran', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $application = Application::first();
        $positions = $application->stages->pluck('position')->all();

        $this->assertEquals([1, 2, 3], $positions);
    }

    // ── Candidate deduplication ───────────────────────────────────────────────

    public function test_existing_candidate_matched_by_email(): void
    {
        $this->seedStages();
        $existingCandidate = Candidate::factory()->create(['email' => 'budi@example.com']);
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $this->assertDatabaseCount('candidates', 1);
        $this->assertEquals($existingCandidate->id, Application::first()->candidate_id);
    }

    // ── Multi-vacancy application ─────────────────────────────────────────────

    public function test_candidate_can_apply_to_multiple_vacancies(): void
    {
        $this->seedStages();
        $vacancy1 = $this->createPublishedVacancyWithStages();
        $vacancy2 = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $this->post(route('karier.lamar.store', $vacancy1), $payload);

        $payload2 = $this->validPayload();
        $this->post(route('karier.lamar.store', $vacancy2), $payload2);

        $this->assertDatabaseCount('candidates', 1);
        $this->assertDatabaseCount('applications', 2);
    }

    public function test_duplicate_application_to_same_vacancy_is_rejected(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $response = $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('applications', 1);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_cv_must_be_pdf_doc_or_docx(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['cv'] = UploadedFile::fake()->create('cv.png', 100, 'image/png');

        $response = $this->post(route('karier.lamar.store', $vacancy), $payload);

        $response->assertSessionHasErrors('cv');
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_cv_must_not_exceed_3mb(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['cv'] = UploadedFile::fake()->create('cv.pdf', 3073, 'application/pdf');

        $response = $this->post(route('karier.lamar.store', $vacancy), $payload);

        $response->assertSessionHasErrors('cv');
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->post(route('karier.lamar.store', $vacancy), []);

        $response->assertSessionHasErrors(['nama_lengkap', 'email', 'no_telepon', 'cv', 'tempat_lahir', 'tanggal_lahir', 'formal_educations', 'informal_educations', 'alasan_melamar', 'gaji_diharapkan', 'pernah_sakit_serius', 'kesiapan_kerja', 'vaksinasi_covid', 'sumber_informasi', 'pernyataan']);
    }

    public function test_adjustable_section_partial_fill_requires_all_columns(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['siblings'] = [
            ['nama' => 'Siti', 'usia' => null, 'jenis_kelamin' => null, 'pendidikan_terakhir' => null, 'pekerjaan_jabatan' => null],
        ];

        $response = $this->post(route('karier.lamar.store', $vacancy), $payload);

        $response->assertSessionHasErrors(['siblings.0.usia']);
    }

    public function test_post_to_draft_vacancy_returns_404(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = Vacancy::factory()->create(['status' => VacancyStatus::Draft]);

        $response = $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $response->assertStatus(404);
    }

    public function test_post_to_expired_vacancy_returns_404(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();
        $vacancy->update(['tenggat_lamaran' => now()->subDay()->format('Y-m-d')]);

        $response = $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $response->assertStatus(404);
    }

    // ── Confirmation page ─────────────────────────────────────────────────────

    public function test_confirmation_page_accessible_by_token(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $application = Application::first();

        $response = $this->get(route('karier.lamaran.konfirmasi', ['token' => $application->token]));

        $response->assertStatus(200);
        $response->assertViewIs('career.confirmation');
        $response->assertSee('Budi Santoso');
    }

    public function test_confirmation_page_returns_404_for_invalid_token(): void
    {
        $response = $this->get(route('karier.lamaran.konfirmasi', ['token' => 'invalid-token-xyz']));

        $response->assertStatus(404);
    }

    // ── HR Admin pipeline view ────────────────────────────────────────────────

    public function test_hr_admin_can_view_pipeline(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->actingAs($admin)->get(route('lowongan.pipeline', $vacancy));

        $response->assertStatus(200);
        $response->assertViewIs('vacancies.pipeline');
    }

    public function test_pipeline_view_shows_candidates_grouped_by_stage(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createPublishedVacancyWithStages(['lamaran', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $response = $this->actingAs($admin)->get(route('lowongan.pipeline', $vacancy));

        $response->assertSee('Budi Santoso');
        $response->assertSee('Skrining CV HR');
    }

    public function test_non_hr_admin_cannot_view_pipeline(): void
    {
        $this->seedStages();
        $user = User::factory()->create(['role' => Role::Employee]);
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->actingAs($user)->get(route('lowongan.pipeline', $vacancy));

        $response->assertStatus(403);
    }

    // ── Fresh graduate toggle ─────────────────────────────────────────────────

    public function test_fresh_graduate_does_not_require_work_experiences(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['is_fresh_graduate'] = '1';
        unset($payload['work_experiences']);

        $response = $this->post(route('karier.lamar.store', $vacancy), $payload);

        $response->assertRedirect();
        $this->assertDatabaseCount('applications', 1);
        $this->assertDatabaseHas('candidates', ['is_fresh_graduate' => true]);
    }

    public function test_non_fresh_graduate_requires_work_experiences(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['is_fresh_graduate'] = '0';
        unset($payload['work_experiences']);

        $response = $this->post(route('karier.lamar.store', $vacancy), $payload);

        $response->assertSessionHasErrors('work_experiences');
    }

    // ── Step 8 fields ─────────────────────────────────────────────────────────

    public function test_step8_fields_stored_in_candidate_and_application(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['pernah_sakit_serius'] = 'ya';
        $payload['diagnosis_sakit'] = 'Hipertensi';
        $payload['vaksinasi_covid'] = 'sudah_1';
        $payload['kesiapan_kerja'] = 'Siap 2 minggu setelah pengumuman.';

        $this->post(route('karier.lamar.store', $vacancy), $payload);

        $this->assertDatabaseHas('candidates', [
            'pernah_sakit_serius' => true,
            'diagnosis_sakit' => 'Hipertensi',
            'vaksinasi_covid' => 'sudah_1',
        ]);
        $this->assertDatabaseHas('applications', [
            'kesiapan_kerja' => 'Siap 2 minggu setelah pengumuman.',
        ]);
    }

    public function test_diagnosis_required_when_pernah_sakit_serius_ya(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['pernah_sakit_serius'] = 'ya';
        unset($payload['diagnosis_sakit']);

        $response = $this->post(route('karier.lamar.store', $vacancy), $payload);

        $response->assertSessionHasErrors('diagnosis_sakit');
    }

    public function test_str_sip_file_uploaded_and_stored(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['str_sip'] = UploadedFile::fake()->create('str.pdf', 200, 'application/pdf');

        $this->post(route('karier.lamar.store', $vacancy), $payload);

        $application = Application::first();
        $this->assertNotNull($application->str_sip_path);
        Storage::disk('local')->assertExists($application->str_sip_path);
    }

    // ── Relation storage ─────────────────────────────────────────────────────

    public function test_work_experiences_stored(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $this->assertDatabaseCount('candidate_work_experiences', 1);
        $this->assertDatabaseHas('candidate_work_experiences', [
            'nama_perusahaan' => 'PT Contoh Teknologi',
            'jabatan' => 'Software Engineer',
        ]);
    }

    public function test_organization_experiences_stored(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['organization_experiences'] = [
            [
                'nama_organisasi' => 'BEM Fakultas',
                'jabatan' => 'Ketua',
                'periode_mulai' => '2013-09-01',
                'periode_selesai' => '2014-08-31',
                'keterangan' => 'Memimpin kegiatan kemahasiswaan.',
            ],
        ];

        $this->post(route('karier.lamar.store', $vacancy), $payload);

        $this->assertDatabaseCount('candidate_organization_experiences', 1);
        $this->assertDatabaseHas('candidate_organization_experiences', [
            'nama_organisasi' => 'BEM Fakultas',
        ]);
    }

    public function test_language_skills_stored(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['language_skills'] = [
            [
                'nama_bahasa' => 'Inggris',
                'berbicara' => 'baik',
                'menulis' => 'baik',
                'membaca' => 'sedang',
            ],
        ];

        $this->post(route('karier.lamar.store', $vacancy), $payload);

        $this->assertDatabaseCount('candidate_language_skills', 1);
        $this->assertDatabaseHas('candidate_language_skills', ['nama_bahasa' => 'Inggris']);
    }

    public function test_references_stored(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['references'] = [
            [
                'nama_karyawan' => 'Dr. Sari',
                'hubungan' => 'Atasan langsung',
                'keterangan' => 'Bisa dihubungi kapan saja.',
            ],
        ];

        $this->post(route('karier.lamar.store', $vacancy), $payload);

        $this->assertDatabaseCount('application_references', 1);
        $this->assertDatabaseHas('application_references', ['nama_karyawan' => 'Dr. Sari']);
    }

    public function test_social_media_accounts_stored(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = $this->validPayload();
        $payload['social_media_accounts'] = [
            ['platform' => 'LinkedIn', 'link' => 'https://linkedin.com/in/budi'],
        ];

        $this->post(route('karier.lamar.store', $vacancy), $payload);

        $this->assertDatabaseCount('application_social_media_accounts', 1);
        $this->assertDatabaseHas('application_social_media_accounts', [
            'platform' => 'LinkedIn',
            'link' => 'https://linkedin.com/in/budi',
        ]);
    }

    // ── View smoke tests ──────────────────────────────────────────────────────

    public function test_view_contains_restore_banner_and_form_key_script(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->get(route('karier.lamar', $vacancy));

        $response->assertStatus(200);
        $response->assertSee('ats-restore-banner', false);
        $response->assertSee('__atsFormKey', false);
        $response->assertSee('File CV dan STR/SIP perlu diunggah ulang', false);
    }

    // ── currentStage() edge cases ────────────────────────────────────────────

    public function test_current_stage_returns_failed_stage_when_candidate_fails(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages(['lamaran', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $application = Application::first();
        $application->stages()->where('key', 'skrining_cv_hr')->update(['status' => ApplicationStageStatus::Gagal]);
        $application->load('stages');

        $current = $application->currentStage();

        $this->assertNotNull($current);
        $this->assertEquals('skrining_cv_hr', $current->key);
        $this->assertEquals(ApplicationStageStatus::Gagal, $current->status);
    }

    public function test_current_stage_returns_last_stage_when_all_selesai(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages(['lamaran', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        $application = Application::first();
        $application->stages()->update(['status' => ApplicationStageStatus::Selesai]);
        $application->load('stages');

        $current = $application->currentStage();

        $this->assertNotNull($current);
        $this->assertEquals('onboarding', $current->key);
    }

    // ── Email notification on application received ────────────────────────────

    public function test_confirmation_email_sent_to_candidate_on_application_submission(): void
    {
        Mail::fake();
        $this->seedStages();

        EmailTemplate::unguarded(fn () => EmailTemplate::create([
            'key' => 'lamaran_diterima',
            'deskripsi' => 'Test',
            'subjek' => 'Konfirmasi {judul_lowongan}',
            'isi' => 'Halo {nama_kandidat}, lamaran Anda untuk {judul_lowongan} diterima. Status: {link_status}',
        ]));

        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        Mail::assertQueued(TemplatedMail::class, function (TemplatedMail $mail) {
            return $mail->hasTo('budi@example.com')
                && $mail->key === 'lamaran_diterima'
                && str_contains($mail->body, 'Budi Santoso');
        });
    }

    public function test_no_confirmation_email_when_template_missing(): void
    {
        Mail::fake();
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), $this->validPayload());

        Mail::assertNothingSent();
    }
}
